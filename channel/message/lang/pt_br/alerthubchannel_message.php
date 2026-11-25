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
 * Strings of alerthubchannel_message (pt_br).
 *
 * @package    alerthubchannel_message
 * @copyright  2026 Moodle Observability contributors
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['bycapability'] = 'Usuários com a permissão de receber avisos no contexto do aviso';
$string['byusers'] = 'Estes usuários';
$string['norecipients'] = 'Nenhum destinatário: ninguém tem a permissão local/alerthub:receive no contexto, ou a lista está vazia.';
$string['pluginname'] = 'Mensagem do Moodle';
$string['privacy:metadata:core_message'] = 'Os avisos são enviados aos destinatários pela Message API.';
$string['recipients'] = 'Destinatários';
$string['recipients_help'] = 'Pela permissão, os avisos de um curso vão para quem tem local/alerthub:receive nele (por padrão, os professores editores) e os do site para quem a tem no contexto do sistema. Pela lista, vão para esses usuários: use usuários de serviço (autenticação "Sem login") cujo Telegram ou Slack seja o grupo da equipe.';
