<img src="docs/logo/logo.svg" alt="moodle-local_alerthub logo" width="96" align="right" />

# moodle-local_alerthub

[![Moodle Plugin CI](https://github.com/moodle-observability/moodle-local_alerthub/actions/workflows/ci.yml/badge.svg)](https://github.com/moodle-observability/moodle-local_alerthub/actions/workflows/ci.yml)
[![Documentation](https://github.com/moodle-observability/moodle-local_alerthub/actions/workflows/docs.yml/badge.svg)](https://github.com/moodle-observability/moodle-local_alerthub/actions/workflows/docs.yml)

Alert hub for Moodle (`local_alerthub`). Other plugins raise and resolve alerts; the hub deduplicates them, routes them by source, category and severity, builds daily and weekly digests and records every delivery. Wherever possible the delivery goes through the Moodle Message API, so the web notification, e-mail and message processors such as [moodle-message_telegrambot](https://github.com/moodle-by-kelsoncm/moodle-message_telegrambot) are reused.

**Status:** 0.1.2 (alpha). Download it from the [releases](https://github.com/moodle-observability/moodle-local_alerthub/releases).

Documentation (English and Brazilian Portuguese): https://moodle-observability.github.io/moodle-local_alerthub/

Requirements: Moodle 4.5 or 5.3 (LTS).

Quick start: install the plugin in `local/alerthub` (`public/local/alerthub` on Moodle 5.1 and later) and upgrade Moodle. Then open *Site administration > Plugins > Local plugins > Alert hub > Overview*.

Part of [Moodle Observability](https://github.com/moodle-observability). Contributions: see the [contributing guide](https://github.com/moodle-observability/.github/blob/main/CONTRIBUTING.md).

License: [GPL-3.0](LICENSE).
