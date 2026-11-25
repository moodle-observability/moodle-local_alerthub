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
 * Administration of local_alerthub.
 *
 * @package    local_alerthub
 * @copyright  2026 Moodle Observability contributors
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

if ($hassiteconfig || has_capability('local/alerthub:manage', context_system::instance())) {
    $ADMIN->add('localplugins', new admin_category('local_alerthub', new lang_string('pluginname', 'local_alerthub')));
    $ADMIN->add('local_alerthub', new admin_externalpage(
        'local_alerthub_overview',
        new lang_string('overview', 'local_alerthub'),
        new moodle_url('/local/alerthub/index.php'),
        'local/alerthub:manage'
    ));
    $ADMIN->add('local_alerthub', new admin_externalpage(
        'local_alerthub_alerts',
        new lang_string('alerts', 'local_alerthub'),
        new moodle_url('/local/alerthub/alerts.php'),
        'local/alerthub:manage'
    ));
    $ADMIN->add('local_alerthub', new admin_externalpage(
        'local_alerthub_channels',
        new lang_string('channels', 'local_alerthub'),
        new moodle_url('/local/alerthub/channels.php'),
        'local/alerthub:manage'
    ));
    $ADMIN->add('local_alerthub', new admin_externalpage(
        'local_alerthub_rules',
        new lang_string('rules', 'local_alerthub'),
        new moodle_url('/local/alerthub/rules.php'),
        'local/alerthub:manage'
    ));

    $settings = new admin_settingpage('local_alerthub_settings', new lang_string('settings', 'local_alerthub'));
    if ($ADMIN->fulltree) {
        $settings->add(new admin_setting_configtext(
            'local_alerthub/retention',
            new lang_string('retention', 'local_alerthub'),
            new lang_string('retention_desc', 'local_alerthub'),
            90,
            PARAM_INT
        ));
    }
    $ADMIN->add('local_alerthub', $settings);

    foreach (core_plugin_manager::instance()->get_plugins_of_type('alerthubchannel') as $plugin) {
        $plugin->load_settings($ADMIN, 'local_alerthub', $hassiteconfig);
    }
}

// The settings are added above, in their own category.
$settings = null;
