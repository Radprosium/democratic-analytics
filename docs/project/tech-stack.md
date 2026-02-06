# Tech Stack

## Overview

Democratic Analytics is built on Symfony 8.0 with the Symfony AI ecosystem, running in a Dockerized environment powered by FrankenPHP.

## Core Framework

| Component | Version | Purpose |
|-----------|---------|---------|
| **PHP** | >= 8.4 | Runtime language |
| **Symfony** | 8.0.* | Web application framework |
| **FrankenPHP** | Latest | High-performance PHP app server (worker mode) |
| **Caddy** | Latest | Web server with automatic HTTPS |
| **Docker Compose** | v2.10+ | Container orchestration |

## AI & Machine Learning

| Component | Version | Purpose |
|-----------|---------|---------|
| **symfony/ai-bundle** | ^0.3.2 | Core AI integration for Symfony |
| **symfony/ai-agent** | ^0.3.0 | AI agent framework (tools, memory, prompts) |
| **symfony/ai-perplexity-platform** | ^0.3.0 | Perplexity AI platform bridge (search-augmented LLM) |
| **symfony/ai-store** | ^0.3.3 | Document storage abstraction for RAG |
| **symfony/ai-chroma-db-store** | * | ChromaDB vector store integration |
| **codewithkyrian/chromadb-php** | ^1.0 | PHP client for ChromaDB |

### Why Perplexity AI?

Perplexity AI is a **search-augmented LLM** — it combines the reasoning capabilities of a large language model with real-time web search. This makes it ideal for Democratic Analytics because:

- It can retrieve **current news** — not limited to a training cutoff date
- It provides **sourced answers** — responses include citations to actual articles
- It excels at **synthesizing information** from multiple sources into coherent analysis

### Why ChromaDB?

ChromaDB serves as the **vector database** for semantic search and retrieval-augmented generation (RAG):

- Store embeddings of political subjects, articles, and analyses
- Enable natural-language queries over the knowledge base
- Power contextual retrieval when AI agents need background information about a subject
- Lightweight and easy to self-host (runs as a Docker service)

## Database & Persistence

| Component | Version | Purpose |
|-----------|---------|---------|
| **PostgreSQL** | 16 | Primary relational database |
| **Doctrine ORM** | ^3.6 | Object-relational mapping |
| **Doctrine Migrations** | ^4.0 | Database schema versioning |

## Frontend

| Component | Version | Purpose |
|-----------|---------|---------|
| **Twig** | ^3.0 | Server-side templating engine |
| **Symfony UX Turbo** | ^2.32 | Hotwire Turbo integration (SPA-like navigation) |
| **Symfony UX Live Component** | ^2.32 | Server-rendered interactive components |
| **Symfony Asset Mapper** | 8.0.* | Modern asset management (no Node.js/Webpack) |
| **Stimulus** | (via UX) | Lightweight JavaScript framework |

### Frontend Philosophy

The frontend uses Symfony's **Hotwire stack** (Turbo + Stimulus + Live Components) rather than a JavaScript SPA framework. This provides:

- **Server-rendered HTML** with SPA-like interactions
- **No build step** — Asset Mapper handles everything without Node.js
- **Live Components** for interactive AI chat interfaces and real-time updates
- **Turbo Frames & Streams** for partial page updates

## Development Tools

| Tool | Version | Purpose |
|------|---------|---------|
| **PHPStan** | ^2.1 | Static analysis |
| **PHP CS Fixer** | ^3.93 | Code style enforcement |
| **Symfony Maker Bundle** | ^1.65 | Code generation |

## Infrastructure (Docker Services)

| Service | Image | Purpose |
|---------|-------|---------|
| **php** | Custom (FrankenPHP) | Application server |
| **database** | postgres:16-alpine | Primary database |
| **chromadb** | chromadb/chroma:0.5.23 | Vector database |

## Environment Variables

| Variable | Purpose |
|----------|---------|
| `PERPLEXITY_API_KEY` | API key for Perplexity AI platform |
| `CHROMADB_HOST` | ChromaDB server URL |
| `DATABASE_URL` | PostgreSQL connection string |

## Configuration Files

- [config/packages/ai.yaml](../../config/packages/ai.yaml) — Core AI bundle configuration (platforms, agents, stores)
- [config/packages/ai_perplexity_platform.yaml](../../config/packages/ai_perplexity_platform.yaml) — Perplexity platform credentials
- [config/packages/ai_chroma_db_store.yaml](../../config/packages/ai_chroma_db_store.yaml) — ChromaDB client and store setup
- [config/packages/doctrine.yaml](../../config/packages/doctrine.yaml) — Doctrine ORM configuration

## Future Additions (Planned)

- **Symfony Messenger** — For async job processing (news retrieval, analysis pipelines)
- **Symfony Scheduler** — For automated periodic news retrieval
- **Additional AI platforms** — OpenAI/Anthropic for specialized analysis tasks
- **Redis/Cache** — For rate limiting, session storage, and caching AI responses
