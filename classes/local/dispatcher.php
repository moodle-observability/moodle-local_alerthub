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

/**
 * Sends the pending deliveries, with retries and a growing pause between them.
 *
 * @package    local_alerthub
 * @copyright  2026 Moodle Observability contributors
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class dispatcher {
    /** @var string Waiting to be sent. */
    public const PENDING = 'pending';

    /** @var string Sent. */
    public const SENT = 'sent';

    /** @var string Given up after the last attempt. */
    public const FAILED = 'failed';

    /** @var int Attempts before giving up. */
    public const MAX_ATTEMPTS = 5;

    /**
     * Sends what is due, within a time limit.
     *
     * @param int $timelimit Seconds.
     * @param \progress_trace|null $trace Output.
     * @return array{sent: int, retried: int, failed: int}
     */
    public static function run(int $timelimit = 50, ?\progress_trace $trace = null): array {
        global $DB;
        $trace = $trace ?? new \null_progress_trace();
        $deadline = microtime(true) + $timelimit;
        $stats = ['sent' => 0, 'retried' => 0, 'failed' => 0];
        $channels = [];
        $deliveries = $DB->get_records_select(
            'local_alerthub_delivery',
            'status = :status AND nextattempt <= :now',
            ['status' => self::PENDING, 'now' => time()],
            'nextattempt ASC, id ASC',
            '*',
            0,
            200
        );
        foreach ($deliveries as $delivery) {
            if (microtime(true) > $deadline) {
                break;
            }
            $channels[$delivery->channelid] ??= channels::load((int) $delivery->channelid);
            $result = self::send($delivery, $channels[$delivery->channelid]);
            $stats[self::record($delivery, $result)]++;
            if (!$result->ok) {
                $trace->output("delivery {$delivery->id}: " . $result->error);
            }
        }
        return $stats;
    }

    /**
     * Sends one delivery.
     *
     * @param \stdClass $delivery Delivery.
     * @param channel\base|null $channel Channel, or null when it was removed or disabled.
     * @return channel\result
     */
    private static function send(\stdClass $delivery, ?channel\base $channel): channel\result {
        global $DB;
        if ($channel === null) {
            return channel\result::failure('channel removed, disabled or not installed', false);
        }
        try {
            if ($delivery->kind === 'digest') {
                $ids = json_decode((string) $delivery->items, true) ?: [];
                $alerts = $ids ? $DB->get_records_list('local_alerthub_alert', 'id', $ids, 'severity DESC, timelast DESC')
                    : [];
                $rule = $DB->get_record('local_alerthub_rule', ['id' => $delivery->ruleid]);
                return $channel->send_digest(array_values($alerts), $rule ?: null);
            }
            $alert = $DB->get_record('local_alerthub_alert', ['id' => $delivery->alertid]);
            if (!$alert) {
                return channel\result::failure('alert removed', false);
            }
            return $channel->send_alert($alert, $delivery->kind);
        } catch (\Throwable $e) {
            return channel\result::failure(get_class($e) . ': ' . $e->getMessage());
        }
    }

    /**
     * Records the result of an attempt.
     *
     * @param \stdClass $delivery Delivery.
     * @param channel\result $result Result.
     * @return string sent, retried or failed.
     */
    private static function record(\stdClass $delivery, channel\result $result): string {
        global $DB;
        $update = (object) ['id' => $delivery->id, 'attempts' => (int) $delivery->attempts + 1];
        if ($result->ok) {
            $update->status = self::SENT;
            $update->timesent = time();
            $update->recipients = $result->recipients;
            $update->lasterror = null;
            $DB->update_record('local_alerthub_delivery', $update);
            return 'sent';
        }
        $update->lasterror = \core_text::substr($result->error, 0, 1000);
        if (!$result->retry || $update->attempts >= self::MAX_ATTEMPTS) {
            $update->status = self::FAILED;
            $DB->update_record('local_alerthub_delivery', $update);
            return 'failed';
        }
        // 2, 4, 8 and 16 minutes.
        $update->nextattempt = time() + 60 * (2 ** $update->attempts);
        $DB->update_record('local_alerthub_delivery', $update);
        return 'retried';
    }
}
