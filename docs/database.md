# Banco de dados

- **SGBD:** PostgreSQL 18 (RNF-003). É a fonte canônica de todos os dados,
  inclusive dos que um dia forem espelhados em calendários externos.
- **Timezone da aplicação:** `UTC` para persistência,
  `America/Sao_Paulo` para exibição — ver
  [ADR 0001](decisions/0001-timezone-strategy.md).
- **Bancos:** `agenda_contabil` (desenvolvimento) e `agenda_contabil_test`
  (suíte de testes, configurado em `phpunit.xml`).

## Estado atual (Milestone 0)

| Tabela | Origem | Observação |
| --- | --- | --- |
| `users` | Laravel + projeto | Acrescidas as colunas `role` e `active`. |
| `password_reset_tokens` | Laravel | Recuperação de senha (RF-003). |
| `sessions` | Laravel | `SESSION_DRIVER=database`. |
| `cache`, `cache_locks` | Laravel | `CACHE_STORE=database`. |
| `jobs`, `job_batches`, `failed_jobs` | Laravel | `QUEUE_CONNECTION=database`. |

### `users`

| Coluna | Tipo | Notas |
| --- | --- | --- |
| `id` | bigserial | |
| `name` | varchar | |
| `email` | varchar unique | Login (RF-001). |
| `email_verified_at` | timestamp null | |
| `password` | varchar | Hash padrão do Laravel (RNF-006). |
| `role` | varchar | `admin` \| `member` \| `viewer` — enum `UserRole`. |
| `active` | boolean | `false` impede login; preserva o histórico. |
| `remember_token` | varchar null | |
| `created_at` / `updated_at` | timestamp | UTC. |

Índice adicional: `(active, name)`, para as futuras listas de responsáveis.

## Planejado (não implementado)

As tabelas abaixo estão especificadas na seção 16 do PRD e entram no Milestone
1 em diante. Não criar antes da feature que as usa.

- `clients` — atendidos, PF (`individual`) ou PJ (`organization`), com
  `deleted_at` (soft delete) e índices de busca por nome, telefone e e-mail.
- `service_categories` e `services` — demandas administráveis; registros
  desativados continuam visíveis no histórico (RD-004).
- `appointments` — `client_id`, `service_id`, `responsible_user_id`,
  `starts_at`, `ends_at`, `status`, `created_by`, `updated_by`, `deleted_at`.
  Constraint `ends_at > starts_at` (RD-001) e índices em `starts_at`,
  `responsible_user_id` e `status`.
- `appointment_activities` — auditoria com `old_values`/`new_values` em
  `jsonb` (RF-061).
- `calendar_connections` e `external_event_links` — **somente** quando a
  primeira integração real for implementada (Milestone 6).

## Regras que o banco precisa garantir

- `ends_at > starts_at` — constraint de verificação, não apenas validação.
- Sobreposição de horário para o mesmo responsável (RD-002) é verificada na
  camada de domínio e coberta por testes; a decisão sobre uma
  `EXCLUDE USING gist` fica para o Milestone 1, quando a tabela existir.
- Atendido com histórico não pode ser removido fisicamente (RD-007).

## Comandos úteis

```sh
php artisan migrate            # aplica migrations
php artisan migrate:fresh --seed
php artisan db:show            # inspeciona a conexão
php artisan db:table users
```
