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

namespace local_alerthub;

/**
 * Hook callbacks of local_alerthub.
 *
 * @package    local_alerthub
 * @copyright  2026 Moodle Observability contributors
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class hook_callbacks {
    /**
     * Brings the alerts of tool_taskmonitor (cron and tasks) to the hub, so that they follow its rules and channels.
     *
     * @param \tool_taskmonitor\hook\alert_raised $hook Hook.
     * @return void
     */
    public static function taskmonitor_alert($hook): void {
        global $CFG;
        if (during_initial_install() || !empty($CFG->upgraderunning)) {
            return;
        }
        $alert = $hook->get_alert();
        $key = (string) ($alert['id'] ?? '');
        if ($key === '') {
            return;
        }
        if ($hook->get_state() === 'resolved') {
            api::resolve('tool_taskmonitor', $key);
            return;
        }
        $severity = ($alert['severity'] ?? '') === 'critical' ? api::CRITICAL : api::WARNING;
        api::raise([
            'source' => 'tool_taskmonitor',
            'dedupkey' => $key,
            'severity' => $severity,
            'category' => clean_param((string) ($alert['type'] ?? 'task'), PARAM_ALPHANUMEXT),
            'title' => (string) ($alert['title'] ?? $key),
            'body' => (string) ($alert['details'] ?? ''),
            'url' => (new \moodle_url('/admin/tool/task/scheduledtasks.php'))->out(false),
        ]);
    }
}
