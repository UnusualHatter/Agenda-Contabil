# ADR 0005 — Identidade visual e temas

- **Status:** aceito
- **Data:** 2026-09-23
- **Requisitos relacionados:** PRD seção 23, RNF-007, RNF-008

## Contexto

O visual padrão do Breeze (cinza e índigo, sans-serif) não conversa com o
projeto. A direção pedida é serifada, quente e orgânica, com tema claro e
escuro. O sistema será mantido por turmas diferentes: a solução precisa impedir
que cada tela invente suas próprias cores.

## Decisão

1. **Tokens semânticos em CSS** (`resources/css/app.css`). As cores são
   variáveis em `:root` e `[data-theme='dark']`, expostas ao Tailwind com
   `@theme inline`. Uma classe como `bg-surface` troca de cor sozinha entre os
   temas, sem prefixo `dark:` nas views.
2. **Paleta padrão do Tailwind removida** (`--color-*: initial`). `bg-gray-100`
   ou `text-indigo-600` deixam de existir; só os tokens compilam. Isso mantém
   os dois temas consistentes por construção.
3. **Tema em `data-theme` no `<html>`.** Um script inline no `<head>`
   (`layouts/partials/theme.blade.php`) decide o tema antes do CSS carregar,
   evitando o "flash" do tema errado. A escolha fica em `localStorage`;
   "automático" remove a chave e segue `prefers-color-scheme`. Baseado no
   exemplo de alternância manual da documentação do Tailwind CSS.
4. **Fontes via `@fontsource-variable`** (Fraunces e Literata), empacotadas
   pelo Vite. Nenhum dado do usuário vai para CDN de fontes.
5. **Contraste verificado**: todo par de texto sobre `canvas`, `surface` e
   `sunken` tem pelo menos 4,5:1 nos dois temas.
6. **Tons com par "-soft".** Verde-azulado, musgo, ocre, terracota, rosa,
   ameixa e casca têm cada um uma versão de fundo (`bg-moss-soft`…): clara no
   tema claro, profunda no escuro. Texto no tom sobre o próprio "-soft" fica
   acima de 4,9:1 nos dois temas. Status, blocos do painel, avatares e o
   histórico usam esses pares, nunca opacidade sobre a cor cheia.
7. **Movimento curto e opcional.** Utilitário `press` (escala 0,96 ao clicar),
   troca de páginas com *view transitions* entre documentos (a aba ativa
   desliza no menu), troca de visão da agenda com *fade*, dropdowns que abrem
   com transição de altura e checklist com ✓ desenhado. Tudo desligado com
   `prefers-reduced-motion`.
8. **Troca de tema por *view transition*.** O tema novo aparece num círculo
   que cresce a partir do botão (900 ms), aplicado como `clip-path` no
   snapshot `::view-transition-new(root)`. Técnica descrita por Akash
   Hamirwasia em "Full-page theme toggle animation with View Transitions API".
   Durante a troca, as transições de cor dos elementos ficam desligadas: com
   ~100 botões animando a mesma mudança de cor, a troca travava. Sem suporte
   a view transitions, a troca é instantânea.
9. **Rolagem sem repintura.** Brilho e textura de fundo ficam em camadas fixas
   próprias (sem `background-attachment: fixed`) e o menu fixo não usa
   `backdrop-filter`; os dois forçavam repintar a tela a cada quadro de
   rolagem. `scroll-behavior: smooth` só para âncoras e rolagem por código;
   a rolagem pela roda do mouse continua nativa.

## Consequências

- Cor nova significa token novo nos dois temas — de propósito.
- O plugin `@tailwindcss/forms` ainda traz valores próprios para campos sem
  classe; os componentes `x-text-input` e o checkbox do login os substituem.
- As fontes são divididas por alfabeto (`unicode-range`); o navegador baixa só
  os arquivos latinos usados pela página, cerca de 175 KB em woff2.
- O Livewire 4 embute o próprio Alpine; `resources/js/app.js` usa esse Alpine
  (ver ADR 0006).
