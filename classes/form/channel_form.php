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

use local_alerthub\local\channels;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/formslib.php');

/**
 * Creates or edits a channel; the type adds its own settings.
 *
 * @package    local_alerthub
 * @copyright  2026 Moodle Observability contributors
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class channel_form extends \moodleform {
    /**
     * Form definition.
     *
     * @return void
     */
    protected function definition() {
        $mform = $this->_form;
        $type = (string) $this->_customdata['type'];

        $mform->addElement('hidden', 'id');
        $mform->setType('id', PARAM_INT);
        $mform->addElement('hidden', 'type', $type);
        $mform->setType('type', PARAM_PLUGIN);

        $mform->addElement(
            'static',
            'typename',
            get_string('channeltype', 'local_alerthub'),
            channels::types()[$type] ?? s($type)
        );
        $mform->addElement('text', 'name', get_string('name'), ['size' => 40]);
        $mform->setType('name', PARAM_TEXT);
        $mform->addRule('name', null, 'required', null, 'client');
        $mform->addRule('name', get_string('maximumchars', '', 255), 'maxlength', 255, 'client');
        $mform->addElement('advcheckbox', 'enabled', get_string('enabled', 'local_alerthub'));
        $mform->setDefault('enabled', 1);

        $class = channels::class_name($type);
        if (class_exists($class)) {
            $class::config_form($mform);
        }
        $this->add_action_buttons();
    }

    /**
     * Validation of the type settings.
     *
     * @param array $data Data.
     * @param array $files Files.
     * @return array
     */
    public function validation($data, $files) {
        $errors = parent::validation($data, $files);
        $class = channels::class_name((string) $data['type']);
        if (!class_exists($class)) {
            $errors['name'] = get_string('errorchanneltype', 'local_alerthub');
            return $errors;
        }
        return $errors + $class::validate_config($data);
    }
}
