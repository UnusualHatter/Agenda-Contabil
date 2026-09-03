# PRD — Sustentabilidade Econômica e Financeira
## Agenda e Gestão de Atendimentos

**Status:** Draft v1
**Stack principal:** PHP + Laravel + PostgreSQL
**Idioma da interface:** Português (pt-BR)
**Objetivo:** iniciar um repositório organizado, testável e preparado para crescer para web, API e integrações de agenda.

---

## 1. Visão do produto

O produto é uma plataforma de agenda e gestão de atendimentos para o projeto de Sustentabilidade Econômica e Financeira.

A agenda é a interface central, mas o conceito principal do domínio é **Atendimento**.

O sistema deve permitir que a equipe:

- cadastre pessoas físicas e jurídicas;
- agende atendimentos;
- classifique demandas e serviços;
- atribua responsáveis;
- acompanhe status e histórico;
- consulte atendimentos anteriores;
- gere indicadores e relatórios;
- opere de forma responsiva no desktop e celular;
- futuramente sincronize eventos com calendários externos, incluindo Google Calendar, Outlook, ICS/CalDAV e, se tecnicamente possível, Mundy.

O sistema **não** deve depender de Mundy ou de qualquer calendário externo para funcionar. O banco local é a fonte canônica dos dados.

## 2. Objetivo do MVP

Entregar uma aplicação web funcional que permita realizar o fluxo:

1. autenticar usuário;
2. cadastrar ou localizar um atendido;
3. criar um atendimento;
4. selecionar data e horário;
5. selecionar demanda/serviço;
6. atribuir responsável;
7. visualizar o atendimento na agenda;
8. alterar status, reagendar ou cancelar;
9. consultar histórico do atendido;
10. consultar indicadores básicos e relatórios.

## 3. Usuários do sistema

### 3.1 Administrador

Pode: gerenciar usuários; gerenciar permissões; cadastrar e editar serviços/categorias; acessar todos os atendimentos; acessar relatórios; editar configurações; gerenciar integrações.

### 3.2 Membro da equipe

Pode: consultar agenda; criar e editar atendimentos; cadastrar atendidos; consultar histórico; atualizar status; acessar relatórios permitidos.

### 3.3 Visualizador

Pode: consultar agenda; consultar atendimentos autorizados; consultar relatórios autorizados; **não** pode excluir ou alterar dados sensíveis.

O sistema deve usar autorização por Policies/Gates e não apenas esconder botões no frontend.

## 4. Escopo funcional

### 4.1 Autenticação

- **RF-001** — O sistema deve permitir login com e-mail e senha.
- **RF-002** — O sistema deve permitir logout.
- **RF-003** — O sistema deve possuir recuperação de senha.
- **RF-004** — O sistema deve impedir acesso às áreas privadas sem autenticação.

## 5. Atendidos

O termo de domínio preferencial é **Atendido**. Internamente, o model pode ser `Client`, desde que a interface utilize "Atendido".

- **RF-010 — Cadastro de atendidos.** Campos mínimos: nome / razão social; tipo (Pessoa Física ou Pessoa Jurídica); telefone; e-mail; documento opcional; nome fantasia opcional; observações opcionais; ativo/inativo.
- **RF-011 — Pesquisa.** Permitir pesquisar por nome, razão social, telefone, e-mail e documento (quando informado).
- **RF-012 — Histórico.** A tela do atendido deve listar seus atendimentos anteriores e futuros.
- **RF-013 — Reutilização.** Ao criar um novo atendimento, deve ser possível selecionar um atendido já existente sem duplicar seus dados.

## 6. Agenda e atendimentos

- **RF-020 — Criar atendimento.** Obrigatórios: atendido; data; horário inicial; horário final; demanda/serviço; responsável; status. Opcionais: localização; tipo/local de atendimento; observações.
- **RF-021 — Dia da semana.** Derivado automaticamente da data. Não armazenar como fonte de verdade no banco.
- **RF-022 — Visualização da agenda.** Visões mensal, semanal e diária. Utilizar FullCalendar ou solução compatível.
- **RF-023 — Reagendamento.** Alterar data e horário mantendo o histórico do registro.
- **RF-024 — Cancelamento.** Não apaga o registro; o status passa a `cancelled`.
- **RF-025 — Exclusão.** Exclusão física não disponível para usuários comuns. Preferir soft delete ou manter registros de auditoria.
- **RF-026 — Conflito de horários.** Impedir conflito para o mesmo responsável quando os períodos se sobrepõem:

  ```
  novo_inicio < atendimento_existente_fim
  AND
  novo_fim    > atendimento_existente_inicio
  ```

  Atendimentos cancelados não bloqueiam horário.
- **RF-027 — Status.** Valores iniciais (enum PHP): `scheduled` (Agendado), `confirmed` (Confirmado), `in_progress` (Em atendimento), `completed` (Concluído), `cancelled` (Cancelado), `no_show` (Não compareceu).
- **RF-028 — Responsável.** Cada atendimento possui um responsável principal. A arquitetura deve permitir múltiplos participantes no futuro sem reescrita completa.

## 7. Demandas e serviços

As demandas devem ser administráveis pelo sistema. Não codificar permanentemente todas as opções em enums.

**Categorias iniciais:**

- **Fiscal** — declaração de imposto de renda; IRPF; pessoa jurídica; MEI; emissão de guias para pagamento de tributos; outras informações fiscais.
- **Finanças pessoais** — fluxo de caixa; planejamento financeiro; orçamento familiar e pessoal; outras demandas pessoais.
- **Empresarial** — fluxo de caixa; planejamento financeiro; formação do preço de venda; cálculo de custos; orçamento empresarial; mapeamento de processos; outras informações financeiras e societárias.
- **Terceiro setor / ONG** — prestação de contas; elaboração de demonstrações contábeis; relatório de responsabilidade social; outras demandas do terceiro setor.
- **Outros** — deve existir uma opção que permita descrição livre.

- **RF-030** — Administrador deve poder criar, editar e desativar categorias e serviços. Registros históricos devem continuar exibindo serviços desativados.

## 8. Dashboard

- **RF-040** — Após login, mostrar: atendimentos de hoje; próximos atendimentos; total de atendimentos no período; quantidade por status; quantidade PF × PJ; principais demandas.

Não criar dashboard excessivamente complexo no primeiro milestone.

## 9. Relatórios

- **RF-050 — Filtros mínimos:** período; responsável; tipo de atendido; categoria; serviço; status.
- **RF-051 — Indicadores mínimos:** total de atendimentos; concluídos; cancelados; não comparecimentos; quantidade de PF; quantidade de PJ; quantidade de MEIs (quando identificável pela classificação); quantidade de ONGs/terceiro setor; demandas mais frequentes; atendimentos por responsável.
- **RF-052** — Preparar arquitetura para exportação futura em CSV, XLSX e PDF. A exportação não precisa estar pronta no primeiro commit.

## 10. Auditoria

- **RF-060** — Registrar pelo menos: quem criou o atendimento; quem alterou; quando foi criado; quando foi alterado.
- **RF-061** — Mudanças relevantes devem possuir histórico: criação; mudança de horário; mudança de status; troca de responsável; cancelamento.

Implementação sugerida: tabela `appointment_activities` ou biblioteca de activity log confiável.

## 11. Integrações de calendário

As integrações não fazem parte do primeiro milestone funcional, mas a arquitetura deve estar preparada desde o início. A entidade `Appointment` local é **sempre** a fonte canônica.

**Providers planejados:** Google Calendar; Outlook Calendar; ICS; CalDAV; Mundy (caso possua API, CalDAV, ICS ou mecanismo compatível).

**Interface obrigatória:**

```php
interface CalendarProvider
{
    public function createEvent(Appointment $appointment): ExternalEventReference;

    public function updateEvent(
        Appointment $appointment,
        ExternalEventReference $reference
    ): ExternalEventReference;

    public function deleteEvent(
        Appointment $appointment,
        ExternalEventReference $reference
    ): void;
}
```

Não implementar providers falsos apenas para "completar" a estrutura. Pode haver um `NullCalendarProvider` apenas se houver um uso concreto e testado.

**Estrutura planejada:**

- `calendar_connections`: `id`, `user_id`, `provider`, `external_calendar_id`, `credentials_encrypted`, `sync_cursor`, `last_synced_at`, timestamps.
- `external_event_links`: `id`, `appointment_id`, `calendar_connection_id`, `external_event_id`, `external_updated_at`, `last_synced_at`, timestamps.

Tokens nunca devem ser armazenados em texto puro.

## 12. Requisitos não funcionais

- **RNF-001 — Responsividade.** Desktop, tablet e smartphone. O MVP é web responsivo; app Android nativo/híbrido fica fora do escopo inicial.
- **RNF-002 — Idioma.** UI em pt-BR. Código, classes, métodos, nomes de tabela e documentação técnica em inglês consistente.
- **RNF-003 — Banco de dados.** PostgreSQL.
- **RNF-004 — Timezone.** Padrão `America/Sao_Paulo`. Preferência: persistir em UTC e apresentar em `America/Sao_Paulo`. Documentar a decisão.
- **RNF-005 — Testes.** Fluxos críticos com testes automatizados. Prioridade: conflito de horários; autorização; criação de atendimento; reagendamento; cancelamento; filtros de relatório.
- **RNF-006 — Segurança.** CSRF habilitado; validação server-side; prepared queries via ORM; rate limiting onde aplicável; passwords com hashing padrão Laravel; dados sensíveis nunca em logs; secrets apenas em `.env`; `.env` nunca versionado.
- **RNF-007 — LGPD.** Minimização de dados. Não armazenar dados fiscais, documentos ou informações financeiras desnecessários ao objetivo operacional. Preparar: controle de acesso; auditoria; política de retenção; anonimização/remoção quando aplicável.
- **RNF-008 — Acessibilidade.** HTML semântico; labels em formulários; navegação por teclado; contraste legível; mensagens de validação compreensíveis.

## 13. Stack técnica

**Backend e Web:** PHP 8.4+; Laravel 13 ou versão estável disponível; Laravel Livewire; Blade; Tailwind CSS.

**Admin:** usar Filament somente onde reduzir significativamente boilerplate. Não duplicar telas entre Filament e aplicação principal sem necessidade.

**Agenda:** FullCalendar; adapter próprio para transformar `Appointment` em eventos do calendário.

**Banco:** PostgreSQL.

**API:** REST em `/api/v1`; Laravel Sanctum; resources JSON; Form Requests; Policies. Criada de maneira incremental.

**Cache/Queues:** Redis é recomendado, mas não obrigatório no desenvolvimento local inicial. Usar database queue inicialmente se isso simplificar onboarding.

## 14. Arquitetura de software

Laravel convencional com separação de domínio suficiente para evitar controllers gigantes. Evitar "Clean Architecture" cerimonial e excesso de abstrações.

```
app/
├── Domain/
│   ├── Appointments/
│   │   ├── Actions/  Data/  Enums/  Events/
│   │   ├── Exceptions/  Models/  Policies/
│   │   ├── Queries/  Services/
│   ├── Clients/
│   │   ├── Actions/  Data/  Models/  Policies/  Queries/
│   ├── Services/
│   │   ├── Models/  Queries/
│   ├── Reporting/
│   │   ├── Queries/  Data/
│   └── Calendar/
│       ├── Contracts/  Data/  Models/  Providers/
├── Http/
│   ├── Controllers/{Api/V1, Web}
│   ├── Requests/
│   └── Resources/
├── Livewire/{Agenda, Appointments, Clients, Dashboard}
├── Models/User.php
├── Providers/
└── Support/
```

**Observação:** não criar diretórios vazios apenas para corresponder ao desenho. Criar cada diretório quando existir uma classe real que justifique sua existência.

## 15. Estrutura do repositório

```
/
├── app/  bootstrap/  config/
├── database/{factories, migrations, seeders}
├── docs/{architecture.md, database.md, decisions/}
├── public/
├── resources/{css, js/calendar, views}
├── routes/{web.php, api.php, console.php}
├── storage/
├── tests/
│   ├── Feature/{Appointments, Auth, Clients, Reports}
│   └── Unit/
├── .editorconfig  .env.example  .gitignore
├── CLAUDE.md  CONTRIBUTING.md  PRD.md  README.md
├── composer.json  package.json  phpunit.xml
```

## 16. Modelo de dados inicial

### `users`

Model padrão do Laravel acrescido de `role` e `active`. Preferencialmente implementar papéis de maneira simples no MVP. Caso seja utilizada uma biblioteca de permissions, justificar em ADR.

### `clients`

```
id                  bigint
type                enum/string: individual | organization
name                varchar
legal_name          nullable
trade_name          nullable
document            nullable
phone               nullable
email               nullable
notes               nullable text
active              boolean default true
created_at / updated_at / deleted_at nullable
```

Criar índices apropriados para busca.

### `service_categories`

```
id, name, slug, description nullable, active boolean, sort_order integer, timestamps
```

### `services`

```
id, service_category_id, name, slug, description nullable,
active boolean, sort_order integer, timestamps
```

### `appointments`

```
id
client_id
service_id
responsible_user_id
starts_at timestamp
ends_at   timestamp
status
location_type nullable
location      nullable
notes         nullable
created_by
updated_by nullable
created_at / updated_at / deleted_at nullable
```

**Constraints:** `ends_at > starts_at`; FKs obrigatórias; índices em `starts_at`, `responsible_user_id`, `status`; índice composto útil para consultas da agenda.

### `appointment_activities`

```
id, appointment_id, user_id nullable, event_type,
old_values jsonb nullable, new_values jsonb nullable, created_at
```

### `calendar_connections` e `external_event_links`

Criar somente quando a primeira integração for implementada.

## 17. Seed inicial de serviços

Criar seeders **idempotentes**.

**Categorias:** Fiscal; Finanças Pessoais; Empresarial; Terceiro Setor; Outros.

**Serviços iniciais:** Fluxo de caixa; Planejamento financeiro; Declaração de imposto de renda; IRPF; Atendimento MEI; Emissão de guias para pagamento de tributos; Prestação de contas para entidades do terceiro setor; Formação do preço de venda; Cálculo de custos; Orçamento familiar e pessoal; Orçamento empresarial; Mapeamento de processos; Elaboração de demonstrações contábeis; Elaboração de relatório de responsabilidade social; Atendimento externo; Outros.

Não duplicar "Orçamento familiar e pessoal" ou "Orçamento empresarial" caso apareçam repetidos na fonte original.

## 18. Rotas web planejadas

```
GET /dashboard
GET /agenda

GET /atendidos
GET /atendidos/create
GET /atendidos/{client}
GET /atendidos/{client}/edit

GET /atendimentos
GET /atendimentos/create
GET /atendimentos/{appointment}
GET /atendimentos/{appointment}/edit

GET /relatorios
GET /configuracoes
```

As ações podem ser Livewire e não precisam mapear 1:1 para controllers.

## 19. API planejada

Prefixo `/api/v1`. Endpoints mínimos futuros:

```
GET    /appointments
POST   /appointments
GET    /appointments/{id}
PATCH  /appointments/{id}
POST   /appointments/{id}/cancel

GET    /clients
POST   /clients
GET    /clients/{id}
PATCH  /clients/{id}

GET    /services
```

Aplicar: API Resources; Form Requests; Policies; paginação; filtros previsíveis; status HTTP corretos.

## 20. Regras de domínio

- **RD-001** — `ends_at` precisa ser posterior a `starts_at`.
- **RD-002** — Um usuário responsável não pode possuir dois atendimentos ativos com horários sobrepostos. Statuses ativos para conflito: `scheduled`, `confirmed`, `in_progress`.
- **RD-003** — Atendimento cancelado permanece no banco.
- **RD-004** — Serviço desativado não pode ser selecionado para novo atendimento, mas continua aparecendo no histórico.
- **RD-005** — Atendido inativo permanece associado ao histórico.
- **RD-006** — Mudanças críticas geram `appointment_activity`.
- **RD-007** — O cliente não deve ser excluído fisicamente se possuir histórico de atendimento.

## 21. UX mínima

**Agenda.** Cada evento deve mostrar horário; nome do atendido; serviço ou categoria; status de forma visual. Ao clicar, abrir detalhes do atendimento.

**Formulário de atendimento.** Ordem sugerida: Atendido; Serviço; Data; Horário inicial; Horário final; Responsável; Tipo/local; Observações; Status.

A busca de atendido deve ser rápida. Incluir ação "Cadastrar novo atendido" sem abandonar o fluxo de criação do atendimento.

## 22. Página de atendido

Mostrar: nome; tipo; telefone; e-mail; dados institucionais quando PJ; observações; próximos atendimentos; histórico de atendimentos.

Não exibir dados sensíveis desnecessariamente em listas.

## 23. Design

Visual limpo, institucional, moderno, acessível, sem excesso de animações. Não tentar reproduzir literalmente o material impresso. Usar a identidade visual posteriormente, quando os assets oficiais estiverem disponíveis.

## 24. Out of scope do MVP

Não implementar no primeiro milestone: aplicativo Android; sincronização Mundy; Google Calendar; Outlook; CalDAV; pagamento; chat; videoconferência; CRM completo; armazenamento de documentos fiscais; automações complexas; IA; BI avançado; multi-tenant.

A arquitetura não deve impedir essas evoluções.

## 25. Milestones

### Milestone 0 — Bootstrap do repositório

**Objetivo:** projeto executa localmente e CI passa.

**Entregáveis:** Laravel instalado; PostgreSQL configurado; `.env.example`; README; autenticação; formatter/linter; test runner; CI; estrutura inicial; seed de usuário de desenvolvimento documentado.

**Aceite:**

```sh
composer install
npm install
php artisan migrate --seed
npm run build
php artisan test
```

### Milestone 1 — Domínio principal

**Entregáveis:** `Client`; `ServiceCategory`; `Service`; `Appointment`; enums; migrations; factories; seeders; policies; conflito de horários; testes.

**Aceite:** é possível cadastrar atendido e atendimento pelo backend/testes.

### Milestone 2 — Interface operacional

**Entregáveis:** dashboard básico; CRUD de atendidos; CRUD de atendimentos; busca; filtros; validação; histórico do atendido.

**Aceite:** usuário consegue realizar o fluxo operacional sem usar banco/CLI.

### Milestone 3 — Agenda

**Entregáveis:** FullCalendar; month/week/day; criação a partir de slot; edição; reagendamento; filtros por responsável/status; detalhes.

**Aceite:** agenda reflete corretamente registros do banco e respeita autorização.

### Milestone 4 — Relatórios e auditoria

**Entregáveis:** indicadores; filtros; activity log; relatório por período; por categoria; por responsável.

### Milestone 5 — API

**Entregáveis:** Sanctum; `/api/v1`; endpoints essenciais; OpenAPI ou documentação equivalente; feature tests. Preparar para futuro Android app.

### Milestone 6 — Integrações

Somente iniciar após confirmação de provider. Primeiro provider recomendado: Google Calendar. Mundy só deve ser implementado após confirmar oficialmente um mecanismo suportado.

## 26. Estratégia de testes

Utilizar PHPUnit **ou** Pest de forma consistente. Não misturar estilos sem necessidade.

**Testes obrigatórios:**

- `AppointmentConflictTest` — cria quando não há conflito; bloqueia sobreposição parcial; bloqueia evento dentro de outro; bloqueia evento que contém outro; permite evento adjacente; permite conflito entre responsáveis diferentes; ignora cancelados.
- `AppointmentAuthorizationTest` — usuário autorizado visualiza; usuário sem permissão não edita; visualizador não cancela.
- `AppointmentLifecycleTest` — criação; confirmação; conclusão; cancelamento; reagendamento.
- `ClientHistoryTest` — histórico lista somente atendimentos corretos; ordenação correta; serviços desativados continuam visíveis.

## 27. Convenções de código

- PSR-12;
- `declare(strict_types=1);` em código de domínio quando apropriado;
- Laravel Pint;
- tipos de retorno;
- Form Requests para validação de HTTP;
- Policies para autorização;
- Actions para operações de domínio que ultrapassem CRUD trivial;
- queries complexas encapsuladas;
- evitar lógica de negócio em Blade;
- evitar lógica grande em controllers;
- evitar repositories genéricos em cima de Eloquent;
- evitar interfaces sem necessidade;
- evitar abstração prematura.

## 28. Convenções Git

```
main
develop
feature/*
fix/*
chore/*
```

Se o projeto for pequeno, `develop` pode ser omitida.

Commits: preferir Conventional Commits.

```
feat: add appointment scheduling
fix: prevent overlapping appointments
test: cover appointment conflict rules
docs: document calendar integration strategy
```

## 29. CI

GitHub Actions. Pipeline mínimo: checkout; PHP setup; Node setup; `composer install`; `npm ci`; build frontend; Laravel Pint check; migrations em banco de teste; testes.

Não adicionar deploy automático no início.

## 30. README inicial

Deve conter: visão geral; requisitos; setup local; PostgreSQL; `.env`; migrations; seed; execução; testes; build frontend; arquitetura resumida; link para `PRD.md`.

## 31. CLAUDE.md esperado

Curto, referenciando este PRD. Ver o arquivo `CLAUDE.md` na raiz.

## 32. Definition of Done

Uma feature só está concluída se:

- comportamento requerido foi implementado;
- validações existem;
- autorização foi considerada;
- testes relevantes passam;
- formatter passa;
- migrations são reversíveis quando razoável;
- sem secrets versionados;
- documentação foi atualizada quando necessário;
- não há TODO crítico ocultando funcionalidade quebrada.

## 33. Critérios de aceite do MVP

O MVP estará funcional quando: usuário consegue autenticar; cadastrar PF; cadastrar PJ; buscar atendido existente; criar atendimento; data e horário armazenados corretamente; dia da semana derivado automaticamente; selecionar demanda/serviço; atribuir responsável; conflitos de horário bloqueados; visualizar agenda dia/semana/mês; reagendar; cancelar; histórico do atendido funciona; filtros principais funcionam; dashboard apresenta indicadores básicos; permissões impedem operações indevidas; auditoria registra mudanças críticas; testes críticos passam; aplicação funciona em desktop e mobile browser; projeto sobe seguindo apenas o README.

## 34. Instrução inicial para Claude Code

Execute **somente o Milestone 0 primeiro**. Não tente implementar todo o PRD de uma vez.

Antes de modificar arquivos: examine o repositório; informe a stack/estado atual encontrados; compare com este PRD; crie um plano curto para o Milestone 0; implemente o bootstrap; execute testes e formatter; apresente resumo das alterações; **pare antes do Milestone 1**.

Caso o repositório esteja vazio: inicialize Laravel; configure PostgreSQL; configure autenticação; configure Livewire; configure ferramentas de qualidade; crie documentação; configure CI; confirme que a aplicação sobe e os testes passam.

Não adicionar integração Mundy, Google Calendar ou Android neste momento.

## 35. Princípio arquitetural principal

> **A agenda é a interface principal; Atendimento é o domínio principal; Laravel/PostgreSQL é a fonte de verdade.**

Qualquer integração externa deve depender do domínio local, nunca o contrário.
