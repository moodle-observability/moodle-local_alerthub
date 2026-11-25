Configuration
=============

1. **Channels**: add a "Moodle message" channel; choose the recipients by capability or a list of users. Use **Send
   test** to check it.
2. **Rules**: which alerts (source, category, minimum severity) go to which channel, and when (immediately, daily or
   weekly digest).
3. **Message processors**: in *Site administration > Messaging > Notification settings*, enable and lock Telegram or
   Slack only for the alert types of the hub.
4. **Overview**: processors per alert type, e-mail blocked (``$CFG->noemailever``), deliveries and recent failures.
