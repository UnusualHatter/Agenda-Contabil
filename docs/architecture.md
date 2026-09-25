# Arquitetura

> Princípio principal (PRD, seção 33): **a agenda é a interface principal;
> Atendimento é o domínio principal; Laravel/PostgreSQL é a fonte de verdade.**
> Qualquer integração externa depende do domínio local, nunca o contrário.

## Camadas

```
HTTP / Livewire  →  Actions & Services de domínio  →  Models (Eloquent)  →  PostgreSQL
                          ↑
                    Policies / Enums / Data
```

- **Controllers e componentes Livewire** cuidam de entrada, validação de
  formulário e apresentação. Nunca contêm regra de negócio.
- **Actions** (`app/Domain/*/Actions`) executam operações que passam do CRUD
  trivial: criar atendimento, reagendar, cancelar.
- **Queries** (`app/Domain/*/Queries`) encapsulam consultas complexas — agenda,
  relatórios, histórico do atendido.
- **Policies** decidem autorização. Esconder um botão não é autorização.
- **Models** ficam finos: casts, relacionamentos e escopos simples.

## Estrutura de `app/`

Os diretórios são criados **quando existe uma classe real** que os justifique —
nada de pastas vazias só para bater com o desenho.

```
app/
├── Console/Commands/   # ExportPreview (prévia estática), SendDueReminders
├── Domain/
│   ├── Appointments/
│   │   ├── Actions/    # ScheduleAppointment, RescheduleAppointment (horário e/ou
│   │   │               # responsável), ChangeAppointmentStatus,
│   │   │               # MarkDocumentReceived, EnsureResponsibleIsAvailable,
│   │   │               # SendAppointmentReminder
│   │   ├── Enums/      # AppointmentStatus, ActivityType, LocationType
│   │   ├── Queries/    # AgendaAppointments, UpcomingAppointments
│   │   └── ReminderMessage.php  # texto do lembrete (e-mail e WhatsApp)
│   ├── Clients/
│   │   ├── Enums/      # ClientType
│   │   └── Queries/    # ClientHistory, SearchClients
│   ├── Services/Actions/  # SaveService (serviço + checklist)
│   └── Users/Enums/    # UserRole
├── Http/
│   ├── Controllers/    # Agenda, Appointment, Client, Dashboard, ReminderResponse
│   ├── Middleware/     # SecurityHeaders, EnsureUserIsActive, SetDatabaseActor
│   ├── Requests/Agenda # conversão de horário local → UTC na borda
│   └── Resources/      # AgendaEventResource (formato do FullCalendar)
├── Livewire/
│   ├── Appointments/   # CreateAppointment, AppointmentDetails
│   ├── Clients/        # ClientIndex, ClientEditor
│   ├── Services/       # ServiceCatalog
│   └── Forms/          # ClientForm (compartilhado pelas duas telas de cadastro)
├── Models/             # todos os models Eloquent
├── Notifications/      # AppointmentReminder
├── Policies/           # Appointment, Client, Service
├── Rules/              # BrazilianDocument (CPF/CNPJ)
├── Providers/
└── Support/            # DisplayTimezone, BlindIndex
```

**Models ficam em `app/Models`**, na convenção do Laravel: factories, policies e
route model binding funcionam sem configuração extra. `app/Domain` guarda o que
é regra de negócio.

## Onde cada regra mora

| Regra | Lugar |
| --- | --- |
| Transições de status permitidas | `AppointmentStatus::allowedTransitions()` |
| Período válido e conflito de horário | `EnsureResponsibleIsAvailable` + constraint no banco |
| Checklist copiado no agendamento | `ScheduleAppointment` |
| Auditoria | cada Action chama `Appointment::recordActivity()` |
| Quem pode o quê | `app/Policies` |

Todas as Actions lançam `ValidationException` com mensagem em pt-BR quando uma
regra é violada, para que formulários Livewire e a API mostrem o erro no campo
certo sem tradução extra.

## Idioma

- **Interface:** português (pt-BR). Traduções em `lang/pt_BR/` e
  `lang/pt_BR.json`.
- **Código:** inglês. Classes, métodos, tabelas, colunas e comentários.
- O termo de negócio "Atendido" corresponde ao model `Client`.

## Fuso horário

Persistência em UTC, apresentação em `America/Sao_Paulo`, conversão via
`App\Support\DisplayTimezone`. Ver
[ADR 0001](decisions/0001-timezone-strategy.md).

## Autenticação e autorização

Breeze (stack Blade), sem cadastro público e sem auto-exclusão de conta. Papel
único por usuário e flag `active`. Ver
[ADR 0002](decisions/0002-authentication-scope.md).

## Frontend

- Blade + Livewire 4 para formulários e telas interativas. Os nomes das
  propriedades dos componentes são os mesmos das chaves das Actions
  (`service_id`, `starts_at`…), então um erro de regra de negócio aparece no
  campo certo sem mapeamento.
- `resources/js/`: `app.js` inicia Livewire e os módulos; `theme.js` (troca
  de tema), `navigation.js` (indicador de aba e entrada das páginas),
  `scroll.js` (Lenis), `curtain.js` (entrada e saída), `agenda.js`
  (FullCalendar, importado só na página da agenda) e `preview.js` (modo
  prévia estática).
- Tokens de cor, tema e movimento: ver [ADR 0005](decisions/0005-visual-identity-and-theming.md).
- Fuso horário na agenda, Alpine do Livewire e navegação: ver [ADR 0006](decisions/0006-agenda-in-the-browser.md).

## Segurança

- `app/Http/Middleware/SecurityHeaders.php` e `EnsureUserIsActive.php`, no
  grupo `web`.
- Campos sensíveis criptografados nos models; busca de documento por
  `App\Support\BlindIndex`.
- Ver [ADR 0007](decisions/0007-security-and-data-protection.md).

## Prévia estática

`app/Console/Commands/ExportPreview.php` (`php artisan preview:export {url}`)
renderiza as páginas reais com os dados de demonstração para o GitHub Pages;
o workflow `.github/workflows/preview.yml` publica o resultado.

## O que ainda não existe

| Área | Milestone |
| --- | --- |
| Painel de impacto e relatórios | 5 |
| API `/api/v1` com Sanctum | 6 |
| Google Calendar / ICS / Mundy / WhatsApp automático | 7 |

Nenhuma dessas áreas deve ganhar contrato, interface ou tabela antes da hora —
ver a seção 27 do PRD sobre abstração prematura.
