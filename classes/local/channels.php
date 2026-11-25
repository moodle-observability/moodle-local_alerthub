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

use local_alerthub\local\channel\base;

/**
 * Channel types (installed subplugins) and channel instances, with the configuration encrypted at rest.
 *
 * @package    local_alerthub
 * @copyright  2026 Moodle Observability contributors
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class channels {
    /**
     * Installed channel types.
     *
     * @return array<string, string> Type => name.
     */
    public static function types(): array {
        $types = [];
        foreach (array_keys(\core_component::get_plugin_list('alerthubchannel')) as $type) {
            if (class_exists(self::class_name($type))) {
                $types[$type] = get_string('pluginname', 'alerthubchannel_' . $type);
            }
        }
        return $types;
    }

    /**
     * Class of a channel type.
     *
     * @param string $type Type.
     * @return string
     */
    public static function class_name(string $type): string {
        return '\\alerthubchannel_' . clean_param($type, PARAM_PLUGIN) . '\\channel';
    }

    /**
     * Instance of an enabled channel.
     *
     * @param int $id Channel id.
     * @param bool $onlyenabled Return null when the channel is disabled.
     * @return base|null
     */
    public static function load(int $id, bool $onlyenabled = true): ?base {
        global $DB;
        $row = $DB->get_record('local_alerthub_channel', ['id' => $id]);
        if (!$row || ($onlyenabled && !$row->enabled)) {
            return null;
        }
        $class = self::class_name($row->type);
        if (!class_exists($class) || !is_subclass_of($class, base::class)) {
            return null;
        }
        return new $class($row, self::decode((string) $row->config));
    }

    /**
     * Encrypts a configuration.
     *
     * @param array $config Configuration.
     * @return string
     */
    public static function encode(array $config): string {
        return \core\encryption::encrypt(json_encode($config));
    }

    /**
     * Decrypts a configuration.
     *
     * @param string $stored Stored value.
     * @return array
     */
    public static function decode(string $stored): array {
        if ($stored === '') {
            return [];
        }
        try {
            $config = json_decode(\core\encryption::decrypt($stored), true);
        } catch (\Throwable $e) {
            debugging('local_alerthub: channel configuration could not be decrypted', DEBUG_DEVELOPER);
            return [];
        }
        return is_array($config) ? $config : [];
    }

    /**
     * Saves a channel.
     *
     * @param \stdClass $data id (0 for new), type, name, enabled and the configuration fields of the type.
     * @return int Channel id.
     */
    public static function save(\stdClass $data): int {
        global $DB;
        $class = self::class_name($data->type);
        if (!class_exists($class)) {
            throw new \moodle_exception('errorchanneltype', 'local_alerthub');
        }
        $config = [];
        foreach ($class::config_fields() as $field) {
            if (property_exists($data, $field)) {
                $config[$field] = $data->$field;
            }
        }
        $row = (object) [
            'type' => $data->type,
            'name' => $data->name,
            'enabled' => empty($data->enabled) ? 0 : 1,
            'config' => self::encode($config),
            'timemodified' => time(),
        ];
        if (!empty($data->id)) {
            $row->id = (int) $data->id;
            $DB->update_record('local_alerthub_channel', $row);
            return $row->id;
        }
        $row->timecreated = $row->timemodified;
        return (int) $DB->insert_record('local_alerthub_channel', $row);
    }

    /**
     * Deletes a channel with its rules and pending deliveries.
     *
     * @param int $id Channel id.
     * @return void
     */
    public static function delete(int $id): void {
        global $DB;
        $transaction = $DB->start_delegated_transaction();
        $DB->delete_records('local_alerthub_delivery', ['channelid' => $id]);
        $DB->delete_records('local_alerthub_rule', ['channelid' => $id]);
        $DB->delete_records('local_alerthub_channel', ['id' => $id]);
        $transaction->allow_commit();
    }
}
