Configuração
============

1. **Canais**: adicione um canal "Mensagem do Moodle" e escolha os destinatários pela permissão ou por uma lista de
   usuários. Use **Enviar teste** para conferir.
2. **Regras**: quais avisos (origem, categoria, severidade mínima) vão para qual canal, e quando (na hora, resumo
   diário ou semanal).
3. **Processadores de mensagem**: em *Administração do site > Mensagens > Configurações de notificação*, habilite e
   trave o Telegram ou o Slack só nos tipos de aviso do hub.
4. **Visão geral**: processadores por tipo de aviso, e-mail bloqueado (``$CFG->noemailever``), entregas e falhas
   recentes.
