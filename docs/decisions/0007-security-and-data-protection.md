# ADR 0007 — Segurança e proteção de dados

- **Status:** aceito
- **Data:** 2026-09-24
- **Requisitos relacionados:** RNF-006, RNF-007 (LGPD)

## Contexto

O sistema guarda dados pessoais (nome, contato, CPF/CNPJ) e anotações que
costumam descrever a situação financeira de quem é atendido. Ele será usado em
computadores compartilhados da universidade, por equipes que mudam a cada
semestre, e terá uma prévia pública no GitHub Pages.

## Decisão

1. **Criptografia em repouso.** `clients.document`, `clients.notes`,
   `appointments.notes` e `appointments.service_details` usam o cast
   `encrypted` do Laravel (AES-256 com a `APP_KEY`). Um vazamento do banco ou
   de um backup não expõe esses campos.
2. **Índice cego para o documento.** `clients.document_index` guarda um
   HMAC-SHA256 dos dígitos do CPF/CNPJ (`App\Support\BlindIndex`). A busca por
   documento passa a ser exata (número completo) e o valor nunca aparece no
   banco em claro. Técnica descrita pela Paragon Initiative em "Building
   Searchable Encrypted Databases with PHP and SQL".
3. **Cabeçalhos em todas as respostas** (`SecurityHeaders`): CSP com nonce
   por requisição para scripts inline, `frame-ancestors 'none'`,
   `X-Content-Type-Options: nosniff`, `Referrer-Policy: same-origin`,
   `Permissions-Policy` restritiva, HSTS quando a requisição é HTTPS e
   `X-Robots-Tag: noindex`. A CSP mantém `unsafe-eval` porque o Alpine do
   Livewire avalia as expressões `x-*` com `new Function`; scripts inline
   injetados continuam bloqueados pelo nonce.
4. **Sem cache de páginas logadas** (`Cache-Control: no-store`): em um
   computador compartilhado, "voltar" depois do logout não mostra dados.
5. **Sessão:** criptografada, 60 minutos, encerrada ao fechar o navegador;
   cookie `HttpOnly` e `SameSite=Lax`, `Secure` em produção.
6. **Conta desativada perde a sessão** no próximo clique
   (`EnsureUserIsActive`), e não só no próximo login.
7. **Senhas:** mínimo de 10 caracteres com letras e números; em produção,
   `uncompromised()` recusa senhas presentes em vazamentos (consulta por
   k-anonimato, só os 5 primeiros caracteres do hash saem do servidor).
8. **Limites de requisição:** login (5 tentativas por e-mail e IP), feed da
   agenda (120/min) e reagendamento (30/min).
9. **Logout só por POST com CSRF.** Um GET em `/logout` apenas volta ao
   painel, sem encerrar a sessão.
10. **Defesas de desenvolvimento:** `Model::shouldBeStrict()` fora de produção
    (N+1 e atributos descartados em silêncio viram erro),
    `DB::prohibitDestructiveCommands()` e HTTPS forçado em produção.
11. **Prévia pública só com dados fictícios.** `preview:export` recusa rodar
    em produção e exige a base de demonstração.

## Consequências

- **A `APP_KEY` passa a proteger dados.** Trocar a chave sem recriptografar
  torna CPF/CNPJ e anotações ilegíveis e invalida o índice cego. Guarde a
  chave fora do repositório e com backup próprio.
- Não é possível buscar por parte do CPF/CNPJ; nome, telefone e e-mail
  continuam com busca parcial.
- Campos criptografados não podem ser ordenados nem filtrados pelo banco.
