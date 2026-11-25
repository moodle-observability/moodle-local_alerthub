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

/**
 * Alerts received by the hub, the active ones first.
 *
 * @package    local_alerthub
 * @copyright  2026 Moodle Observability contributors
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use local_alerthub\api;

require(__DIR__ . '/../../config.php');
require_once($CFG->libdir . '/adminlib.php');

admin_externalpage_setup('local_alerthub_alerts');

$state = optional_param('state', api::RAISED, PARAM_ALPHA);
$state = in_array($state, [api::RAISED, api::RESOLVED], true) ? $state : api::RAISED;
$baseurl = new moodle_url('/local/alerthub/alerts.php');

$rows = [];
foreach ($DB->get_records('local_alerthub_alert', ['state' => $state], 'timelast DESC', '*', 0, 200) as $row) {
    $rows[] = [
        'severity' => get_string('severity_' . $row->severity, 'local_alerthub'),
        'critical' => $row->severity === api::CRITICAL,
        'warning' => $row->severity === api::WARNING,
        'title' => s($row->title),
        'body' => s($row->body),
        'source' => s($row->source),
        'category' => s($row->category),
        'occurrences' => (int) $row->occurrences,
        'since' => userdate((int) $row->timefirst),
        'last' => userdate((int) $row->timelast),
        'url' => clean_param((string) $row->url, PARAM_URL),
    ];
}

echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('alerts', 'local_alerthub'));
echo $OUTPUT->render_from_template('local_alerthub/alerts', [
    'alerts' => $rows,
    'hasalerts' => !empty($rows),
    'raised' => $state === api::RAISED,
    'raisedurl' => (new moodle_url($baseurl, ['state' => api::RAISED]))->out(false),
    'resolvedurl' => (new moodle_url($baseurl, ['state' => api::RESOLVED]))->out(false),
]);
echo $OUTPUT->footer();
