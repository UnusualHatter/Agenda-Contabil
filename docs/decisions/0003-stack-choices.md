# ADR 0003 — Escolhas de stack no bootstrap

- **Status:** aceito
- **Data:** 2026-09-03
- **Requisitos relacionados:** seção 13 do PRD

## Decisão

| Item | Escolha | Motivo |
| --- | --- | --- |
| Framework | Laravel 13 | Versão estável no momento do bootstrap. |
| PHP | ^8.3 (8.5 em dev, 8.4 na CI) | Constraint do skeleton do Laravel 13. |
| Banco | PostgreSQL 18 | RNF-003; `jsonb` para auditoria (RF-061). |
| Auth | Breeze, stack **Blade** | Controllers e views explícitos, fáceis de traduzir e testar. |
| UI interativa | Livewire 4 (classes) | Componentes em `app/Livewire/*`, como no PRD (seção 14). |
| CSS | Tailwind 4 (`@tailwindcss/vite`) | Já vem no skeleton do Laravel 13. |
| Testes | PHPUnit | Padrão do skeleton; um estilo só (RNF-005). |
| Formatter | Laravel Pint | Seção 27 do PRD. |

### Nota sobre o Tailwind

O `breeze:install` rebaixa o projeto para Tailwind 3 + PostCSS (ele ainda gera
`tailwind.config.js`, `postcss.config.js` e `autoprefixer`), deixando o
`@tailwindcss/vite` 4 do skeleton instalado e sem uso. O bootstrap desfez esse
rebaixamento: a configuração voltou para Tailwind 4 via plugin do Vite, com o
tema em `resources/css/app.css` (`@import`, `@plugin`, `@source`, `@theme`) e
sem arquivos de configuração JS.

Efeito colateral conhecido: no Tailwind 4 os utilitários `shadow-*` e
`rounded-*` deslocaram uma posição na escala, então os componentes do Breeze
têm sombras levemente mais fortes que o padrão original. Como o visual será
refeito quando a identidade oficial chegar (PRD, seção 23), não vale ajustar
agora.

## Alternativas descartadas

- **Breeze com stack Livewire/Volt.** Volt usa componentes funcionais em
  arquivos Blade, o que conflita com a estrutura `app/Livewire/<Área>/` do PRD
  e prenderia o projeto ao Livewire 3.
- **Filament no bootstrap.** O PRD só o autoriza onde reduzir boilerplate de
  forma significativa. Ainda não há CRUD para justificar a dependência.
- **Redis obrigatório.** `CACHE_STORE`/`QUEUE_CONNECTION` usam `database` para
  simplificar o onboarding (seção 13 do PRD).

## Consequências

- Livewire está instalado mas ainda sem componentes: o primeiro entra no
  Milestone 2.
- Os testes rodam contra PostgreSQL (não SQLite em memória) para garantir
  paridade com produção em `jsonb`, constraints e comparações de intervalo.
