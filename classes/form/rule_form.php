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

namespace local_alerthub\form;

use local_alerthub\api;
use local_alerthub\local\router;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/formslib.php');

/**
 * Creates or edits a rule: which alerts go to which channel, immediately or in a digest.
 *
 * @package    local_alerthub
 * @copyright  2026 Moodle Observability contributors
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class rule_form extends \moodleform {
    /**
     * Form definition.
     *
     * @return void
     */
    protected function definition() {
        global $DB;
        $mform = $this->_form;

        $mform->addElement('hidden', 'id');
        $mform->setType('id', PARAM_INT);

        $mform->addElement('text', 'name', get_string('name'), ['size' => 40]);
        $mform->setType('name', PARAM_TEXT);
        $mform->addRule('name', null, 'required', null, 'client');
        $mform->addRule('name', get_string('maximumchars', '', 255), 'maxlength', 255, 'client');

        $channels = $DB->get_records_menu('local_alerthub_channel', null, 'name', 'id, name');
        $mform->addElement(
            'select',
            'channelid',
            get_string('channel', 'local_alerthub'),
            array_map('format_string', $channels)
        );
        $mform->addRule('channelid', null, 'required', null, 'client');

        $sources = ['' => get_string('any', 'local_alerthub')];
        foreach ($DB->get_fieldset_sql('SELECT DISTINCT source FROM {local_alerthub_alert} ORDER BY source') as $source) {
            $sources[$source] = $source;
        }
        foreach (['tool_extdeps', 'tool_taskmonitor'] as $known) {
            $sources[$known] ??= $known;
        }
        $mform->addElement('select', 'source', get_string('source', 'local_alerthub'), $sources);
        $mform->setType('source', PARAM_COMPONENT);

        $mform->addElement('text', 'category', get_string('category', 'local_alerthub'), ['size' => 20]);
        $mform->setType('category', PARAM_ALPHANUMEXT);
        $mform->addHelpButton('category', 'category', 'local_alerthub');

        $severities = [];
        foreach (api::severities() as $severity) {
            $severities[$severity] = get_string('severity_' . $severity, 'local_alerthub');
        }
        $mform->addElement('select', 'minseverity', get_string('minseverity', 'local_alerthub'), $severities);
        $mform->setDefault('minseverity', api::WARNING);

        $modes = [];
        foreach (router::modes() as $mode) {
            $modes[$mode] = get_string('mode_' . $mode, 'local_alerthub');
        }
        $mform->addElement('select', 'mode', get_string('mode', 'local_alerthub'), $modes);

        $hours = [];
        for ($i = 0; $i < 24; $i++) {
            $hours[$i] = sprintf('%02d:00', $i);
        }
        $mform->addElement('select', 'digesthour', get_string('digesthour', 'local_alerthub'), $hours);
        $mform->setDefault('digesthour', 8);
        $mform->hideIf('digesthour', 'mode', 'eq', router::IMMEDIATE);

        $days = [];
        foreach (['sunday', 'monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday'] as $i => $day) {
            $days[$i] = get_string($day, 'calendar');
        }
        $mform->addElement('select', 'digestday', get_string('digestday', 'local_alerthub'), $days);
        $mform->setDefault('digestday', 1);
        $mform->hideIf('digestday', 'mode', 'neq', router::WEEKLY);

        $mform->addElement('advcheckbox', 'notifyresolved', get_string('notifyresolved', 'local_alerthub'));
        $mform->setDefault('notifyresolved', 1);
        $mform->hideIf('notifyresolved', 'mode', 'neq', router::IMMEDIATE);

        $mform->addElement('advcheckbox', 'enabled', get_string('enabled', 'local_alerthub'));
        $mform->setDefault('enabled', 1);

        $this->add_action_buttons();
    }
}
