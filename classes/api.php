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

use local_alerthub\local\router;

/**
 * Public API: other plugins raise and resolve alerts; the hub deduplicates, routes and delivers them.
 *
 * Usage from an optional dependency:
 * <code>
 * if (class_exists('\local_alerthub\api')) {
 *     \local_alerthub\api::raise(['source' => 'tool_x', 'dedupkey' => 'disk', 'severity' => 'critical',
 *         'title' => 'Disk almost full', 'body' => '95% used']);
 * }
 * </code>
 *
 * @package    local_alerthub
 * @copyright  2026 Moodle Observability contributors
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class api {
    /** @var string Information. */
    public const INFO = 'info';

    /** @var string Something needs attention. */
    public const WARNING = 'warning';

    /** @var string Something is broken now. */
    public const CRITICAL = 'critical';

    /** @var string The alert is active. */
    public const RAISED = 'raised';

    /** @var string The alert ended. */
    public const RESOLVED = 'resolved';

    /**
     * Severities from the lowest to the highest.
     *
     * @return string[]
     */
    public static function severities(): array {
        return [self::INFO, self::WARNING, self::CRITICAL];
    }

    /**
     * Raises an alert, or counts one more occurrence of an active alert with the same source and key.
     *
     * Only a new alert, or one that was resolved and came back, is delivered again: repeated raises of an active
     * alert only update it.
     *
     * @param array $alert source and dedupkey (required); severity, category, title, body (plain text), url,
     *                     contextid and payload (array of identifiers, never personal data).
     * @return int Alert id.
     */
    public static function raise(array $alert): int {
        global $DB;
        $data = self::clean($alert);
        $now = time();
        $existing = $DB->get_record('local_alerthub_alert', ['source' => $data->source, 'dedupkey' => $data->dedupkey]);
        if ($existing && $existing->state === self::RAISED) {
            $data->id = $existing->id;
            $data->occurrences = (int) $existing->occurrences + 1;
            $data->timelast = $now;
            $DB->update_record('local_alerthub_alert', $data);
            return (int) $existing->id;
        }
        $data->state = self::RAISED;
        $data->occurrences = 1;
        $data->timefirst = $now;
        $data->timelast = $now;
        $data->timeresolved = 0;
        if ($existing) {
            $data->id = $existing->id;
            $DB->update_record('local_alerthub_alert', $data);
        } else {
            try {
                $data->id = $DB->insert_record('local_alerthub_alert', $data);
            } catch (\dml_write_exception $e) {
                // Raised at the same time by another request: count it there.
                return self::raise($alert);
            }
        }
        router::route($DB->get_record('local_alerthub_alert', ['id' => $data->id], '*', MUST_EXIST), self::RAISED);
        return (int) $data->id;
    }

    /**
     * Resolves an active alert. Nothing happens when there is no active alert with that source and key.
     *
     * @param string $source Component that raised it.
     * @param string $dedupkey Key of the alert.
     * @return bool Whether an active alert was resolved.
     */
    public static function resolve(string $source, string $dedupkey): bool {
        global $DB;
        $alert = $DB->get_record('local_alerthub_alert', ['source' => $source, 'dedupkey' => $dedupkey]);
        if (!$alert || $alert->state !== self::RAISED) {
            return false;
        }
        $alert->state = self::RESOLVED;
        $alert->timeresolved = time();
        $DB->update_record('local_alerthub_alert', $alert);
        router::route($alert, self::RESOLVED);
        return true;
    }

    /**
     * Validates and normalises an alert.
     *
     * @param array $alert Alert as given by the caller.
     * @return \stdClass
     */
    private static function clean(array $alert): \stdClass {
        $source = clean_param((string) ($alert['source'] ?? ''), PARAM_COMPONENT);
        $dedupkey = trim((string) ($alert['dedupkey'] ?? ''));
        if ($source === '' || $dedupkey === '') {
            throw new \coding_exception('local_alerthub: source and dedupkey are required');
        }
        $severity = (string) ($alert['severity'] ?? self::WARNING);
        if (!in_array($severity, self::severities(), true)) {
            throw new \coding_exception('local_alerthub: invalid severity ' . s($severity));
        }
        $payload = $alert['payload'] ?? null;
        $contextid = isset($alert['contextid']) ? (int) $alert['contextid'] : null;
        return (object) [
            'source' => $source,
            'dedupkey' => \core_text::substr($dedupkey, 0, 200),
            'severity' => $severity,
            'category' => \core_text::substr(clean_param((string) ($alert['category'] ?? ''), PARAM_ALPHANUMEXT), 0, 40),
            'title' => \core_text::substr(clean_param((string) ($alert['title'] ?? $dedupkey), PARAM_TEXT), 0, 255),
            'body' => clean_param((string) ($alert['body'] ?? ''), PARAM_TEXT),
            'url' => clean_param((string) ($alert['url'] ?? ''), PARAM_URL),
            'contextid' => $contextid ?: null,
            'payload' => $payload === null ? null : json_encode($payload),
        ];
    }
}
