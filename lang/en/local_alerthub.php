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

/**
 * Strings of local_alerthub (en).
 *
 * @package    local_alerthub
 * @copyright  2026 Moodle Observability contributors
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['activealerts'] = '{$a} active alerts';
$string['activechannels'] = '{$a} enabled channels';
$string['activerules'] = '{$a} enabled rules';
$string['addchannel'] = 'Add channel';
$string['addchanneltype'] = 'Add channel: {$a}';
$string['addrule'] = 'Add rule';
$string['alert'] = 'Alert';
$string['alerthub:manage'] = 'Manage the alert hub';
$string['alerthub:receive'] = 'Receive alerts of the alert hub';
$string['alerts'] = 'Alerts';
$string['any'] = 'Any';
$string['category'] = 'Category';
$string['category_help'] = 'Category given by the source, such as externaldependency (tool_extdeps) or cron (tool_taskmonitor). Empty for any category.';
$string['channel'] = 'Channel';
$string['channels'] = 'Channels';
$string['channeltype'] = 'Channel type';
$string['confirmdeletechannel'] = 'Delete the channel "{$a}" with its rules and pending deliveries?';
$string['confirmdeleterule'] = 'Delete the rule "{$a}"?';
$string['deleted'] = 'Deleted.';
$string['delivery_failed'] = 'Failed';
$string['delivery_pending'] = 'Pending';
$string['delivery_sent'] = 'Sent';
$string['digestday'] = 'Day of the week';
$string['digesthour'] = 'Time of the digest';
$string['digestintro'] = '{$a} alerts active or changed since the previous digest:';
$string['digestsubject'] = 'Alerts digest: {$a} alerts';
$string['editchannel'] = 'Edit channel';
$string['editrule'] = 'Edit rule';
$string['enabled'] = 'Enabled';
$string['errorchanneltype'] = 'The channel type is not installed.';
$string['gettingstarted'] = 'To test: add a "Moodle message" channel with you in the list of users and click "Send test" on it. The alert arrives in the bell (web notification), also with e-mail disabled. Then add rules to choose which alerts go to the channel.';
$string['last'] = 'Last';
$string['messageprovider:alert_critical'] = 'Critical alerts';
$string['messageprovider:alert_info'] = 'Informative alerts';
$string['messageprovider:alert_warning'] = 'Warning alerts';
$string['messageprovider:digest'] = 'Alerts digest';
$string['minseverity'] = 'Minimum severity';
$string['mode'] = 'When to send';
$string['mode_daily'] = 'Daily digest';
$string['mode_immediate'] = 'Immediately';
$string['mode_weekly'] = 'Weekly digest';
$string['noalerts'] = 'No alert.';
$string['nochannels'] = 'No channel yet. Add a channel before the rules.';
$string['noemailever'] = 'E-mail is disabled on this site ($CFG->noemailever): the e-mail processor delivers nothing. The web notification (the bell) still works.';
$string['norules'] = 'No rule yet: no alert is sent.';
$string['notificationsettings'] = 'Notification settings';
$string['notifyresolved'] = 'Also send when the alert is resolved';
$string['occurrences'] = 'Times';
$string['open'] = 'Open';
$string['overview'] = 'Overview';
$string['pluginname'] = 'Alert hub';
$string['privacy:metadata:core_message'] = 'The alerts are about the site, not about people, and are sent to the recipients through the Message API.';
$string['processors'] = 'Processors';
$string['processorsperprovider'] = 'Message processors per alert type';
$string['processorsperprovider_desc'] = 'Green: enabled by default. Lock: users cannot change it. Restrict Telegram or Slack to these types in the notification settings.';
$string['provider'] = 'Alert type';
$string['recentfailures'] = 'Recent failures';
$string['repeated'] = 'Raised {$a} times while active.';
$string['retention'] = 'Retention (days)';
$string['retention_desc'] = 'Sent deliveries and resolved alerts older than this are removed.';
$string['rules'] = 'Rules';
$string['saved'] = 'Saved.';
$string['sendtest'] = 'Send test';
$string['settings'] = 'Settings';
$string['severity'] = 'Severity';
$string['severity_critical'] = 'Critical';
$string['severity_info'] = 'Information';
$string['severity_warning'] = 'Warning';
$string['since'] = 'Since';
$string['source'] = 'Source';
$string['state_raised'] = 'Active';
$string['state_resolved'] = 'Resolved';
$string['subjectraised'] = '[{$a->severity}] {$a->title}';
$string['subjectresolved'] = '[Resolved] {$a->title}';
$string['subplugintype_alerthubchannel'] = 'Channel';
$string['subplugintype_alerthubchannel_plural'] = 'Channels';
$string['task_cleanup'] = 'Remove old alerts and deliveries';
$string['task_digest'] = 'Build the alert digests';
$string['task_dispatch'] = 'Send the pending alerts';
$string['testbody'] = 'Test sent through the channel "{$a}". If you received it, the channel works.';
$string['testfailed'] = 'The test was not sent: {$a}';
$string['testsent'] = 'Test sent to {$a} recipients.';
$string['testtitle'] = 'Alert hub test';
$string['type_alerthubchannel'] = 'Channel';
$string['type_alerthubchannel_plural'] = 'Channels';
