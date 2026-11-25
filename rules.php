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
 * Rules of the alert hub: which alerts go to which channel, immediately or in a digest.
 *
 * @package    local_alerthub
 * @copyright  2026 Moodle Observability contributors
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use core\output\notification;

require(__DIR__ . '/../../config.php');
require_once($CFG->libdir . '/adminlib.php');

admin_externalpage_setup('local_alerthub_rules');

$action = optional_param('action', '', PARAM_ALPHA);
$id = optional_param('id', 0, PARAM_INT);
$baseurl = new moodle_url('/local/alerthub/rules.php');

if ($action === 'delete' && $id) {
    $row = $DB->get_record('local_alerthub_rule', ['id' => $id], '*', MUST_EXIST);
    if (optional_param('confirm', 0, PARAM_BOOL)) {
        require_sesskey();
        $DB->delete_records('local_alerthub_delivery', ['ruleid' => $id, 'status' => 'pending']);
        $DB->delete_records('local_alerthub_rule', ['id' => $id]);
        redirect($baseurl, get_string('deleted', 'local_alerthub'), null, notification::NOTIFY_SUCCESS);
    }
    echo $OUTPUT->header();
    echo $OUTPUT->confirm(
        get_string('confirmdeleterule', 'local_alerthub', format_string($row->name)),
        new moodle_url($baseurl, ['action' => 'delete', 'id' => $id, 'confirm' => 1, 'sesskey' => sesskey()]),
        $baseurl
    );
    echo $OUTPUT->footer();
    die;
}

if ($action === 'edit') {
    if (!$DB->record_exists('local_alerthub_channel', [])) {
        redirect(new moodle_url('/local/alerthub/channels.php'), get_string('nochannels', 'local_alerthub'));
    }
    $row = $id ? $DB->get_record('local_alerthub_rule', ['id' => $id], '*', MUST_EXIST) : null;
    $form = new \local_alerthub\form\rule_form(new moodle_url($baseurl, ['action' => 'edit', 'id' => $id]));
    if ($row) {
        $form->set_data($row);
    }
    if ($form->is_cancelled()) {
        redirect($baseurl);
    }
    if ($data = $form->get_data()) {
        $data->source = (string) $data->source;
        $data->category = (string) $data->category;
        $data->timemodified = time();
        if ($row) {
            $data->id = $row->id;
            $DB->update_record('local_alerthub_rule', $data);
        } else {
            unset($data->id);
            $data->timecreated = $data->timemodified;
            $data->lastdigest = 0;
            $DB->insert_record('local_alerthub_rule', $data);
        }
        redirect($baseurl, get_string('saved', 'local_alerthub'), null, notification::NOTIFY_SUCCESS);
    }
    echo $OUTPUT->header();
    echo $OUTPUT->heading(get_string($row ? 'editrule' : 'addrule', 'local_alerthub'));
    $form->display();
    echo $OUTPUT->footer();
    die;
}

$channelnames = $DB->get_records_menu('local_alerthub_channel', null, '', 'id, name');
$rows = [];
foreach ($DB->get_records('local_alerthub_rule', null, 'name') as $row) {
    $rows[] = [
        'name' => format_string($row->name),
        'channel' => format_string($channelnames[$row->channelid] ?? '?'),
        'source' => $row->source !== '' ? $row->source : get_string('any', 'local_alerthub'),
        'category' => $row->category !== '' ? $row->category : get_string('any', 'local_alerthub'),
        'minseverity' => get_string('severity_' . $row->minseverity, 'local_alerthub'),
        'mode' => get_string('mode_' . $row->mode, 'local_alerthub'),
        'enabled' => (bool) $row->enabled,
        'editurl' => (new moodle_url($baseurl, ['action' => 'edit', 'id' => $row->id]))->out(false),
        'deleteurl' => (new moodle_url($baseurl, ['action' => 'delete', 'id' => $row->id]))->out(false),
    ];
}

echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('rules', 'local_alerthub'));
echo $OUTPUT->render_from_template('local_alerthub/rules', [
    'rules' => $rows,
    'hasrules' => !empty($rows),
    'addurl' => (new moodle_url($baseurl, ['action' => 'edit']))->out(false),
]);
echo $OUTPUT->footer();
