moodle-local_alerthub
=====================

**Hub de avisos do Moodle.**

Um lugar único para onde os plugins mandam seus avisos. O hub evita mensagens repetidas, escolhe quem recebe o quê,
envia resumos e registra cada entrega.

.. note::

   Versão 0.1.2 (alfa).

Recursos
--------

* **Deduplicação**: disparar de novo um aviso ativo só conta a repetição; um aviso resolvido que volta é entregue de
  novo.
* **Regras**: por origem, categoria e severidade mínima; na hora ou em resumo diário ou semanal; mensagem opcional
  quando o aviso é resolvido.
* **Message API**: o canal ``message`` entrega com ``message_send``, então cada usuário recebe pelos processadores
  habilitados nos tipos de aviso do hub (notificação web, e-mail, Telegram, Slack).
* **Destinatários**: usuários com ``local/alerthub:receive`` no contexto do aviso (por exemplo, os professores de um
  curso) ou uma lista fixa, como usuários de serviço cujo chat do Telegram é o grupo da equipe.
* **Confiabilidade**: entregas registradas, com novas tentativas; nada é enviado na requisição que disparou o aviso;
  nenhuma tarefa adhoc.
* **Canais como subplugins** (``alerthubchannel``) para destinos que não são mensagem.
* **Moodle Observability**: os avisos do ``tool_taskmonitor`` (cron e tarefas) passam pelo hub.

Requisitos
----------

* Moodle 4.5 ou 5.3 (LTS).

.. toctree::
   :maxdepth: 1

   installation
   configuration
   usage
