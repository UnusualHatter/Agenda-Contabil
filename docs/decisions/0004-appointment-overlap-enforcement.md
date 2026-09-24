# ADR 0004 — Conflito de horário garantido na aplicação e no banco

- **Status:** aceito
- **Data:** 2026-09-23
- **Requisitos relacionados:** RF-026, RD-002

## Contexto

Um responsável não pode ter dois atendimentos ativos sobrepostos. Uma checagem
só na aplicação (`SELECT` antes do `INSERT`) falha quando duas pessoas agendam
o mesmo responsável ao mesmo tempo: as duas leituras não encontram conflito e
as duas gravações passam.

## Decisão

Duas camadas, cada uma com um papel:

1. **Aplicação — `EnsureResponsibleIsAvailable`.** Dentro da transação, trava
   a linha do responsável (`lockForUpdate`) e procura sobreposição. É ela que
   produz a mensagem para o usuário: "Maria já tem atendimento das 14:00 às
   15:00…". O lock faz agendamentos simultâneos do mesmo responsável esperarem
   um pelo outro.
2. **Banco — `appointments_no_overlap`.** *Exclusion constraint* com
   `btree_gist` sobre `(responsible_user_id, tsrange(starts_at, ends_at, '[)'))`,
   filtrada por status ativos e `deleted_at IS NULL`. Protege contra qualquer
   caminho que ignore a Action: seeders, tinker, scripts, bugs futuros.

O intervalo semiaberto `[)` faz atendimentos encostados (10:00–11:00 e
11:00–12:00) não serem conflito, como pede o RF-026.

Referência: exemplo de reservas na documentação do PostgreSQL
(*Range Types — Constraints on Ranges*).

## Consequências

- `btree_gist` é uma extensão *trusted* desde o PostgreSQL 13: o dono do banco
  consegue criá-la sem superusuário.
- A lista de status ativos existe em dois lugares: `AppointmentStatus::blocking()`
  e a migration. Um novo status bloqueante exige uma nova migration; o teste
  `AppointmentConflictTest` quebra se as duas divergirem para os status atuais.
- Os testes precisam de PostgreSQL real — já era a regra do projeto.
