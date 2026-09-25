# Tecnologias e referências

Tudo o que o projeto usa, e onde cada escolha foi apoiada. As decisões com
contexto e consequências estão nos ADRs em [`decisions/`](decisions/).

## Back-end

| Tecnologia | Versão | Uso |
| --- | --- | --- |
| PHP | 8.4 (CI) / 8.5 (dev) | Linguagem |
| Laravel | 13 | Framework: rotas, Eloquent, validação, policies, filas, criptografia |
| Livewire | 4 | Formulários e telas interativas sem API separada; navegação sem recarregar (`wire:navigate`) |
| Laravel Breeze (Blade) | 2 | Base da autenticação (login, recuperação de senha, perfil) |
| laravel-lang/common | 6 | Traduções pt-BR de validação, autenticação e paginação |
| PostgreSQL | 18 | Banco de dados; `jsonb` na auditoria e extensão `btree_gist` para a restrição de conflito de horário |
| PHPUnit | 12 | Testes (contra PostgreSQL real, não SQLite) |
| Laravel Pint | 1 | Formatação (preset `laravel`), verificada na CI |

## Front-end

| Tecnologia | Versão | Uso |
| --- | --- | --- |
| Vite + laravel-vite-plugin | 8 / 3 | Build dos assets; a agenda é um pacote separado, baixado só na página dela |
| Tailwind CSS | 4 | Estilos, com a paleta padrão removida e tokens próprios de cor |
| @tailwindcss/forms | 0.5 | Base dos campos de formulário |
| Alpine.js (embutido no Livewire) | 3 | Dropdowns, seletor de tema, estados de formulário |
| FullCalendar | 6.1 | Agenda: visões mês, semana, 3 dias, dia e lista; arrastar para reagendar |
| Lenis | 1.3 | Rolagem suave sobre a rolagem nativa do navegador |
| Fontsource: Fraunces e Literata | 5 | Fontes serifadas empacotadas no build, sem CDN externa |

## Recursos da plataforma web

- **View Transitions API** — troca de tema com círculo que se expande a partir
  do botão.
- **Web Animations API** — indicador de aba deslizante e entrada das páginas.
- **CSS container queries** — cards da agenda que escondem detalhes quando
  ficam estreitos.
- **`clip-path`, `color-mix()`, `:has()`, `inert`, `scrollbar-gutter`** —
  cortinas de entrada/saída, tons de status, conteúdo fechado fora do Tab.
- **`prefers-color-scheme` e `prefers-reduced-motion`** — tema automático e
  animações desligadas para quem pede menos movimento.

## Infraestrutura

- **GitHub Actions** — CI (`ci.yml`: Pint, migrations e testes) e prévia
  (`preview.yml`: `actions/configure-pages`, `actions/upload-pages-artifact`,
  `actions/deploy-pages`).
- **GitHub Pages** — prévia estática gerada por `php artisan preview:export`.

## Referências

**Código de projetos mantidos por pessoas, usado como base de estilo**

- laravel.io — [github.com/laravelio/laravel.io](https://github.com/laravelio/laravel.io):
  classes de ação com um único `handle()`, classes de consulta, enums com
  `match`, notificações e componentes Livewire enxutos.
- Easy!Appointments — [Appointments_model.php](https://github.com/alextselegidis/easyappointments/blob/master/application/models/Appointments_model.php):
  regra de sobreposição de horários.
- Monica CRM — [notificações](https://laraveldaily.com/code-examples/example/monicahq-monica/notifications):
  lembretes agendados (base para o Milestone 4).

**Documentação**

- PostgreSQL — [Range Types: constraints on ranges](https://www.postgresql.org/docs/current/rangetypes.html#RANGETYPES-CONSTRAINT)
  (restrição de exclusão para reservas),
  [Trigger functions em PL/pgSQL](https://www.postgresql.org/docs/current/plpgsql-trigger.html),
  [GRANT](https://www.postgresql.org/docs/current/sql-grant.html),
  [ALTER DEFAULT PRIVILEGES](https://www.postgresql.org/docs/current/sql-alterdefaultprivileges.html),
  [Índices parciais](https://www.postgresql.org/docs/current/indexes-partial.html).
- CPF e CNPJ — cálculo dos dígitos verificadores (módulo 11) conforme as
  regras da Receita Federal.
- Laravel — [Encryption](https://laravel.com/docs/encryption),
  [Vite: CSP nonce](https://laravel.com/docs/vite#content-security-policy-csp-nonce),
  [Rate limiting](https://laravel.com/docs/rate-limiting),
  [Pessimistic locking](https://laravel.com/docs/queries#pessimistic-locking),
  [Password validation](https://laravel.com/docs/validation#validating-passwords),
  [Notifications](https://laravel.com/docs/notifications),
  [Signed URLs](https://laravel.com/docs/urls#signed-urls),
  [Task scheduling](https://laravel.com/docs/scheduling).
- WhatsApp — [Click to chat (`wa.me`)](https://faq.whatsapp.com/5913398998672934).
- Livewire — [Navigate](https://livewire.laravel.com/docs/navigate),
  [Form objects](https://livewire.laravel.com/docs/forms),
  [Bundling Livewire and Alpine](https://livewire.laravel.com/docs/installation#manually-bundling-livewire-and-alpine).
- FullCalendar — [timeZone](https://fullcalendar.io/docs/timeZone),
  [eventContent](https://fullcalendar.io/docs/event-render-hooks),
  [custom views](https://fullcalendar.io/docs/custom-view-with-settings).
- Tailwind CSS — [Dark mode: toggling manually](https://tailwindcss.com/docs/dark-mode#toggling-dark-mode-manually),
  [Theme variables](https://tailwindcss.com/docs/theme).
- Lenis — [github.com/darkroomengineering/lenis](https://github.com/darkroomengineering/lenis).
- MDN — [View Transition API](https://developer.mozilla.org/docs/Web/API/View_Transition_API),
  [Content-Security-Policy](https://developer.mozilla.org/docs/Web/HTTP/Headers/Content-Security-Policy),
  [Container queries](https://developer.mozilla.org/docs/Web/CSS/CSS_containment/Container_queries).

**Artigos**

- Akash Hamirwasia — [Full-page theme toggle animation with View Transitions API](https://akashhamirwasia.com/blog/full-page-theme-toggle-animation-with-view-transitions-api/).
- CSS-Tricks — [7 View Transitions Recipes to Try](https://css-tricks.com/7-view-transitions-recipes-to-try/).
- Ian K Duffy — [Creating a theme switcher using View Transition](https://iankduffy.com/articles/creating-a-theme-switcher-using-view-transition).
- Gabriel Anhaia — [Enums as State Machines in PHP 8.3](https://dev.to/gabrielanhaia/enums-as-state-machines-modeling-order-status-the-right-way-in-php-83-4pd0).
- Paragon Initiative — [Building Searchable Encrypted Databases with PHP and SQL](https://paragonie.com/blog/2017/05/building-searchable-encrypted-databases-with-php-and-sql).

**Normas**

- WCAG 2.1 — [contraste mínimo 4,5:1](https://www.w3.org/TR/WCAG21/#contrast-minimum),
  usado para validar a paleta nos dois temas.
- LGPD — [Lei nº 13.709/2018](https://www.planalto.gov.br/ccivil_03/_ato2015-2018/2018/lei/l13709.htm):
  minimização de dados, consentimento para lembretes, proteção em repouso.
