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
 * Builds the daily and weekly digests of the rules whose time has come.
 *
 * @package    local_alerthub
 * @copyright  2026 Moodle Observability contributors
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class digests {
    /**
     * Whether the digest of a rule is due at a given time (server timezone).
     *
     * @param \stdClass $rule Rule.
     * @param int $now Time.
     * @return bool
     */
    public static function is_due(\stdClass $rule, int $now): bool {
        if (!in_array($rule->mode, [router::DAILY, router::WEEKLY], true)) {
            return false;
        }
        $date = usergetdate($now, \core_date::get_server_timezone());
        if ((int) $date['hours'] < (int) $rule->digesthour) {
            return false;
        }
        if ($rule->mode === router::WEEKLY && (int) $date['wday'] !== (int) $rule->digestday) {
            return false;
        }
        // Once per day (or week): not again if it was already built today.
        $today = make_timestamp($date['year'], $date['mon'], $date['mday'], 0, 0, 0, \core_date::get_server_timezone());
        return (int) $rule->lastdigest < $today;
    }

    /**
     * Alerts of a digest: active ones and those that changed since the previous digest.
     *
     * @param \stdClass $rule Rule.
     * @param int $since Time of the previous digest, or 0.
     * @return int[] Alert ids.
     */
    public static function alerts(\stdClass $rule, int $since): array {
        global $DB;
        $ids = [];
        $select = 'state = :raised OR timelast > :since1 OR timeresolved > :since2';
        $params = ['raised' => api::RAISED, 'since1' => $since, 'since2' => $since];
        foreach ($DB->get_recordset_select('local_alerthub_alert', $select, $params, 'id') as $alert) {
            if (router::matches($rule, $alert)) {
                $ids[] = (int) $alert->id;
            }
        }
        return $ids;
    }

    /**
     * Creates the digest deliveries that are due.
     *
     * @param int|null $now Time.
     * @return int Digests created.
     */
    public static function run(?int $now = null): int {
        global $DB;
        $now = $now ?? time();
        $sql = "SELECT r.*
                  FROM {local_alerthub_rule} r
                  JOIN {local_alerthub_channel} c ON c.id = r.channelid
                 WHERE r.enabled = 1 AND c.enabled = 1 AND r.mode <> :immediate";
        $created = 0;
        foreach ($DB->get_records_sql($sql, ['immediate' => router::IMMEDIATE]) as $rule) {
            if (!self::is_due($rule, $now)) {
                continue;
            }
            $ids = self::alerts($rule, (int) $rule->lastdigest);
            if ($ids) {
                $DB->insert_record('local_alerthub_delivery', (object) [
                    'alertid' => null,
                    'ruleid' => $rule->id,
                    'channelid' => $rule->channelid,
                    'kind' => 'digest',
                    'items' => json_encode($ids),
                    'status' => dispatcher::PENDING,
                    'attempts' => 0,
                    'nextattempt' => $now,
                    'timecreated' => $now,
                ]);
                $created++;
            }
            $DB->set_field('local_alerthub_rule', 'lastdigest', $now, ['id' => $rule->id]);
        }
        return $created;
    }
}
