<?php
// This file is part of Moodle - https://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <https://www.gnu.org/licenses/>.

namespace alerthubchannel_message;

use local_alerthub\api;
use local_alerthub\local\channel\base;
use local_alerthub\local\channel\result;

/**
 * Delivers through the Message API (message_send): the processors enabled for the providers of local_alerthub
 * (web notification, e-mail, Telegram, Slack) decide how each recipient receives it.
 *
 * Recipients are the users with local/alerthub:receive in the context of the alert (for example the editors of a
 * course), or a fixed list of users, such as service users whose Telegram chat is the team group.
 *
 * @package    alerthubchannel_message
 * @copyright  2026 Moodle Observability contributors
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class channel extends base {
    /** @var string Users with the capability in the context of the alert. */
    public const BY_CAPABILITY = 'capability';

    /** @var string A fixed list of users. */
    public const BY_USERS = 'users';

    /**
     * Adds the settings to the channel form.
     *
     * @param \MoodleQuickForm $mform Form.
     * @return void
     */
    public static function config_form(\MoodleQuickForm $mform): void {
        $mform->addElement('select', 'recipients', get_string('recipients', 'alerthubchannel_message'), [
            self::BY_CAPABILITY => get_string('bycapability', 'alerthubchannel_message'),
            self::BY_USERS => get_string('byusers', 'alerthubchannel_message'),
        ]);
        $mform->setType('recipients', PARAM_ALPHA);
        $mform->addHelpButton('recipients', 'recipients', 'alerthubchannel_message');

        $mform->addElement('autocomplete', 'userids', get_string('users'), [], [
            'multiple' => true,
            'ajax' => 'core_user/form_user_selector',
        ]);
        $mform->setType('userids', PARAM_INT);
        $mform->hideIf('userids', 'recipients', 'neq', self::BY_USERS);
    }

    /**
     * Fields stored in the configuration.
     *
     * @return string[]
     */
    public static function config_fields(): array {
        return ['recipients', 'userids'];
    }

    /**
     * A list of users needs at least one user.
     *
     * @param array $data Form data.
     * @return array
     */
    public static function validate_config(array $data): array {
        if (($data['recipients'] ?? '') === self::BY_USERS && empty($data['userids'])) {
            return ['userids' => get_string('required')];
        }
        return [];
    }

    /**
     * Recipients of an alert or digest.
     *
     * @param int|null $contextid Context of the alert, or null for the site.
     * @return \stdClass[]
     */
    public function recipients(?int $contextid): array {
        global $DB;
        if (($this->config['recipients'] ?? self::BY_CAPABILITY) === self::BY_USERS) {
            $ids = array_filter(array_map('intval', (array) ($this->config['userids'] ?? [])));
            if (!$ids) {
                return [];
            }
            [$in, $params] = $DB->get_in_or_equal($ids);
            return $DB->get_records_select('user', "id {$in} AND deleted = 0 AND suspended = 0", $params);
        }
        $context = $contextid ? \context::instance_by_id($contextid, IGNORE_MISSING) : null;
        $context = $context ?: \context_system::instance();
        return get_users_by_capability($context, 'local/alerthub:receive', 'u.*', '', '', '', '', '', true, true);
    }

    /**
     * Sends an alert.
     *
     * @param \stdClass $alert Alert.
     * @param string $kind api::RAISED or api::RESOLVED.
     * @return result
     */
    public function send_alert(\stdClass $alert, string $kind): result {
        $recipients = $this->recipients($alert->contextid ? (int) $alert->contextid : null);
        if (!$recipients) {
            return result::failure(get_string('norecipients', 'alerthubchannel_message'), false);
        }
        $text = self::text($alert, $kind);
        return $this->send(
            $recipients,
            'alert_' . $alert->severity,
            self::subject($alert, $kind),
            $text,
            (string) $alert->url,
            $alert->contextid ? (int) $alert->contextid : null
        );
    }

    /**
     * Sends a digest.
     *
     * @param \stdClass[] $alerts Alerts.
     * @param \stdClass|null $rule Rule.
     * @return result
     */
    public function send_digest(array $alerts, ?\stdClass $rule): result {
        $recipients = $this->recipients(null);
        if (!$recipients) {
            return result::failure(get_string('norecipients', 'alerthubchannel_message'), false);
        }
        $subject = get_string('digestsubject', 'local_alerthub', count($alerts));
        $url = (new \moodle_url('/local/alerthub/alerts.php'))->out(false);
        return $this->send($recipients, 'digest', $subject, self::digest_text($alerts), $url, null);
    }

    /**
     * Sends one message to each recipient.
     *
     * @param \stdClass[] $recipients Users.
     * @param string $provider Message provider of local_alerthub.
     * @param string $subject Subject.
     * @param string $text Plain text.
     * @param string $url Link.
     * @param int|null $contextid Context.
     * @return result
     */
    private function send(
        array $recipients,
        string $provider,
        string $subject,
        string $text,
        string $url,
        ?int $contextid
    ): result {
        $sent = 0;
        $errors = [];
        foreach ($recipients as $user) {
            $message = new \core\message\message();
            $message->component = 'local_alerthub';
            $message->name = $provider;
            $message->userfrom = \core_user::get_noreply_user();
            $message->userto = $user;
            $message->subject = $subject;
            $message->fullmessage = $text;
            $message->fullmessageformat = FORMAT_PLAIN;
            $message->fullmessagehtml = text_to_html(s($text), false, false, true);
            $message->smallmessage = $subject;
            $message->notification = 1;
            if ($url !== '') {
                $message->contexturl = $url;
                $message->contexturlname = get_string('open', 'local_alerthub');
            }
            if ($contextid) {
                $context = \context::instance_by_id($contextid, IGNORE_MISSING);
                if ($context) {
                    $message->contextid = $context->id;
                    if ($context->contextlevel == CONTEXT_COURSE) {
                        $message->courseid = $context->instanceid;
                    }
                }
            }
            try {
                if (message_send($message)) {
                    $sent++;
                } else {
                    $errors[] = 'user ' . $user->id;
                }
            } catch (\Throwable $e) {
                $errors[] = 'user ' . $user->id . ': ' . $e->getMessage();
            }
        }
        if ($sent === 0) {
            return result::failure('message_send failed: ' . implode('; ', $errors));
        }
        return result::success($sent);
    }
}
