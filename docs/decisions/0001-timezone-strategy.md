# ADR 0001 — Estratégia de fuso horário

- **Status:** aceito
- **Data:** 2026-09-03
- **Requisito relacionado:** RNF-004

## Contexto

O projeto atende presencialmente em Novo Hamburgo/RS. Toda a operação acontece
em `America/Sao_Paulo`, que possui histórico de horário de verão. Guardar
horários locais no banco torna impossível distinguir, sem ambiguidade, os
instantes que se repetem no fim do horário de verão — e o Brasil pode voltar a
adotá-lo.

## Decisão

1. `APP_TIMEZONE=UTC`. Todos os `timestamp` são persistidos em UTC.
2. `APP_DISPLAY_TIMEZONE=America/Sao_Paulo`. É o fuso de apresentação.
3. A conversão é responsabilidade da borda da aplicação
   (`App\Support\DisplayTimezone`): entrada do usuário → UTC, UTC → exibição.
4. O **dia da semana** nunca é persistido (RF-021): é derivado da data já
   convertida para o fuso de exibição.

## Consequências

- Comparações de sobreposição de horário (RD-002) operam sobre UTC, sem
  ambiguidade.
- Qualquer relatório agrupado por dia precisa converter para o fuso de
  exibição **antes** de agrupar, caso contrário atendimentos noturnos caem no
  dia seguinte.
- Testes fixam ambos os fusos em `phpunit.xml`.
