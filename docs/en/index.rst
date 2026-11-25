moodle-local_alerthub
=====================

**Alert hub for Moodle.**

One place where plugins send their alerts. The hub avoids repeated messages, chooses who receives what, sends
digests and keeps a record of every delivery.

.. note::

   Version 0.1.2 (alpha).

Features
--------

* **Deduplication**: raising an active alert again only counts it; a resolved alert that comes back is delivered
  again.
* **Rules**: by source, category and minimum severity; immediately or in daily or weekly digests; optional message
  when the alert is resolved.
* **Message API**: the ``message`` channel delivers with ``message_send``, so each user receives by the processors
  enabled for the alert types of the hub (web notification, e-mail, Telegram, Slack).
* **Recipients**: users with ``local/alerthub:receive`` in the context of the alert (for example the teachers of a
  course), or a fixed list, such as service users whose Telegram chat is the team group.
* **Reliability**: deliveries recorded with retries; nothing is sent during the request that raised the alert; no ad
  hoc tasks.
* **Channels as subplugins** (``alerthubchannel``) for destinations that are not messages.
* **Moodle Observability**: alerts of ``tool_taskmonitor`` (cron and tasks) go through the hub.

Requirements
------------

* Moodle 4.5 or 5.3 (LTS).

.. toctree::
   :maxdepth: 1

   installation
   configuration
   usage
