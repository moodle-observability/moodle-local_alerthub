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

namespace local_alerthub\local\channel;

/**
 * Result of a sending.
 *
 * @package    local_alerthub
 * @copyright  2026 Moodle Observability contributors
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class result {
    /**
     * Constructor.
     *
     * @param bool $ok Sent.
     * @param int $recipients How many received it.
     * @param string $error Error, without credentials.
     * @param bool $retry Whether trying again can work.
     */
    public function __construct(
        /** @var bool Sent. */
        public readonly bool $ok,
        /** @var int How many received it. */
        public readonly int $recipients = 0,
        /** @var string Error, without credentials. */
        public readonly string $error = '',
        /** @var bool Whether trying again can work. */
        public readonly bool $retry = true,
    ) {
    }

    /**
     * Success.
     *
     * @param int $recipients How many received it.
     * @return self
     */
    public static function success(int $recipients): self {
        return new self(true, $recipients);
    }

    /**
     * Failure.
     *
     * @param string $error Error.
     * @param bool $retry Whether trying again can work.
     * @return self
     */
    public static function failure(string $error, bool $retry = true): self {
        return new self(false, 0, $error, $retry);
    }
}
