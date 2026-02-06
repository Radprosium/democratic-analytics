# Architecture

## System Overview

Democratic Analytics follows a layered architecture centered around **AI agent pipelines** that feed a growing political knowledge base. The system combines real-time news retrieval, AI-powered analysis, persistent storage, and vector search to deliver multi-perspective political analysis.

```
┌──────────────────────────────────────────────────────────────────┐
│                        USER INTERFACE                            │
│           Twig + Turbo + Live Components + Stimulus              │
├──────────────────────────────────────────────────────────────────┤
│                       CONTROLLERS                                │
│          Subject, Analysis, Media, Actor, Chat                   │
├──────────────────┬───────────────────────────────────────────────┤
│   AI AGENT LAYER │           SERVICE LAYER                       │
│                  │                                               │
│  News Retriever  │  SubjectManager    MediaSourceService         │
│  Subject Analyst │  AnalysisService   ActorService               │
│  Tone Analyzer   │  ArgumentService   TimelineService            │
│  Debate Mapper   │  PositionService   SearchService              │
│  Subject Updater │                                               │
│  User Query (RAG)│                                               │
├──────────────────┴───────────────────────────────────────────────┤
│                     PERSISTENCE LAYER                            │
│         Doctrine ORM (PostgreSQL)  │  ChromaDB (Vectors)         │
└──────────────────────────────────────────────────────────────────┘
            │                                    │
    ┌───────┴────────┐                 ┌─────────┴──────────┐
    │  PostgreSQL DB  │                │  ChromaDB Vector DB │
    │  (Entities,     │                │  (Embeddings,       │
    │   Relations,    │                │   Semantic Search,  │
    │   Metadata)     │                │   RAG Context)      │
    └────────────────┘                 └────────────────────┘
```

## Layer Responsibilities

### 1. User Interface Layer

Built with Symfony's Hotwire stack:
- **Twig templates** render server-side HTML
- **Turbo Frames** enable partial page updates without full reloads
- **Turbo Streams** push real-time updates (e.g., analysis progress)
- **Live Components** power interactive elements (search, chat, filters)
- **Stimulus controllers** handle client-side behavior

### 2. Controller Layer

Standard Symfony controllers handling HTTP requests. Key controller groups:
- **SubjectController** — Browse, view, and explore political subjects
- **AnalysisController** — View AI-generated analyses and comparisons
- **MediaController** — Media source tracking and tone dashboards
- **ActorController** — Political actor profiles and positions
- **ChatController** — AI chat interface for natural-language queries
- **AdminController** — Trigger retrieval, manage sources, review pipelines

### 3. AI Agent Layer

The core intelligence of the application. Each agent is a Symfony AI agent with its own platform, model, system prompt, and available tools. See [AI Agents](ai-agents.md) for detailed agent design.

Agents interact with the service layer to read/write data and use ChromaDB for contextual retrieval.

### 4. Service Layer

Business logic services that manage the domain model:
- **SubjectManager** — CRUD and lifecycle management for political subjects
- **AnalysisService** — Orchestrates AI analysis pipelines
- **MediaSourceService** — Manages media outlets and their metadata
- **ActorService** — Manages political actors and their affiliations
- **ArgumentService** — Tracks arguments and counter-arguments in debates
- **PositionService** — Records actor stances on subjects
- **TimelineService** — Manages chronological events for subjects
- **SearchService** — Wraps ChromaDB queries for semantic search

### 5. Persistence Layer

Two complementary storage systems:

**PostgreSQL (via Doctrine ORM)**:
- Structured data: entities, relationships, metadata
- Full relational model with migrations
- Queryable with DQL/QueryBuilder

**ChromaDB (Vector Store)**:
- Document embeddings for semantic similarity search
- RAG (Retrieval-Augmented Generation) context for AI agents
- Stores vectorized versions of subjects, articles, analyses

## Data Flow: News Retrieval Pipeline

```
1. TRIGGER (manual/scheduled)
        │
        ▼
2. NEWS RETRIEVER AGENT (Perplexity AI)
   "What are the main political subjects today in [country]?"
        │
        ▼
3. SUBJECT IDENTIFICATION
   Parse response → Extract distinct subjects
        │
        ▼
4. FOR EACH SUBJECT:
   ├── Check if subject already exists (semantic similarity via ChromaDB)
   │
   ├── IF NEW → Create Subject entity
   │   ├── SUBJECT ANALYZER AGENT → Generate in-depth analysis
   │   ├── TONE ANALYZER AGENT → Analyze media coverage tone
   │   ├── DEBATE MAPPER AGENT → Identify actors, arguments, positions
   │   └── Store embeddings in ChromaDB
   │
   └── IF EXISTS → Update existing subject
       ├── SUBJECT UPDATER AGENT → Enrich with new information
       ├── Update timeline with new events
       └── Refresh embeddings in ChromaDB
        │
        ▼
5. PERSIST all entities to PostgreSQL
6. INDEX updated content in ChromaDB
```

## Data Flow: User Query (RAG)

```
1. USER QUESTION
   "What are the main arguments for and against [topic]?"
        │
        ▼
2. VECTORIZE the question (embedding)
        │
        ▼
3. SEMANTIC SEARCH in ChromaDB
   Retrieve relevant subjects, analyses, arguments
        │
        ▼
4. USER QUERY AGENT (with RAG context)
   Prompt includes: question + retrieved context
        │
        ▼
5. GENERATE RESPONSE
   Structured, sourced answer with references to subjects
        │
        ▼
6. RENDER in Live Component (streaming response)
```

## Entity Model

```
Subject ─────────┬──── has many ──── Analysis
                 ├──── has many ──── Argument
                 ├──── has many ──── TimelineEvent
                 ├──── many-to-many ── Actor
                 ├──── many-to-many ── MediaSource
                 └──── many-to-many ── Subject (related subjects)

Actor ───────────┬──── has many ──── Position (on Subject)
                 └──── belongs to ── ActorType (politician, party, institution...)

MediaSource ─────┬──── has many ──── ArticleCoverage
                 └──── has ───────── MediaProfile (bias, tone tendencies)

Argument ────────┬──── belongs to ── Subject
                 ├──── has ───────── side (for/against/neutral)
                 └──── linked to ─── Actor (who makes this argument)

Analysis ────────┬──── belongs to ── Subject
                 ├──── has ───────── type (summary, media_analysis, debate_map...)
                 └──── has ───────── sources (citations)

TimelineEvent ───┬──── belongs to ── Subject
                 └──── has ───────── date, description, significance
```

## Directory Structure (Planned)

```
src/
├── Controller/
│   ├── SubjectController.php
│   ├── AnalysisController.php
│   ├── MediaController.php
│   ├── ActorController.php
│   └── ChatController.php
├── Entity/
│   ├── Subject.php
│   ├── Analysis.php
│   ├── Argument.php
│   ├── Actor.php
│   ├── ActorType.php
│   ├── Position.php
│   ├── MediaSource.php
│   ├── ArticleCoverage.php
│   ├── MediaProfile.php
│   └── TimelineEvent.php
├── Repository/
│   └── (one per entity)
├── Service/
│   ├── SubjectManager.php
│   ├── AnalysisService.php
│   ├── MediaSourceService.php
│   ├── ActorService.php
│   ├── SearchService.php
│   └── Pipeline/
│       ├── NewsRetrievalPipeline.php
│       └── SubjectAnalysisPipeline.php
├── AI/
│   └── Tool/
│       ├── SubjectLookupTool.php
│       ├── SubjectCreateTool.php
│       ├── SubjectUpdateTool.php
│       ├── MediaAnalysisTool.php
│       └── ActorLookupTool.php
└── Command/
    ├── RetrieveNewsCommand.php
    ├── AnalyzeSubjectCommand.php
    └── IndexSubjectsCommand.php
```

## Key Architectural Decisions

### 1. Perplexity as Primary Retrieval Platform
Perplexity AI's search-augmented capabilities make it the primary platform for news retrieval. Unlike static LLMs, it can access current information with source citations.

### 2. Dual Storage Strategy
PostgreSQL handles structured relational data (entities, relationships), while ChromaDB handles unstructured semantic data (embeddings, similarity search). This separation plays to each system's strengths.

### 3. Agent-per-Concern Design
Each AI agent has a single, focused responsibility. This makes prompts more precise, outputs more reliable, and the system easier to debug and improve.

### 4. Subject-Centric Data Model
The `Subject` entity is the central node of the data model. Everything — analyses, arguments, actors, media coverage — connects back to a Subject. This provides a natural organization for the knowledge base.

### 5. Server-Rendered Frontend
Using Turbo + Live Components instead of a JavaScript SPA keeps the stack unified (PHP all the way), reduces complexity, and still provides dynamic, interactive experiences.
