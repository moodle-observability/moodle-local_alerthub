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
 * Strings of local_alerthub (pt_br).
 *
 * @package    local_alerthub
 * @copyright  2026 Moodle Observability contributors
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['activealerts'] = '{$a} avisos ativos';
$string['activechannels'] = '{$a} canais habilitados';
$string['activerules'] = '{$a} regras habilitadas';
$string['addchannel'] = 'Adicionar canal';
$string['addchanneltype'] = 'Adicionar canal: {$a}';
$string['addrule'] = 'Adicionar regra';
$string['alert'] = 'Aviso';
$string['alerthub:manage'] = 'Gerenciar o hub de avisos';
$string['alerthub:receive'] = 'Receber avisos do hub de avisos';
$string['alerts'] = 'Avisos';
$string['any'] = 'Qualquer';
$string['category'] = 'Categoria';
$string['category_help'] = 'Categoria informada pela origem, como externaldependency (tool_extdeps) ou cron (tool_taskmonitor). Vazio para qualquer categoria.';
$string['channel'] = 'Canal';
$string['channels'] = 'Canais';
$string['channeltype'] = 'Tipo de canal';
$string['confirmdeletechannel'] = 'Excluir o canal "{$a}", com as suas regras e entregas pendentes?';
$string['confirmdeleterule'] = 'Excluir a regra "{$a}"?';
$string['deleted'] = 'Excluído.';
$string['delivery_failed'] = 'Com falha';
$string['delivery_pending'] = 'Pendentes';
$string['delivery_sent'] = 'Enviadas';
$string['digestday'] = 'Dia da semana';
$string['digesthour'] = 'Hora do resumo';
$string['digestintro'] = '{$a} avisos ativos ou alterados desde o resumo anterior:';
$string['digestsubject'] = 'Resumo de avisos: {$a} avisos';
$string['editchannel'] = 'Editar canal';
$string['editrule'] = 'Editar regra';
$string['enabled'] = 'Habilitado';
$string['errorchanneltype'] = 'O tipo de canal não está instalado.';
$string['gettingstarted'] = 'Para testar: adicione um canal "Mensagem do Moodle" com você na lista de usuários e clique em "Enviar teste" nele. O aviso chega no sino (notificação web), mesmo com o e-mail desligado. Depois crie regras para escolher quais avisos vão para o canal.';
$string['last'] = 'Último';
$string['messageprovider:alert_critical'] = 'Avisos críticos';
$string['messageprovider:alert_info'] = 'Avisos informativos';
$string['messageprovider:alert_warning'] = 'Avisos de atenção';
$string['messageprovider:digest'] = 'Resumo de avisos';
$string['minseverity'] = 'Severidade mínima';
$string['mode'] = 'Quando enviar';
$string['mode_daily'] = 'Resumo diário';
$string['mode_immediate'] = 'Na hora';
$string['mode_weekly'] = 'Resumo semanal';
$string['noalerts'] = 'Nenhum aviso.';
$string['nochannels'] = 'Nenhum canal ainda. Adicione um canal antes das regras.';
$string['noemailever'] = 'O e-mail está desligado neste site ($CFG->noemailever): o processador de e-mail não entrega nada. A notificação web (o sino) continua funcionando.';
$string['norules'] = 'Nenhuma regra ainda: nenhum aviso é enviado.';
$string['notificationsettings'] = 'Configurações de notificação';
$string['notifyresolved'] = 'Enviar também quando o aviso for resolvido';
$string['occurrences'] = 'Vezes';
$string['open'] = 'Abrir';
$string['overview'] = 'Visão geral';
$string['pluginname'] = 'Hub de avisos';
$string['privacy:metadata:core_message'] = 'Os avisos são sobre o site, e não sobre pessoas, e são enviados aos destinatários pela Message API.';
$string['processors'] = 'Processadores';
$string['processorsperprovider'] = 'Processadores de mensagem por tipo de aviso';
$string['processorsperprovider_desc'] = 'Verde: habilitado por padrão. Cadeado: o usuário não pode mudar. Restrinja o Telegram ou o Slack a estes tipos nas configurações de notificação.';
$string['provider'] = 'Tipo de aviso';
$string['recentfailures'] = 'Falhas recentes';
$string['repeated'] = 'Disparado {$a} vezes enquanto ativo.';
$string['retention'] = 'Retenção (dias)';
$string['retention_desc'] = 'Entregas enviadas e avisos resolvidos mais antigos que isto são removidos.';
$string['rules'] = 'Regras';
$string['saved'] = 'Salvo.';
$string['sendtest'] = 'Enviar teste';
$string['settings'] = 'Configurações';
$string['severity'] = 'Severidade';
$string['severity_critical'] = 'Crítico';
$string['severity_info'] = 'Informação';
$string['severity_warning'] = 'Atenção';
$string['since'] = 'Desde';
$string['source'] = 'Origem';
$string['state_raised'] = 'Ativos';
$string['state_resolved'] = 'Resolvidos';
$string['subjectraised'] = '[{$a->severity}] {$a->title}';
$string['subjectresolved'] = '[Resolvido] {$a->title}';
$string['subplugintype_alerthubchannel'] = 'Canal';
$string['subplugintype_alerthubchannel_plural'] = 'Canais';
$string['task_cleanup'] = 'Remover avisos e entregas antigos';
$string['task_digest'] = 'Montar os resumos de avisos';
$string['task_dispatch'] = 'Enviar os avisos pendentes';
$string['testbody'] = 'Teste enviado pelo canal "{$a}". Se você recebeu, o canal funciona.';
$string['testfailed'] = 'O teste não foi enviado: {$a}';
$string['testsent'] = 'Teste enviado a {$a} destinatários.';
$string['testtitle'] = 'Teste do hub de avisos';
$string['type_alerthubchannel'] = 'Canal';
$string['type_alerthubchannel_plural'] = 'Canais';
