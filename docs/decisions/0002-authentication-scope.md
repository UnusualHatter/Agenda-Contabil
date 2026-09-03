# ADR 0002 — Escopo da autenticação

- **Status:** aceito
- **Data:** 2026-09-03
- **Requisitos relacionados:** RF-001 a RF-004, RNF-006, RNF-007

## Contexto

O PRD descreve três perfis (administrador, membro da equipe, visualizador) e
determina que a administração de usuários é feita pelo administrador. O
scaffolding padrão do Laravel Breeze traz cadastro público e auto-exclusão de
conta, que não constam em nenhum requisito.

## Decisão

1. **Sem cadastro público.** As rotas `register` foram removidas. Contas são
   criadas por administradores. Em desenvolvimento, `DevelopmentUserSeeder`
   cria as contas locais.
2. **Sem auto-exclusão de conta.** Usuários são autores de atendimentos e
   registros de auditoria (RF-060); apagar a linha destruiria histórico. A
   desativação usa `users.active`.
3. **Contas desativadas não autenticam.** Verificado em
   `LoginRequest::authenticate()`, depois das credenciais e antes de liberar a
   sessão, com mensagem própria (`auth.inactive`).
4. **Papel único por usuário** (`users.role`, enum `UserRole`). Autorização
   real virá de Policies/Gates no Milestone 1 — esconder botões não é
   autorização.

## Consequências

- Um administrador inicial precisa existir antes do primeiro login em
  produção. Enquanto não houver tela de administração de usuários, isso é
  feito via seeder/tinker — documentado no README.
- Não há dependência de bibliotecas de permissão no MVP. Se isso mudar, exige
  um novo ADR (PRD, seção 16).
