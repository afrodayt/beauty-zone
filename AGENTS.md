# Repository Guidelines

## Project Structure & Module Organization
- `app/` contains Laravel application code (models, controllers, providers).
- `routes/` defines entry points (`web.php` for HTTP routes, `console.php` for CLI tasks).
- `resources/` stores frontend assets: Vue/JS in `resources/js`, styles in `resources/css`, Blade templates in `resources/views`.
- `database/` includes migrations, factories, and seeders.
- `tests/Feature` holds integration/HTTP tests; `tests/Unit` holds isolated logic tests.
- `public/` serves compiled assets, and `docker/` contains local container definitions.

## Build, Test, and Development Commands
- `composer install` / `npm install` — install PHP and Node dependencies.
- `composer dev` — run the full local stack (Laravel server, queue listener, logs, Vite).
- `npm run dev` — run Vite only for frontend development.
- `npm run build` — build production frontend assets.
- `composer test` — clear config and run PHPUnit through `php artisan test`.
- `npm run lint` / `npm run lint:fix` — lint (or auto-fix) JS/Vue files.
- `vendor/bin/pint` — format PHP code before committing.

## Coding Style & Naming Conventions
- Follow `.editorconfig`: UTF-8, LF, final newline, 4-space indentation by default.
- PHP should follow PSR-12 and Laravel conventions (`UserController`, `App\\Services\\...`).
- JS/Vue is linted with ESLint and formatted with Prettier (`tabWidth: 2`, `printWidth: 120`).
- Use `PascalCase` for Vue component filenames, `camelCase` for JS variables/functions, and `snake_case` for migration filenames.

## Testing Guidelines
- Use PHPUnit (configured in `phpunit.xml`) and run via `composer test`.
- Add feature tests for routes/controllers and unit tests for pure business logic.
- Name test files `*Test.php`; prefer descriptive method names such as `test_it_returns_200_for_homepage`.
- Cover both happy paths and validation/error cases for new endpoints.

## Commit & Pull Request Guidelines
- This workspace snapshot does not include `.git` history, so use Conventional Commits (e.g., `feat: add booking endpoint`, `fix: validate phone input`).
- Keep commits focused and atomic; avoid mixing backend, frontend, and formatting-only changes.
- PRs should include: what changed, why, test evidence (`composer test`, `npm run lint`), and screenshots for UI updates.

## Security & Configuration Tips
- Never commit `.env`, secrets, database dumps, or files from `storage/` containing sensitive data.
- Keep `.env.example` updated whenever new environment variables are introduced.
- Prefer test-safe defaults (`DB_CONNECTION=sqlite` in tests) and validate all request input server-side.
