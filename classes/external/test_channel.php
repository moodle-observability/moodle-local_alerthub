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

namespace local_alerthub\external;

use core_external\external_api;
use core_external\external_function_parameters;
use core_external\external_single_structure;
use core_external\external_value;
use local_alerthub\local\channels;

/**
 * Sends a test alert through one channel, during the request.
 *
 * @package    local_alerthub
 * @copyright  2026 Moodle Observability contributors
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class test_channel extends external_api {
    /**
     * Parameters.
     *
     * @return external_function_parameters
     */
    public static function execute_parameters(): external_function_parameters {
        return new external_function_parameters([
            'channelid' => new external_value(PARAM_INT, 'Channel id'),
        ]);
    }

    /**
     * Sends the test.
     *
     * @param int $channelid Channel id.
     * @return array
     */
    public static function execute(int $channelid): array {
        $params = self::validate_parameters(self::execute_parameters(), ['channelid' => $channelid]);
        $context = \context_system::instance();
        self::validate_context($context);
        require_capability('local/alerthub:manage', $context);

        $channel = channels::load($params['channelid'], false);
        if (!$channel) {
            throw new \moodle_exception('errorchanneltype', 'local_alerthub');
        }
        $result = $channel->test();
        return ['ok' => $result->ok, 'recipients' => $result->recipients, 'error' => $result->error];
    }

    /**
     * Result.
     *
     * @return external_single_structure
     */
    public static function execute_returns(): external_single_structure {
        return new external_single_structure([
            'ok' => new external_value(PARAM_BOOL, 'Sent'),
            'recipients' => new external_value(PARAM_INT, 'How many received it'),
            'error' => new external_value(PARAM_TEXT, 'Error, when not sent'),
        ]);
    }
}
