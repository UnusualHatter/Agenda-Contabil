# ADR 0006 — Agenda no navegador: fuso horário e Alpine

- **Status:** aceito
- **Data:** 2026-09-23
- **Requisitos relacionados:** RF-021, RF-022, RF-023, ADR 0001

## Contexto

O FullCalendar interpreta datas no fuso do navegador por padrão. Um voluntário
com o computador em outro fuso, ou um celular com horário automático errado,
veria os atendimentos deslocados. Suportar fusos nomeados no FullCalendar exige
um plugin extra (Luxon ou Moment).

## Decisão

1. **"UTC-coercion".** O servidor envia horários de parede de São Paulo sem
   offset (`2026-10-05T14:00:00`) e o calendário roda com `timeZone: 'UTC'`.
   Nada é convertido no navegador: 14:00 é desenhado às 14:00 para qualquer
   pessoa. O que volta do calendário (clique em horário, arrastar) também é
   horário de parede, convertido para UTC no servidor, em
   `AgendaEventsRequest` e `RescheduleAppointmentRequest` — a mesma borda do
   ADR 0001. Técnica descrita na documentação do FullCalendar sobre
   `timeZone`.
2. **Um Alpine só.** O Livewire 4 embute o Alpine. `resources/js/app.js`
   importa `Alpine` e `Livewire` de `livewire.esm` e os layouts usam
   `@livewireScriptConfig`; o pacote `alpinejs` foi removido. Duas instâncias
   do Alpine na mesma página quebram `x-data` de forma silenciosa.
3. **Arrastar usa HTTP simples, não Livewire.** `PATCH
   /atendimentos/{id}/horario` chama a mesma `RescheduleAppointment` das
   telas. Em conflito, a resposta 422 traz a mensagem da regra e o evento
   volta para o lugar.

## Consequências

- O projeto assume um único fuso de operação (`APP_DISPLAY_TIMEZONE`). Se um
  dia houver atendimentos em fusos diferentes, esta decisão precisa ser
  revista junto com o ADR 0001.
- Testes de `AgendaTest` fixam os dois lados da conversão (resposta em horário
  local; intervalo pedido lido como horário local).
