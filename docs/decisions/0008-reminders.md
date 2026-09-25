# ADR 0008 — Lembretes e confirmação pelo atendido

- **Status:** aceito
- **Data:** 2026-09-25
- **Requisitos relacionados:** RF-070 a RF-074, RNF-007

## Contexto

Faltas ocupam horários que outras pessoas poderiam usar, e atendimentos
fiscais sem os documentos certos precisam ser remarcados. O lembrete precisa
chegar antes, dizer o que trazer e deixar o atendido responder sem criar
conta.

## Decisão

1. **Só com consentimento.** `clients.accepts_reminders` nasce desmarcado; o
   lembrete (e-mail ou WhatsApp) só é oferecido quando ele está marcado.
2. **Um envio automático por atendimento.** O comando
   `appointments:send-reminders` roda a cada 15 minutos e pega atendimentos
   abertos que começam nas próximas 24 horas. O envio é "reivindicado" com um
   `UPDATE … WHERE reminder_sent_at IS NULL`: se duas execuções se
   sobrepuserem, só uma consegue marcar e enviar. A equipe pode reenviar à mão.
3. **Link assinado que expira no início do atendimento**
   (`URL::temporarySignedRoute`). Alterar qualquer parte do link invalida a
   assinatura.
4. **Abrir o link não muda nada.** Antivírus e prévias de e-mail abrem links
   sozinhos; a página mostra o atendimento e só um POST (botão) confirma ou
   cancela. As transições seguem as mesmas regras de status da equipe.
5. **Autor nulo = atendido.** A mudança feita pelo link entra no histórico
   sem usuário e aparece como "pelo atendido, no link do lembrete".
6. **Mesmo texto no e-mail e no WhatsApp** (`ReminderMessage`). O WhatsApp é
   manual (`wa.me` abre a conversa com a mensagem pronta), sem API paga.

## Consequências

- Produção precisa do cron do agendador, de um worker de fila e do `APP_URL`
  correto (ver README).
- O link revela ao portador data, serviço e documentos do atendimento; por
  isso expira no horário marcado e a página não mostra contato nem CPF/CNPJ.
- WhatsApp automático (RF-075) continua fora até existir conta Business.
