# Copilot instructions

## Container workflow
- Use `make bash` or `make sh` to open a shell in the PHP container.
- Prefer running Symfony commands via `make sf c="<command>"` (e.g., `make sf c="app:neo4j:init-schema"`).
- Use `make composer c="<command>"` for Composer tasks.
- Use `make logs`, `make up`, and `make down` for Docker lifecycle management.

## Notes
- The Docker Compose stack is managed via the Makefile aliases; avoid running `docker compose exec` directly unless necessary.
