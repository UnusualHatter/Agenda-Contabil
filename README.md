# Agenda e Gestão de Atendimentos

Plataforma de agenda e gestão de atendimentos do projeto de **Sustentabilidade
Econômica e Financeira**: cadastro de atendidos (pessoas físicas e jurídicas),
agendamento por demanda e responsável, histórico e indicadores.

A agenda é a interface principal, mas o domínio central é o **Atendimento**. O
banco PostgreSQL local é a fonte canônica dos dados — integrações com
calendários externos, quando existirem, dependem dele e nunca o contrário.

Requisitos completos: [`PRD.md`](PRD.md).

## Status

**Milestone 0 — bootstrap** concluído: aplicação sobe, autentica e a CI passa.
O domínio (atendidos, serviços, atendimentos) começa no Milestone 1. Ver
[`docs/architecture.md`](docs/architecture.md).

## Requisitos

| Ferramenta | Versão |
| --- | --- |
| PHP | 8.3+ (com `pdo_pgsql`, `pgsql`, `mbstring`, `intl`, `bcmath`, `gd`, `zip`) |
| Composer | 2.x |
| Node.js | 20+ |
| PostgreSQL | 16+ |

Verificação rápida:

```sh
php -v && composer -V && node -v && psql --version
php -m | grep -E 'pdo_pgsql|mbstring|intl'
```

## Setup local

### 1. Banco de dados

```sh
sudo systemctl start postgresql

sudo -u postgres psql -c "CREATE ROLE agenda LOGIN PASSWORD 'agenda' CREATEDB;"
sudo -u postgres createdb -O agenda agenda_contabil
sudo -u postgres createdb -O agenda agenda_contabil_test
```

`agenda_contabil_test` é usado pela suíte de testes (configurado em
`phpunit.xml`) — os testes rodam contra PostgreSQL, não SQLite.

### 2. Aplicação

```sh
git clone <repo> && cd Agenda-Contabil

composer install
npm install

cp .env.example .env
php artisan key:generate

php artisan migrate --seed
npm run build
```

Ajuste `DB_*` no `.env` se usar credenciais diferentes das acima.

### 3. Executar

```sh
composer dev     # servidor + fila + logs + Vite, tudo junto
```

ou, separadamente:

```sh
php artisan serve   # http://localhost:8000
npm run dev
```

## Usuários de desenvolvimento

Criados por `DevelopmentUserSeeder` (`php artisan db:seed`). O seeder é
idempotente e **não roda em produção**.

| E-mail | Senha | Papel |
| --- | --- | --- |
| `admin@agenda.local` | `password` | Administrador |
| `equipe@agenda.local` | `password` | Membro da equipe |
| `visualizador@agenda.local` | `password` | Visualizador |

Não há cadastro público: contas são criadas por administradores
([ADR 0002](docs/decisions/0002-authentication-scope.md)). Em produção, crie o
primeiro administrador via `php artisan tinker`:

```php
App\Models\User::create([
    'name' => 'Nome',
    'email' => 'email@exemplo.com',
    'password' => 'senha-forte',
    'role' => App\Domain\Users\Enums\UserRole::Admin,
]);
```

## Testes e qualidade

```sh
php artisan test          # PHPUnit
vendor/bin/pint           # formata (Laravel preset)
vendor/bin/pint --test    # apenas verifica — é o que a CI roda
npm run build             # build de produção do frontend
```

A CI (`.github/workflows/ci.yml`) sobe um PostgreSQL, instala dependências,
compila o frontend, checa formatação, roda migrations e a suíte de testes a
cada push em `main`/`develop` e a cada pull request.

## Arquitetura resumida

```
HTTP / Livewire  →  Actions de domínio  →  Models  →  PostgreSQL
                          ↑
                  Policies / Enums / Queries
```

- `app/Domain/` — regras de negócio por área (`Users` hoje; `Appointments`,
  `Clients`, `Services`, `Reporting`, `Calendar` chegam nos próximos
  milestones).
- `app/Http/` — controllers, form requests e resources.
- `app/Livewire/` — componentes interativos (a partir do Milestone 2).
- `app/Support/` — utilidades transversais, como `DisplayTimezone`.
- `docs/` — arquitetura, banco e ADRs.

**Idioma:** interface em pt-BR, código em inglês. **Fuso:** persistência em
UTC, exibição em `America/Sao_Paulo`
([ADR 0001](docs/decisions/0001-timezone-strategy.md)).

## Documentação

- [`PRD.md`](PRD.md) — requisitos, regras de domínio e milestones
- [`docs/architecture.md`](docs/architecture.md) — estrutura do código
- [`docs/database.md`](docs/database.md) — esquema atual e planejado
- [`docs/decisions/`](docs/decisions/) — ADRs
- [`CONTRIBUTING.md`](CONTRIBUTING.md) — branches, commits, Definition of Done
# Agenda-Contabil
