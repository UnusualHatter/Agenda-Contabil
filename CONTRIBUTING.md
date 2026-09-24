# Contribuindo

## Fluxo de branches

```
main          # sempre estável, CI verde
feature/*     # novas funcionalidades
fix/*         # correções
chore/*       # infraestrutura, dependências, documentação
```

`develop` fica de fora enquanto o time for pequeno (PRD, seção 28).

## Commits

[Conventional Commits](https://www.conventionalcommits.org/):

```
feat: add appointment scheduling
fix: prevent overlapping appointments
test: cover appointment conflict rules
docs: document calendar integration strategy
chore: bump laravel to 13.31
```

## Antes de abrir um PR

```sh
vendor/bin/pint          # formata
php artisan test         # testes
npm run build            # frontend compila
```

A CI roda exatamente isso (`.github/workflows/ci.yml`) com
`vendor/bin/pint --test`, então formate antes de subir.

## Convenções de código

Resumo da seção 27 do PRD:

- PSR-12, garantido pelo Pint (preset `laravel`).
- `declare(strict_types=1);` em código de domínio.
- Tipos de retorno sempre que possível.
- **Form Requests** validam HTTP; **Policies** autorizam; **Actions**
  executam operações de domínio que passam do CRUD trivial.
- Sem lógica de negócio em Blade, sem controllers gigantes, sem repositories
  genéricos em cima do Eloquent, sem interface sem necessidade.
- Código em inglês; interface em pt-BR.

## Definition of Done

Uma feature só está pronta quando (PRD, seção 31):

- [ ] comportamento implementado;
- [ ] validações existem;
- [ ] autorização considerada;
- [ ] testes relevantes passam;
- [ ] formatter passa;
- [ ] migrations reversíveis quando razoável;
- [ ] nenhum secret versionado;
- [ ] documentação atualizada quando necessário;
- [ ] nenhum TODO crítico escondendo funcionalidade quebrada.

## Decisões de arquitetura

Mudanças estruturais — trocar de banco, adicionar biblioteca de permissões,
adotar um provider de calendário — exigem um ADR novo em `docs/decisions/`,
numerado em sequência.
