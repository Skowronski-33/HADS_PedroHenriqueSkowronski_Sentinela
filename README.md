# Sentinela

Sistema de apoio à gestão de ocorrências e operações de defesa civil, desenvolvido como Trabalho de Conclusão de Curso (TCC) por **Pedro Henrique Skowronski** para o curso de Análise e Desenvolvimento de Sistemas pela Universidade de Passo Fundo (UPF).

O Sentinela centraliza o registro e acompanhamento de ocorrências emergenciais (enchentes, deslizamentos, incêndios, resgates etc.), o despacho de equipes e viaturas, a gestão de abrigos e donativos, e o controle de recursos humanos e materiais envolvidos na resposta a desastres.

## Funcionalidades

- **Ocorrências** — registro, triagem/classificação por prioridade, despacho de atendimentos (equipe, viatura e equipamentos), acompanhamento de status e encerramento.
- **Abrigos** — cadastro de abrigos, controle de ocupação, entrada/saída/transferência de pessoas abrigadas (com tratamento de grupos familiares) e gestão de estoque de donativos (entrada, saída e transferência entre abrigos).
- **Recursos operacionais** — cadastro e controle de status de viaturas, equipamentos e equipes (com função de cada membro), além de uma tela de consulta consolidada de recursos disponíveis.
- **Voluntários** — cadastro e disponibilidade, com organização em equipes.
- **Usuários e perfis de acesso** — gestão de contas com perfis distintos (ver abaixo).
- **Atividades** — trilha de auditoria das principais ações do sistema (triagem, despacho, mudanças de status etc.), visível para administradores.
- **Backup periódico** — rotina agendada de backup do banco de dados.

### Perfis de acesso

| Perfil | Acesso |
|---|---|
| **Admin** | Gestão de usuários e visualização da trilha de atividades, além de acesso a todo o restante do sistema. |
| **Atendente de Ocorrências** | Registro, triagem e despacho de ocorrências. |
| **Gerente Operacional** | Viaturas, equipamentos, equipes, voluntários e consulta de recursos. |
| **Gerenciador de Abrigo** | Abrigos, pessoas abrigadas e donativos. |

## Tecnologias

- [Laravel 12](https://laravel.com/) (PHP 8.2+)
- Blade + Bootstrap 5 (sem build de frontend — os assets do Bootstrap já estão versionados em `public/vendor`)
- PostgreSQL 17
- [Spatie Laravel Permission](https://spatie.be/docs/laravel-permission) para perfis de acesso

## Pré-requisitos

- PHP >= 8.2 com Composer
- PostgreSQL (o sistema usa recursos específicos do Postgres, como `ILIKE` nas buscas — **não funciona com SQLite** em uso normal; SQLite é usado apenas pela suíte de testes automatizados, em memória)

## Instalação

```bash
# 1. Clonar o repositório e instalar as dependências PHP
git clone https://github.com/Skowronski-33/HADS_PedroHenriqueSkowronski_Sentinela.git sentinela
cd sentinela
composer install

# 2. Configurar o ambiente
cp .env.example .env
php artisan key:generate
```

Edite o `.env` com as credenciais do seu banco Postgres:

```env
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=sentinela_db
DB_USERNAME=postgres
DB_PASSWORD=sua_senha
```

Crie o banco (`sentinela_db` ou o nome escolhido acima) e rode as migrations e seeders:

```bash
php artisan migrate
php artisan db:seed
```

Suba o servidor local:

```bash
php artisan serve
```

A aplicação estará disponível em `http://localhost:8000`.

> Não é necessário rodar `npm install`/`npm run build` — o Bootstrap e os ícones já estão versionados em `public/vendor` e são referenciados diretamente pelas views.

### Credenciais iniciais

O seeder cria um usuário administrador padrão:

- **E-mail:** `admin@sentinela.local`
- **Senha:** `sentinela2026`

Os demais seeders populam tipos de ocorrência, prioridades, funções de equipe, equipamentos, viaturas, equipes, voluntários, abrigos, donativos, pessoas abrigadas e ocorrências de exemplo, para permitir testar o sistema com dados realistas logo após a instalação.

## Testes automatizados

```bash
php artisan test
```

A suíte roda em SQLite em memória (configurado em `phpunit.xml`), isolada do banco Postgres de desenvolvimento.

## Backup periódico (RNF10)

O comando `backup:database` gera um dump da base via `pg_dump` e remove backups antigos além do limite configurado:

```bash
php artisan backup:database --manter=10
```

Esse comando já está agendado para rodar diariamente às 02:00 em `routes/console.php`. Para que o agendamento seja executado de fato, é preciso configurar um disparo externo do scheduler do Laravel a cada minuto — um cron job no Linux (`* * * * * php artisan schedule:run`) ou o Agendador de Tarefas no Windows — já que o Laravel não se auto-agenda sozinho.
