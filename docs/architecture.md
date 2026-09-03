# Arquitetura

> Princípio principal (PRD, seção 35): **a agenda é a interface principal;
> Atendimento é o domínio principal; Laravel/PostgreSQL é a fonte de verdade.**
> Qualquer integração externa depende do domínio local, nunca o contrário.

## Camadas

```
HTTP / Livewire  →  Actions & Services de domínio  →  Models (Eloquent)  →  PostgreSQL
                          ↑
                    Policies / Enums / Data
```

- **Controllers e componentes Livewire** cuidam de entrada, validação de
  formulário e apresentação. Nunca contêm regra de negócio.
- **Actions** (`app/Domain/*/Actions`) executam operações que passam do CRUD
  trivial: criar atendimento, reagendar, cancelar.
- **Queries** (`app/Domain/*/Queries`) encapsulam consultas complexas — agenda,
  relatórios, histórico do atendido.
- **Policies** decidem autorização. Esconder um botão não é autorização.
- **Models** ficam finos: casts, relacionamentos e escopos simples.

## Estrutura de `app/`

Os diretórios são criados **quando existe uma classe real** que os justifique —
nada de pastas vazias só para bater com o desenho.

```
app/
├── Domain/
│   ├── Users/Enums/UserRole.php        # papéis (admin, member, viewer)
│   ├── Appointments/                   # Milestone 1
│   ├── Clients/                        # Milestone 1
│   ├── Services/                       # Milestone 1
│   ├── Reporting/                      # Milestone 4
│   └── Calendar/                       # Milestone 6
├── Http/
│   ├── Controllers/{Auth,ProfileController}
│   └── Requests/
├── Livewire/                           # Milestone 2 em diante
├── Models/User.php
├── Providers/
└── Support/DisplayTimezone.php
```

`User` permanece em `app/Models` (convenção do Laravel); o que é regra de
domínio de usuário — hoje o enum `UserRole` — vive em `app/Domain/Users`.

## Idioma

- **Interface:** português (pt-BR). Traduções em `lang/pt_BR/` e
  `lang/pt_BR.json`.
- **Código:** inglês. Classes, métodos, tabelas, colunas e comentários.
- O termo de negócio "Atendido" corresponde ao model `Client`.

## Fuso horário

Persistência em UTC, apresentação em `America/Sao_Paulo`, conversão via
`App\Support\DisplayTimezone`. Ver
[ADR 0001](decisions/0001-timezone-strategy.md).

## Autenticação e autorização

Breeze (stack Blade), sem cadastro público e sem auto-exclusão de conta. Papel
único por usuário e flag `active`. Ver
[ADR 0002](decisions/0002-authentication-scope.md).

## O que ainda não existe

| Área | Milestone |
| --- | --- |
| `Client`, `Service`, `Appointment`, conflito de horário | 1 |
| CRUD e histórico do atendido em Livewire | 2 |
| Agenda com FullCalendar | 3 |
| Relatórios e auditoria (`appointment_activities`) | 4 |
| API `/api/v1` com Sanctum | 5 |
| Google Calendar / ICS / Mundy | 6 |

Nenhuma dessas áreas deve ganhar contrato, interface ou tabela antes da hora —
ver a seção 27 do PRD sobre abstração prematura.
