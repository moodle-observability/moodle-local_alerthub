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

namespace local_alerthub\plugininfo;

/**
 * Channel subplugins of local_alerthub.
 *
 * @package    local_alerthub
 * @copyright  2026 Moodle Observability contributors
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class alerthubchannel extends \core\plugininfo\base {
    /**
     * Channels can be uninstalled; their instances stop working.
     *
     * @return bool
     */
    public function is_uninstall_allowed() {
        return true;
    }

    /**
     * Removes the instances of the channel when it is uninstalled.
     *
     * @return void
     */
    public function uninstall_cleanup() {
        global $DB;
        foreach ($DB->get_fieldset_select('local_alerthub_channel', 'id', 'type = ?', [$this->name]) as $id) {
            \local_alerthub\local\channels::delete((int) $id);
        }
        parent::uninstall_cleanup();
    }
}
