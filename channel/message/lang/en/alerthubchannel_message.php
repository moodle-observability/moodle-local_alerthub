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
 * Strings of alerthubchannel_message (en).
 *
 * @package    alerthubchannel_message
 * @copyright  2026 Moodle Observability contributors
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['bycapability'] = 'Users with the capability to receive alerts in the context of the alert';
$string['byusers'] = 'These users';
$string['norecipients'] = 'No recipient: nobody has the capability local/alerthub:receive in the context, or the list is empty.';
$string['pluginname'] = 'Moodle message';
$string['privacy:metadata:core_message'] = 'The alerts are sent to the recipients through the Message API.';
$string['recipients'] = 'Recipients';
$string['recipients_help'] = 'With the capability, the alerts of a course go to whoever has local/alerthub:receive in it (by default, the editing teachers) and the site alerts to whoever has it in the system context. With a list, they go to those users: use service users (authentication "No login") whose Telegram or Slack is the group of the team.';
