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
 * Channels of the alert hub: list, create, edit, delete and send a test.
 *
 * @package    local_alerthub
 * @copyright  2026 Moodle Observability contributors
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use core\output\notification;
use local_alerthub\local\channels;

require(__DIR__ . '/../../config.php');
require_once($CFG->libdir . '/adminlib.php');

admin_externalpage_setup('local_alerthub_channels');

$action = optional_param('action', '', PARAM_ALPHA);
$id = optional_param('id', 0, PARAM_INT);
$type = optional_param('type', '', PARAM_PLUGIN);
$baseurl = new moodle_url('/local/alerthub/channels.php');

if ($action === 'test' && $id) {
    require_sesskey();
    $channel = channels::load($id, false);
    if (!$channel) {
        throw new moodle_exception('errorchanneltype', 'local_alerthub');
    }
    $result = $channel->test();
    if ($result->ok) {
        redirect($baseurl, get_string('testsent', 'local_alerthub', $result->recipients), null, notification::NOTIFY_SUCCESS);
    }
    redirect($baseurl, get_string('testfailed', 'local_alerthub', s($result->error)), null, notification::NOTIFY_ERROR);
}

if ($action === 'delete' && $id) {
    $row = $DB->get_record('local_alerthub_channel', ['id' => $id], '*', MUST_EXIST);
    if (optional_param('confirm', 0, PARAM_BOOL)) {
        require_sesskey();
        channels::delete($id);
        redirect($baseurl, get_string('deleted', 'local_alerthub'), null, notification::NOTIFY_SUCCESS);
    }
    echo $OUTPUT->header();
    echo $OUTPUT->confirm(
        get_string('confirmdeletechannel', 'local_alerthub', format_string($row->name)),
        new moodle_url($baseurl, ['action' => 'delete', 'id' => $id, 'confirm' => 1, 'sesskey' => sesskey()]),
        $baseurl
    );
    echo $OUTPUT->footer();
    die;
}

if ($action === 'edit') {
    $row = $id ? $DB->get_record('local_alerthub_channel', ['id' => $id], '*', MUST_EXIST) : null;
    $type = $row ? $row->type : $type;
    if (!array_key_exists($type, channels::types())) {
        throw new moodle_exception('errorchanneltype', 'local_alerthub');
    }
    $form = new \local_alerthub\form\channel_form(
        new moodle_url($baseurl, ['action' => 'edit', 'id' => $id, 'type' => $type]),
        ['type' => $type]
    );
    if ($row) {
        $form->set_data((object) (['id' => $row->id, 'type' => $row->type, 'name' => $row->name, 'enabled' => $row->enabled]
            + channels::decode((string) $row->config)));
    }
    if ($form->is_cancelled()) {
        redirect($baseurl);
    }
    if ($data = $form->get_data()) {
        $data->type = $type;
        channels::save($data);
        redirect($baseurl, get_string('saved', 'local_alerthub'), null, notification::NOTIFY_SUCCESS);
    }
    echo $OUTPUT->header();
    echo $OUTPUT->heading(get_string($row ? 'editchannel' : 'addchannel', 'local_alerthub'));
    $form->display();
    echo $OUTPUT->footer();
    die;
}

$rows = [];
foreach ($DB->get_records('local_alerthub_channel', null, 'name') as $row) {
    $rows[] = [
        'name' => format_string($row->name),
        'type' => channels::types()[$row->type] ?? s($row->type),
        'enabled' => (bool) $row->enabled,
        'rules' => $DB->count_records('local_alerthub_rule', ['channelid' => $row->id]),
        'editurl' => (new moodle_url($baseurl, ['action' => 'edit', 'id' => $row->id]))->out(false),
        'deleteurl' => (new moodle_url($baseurl, ['action' => 'delete', 'id' => $row->id]))->out(false),
        'testurl' => $baseurl->out(false),
        'id' => $row->id,
        'sesskey' => sesskey(),
    ];
}
$types = [];
foreach (channels::types() as $name => $label) {
    $types[] = ['label' => $label, 'url' => (new moodle_url($baseurl, ['action' => 'edit', 'type' => $name]))->out(false)];
}

echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('channels', 'local_alerthub'));
echo $OUTPUT->render_from_template('local_alerthub/channels', [
    'channels' => $rows,
    'haschannels' => !empty($rows),
    'types' => $types,
]);
echo $OUTPUT->footer();
