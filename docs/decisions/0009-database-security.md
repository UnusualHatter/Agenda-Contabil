# ADR 0009 — Segurança no banco: triggers, papéis e privilégios

- **Status:** aceito
- **Data:** 2026-09-25
- **Requisitos relacionados:** RNF-006, RNF-007, RF-025, RF-060, RF-061

## Contexto

As regras de integridade e o controle de acesso viviam só na aplicação. A
aplicação conectava ao PostgreSQL com o usuário dono do banco: uma falha no
código, um script de manutenção ou uma credencial vazada podiam apagar o
histórico de atendimentos ou tabelas inteiras.

## Decisão

### Constraints antes de triggers

O que uma constraint resolve continua sendo constraint: período válido
(`CHECK`), conflito de horário (`EXCLUDE`, ADR 0004), chaves estrangeiras e,
agora, CPF/CNPJ único entre atendidos ativos (índice único parcial sobre o
índice cego do documento).

### Triggers onde a constraint não alcança

| Trigger | Tabela | Evento | Efeito |
| --- | --- | --- | --- |
| `appointment_activities_append_only` | `appointment_activities` | `BEFORE UPDATE OR DELETE`, `TRUNCATE` | O histórico só recebe inclusões. |
| `appointments_keep_rows` | `appointments` | `BEFORE DELETE` | Atendimento é cancelado ou excluído logicamente, nunca apagado (RF-025). |
| `clients_audit`, `users_audit` | `clients`, `users` | `AFTER INSERT OR UPDATE OR DELETE` | Registra em `data_audits` a operação (`TG_OP`), as colunas alteradas (comparando `OLD` e `NEW`), o usuário da aplicação e o papel do banco. |
| `data_audits_append_only` | `data_audits` | `BEFORE UPDATE OR DELETE`, `TRUNCATE` | A trilha de auditoria também é só de inclusão. |

- A auditoria guarda **nomes de colunas**, não valores: copiar valores
  duplicaria dado pessoal fora da criptografia.
- O usuário da aplicação chega ao banco pelo middleware `SetDatabaseActor`
  (`set_config('app.user_id', …)`); o trigger lê com
  `current_setting('app.user_id', true)`.
- **Sem recursão:** o trigger de auditoria grava em `data_audits`, que não tem
  trigger de auditoria; os triggers de bloqueio só lançam exceção.
- As funções usam `CREATE OR REPLACE`, porque `migrate:fresh` apaga tabelas e
  views, mas não funções.

### Papéis com privilégio mínimo (DCL)

`database/sql/privileges.sql` cria os papéis e aplica `GRANT`/`REVOKE`:

| Papel | Uso | Pode | Não pode |
| --- | --- | --- | --- |
| dono (ex.: `agenda`) | migrations | DDL | — |
| `agenda_app` | aplicação | ler e gravar dados | DDL, desligar triggers, apagar atendidos/atendimentos/usuários, alterar ou apagar histórico e auditoria, alterar `migrations` |
| `agenda_reports` | relatórios externos | `SELECT` na view `appointment_facts` | ler qualquer tabela com dado pessoal |

`ALTER DEFAULT PRIVILEGES` estende a regra às tabelas de migrations futuras.
Os privilégios de banco e de schema também foram retirados de `PUBLIC`.

O script foi executado duas vezes num banco de teste e verificado papel a
papel (21 casos permitidos/negados) e com a aplicação inteira rodando como
`agenda_app` (login, telas, agendamento, cancelamento e auditoria).

### O que não se aplica

- **Cursores:** nenhuma rotina processa linhas uma a uma dentro do banco. A
  futura política de retenção (anonimizar atendidos inativos há anos, RNF-007)
  é a candidata natural a um `FOR registro IN SELECT … LOOP` em PL/pgSQL.
- **Sinônimos:** o PostgreSQL não tem `SYNONYM`; o equivalente é o
  `search_path`. Com um único schema, não há o que apelidar.

## Consequências

- Em produção, a aplicação usa `agenda_app` e o deploy roda as migrations com
  o dono (`DB_USERNAME=<dono> php artisan migrate --force`).
- Correções administrativas que exigiriam apagar histórico passam a ser feitas
  por novos registros, não por edição — é essa a intenção.
- Toda tabela nova com dado pessoal deve ganhar o trigger de auditoria na
  mesma migration que a cria.
