# Banco de dados

- **SGBD:** PostgreSQL 18 (RNF-003). É a fonte canônica de todos os dados,
  inclusive dos que um dia forem espelhados em calendários externos.
- **Timezone da aplicação:** `UTC` para persistência,
  `America/Sao_Paulo` para exibição — ver
  [ADR 0001](decisions/0001-timezone-strategy.md).
- **Bancos:** `agenda_contabil` (desenvolvimento) e `agenda_contabil_test`
  (suíte de testes, configurado em `phpunit.xml`).

## Estado atual (Milestone 1)

| Tabela | Origem | Observação |
| --- | --- | --- |
| `users` | Laravel + projeto | Acrescidas as colunas `role` e `active`. |
| `password_reset_tokens` | Laravel | Recuperação de senha (RF-003). |
| `sessions` | Laravel | `SESSION_DRIVER=database`. |
| `cache`, `cache_locks` | Laravel | `CACHE_STORE=database`. |
| `jobs`, `job_batches`, `failed_jobs` | Laravel | `QUEUE_CONNECTION=database`. |
| `clients` | Projeto | Atendidos PF (`individual`) ou PJ (`organization`). Soft delete. CPF/CNPJ e observações criptografados. |
| `service_categories`, `services` | Projeto | Catálogo administrável; desativar em vez de apagar (RD-004). |
| `service_documents` | Projeto | Checklist de documentos de cada serviço (RF-031). |
| `appointments` | Projeto | Atendimentos. Soft delete. |
| `appointment_documents` | Projeto | Cópia do checklist no agendamento (RF-032). |
| `appointment_activities` | Projeto | Auditoria append-only, `jsonb` (RF-061). |

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

Índice adicional: `(active, name)`, para as listas de responsáveis.

### `clients`

`name` guarda o nome (PF) ou a razão social (PJ); `trade_name` o nome fantasia.
A coluna `legal_name` do rascunho do PRD foi descartada por duplicar `name`.
`accepts_reminders` nasce `false`: lembrete só com consentimento (RNF-007).
`document` e `notes` são criptografados (cast `encrypted`); `document_index`
guarda o HMAC dos dígitos do documento para a busca exata
([ADR 0007](decisions/0007-security-and-data-protection.md)). Índices em
`name`, `document_index`, `email` e `phone` para a busca (RF-011).

### `services`

`requires_details` marca serviços como "Outros", que exigem descrição livre em
`appointments.service_details`. `default_duration_minutes` alimenta a duração
sugerida no formulário (PRD, seção 21).

### `appointments`

`status` é o enum `AppointmentStatus`; `location_type` o enum `LocationType`.
Não existe coluna de dia da semana: ele é derivado de `starts_at` no fuso de
exibição (RF-021). `notes` e `service_details` são criptografados. Índices:
`starts_at`, `status`, `(responsible_user_id, starts_at)` e
`(client_id, starts_at)`.

## Planejado (não implementado)

- `appointments.reminder_sent_at` — Milestone 4 (lembretes).
- `calendar_connections` e `external_event_links` — **somente** quando a
  primeira integração real for implementada (Milestone 7).

## Regras garantidas pelo banco

- `appointments_period_check`: `ends_at > starts_at` (RD-001).
- `appointments_no_overlap`: *exclusion constraint* com `btree_gist` que impede
  dois atendimentos ativos sobrepostos para o mesmo responsável (RD-002). Ver
  [ADR 0004](decisions/0004-appointment-overlap-enforcement.md).
- Chaves estrangeiras com `restrictOnDelete` para atendido, serviço e
  responsável: ninguém some do histórico por acidente (RD-005, RD-007).

## Comandos úteis

```sh
php artisan migrate            # aplica migrations
php artisan migrate:fresh --seed
php artisan db:show            # inspeciona a conexão
php artisan db:table users
```
