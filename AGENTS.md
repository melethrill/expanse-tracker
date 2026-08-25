\# Repository Guidelines



\## Project Tech Stack

\- Framework: Laravel 11+

\- Language: PHP 8.2+

\- Database: SQLite (for local testing/CI)



\## Conventions

\- Use Standard Laravel Eloquent conventions for models, migrations, and relationships.

\- Always generate database migrations alongside models (`php artisan make:model ModelName -m`).

\- Run `php artisan test` to verify changes before completing tasks.

\- Keep business logic in Services or Controller actions, avoiding fat routes.

