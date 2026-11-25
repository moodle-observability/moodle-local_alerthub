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

namespace local_alerthub\task;

use local_alerthub\api;
use local_alerthub\local\dispatcher;

/**
 * Removes old deliveries and alerts resolved long ago.
 *
 * @package    local_alerthub
 * @copyright  2026 Moodle Observability contributors
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class cleanup extends \core\task\scheduled_task {
    /**
     * Task name.
     *
     * @return string
     */
    public function get_name() {
        return get_string('task_cleanup', 'local_alerthub');
    }

    /**
     * Removes what is older than the retention.
     *
     * @return void
     */
    public function execute() {
        global $DB;
        $days = (int) get_config('local_alerthub', 'retention') ?: 90;
        $before = time() - $days * DAYSECS;
        $DB->delete_records_select(
            'local_alerthub_delivery',
            'status <> :pending AND timecreated < :before',
            ['pending' => dispatcher::PENDING, 'before' => $before]
        );
        $DB->delete_records_select(
            'local_alerthub_alert',
            'state = :resolved AND timeresolved < :before
             AND id NOT IN (SELECT d.alertid FROM {local_alerthub_delivery} d WHERE d.alertid IS NOT NULL)',
            ['resolved' => api::RESOLVED, 'before' => $before]
        );
    }
}
