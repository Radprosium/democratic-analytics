# Democratic Analytics

An AI-powered political analysis platform that helps citizens better understand political issues, media coverage, and public debate.

Built with **Symfony 8.0**, **Symfony AI**, **Perplexity AI**, and **ChromaDB**.

## What It Does

Democratic Analytics uses AI agents to:

- **Retrieve** the latest political news and identify key subjects of the day
- **Analyze** each subject in depth — context, arguments, actors, stakes
- **Map media coverage** — track how different outlets cover the same subject, their tone and framing
- **Track actors** — politicians, parties, institutions, and their positions on each issue
- **Evolve subjects over time** — update and enrich subjects as new information emerges
- **Answer questions** — RAG-powered chat interface for natural-language queries about the knowledge base

The goal: help users understand political issues from every angle and make informed decisions.

## Tech Stack

| Layer | Technologies |
|-------|-------------|
| **Framework** | Symfony 8.0, PHP 8.4+ |
| **AI** | Symfony AI Bundle, Perplexity AI (search-augmented LLM) |
| **Vector DB** | ChromaDB (semantic search & RAG) |
| **Database** | PostgreSQL 16, Doctrine ORM |
| **Frontend** | Twig, Turbo, Live Components, Stimulus |
| **Server** | FrankenPHP + Caddy (Docker) |

## Getting Started

### Prerequisites

- [Docker Compose](https://docs.docker.com/compose/install/) (v2.10+)
- A [Perplexity AI API key](https://docs.perplexity.ai/)

### Setup

1. Clone the repository
2. Copy `.env` and configure your API keys:
   ```bash
   # Set your Perplexity API key
   PERPLEXITY_API_KEY=your_key_here
   ```
3. Build and start the containers:
   ```bash
   make start
   ```
4. Open `https://localhost` and accept the auto-generated TLS certificate

### Useful Commands

```bash
make help       # Show all available commands
make up         # Start containers
make down       # Stop containers
make sh         # Shell into the PHP container
make sf c=about # Run Symfony console commands
make logs       # Follow container logs
```

## Project Documentation

### Project

- [Vision](docs/project/vision.md) — Mission, problem statement, and long-term goals
- [Architecture](docs/project/architecture.md) — System design, data flow, and entity model
- [Tech Stack](docs/project/tech-stack.md) — Technologies used and why
- [AI Agents](docs/project/ai-agents.md) — Agent design, prompts, and tool definitions
- [Features Roadmap](docs/project/features-roadmap.md) — Phased development plan (MVP → full scale)

### Infrastructure

- [Options available](docs/options.md)
- [Using Symfony Docker with an existing project](docs/existing-project.md)
- [Support for extra services](docs/extra-services.md)
- [Deploying in production](docs/production.md)
- [Debugging with Xdebug](docs/xdebug.md)
- [TLS Certificates](docs/tls.md)
- [Using MySQL instead of PostgreSQL](docs/mysql.md)
- [Using Alpine Linux instead of Debian](docs/alpine.md)
- [Using a Makefile](docs/makefile.md)
- [Updating the template](docs/updating.md)
- [Troubleshooting](docs/troubleshooting.md)

## Development Status

**Current Phase**: Phase 1 — MVP (News Retrieval & Subject Tracking)

See the [Features Roadmap](docs/project/features-roadmap.md) for the full development plan across 5 phases.

## License

Available under the MIT License.

## Credits

Infrastructure based on [Symfony Docker](https://github.com/dunglas/symfony-docker) by [Kévin Dunglas](https://dunglas.dev).
