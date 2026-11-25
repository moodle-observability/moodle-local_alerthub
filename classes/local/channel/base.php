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

namespace local_alerthub\local\channel;

use local_alerthub\api;

/**
 * Base of the channels (subplugins alerthubchannel): each one sends alerts and digests to one kind of destination.
 *
 * A subplugin alerthubchannel_<name> implements the class \alerthubchannel_<name>\channel.
 *
 * @package    local_alerthub
 * @copyright  2026 Moodle Observability contributors
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
abstract class base {
    /**
     * Constructor.
     *
     * @param \stdClass $instance Row of local_alerthub_channel.
     * @param array $config Decrypted configuration.
     */
    public function __construct(
        /** @var \stdClass Row of local_alerthub_channel. */
        protected readonly \stdClass $instance,
        /** @var array Decrypted configuration. */
        protected readonly array $config,
    ) {
    }

    /**
     * Sends an alert that was raised or resolved.
     *
     * @param \stdClass $alert Row of local_alerthub_alert.
     * @param string $kind api::RAISED or api::RESOLVED.
     * @return result
     */
    abstract public function send_alert(\stdClass $alert, string $kind): result;

    /**
     * Sends a digest.
     *
     * @param \stdClass[] $alerts Alerts of the digest.
     * @param \stdClass|null $rule Rule of the digest.
     * @return result
     */
    abstract public function send_digest(array $alerts, ?\stdClass $rule): result;

    /**
     * Adds the settings of the channel to the channel form.
     *
     * @param \MoodleQuickForm $mform Form.
     * @return void
     */
    public static function config_form(\MoodleQuickForm $mform): void {
    }

    /**
     * Names of the form fields stored in the configuration.
     *
     * @return string[]
     */
    public static function config_fields(): array {
        return [];
    }

    /**
     * Validates the settings of the channel.
     *
     * @param array $data Form data.
     * @return array Errors by field.
     */
    public static function validate_config(array $data): array {
        return [];
    }

    /**
     * Sends a test alert.
     *
     * @return result
     */
    public function test(): result {
        $alert = (object) [
            'id' => 0,
            'source' => 'local_alerthub',
            'dedupkey' => 'test',
            'severity' => api::INFO,
            'category' => 'test',
            'title' => get_string('testtitle', 'local_alerthub'),
            'body' => get_string('testbody', 'local_alerthub', format_string($this->instance->name)),
            'url' => (new \moodle_url('/local/alerthub/index.php'))->out(false),
            'contextid' => null,
            'payload' => null,
            'state' => api::RAISED,
            'occurrences' => 1,
            'timefirst' => time(),
            'timelast' => time(),
            'timeresolved' => 0,
        ];
        return $this->send_alert($alert, api::RAISED);
    }

    /**
     * Subject of an alert.
     *
     * @param \stdClass $alert Alert.
     * @param string $kind api::RAISED or api::RESOLVED.
     * @return string
     */
    public static function subject(\stdClass $alert, string $kind): string {
        $a = (object) ['severity' => get_string('severity_' . $alert->severity, 'local_alerthub'), 'title' => $alert->title];
        return get_string($kind === api::RESOLVED ? 'subjectresolved' : 'subjectraised', 'local_alerthub', $a);
    }

    /**
     * Plain text of an alert.
     *
     * @param \stdClass $alert Alert.
     * @param string $kind api::RAISED or api::RESOLVED.
     * @return string
     */
    public static function text(\stdClass $alert, string $kind): string {
        $lines = [self::subject($alert, $kind)];
        if ((string) $alert->body !== '') {
            $lines[] = '';
            $lines[] = (string) $alert->body;
        }
        if ((int) $alert->occurrences > 1) {
            $lines[] = get_string('repeated', 'local_alerthub', (int) $alert->occurrences);
        }
        if ((string) $alert->url !== '') {
            $lines[] = '';
            $lines[] = (string) $alert->url;
        }
        return implode("\n", $lines);
    }

    /**
     * Plain text of a digest.
     *
     * @param \stdClass[] $alerts Alerts.
     * @return string
     */
    public static function digest_text(array $alerts): string {
        $lines = [get_string('digestintro', 'local_alerthub', count($alerts)), ''];
        foreach ($alerts as $alert) {
            $state = get_string('state_' . $alert->state, 'local_alerthub');
            $line = '- [' . get_string('severity_' . $alert->severity, 'local_alerthub') . '] ' . $alert->title
                . ' (' . $state . ')';
            if ((string) $alert->url !== '') {
                $line .= ' ' . $alert->url;
            }
            $lines[] = $line;
        }
        return implode("\n", $lines);
    }
}
