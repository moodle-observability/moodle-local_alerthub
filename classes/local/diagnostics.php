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
 * What the administrator needs to see to trust the hub: processors per provider, e-mail blocked, failures.
 *
 * @package    local_alerthub
 * @copyright  2026 Moodle Observability contributors
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class diagnostics {
    /** @var string[] Message providers of the hub. */
    public const PROVIDERS = ['alert_critical', 'alert_warning', 'alert_info', 'digest'];

    /**
     * Data of the overview page.
     *
     * @return array
     */
    public static function data(): array {
        global $CFG, $DB;
        $processors = [];
        foreach (get_message_processors(true) as $processor) {
            $processors[$processor->name] = get_string('pluginname', 'message_' . $processor->name);
        }
        $providers = [];
        foreach (self::PROVIDERS as $name) {
            $enabled = [];
            foreach ($processors as $processorname => $label) {
                $locked = get_config('message', "{$processorname}_provider_local_alerthub_{$name}_locked");
                $default = get_config('message', "message_provider_local_alerthub_{$name}_enabled");
                $on = $default !== false && in_array($processorname, explode(',', (string) $default), true);
                $enabled[] = ['name' => $label, 'on' => $on, 'locked' => (bool) $locked];
            }
            $providers[] = ['name' => get_string('messageprovider:' . $name, 'local_alerthub'), 'processors' => $enabled];
        }
        $counts = [];
        foreach ([dispatcher::PENDING, dispatcher::SENT, dispatcher::FAILED] as $status) {
            $counts[] = ['name' => get_string('delivery_' . $status, 'local_alerthub'),
                'count' => $DB->count_records('local_alerthub_delivery', ['status' => $status])];
        }
        $failures = [];
        foreach ($DB->get_records('local_alerthub_delivery', ['status' => dispatcher::FAILED], 'id DESC', '*', 0, 10) as $row) {
            $failures[] = ['time' => userdate((int) $row->timecreated), 'error' => s((string) $row->lasterror)];
        }
        return [
            'noemailever' => !empty($CFG->noemailever),
            'processors' => array_values($processors),
            'hasprocessors' => !empty($processors),
            'providers' => $providers,
            'active' => $DB->count_records('local_alerthub_alert', ['state' => api::RAISED]),
            'channels' => $DB->count_records('local_alerthub_channel', ['enabled' => 1]),
            'rules' => $DB->count_records('local_alerthub_rule', ['enabled' => 1]),
            'counts' => $counts,
            'failures' => $failures,
            'hasfailures' => !empty($failures),
            'notificationsurl' => (new \moodle_url('/admin/message.php'))->out(false),
            'channelsurl' => (new \moodle_url('/local/alerthub/channels.php'))->out(false),
            'addchannelurl' => (new \moodle_url('/local/alerthub/channels.php', ['action' => 'edit', 'type' => 'message']))
                ->out(false),
            'rulesurl' => (new \moodle_url('/local/alerthub/rules.php'))->out(false),
            'alertsurl' => (new \moodle_url('/local/alerthub/alerts.php'))->out(false),
            'nochannels' => $DB->count_records('local_alerthub_channel') === 0,
        ];
    }
}
