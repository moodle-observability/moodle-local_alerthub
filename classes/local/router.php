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

namespace local_alerthub\local;

use local_alerthub\api;

/**
 * Matches alerts against the rules and creates the deliveries of the immediate ones.
 *
 * Deliveries are sent by the scheduled task dispatch; nothing is sent during the request that raised the alert.
 *
 * @package    local_alerthub
 * @copyright  2026 Moodle Observability contributors
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class router {
    /** @var string Sent as soon as the alert changes. */
    public const IMMEDIATE = 'immediate';

    /** @var string Sent once a day. */
    public const DAILY = 'daily';

    /** @var string Sent once a week. */
    public const WEEKLY = 'weekly';

    /**
     * Rule modes.
     *
     * @return string[]
     */
    public static function modes(): array {
        return [self::IMMEDIATE, self::DAILY, self::WEEKLY];
    }

    /**
     * Whether a rule applies to an alert.
     *
     * @param \stdClass $rule Rule.
     * @param \stdClass $alert Alert.
     * @return bool
     */
    public static function matches(\stdClass $rule, \stdClass $alert): bool {
        if ($rule->source !== '' && $rule->source !== $alert->source) {
            return false;
        }
        if ($rule->category !== '' && $rule->category !== $alert->category) {
            return false;
        }
        $levels = array_flip(api::severities());
        return ($levels[$alert->severity] ?? 0) >= ($levels[$rule->minseverity] ?? 0);
    }

    /**
     * Creates the deliveries of an alert that was raised or resolved.
     *
     * @param \stdClass $alert Alert.
     * @param string $kind api::RAISED or api::RESOLVED.
     * @return int Deliveries created.
     */
    public static function route(\stdClass $alert, string $kind): int {
        global $DB;
        $sql = "SELECT r.*
                  FROM {local_alerthub_rule} r
                  JOIN {local_alerthub_channel} c ON c.id = r.channelid
                 WHERE r.enabled = 1 AND c.enabled = 1 AND r.mode = :mode";
        $created = 0;
        $now = time();
        foreach ($DB->get_records_sql($sql, ['mode' => self::IMMEDIATE]) as $rule) {
            if (!self::matches($rule, $alert)) {
                continue;
            }
            if ($kind === api::RESOLVED && !$rule->notifyresolved) {
                continue;
            }
            $DB->insert_record('local_alerthub_delivery', (object) [
                'alertid' => $alert->id,
                'ruleid' => $rule->id,
                'channelid' => $rule->channelid,
                'kind' => $kind,
                'status' => dispatcher::PENDING,
                'attempts' => 0,
                'nextattempt' => $now,
                'timecreated' => $now,
            ]);
            $created++;
        }
        return $created;
    }
}
