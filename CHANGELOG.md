# Changelog

## 0.1.2 - 2026-11-25

- Credits of the Moodle Observability family; first public release.

## 0.1.1 - 2026-11-24

- The overview has shortcuts to channels, rules and alerts and, while there is no channel, explains how to test
  through the web notification, which works even with e-mail disabled.

## 0.1.0 - 2026-11-24

- API `raise()` and `resolve()`, deduplicated by source and key, counting repetitions.
- Rules by source, category and minimum severity, sent immediately or in daily or weekly digests.
- Deliveries recorded, with up to 5 attempts and growing pauses.
- `message` channel (subplugin `alerthubchannel_message`) through the Message API, with the providers "Critical
  alerts", "Warning alerts", "Informative alerts" and "Alerts digest"; recipients by capability or a list of users.
- Channel settings encrypted with `\core\encryption`; overview, alerts, channels and rules pages; Send test.
- Bridge with the `tool_taskmonitor` hook `alert_raised`.
