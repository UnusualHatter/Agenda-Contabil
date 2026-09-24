# Agenda e Gestão de Atendimentos

Plataforma de agenda e gestão de atendimentos do projeto de **Sustentabilidade
Econômica e Financeira**: cadastro de atendidos (pessoas físicas e jurídicas),
agendamento por demanda e responsável, histórico e indicadores.

A agenda é a interface principal, mas o domínio central é o **Atendimento**. O
banco PostgreSQL local é a fonte canônica dos dados — integrações com
calendários externos, quando existirem, dependem dele e nunca o contrário.

Requisitos completos: [`PRD.md`](PRD.md).

## Status

**Milestones 1 a 3** concluídos:

- domínio: atendidos, catálogo de serviços com checklist de documentos,
  conflito de horário garantido também pelo banco, status e auditoria;
- telas: painel, atendidos, atendimentos com checklist e histórico, detalhes
  que abrem no próprio lugar;
- agenda com FullCalendar (mês, semana, 3 dias, dia e lista): clicar para
  agendar, arrastar para reagendar;
- identidade visual serifada com tema claro/escuro, navegação sem recarregar
  a página e proteção de dados pessoais (ver *Segurança* abaixo).

Próximos passos: lembretes (Milestone 4) e painel de impacto (Milestone 5).
Ver [`docs/architecture.md`](docs/architecture.md).

Em desenvolvimento, `php artisan migrate:fresh --seed` também cria uma agenda
de demonstração com duas semanas de atendimentos ao redor da data atual
(`DemoAgendaSeeder`, nunca roda em produção).

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
    'password' => 'troque-esta-senha-2026',
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

- `app/Domain/` — regras de negócio: Actions, Enums e Queries de
  `Appointments`, `Clients` e `Users`.
- `app/Models/` — models Eloquent; `app/Policies/` — quem pode o quê.
- `app/Http/` — controllers finos, form requests, resources e middlewares de
  segurança.
- `app/Livewire/` — formulários e telas interativas.
- `app/Support/` — `DisplayTimezone` (UTC ↔ São Paulo) e `BlindIndex`.
- `resources/js/` — tema, navegação, agenda (carregada só na página dela),
  cortinas de entrada/saída e modo prévia.
- `docs/` — arquitetura, banco, ADRs e tecnologias utilizadas.

**Idioma:** interface em pt-BR, código em inglês. **Fuso:** persistência em
UTC, exibição em `America/Sao_Paulo`
([ADR 0001](docs/decisions/0001-timezone-strategy.md)).

## Segurança e proteção de dados

- CPF/CNPJ e observações livres criptografados no banco; o documento continua
  localizável pela busca exata através de um índice HMAC.
- Cabeçalhos de segurança em todas as respostas (CSP com nonce, anti-iframe,
  `nosniff`, HSTS em HTTPS) e páginas logadas sem cache no navegador.
- Sessão criptografada, com 60 minutos e encerrada ao fechar o navegador;
  contas desativadas perdem a sessão no próximo clique.
- Senhas com no mínimo 10 caracteres, letras e números; em produção, senhas
  que aparecem em vazamentos conhecidos são recusadas.
- Limite de tentativas no login e nas rotas da agenda.

Detalhes em [ADR 0007](docs/decisions/0007-security-and-data-protection.md).
Em produção, defina `APP_ENV=production`, `APP_DEBUG=false` e
`SESSION_SECURE_COOKIE=true`, e sirva a aplicação apenas por HTTPS.

## Prévia no GitHub Pages

O GitHub Pages hospeda apenas arquivos estáticos, então a prévia é gerada pela
própria aplicação: `php artisan preview:export` renderiza as páginas reais
(mesmas rotas, views e assets) com os dados de demonstração e grava HTML
estático em `build/preview/`. Navegação, agenda, detalhes, tema e animações
funcionam; ações que gravam dados ficam desativadas e um aviso informa isso.
O comando se recusa a rodar em produção, para que dados reais nunca sejam
publicados.

**Publicar:** em *Settings → Pages → Build and deployment*, escolha a fonte
**GitHub Actions**. A cada push em `main`, o workflow
[`preview.yml`](.github/workflows/preview.yml) gera e publica a prévia em
`https://<usuário>.github.io/<repositório>/`. Na prévia, o formulário de
login já vem preenchido com a conta de demonstração.

**Gerar localmente:**

```sh
php artisan migrate:fresh --seed
npm run build
php artisan preview:export http://127.0.0.1:8090
python3 -m http.server 8090 -d build/preview
```

## Documentação

- [`PRD.md`](PRD.md) — requisitos, regras de domínio e milestones
- [`docs/architecture.md`](docs/architecture.md) — estrutura do código
- [`docs/database.md`](docs/database.md) — esquema atual e planejado
- [`docs/decisions/`](docs/decisions/) — ADRs
- [`docs/tecnologias-e-referencias.md`](docs/tecnologias-e-referencias.md) —
  bibliotecas, técnicas e referências utilizadas
- [`CONTRIBUTING.md`](CONTRIBUTING.md) — branches, commits, Definition of Done
