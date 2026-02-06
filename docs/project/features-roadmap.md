# Features Roadmap

## Overview

The development of Democratic Analytics is organized into **5 phases**, progressing from a minimal viable product to a full-scale political analysis platform. Each phase builds on the previous one, delivering incremental value while expanding capabilities.

---

## Phase 1: MVP — News Retrieval & Subject Tracking

**Goal**: Get the core loop working — retrieve news, identify subjects, persist them, display them.

**Priority**: 🔴 Critical — Foundation for everything else

### Features

- [ ] **Perplexity AI integration** — Configure the AI platform and verify connectivity
- [ ] **News Retriever Agent** — Console command that triggers the agent to scan latest political news
- [ ] **Subject entity & CRUD** — `Subject` entity with title, summary, category, importance, status, timestamps
- [ ] **Basic subject list page** — Twig template listing all subjects, sorted by date/importance
- [ ] **Subject detail page** — View a single subject's full summary and metadata
- [ ] **ChromaDB indexing** — Store subject embeddings for future semantic search
- [ ] **Console command: `app:retrieve-news`** — Manual trigger for the news retrieval pipeline
- [ ] **Basic error handling** — Graceful handling of API failures, rate limits

### Entities

```
Subject
├── id (UUID)
├── title (string)
├── slug (string, unique)
├── summary (text)
├── category (string, enum: domestic, european, international, economy, social, environment...)
├── importance (int, 1-10)
├── status (string, enum: active, resolved, dormant)
├── sources (json, array of URLs)
├── createdAt (datetime)
├── updatedAt (datetime)
└── lastRetrievedAt (datetime)
```

### Technical Tasks

- [ ] Set up Doctrine entity and repository
- [ ] Create and run initial migration
- [ ] Configure AI agent in `ai.yaml`
- [ ] Write agent system prompt
- [ ] Create `SubjectLookupTool` (basic DB search)
- [ ] Create `SubjectCreateTool`
- [ ] Build Twig templates (list + detail)
- [ ] Create console command
- [ ] Set up ChromaDB collection and basic indexing

### Definition of Done

A user can run `make sf c=app:retrieve-news`, the system fetches today's political subjects via Perplexity, stores them in PostgreSQL, and they appear on a web page at `https://localhost/subjects`.

---

## Phase 2: Deep Analysis & Media Mapping

**Goal**: Add in-depth analysis capabilities — media tone analysis, actor tracking, and richer subject data.

**Priority**: 🟠 High

### Features

- [ ] **Analysis entity** — Store AI-generated analyses linked to subjects
- [ ] **Subject Analyzer Agent** — Deep analysis of individual subjects (context, stakes, key points)
- [ ] **Media Tone Analyzer Agent** — Analyze how different outlets cover a subject
- [ ] **MediaSource entity** — Track news outlets with metadata
- [ ] **ArticleCoverage entity** — Record individual article coverage instances with tone classification
- [ ] **Actor entity** — Track politicians, parties, institutions
- [ ] **Actor-Subject linking** — Many-to-many relationship between actors and subjects
- [ ] **Media coverage view** — Page showing how different media cover a subject
- [ ] **Analysis view** — Display AI analyses on the subject detail page
- [ ] **Console command: `app:analyze-subject [id]`** — Trigger deep analysis for a specific subject

### Entities

```
Analysis
├── id (UUID)
├── subject (ManyToOne → Subject)
├── type (string, enum: comprehensive_summary, media_analysis, debate_map)
├── content (text)
├── sources (json, array of citations)
├── createdAt (datetime)
└── model (string, which AI model generated this)

MediaSource
├── id (UUID)
├── name (string)
├── url (string)
├── type (string, enum: newspaper, tv, radio, online, wire_agency)
├── country (string)
├── politicalLeaning (string, nullable, enum: left, center-left, center, center-right, right)
└── description (text, nullable)

ArticleCoverage
├── id (UUID)
├── subject (ManyToOne → Subject)
├── mediaSource (ManyToOne → MediaSource)
├── tone (string, enum: neutral, favorable, critical, alarmist, dismissive, supportive)
├── framing (text, how the outlet frames the subject)
├── emphasis (text, what aspects are emphasized)
├── omissions (text, what aspects are omitted, nullable)
├── articleUrl (string, nullable)
├── articleDate (datetime, nullable)
└── analyzedAt (datetime)

Actor
├── id (UUID)
├── name (string)
├── type (string, enum: politician, party, institution, lobby, union, ngo, other)
├── description (text, nullable)
├── affiliation (string, nullable, e.g. party name)
├── role (string, nullable, e.g. "Minister of Finance")
└── subjects (ManyToMany → Subject)
```

### Definition of Done

Running `app:analyze-subject` on a subject produces a structured analysis, a media coverage breakdown, and identified actors. The web UI displays this data on the subject detail page.

---

## Phase 3: Subject Evolution & Debate Structure

**Goal**: Make subjects living documents — track their evolution over time, map debate structure with arguments and positions.

**Priority**: 🟡 Medium

### Features

- [ ] **TimelineEvent entity** — Record chronological events in a subject's life
- [ ] **Argument entity** — Distinct arguments made in a debate (for/against/nuanced)
- [ ] **Position entity** — Link actors to their stance on a subject
- [ ] **Subject Updater Agent** — Enriches existing subjects with new information without losing history
- [ ] **Debate Mapper Agent** — Maps the full argument structure of a debate
- [ ] **Subject timeline view** — Visual chronological display of a subject's evolution
- [ ] **Debate map view** — Visualization of arguments, counter-arguments, and actor positions
- [ ] **Related subjects** — Self-referencing many-to-many to link subjects together
- [ ] **Subject diff tracking** — Record what changed between updates (summary changes, new events)
- [ ] **Automated re-analysis** — Detect when a subject needs updating based on age and importance
- [ ] **Console command: `app:update-subjects`** — Batch update subjects that need refreshing

### Entities

```
TimelineEvent
├── id (UUID)
├── subject (ManyToOne → Subject)
├── date (date)
├── title (string)
├── description (text)
├── significance (int, 1-10)
├── sources (json)
└── createdAt (datetime)

Argument
├── id (UUID)
├── subject (ManyToOne → Subject)
├── content (text)
├── side (string, enum: for, against, nuanced)
├── underlyingValues (text, nullable, the principles behind the argument)
├── actor (ManyToOne → Actor, nullable, who makes this argument)
├── counterArguments (ManyToMany → Argument, self-referencing)
└── createdAt (datetime)

Position
├── id (UUID)
├── actor (ManyToOne → Actor)
├── subject (ManyToOne → Subject)
├── stance (string, enum: strongly_for, for, neutral, against, strongly_against, ambiguous)
├── summary (text, brief description of their position)
├── sources (json)
├── recordedAt (datetime)
└── updatedAt (datetime)
```

### Definition of Done

Subjects have timelines, debates are mapped with arguments and positions, and subjects can be updated multiple times while preserving their history. The web UI shows timeline and debate map views.

---

## Phase 4: Interactive User Experience

**Goal**: Build the interactive frontend — chat interface, search, dashboards, and exploration tools.

**Priority**: 🟡 Medium

### Features

- [ ] **User Query Agent (RAG)** — Answer user questions using ChromaDB retrieval + Perplexity
- [ ] **Chat interface** — Live Component for real-time AI conversation about political subjects
- [ ] **Semantic search** — Search bar that uses ChromaDB to find relevant subjects by meaning, not just keywords
- [ ] **Subject exploration** — Browse subjects by category, importance, actor, or media source
- [ ] **Actor profile pages** — Dedicated pages for actors showing all their positions and subjects
- [ ] **Media source dashboard** — Overview of a media outlet's coverage patterns and tone distribution
- [ ] **Comparison view** — Side-by-side comparison of how two media outlets cover the same subject
- [ ] **Dashboard homepage** — Landing page with today's top subjects, recent analyses, trend indicators
- [ ] **Filtering & sorting** — Advanced filters on subject list (category, date range, importance, status)
- [ ] **Responsive design** — Mobile-friendly layout

### Technical Tasks

- [ ] Configure `ai.chat` with message store for conversation persistence
- [ ] Build Live Component for chat UI with streaming responses
- [ ] Implement ChromaDB-backed search endpoint
- [ ] Build Stimulus controllers for interactive filtering
- [ ] Create Turbo Frames for partial page updates on exploration pages
- [ ] Design and implement responsive CSS

### Definition of Done

Users can chat with the AI about political subjects, search the knowledge base semantically, explore subjects through multiple entry points, and compare media coverage interactively.

---

## Phase 5: Automation, Scale & Advanced Features

**Goal**: Automate the pipeline, add advanced analysis capabilities, and prepare for scale.

**Priority**: 🟢 Long-term

### Features

- [ ] **Scheduled news retrieval** — Symfony Scheduler/Messenger for automatic periodic retrieval (e.g., every 6 hours)
- [ ] **Multi-agent orchestration** — Coordinate agents automatically (retrieve → analyze → map → update)
- [ ] **Additional AI platforms** — Add OpenAI or Anthropic for specialized tasks (e.g., embeddings, fact-checking)
- [ ] **Fact-checking integration** — Cross-reference claims with fact-checking databases
- [ ] **Historical trend analysis** — Analyze how media coverage and debate positions evolve over weeks/months
- [ ] **Subject clustering** — AI-driven grouping of related subjects into themes
- [ ] **Notification system** — Alert users when subjects they follow have significant updates
- [ ] **API endpoints** — REST/JSON API for external integrations
- [ ] **User accounts** — Authentication, personal dashboards, subject following
- [ ] **Export features** — Export analyses, timelines, and debate maps as PDF/CSV
- [ ] **Multi-language support** — Analyze news in multiple languages (French, English, etc.)
- [ ] **Source credibility scoring** — Score and weight sources based on reliability and track record
- [ ] **Sentiment tracking** — Track public sentiment evolution over time for subjects
- [ ] **Caching layer** — Redis-based caching for AI responses, search results, and computed dashboards
- [ ] **Rate limiting** — Protect AI API calls with Symfony's rate limiter
- [ ] **Admin panel** — Management interface for reviewing and curating AI outputs

### Technical Tasks

- [ ] Add `symfony/messenger` for async job processing
- [ ] Add `symfony/scheduler` for periodic tasks
- [ ] Configure multi-agent system in `ai.yaml`
- [ ] Add Redis service to Docker Compose
- [ ] Implement caching strategy for AI platforms
- [ ] Build REST API controllers
- [ ] Add authentication bundle

---

## Feature Matrix — Quick Reference

| Feature | Phase | Priority | Depends On |
|---------|-------|----------|------------|
| Perplexity AI integration | 1 | 🔴 | — |
| News Retriever Agent | 1 | 🔴 | Perplexity |
| Subject entity & CRUD | 1 | 🔴 | — |
| Subject list/detail pages | 1 | 🔴 | Subject entity |
| ChromaDB indexing | 1 | 🔴 | Subject entity |
| Subject Analyzer Agent | 2 | 🟠 | Phase 1 |
| Media Tone Analyzer Agent | 2 | 🟠 | Phase 1 |
| Actor entity & tracking | 2 | 🟠 | Phase 1 |
| MediaSource & ArticleCoverage | 2 | 🟠 | Phase 1 |
| Timeline tracking | 3 | 🟡 | Phase 2 |
| Argument & Position mapping | 3 | 🟡 | Phase 2 |
| Subject Updater Agent | 3 | 🟡 | Phase 2 |
| Debate Mapper Agent | 3 | 🟡 | Phase 2 |
| RAG chat interface | 4 | 🟡 | Phase 3 |
| Semantic search | 4 | 🟡 | ChromaDB |
| Interactive dashboards | 4 | 🟡 | Phase 3 |
| Scheduled automation | 5 | 🟢 | Phase 4 |
| Multi-agent orchestration | 5 | 🟢 | All agents |
| API endpoints | 5 | 🟢 | Phase 4 |
| User accounts | 5 | 🟢 | Phase 4 |

---

## Development Notes

### Iterative Approach

Each phase should be completed and working before moving to the next. Within a phase, features should be developed incrementally:

1. Entity/schema first
2. Service layer
3. AI agent configuration and prompt engineering
4. Console command for testing
5. Web interface last

### Prompt Engineering

AI agent prompts are the most critical and iterative part of the system. They should be:
- Stored as separate files in `config/ai/prompts/`
- Version-controlled and reviewed carefully
- Tested extensively with diverse inputs
- Refined based on output quality

### Testing Strategy

- **Unit tests**: Service layer, entity validation
- **Integration tests**: Agent tool execution, database queries
- **Functional tests**: Console commands, controller responses
- **Manual review**: AI output quality (inherently non-deterministic)
