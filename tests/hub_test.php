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

namespace local_alerthub;

use local_alerthub\local\channels;
use local_alerthub\local\digests;
use local_alerthub\local\dispatcher;
use local_alerthub\local\router;

/**
 * Tests of the alert hub: deduplication, routing, delivery through the Message API, digests and retries.
 *
 * @package    local_alerthub
 * @copyright  2026 Moodle Observability contributors
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @covers     \local_alerthub\api
 * @covers     \local_alerthub\local\router
 * @covers     \local_alerthub\local\dispatcher
 * @covers     \local_alerthub\local\digests
 * @covers     \local_alerthub\local\channels
 * @covers     \alerthubchannel_message\channel
 */
final class hub_test extends \advanced_testcase {
    /**
     * A message channel to a list of users and a rule.
     *
     * @param array $rule Rule fields.
     * @param int[] $userids Recipients.
     * @return array{0: int, 1: int} Channel and rule ids.
     */
    private function setup_channel(array $rule, array $userids): array {
        global $DB;
        $channelid = channels::save((object) ['type' => 'message', 'name' => 'Team', 'enabled' => 1,
            'recipients' => 'users', 'userids' => $userids]);
        $ruleid = $DB->insert_record('local_alerthub_rule', (object) ($rule + [
            'name' => 'Rule', 'source' => '', 'category' => '', 'minseverity' => api::WARNING, 'channelid' => $channelid,
            'mode' => router::IMMEDIATE, 'digesthour' => 8, 'digestday' => 1, 'notifyresolved' => 1, 'enabled' => 1,
            'lastdigest' => 0, 'timecreated' => time(), 'timemodified' => time(),
        ]));
        return [$channelid, $ruleid];
    }

    /**
     * Repeated raises of an active alert are counted, not delivered again; a resolved alert that comes back is.
     *
     * @return void
     */
    public function test_raise_dedup_and_resolve(): void {
        global $DB;
        $this->resetAfterTest();
        $user = $this->getDataGenerator()->create_user();
        $this->setup_channel([], [$user->id]);

        $alert = ['source' => 'tool_extdeps', 'dedupkey' => '1:2', 'severity' => 'warning', 'title' => 'Video private',
            'payload' => ['courseid' => 2]];
        $id = api::raise($alert);
        $this->assertSame($id, api::raise($alert));
        $row = $DB->get_record('local_alerthub_alert', ['id' => $id]);
        $this->assertSame(2, (int) $row->occurrences);
        $this->assertSame(1, $DB->count_records('local_alerthub_delivery', ['kind' => api::RAISED]));

        $this->assertTrue(api::resolve('tool_extdeps', '1:2'));
        $this->assertFalse(api::resolve('tool_extdeps', '1:2'));
        $this->assertSame(1, $DB->count_records('local_alerthub_delivery', ['kind' => api::RESOLVED]));

        api::raise($alert);
        $this->assertSame(2, $DB->count_records('local_alerthub_delivery', ['kind' => api::RAISED]));
    }

    /**
     * Rules filter by source, category and minimum severity.
     *
     * @return void
     */
    public function test_rule_matching(): void {
        $rule = (object) ['source' => 'tool_extdeps', 'category' => '', 'minseverity' => api::WARNING];
        $alert = (object) ['source' => 'tool_extdeps', 'category' => 'x', 'severity' => api::CRITICAL];
        $this->assertTrue(router::matches($rule, $alert));
        $alert->severity = api::INFO;
        $this->assertFalse(router::matches($rule, $alert));
        $alert->severity = api::WARNING;
        $alert->source = 'tool_other';
        $this->assertFalse(router::matches($rule, $alert));
        $rule->source = '';
        $rule->category = 'y';
        $this->assertFalse(router::matches($rule, $alert));
    }

    /**
     * Invalid alerts are refused.
     *
     * @return void
     */
    public function test_invalid(): void {
        $this->resetAfterTest();
        $this->expectException(\coding_exception::class);
        api::raise(['source' => 'tool_extdeps', 'dedupkey' => 'x', 'severity' => 'urgent']);
    }

    /**
     * The dispatch sends through the Message API with the provider of the severity.
     *
     * @return void
     */
    public function test_dispatch_message(): void {
        global $DB;
        $this->resetAfterTest();
        $this->preventResetByRollback();
        $user = $this->getDataGenerator()->create_user();
        $this->setup_channel([], [$user->id]);
        api::raise(['source' => 'tool_extdeps', 'dedupkey' => 'a', 'severity' => 'critical', 'title' => 'Disk full',
            'body' => 'Only 1% left', 'url' => 'https://example.com/x']);

        $sink = $this->redirectMessages();
        $stats = dispatcher::run();
        $messages = $sink->get_messages();
        $sink->close();

        $this->assertSame(1, $stats['sent']);
        $this->assertCount(1, $messages);
        $this->assertSame('alert_critical', $messages[0]->eventtype);
        $this->assertSame((int) $user->id, (int) $messages[0]->useridto);
        $this->assertStringContainsString('Disk full', $messages[0]->subject);
        $this->assertStringContainsString('Only 1% left', $messages[0]->fullmessage);
        $delivery = $DB->get_record('local_alerthub_delivery', []);
        $this->assertSame(dispatcher::SENT, $delivery->status);
        $this->assertSame(1, (int) $delivery->recipients);
    }

    /**
     * Without recipients the delivery fails at once, without retries.
     *
     * @return void
     */
    public function test_dispatch_without_recipients(): void {
        global $DB;
        $this->resetAfterTest();
        $user = $this->getDataGenerator()->create_user();
        $this->setup_channel([], [$user->id]);
        delete_user($user);
        api::raise(['source' => 'tool_extdeps', 'dedupkey' => 'a', 'severity' => 'warning']);
        $stats = dispatcher::run();
        $this->assertSame(1, $stats['failed']);
        $this->assertSame(dispatcher::FAILED, $DB->get_field('local_alerthub_delivery', 'status', []));
    }

    /**
     * Daily digests are built once a day, after the configured hour, with the active alerts.
     *
     * @return void
     */
    public function test_digest(): void {
        global $DB;
        $this->resetAfterTest();
        $this->preventResetByRollback();
        $user = $this->getDataGenerator()->create_user();
        [, $ruleid] = $this->setup_channel(['mode' => router::DAILY, 'digesthour' => 8], [$user->id]);
        api::raise(['source' => 'tool_extdeps', 'dedupkey' => 'a', 'severity' => 'warning', 'title' => 'First']);
        api::raise(['source' => 'tool_extdeps', 'dedupkey' => 'b', 'severity' => 'critical', 'title' => 'Second']);
        $this->assertSame(0, $DB->count_records('local_alerthub_delivery'));

        $tz = \core_date::get_server_timezone();
        $early = make_timestamp(2026, 11, 24, 7, 30, 0, $tz);
        $late = make_timestamp(2026, 11, 24, 9, 0, 0, $tz);
        $this->assertSame(0, digests::run($early));
        $this->assertSame(1, digests::run($late));
        $this->assertSame(0, digests::run($late + 600));
        $this->assertSame($late, (int) $DB->get_field('local_alerthub_rule', 'lastdigest', ['id' => $ruleid]));

        $sink = $this->redirectMessages();
        dispatcher::run();
        $messages = $sink->get_messages();
        $sink->close();
        $this->assertCount(1, $messages);
        $this->assertSame('digest', $messages[0]->eventtype);
        $this->assertStringContainsString('First', $messages[0]->fullmessage);
        $this->assertStringContainsString('Second', $messages[0]->fullmessage);
    }

    /**
     * The configuration is encrypted at rest.
     *
     * @return void
     */
    public function test_channel_config_encrypted(): void {
        global $DB;
        $this->resetAfterTest();
        $id = channels::save((object) ['type' => 'message', 'name' => 'Team', 'enabled' => 1, 'recipients' => 'users',
            'userids' => [12345]]);
        $stored = $DB->get_field('local_alerthub_channel', 'config', ['id' => $id]);
        $this->assertStringNotContainsString('12345', $stored);
        $this->assertSame([12345], channels::decode($stored)['userids']);
        $this->assertNotNull(channels::load($id));
        $DB->set_field('local_alerthub_channel', 'enabled', 0, ['id' => $id]);
        $this->assertNull(channels::load($id));
        $this->assertNotNull(channels::load($id, false));
    }

    /**
     * Alerts of tool_taskmonitor are raised and resolved through the hub.
     *
     * @return void
     */
    public function test_taskmonitor_bridge(): void {
        global $DB;
        $this->resetAfterTest();
        $hook = new class (['id' => 'cron', 'type' => 'cron', 'severity' => 'critical', 'title' => 'Cron is not running',
                'details' => 'Last run 2 hours ago'], 'raised') {
            /**
             * Constructor.
             *
             * @param array $alert Alert.
             * @param string $state State.
             */
            public function __construct(
                /** @var array Alert. */
                private array $alert,
                /** @var string State. */
                private string $state,
            ) {
            }

            /**
             * Alert.
             *
             * @return array
             */
            public function get_alert(): array {
                return $this->alert;
            }

            /**
             * State.
             *
             * @return string
             */
            public function get_state(): string {
                return $this->state;
            }
        };
        hook_callbacks::taskmonitor_alert($hook);
        $alert = $DB->get_record('local_alerthub_alert', ['source' => 'tool_taskmonitor', 'dedupkey' => 'cron']);
        $this->assertSame(api::CRITICAL, $alert->severity);
        $this->assertSame('cron', $alert->category);
        $this->assertSame(api::RAISED, $alert->state);
        $this->assertTrue(api::resolve('tool_taskmonitor', 'cron'));
    }
}
