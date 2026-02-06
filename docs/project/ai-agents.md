# AI Agents

## Overview

Democratic Analytics uses the **Symfony AI Agent** framework to orchestrate multiple specialized AI agents. Each agent has a clearly defined role, its own system prompt, and access to specific tools that let it interact with the application's data layer.

All agents use the **Perplexity AI platform** as the primary LLM, leveraging its search-augmented capabilities for real-time information retrieval with source citations.

## Agent Architecture

```
┌─────────────────────────────────────────────────────────────┐
│                    ORCHESTRATOR (future)                     │
│         Multi-agent routing & handoff coordination          │
├─────────┬───────────┬───────────┬──────────┬───────────────┤
│  News   │  Subject  │  Tone     │  Debate  │  User Query   │
│Retriever│  Analyzer │  Analyzer │  Mapper  │  Agent (RAG)  │
├─────────┴───────────┴───────────┴──────────┴───────────────┤
│                    TOOL LAYER                               │
│  SubjectLookup · SubjectCreate · SubjectUpdate              │
│  MediaAnalysis · ActorLookup · ChromaDB Search              │
├─────────────────────────────────────────────────────────────┤
│              PLATFORM: Perplexity AI                        │
└─────────────────────────────────────────────────────────────┘
```

## Agent Definitions

### 1. News Retriever Agent

**Role**: Scan the news landscape and identify the most important political subjects of the day.

**When**: Triggered manually via console command or automatically via scheduler.

**Platform**: Perplexity AI (leverages real-time web search)

**System Prompt** (draft):
```
You are a political news analyst. Your task is to identify the most important 
political subjects being discussed today in France (and optionally Europe/World).

For each subject you identify:
1. Provide a clear, concise title
2. Write a summary of the current state of the debate (2-3 paragraphs)
3. List the main actors involved (politicians, parties, institutions)
4. List the media sources covering this subject
5. Indicate whether this is a new subject or an evolution of an existing one
6. Rate the importance/urgency on a scale of 1-10

Output your response as structured JSON.
```

**Tools available**:
- `SubjectLookupTool` — Check if a subject already exists (via semantic similarity)
- `SubjectCreateTool` — Create a new Subject entity
- `SubjectUpdateTool` — Update an existing Subject with new information

**Output**: List of identified subjects with metadata, ready for deeper analysis.

---

### 2. Subject Analyzer Agent

**Role**: Produce an in-depth analysis of a specific political subject.

**When**: After a new subject is identified by the News Retriever, or on demand.

**System Prompt** (draft):
```
You are a political analyst specializing in providing comprehensive, neutral 
analysis of political subjects. Given a political subject, you must:

1. Provide historical context — how did this issue emerge?
2. Explain the current state of the debate
3. Identify the key arguments on all sides
4. List the main actors and their positions
5. Explain what is at stake and who is affected
6. Note any factual elements that are agreed upon by all sides
7. Highlight areas of genuine disagreement vs. rhetorical framing

Your analysis must be balanced, factual, and avoid taking any side.
Always cite your sources.
```

**Tools available**:
- `SubjectLookupTool` — Retrieve existing subject data
- `ActorLookupTool` — Get information about political actors
- ChromaDB retriever — Fetch related context from the knowledge base

**Output**: A structured `Analysis` entity of type `comprehensive_summary`.

---

### 3. Media Tone Analyzer Agent

**Role**: Analyze how different media outlets cover a given subject, detecting tone, framing, and potential bias.

**When**: After a subject is created or updated, to map media coverage patterns.

**System Prompt** (draft):
```
You are a media analysis expert. Given a political subject, analyze how different 
media outlets are covering it. For each source you examine:

1. Identify the outlet's editorial framing of the subject
2. Classify the tone: neutral, favorable, critical, alarmist, dismissive, etc.
3. Note what aspects of the subject the outlet emphasizes or omits
4. Identify loaded language, emotional framing, or rhetorical devices
5. Compare coverage across outlets to highlight discrepancies

Be objective and analytical. Do not judge outlets — describe their approach factually.
Use specific quotes or examples when possible.
```

**Tools available**:
- `SubjectLookupTool` — Get the subject being analyzed
- `MediaAnalysisTool` — Record media coverage analysis for a subject/source pair

**Output**: A structured `Analysis` entity of type `media_analysis`, plus `ArticleCoverage` records.

---

### 4. Debate Mapper Agent

**Role**: Map the full structure of a political debate — arguments, counter-arguments, actors, and their positions.

**When**: During deep analysis of a subject, or when updating a subject with new developments.

**System Prompt** (draft):
```
You are a debate analyst. Given a political subject, map the complete structure 
of the debate:

1. List every distinct argument being made (for, against, nuanced)
2. For each argument, identify who is making it (which actor, party, group)
3. Identify the underlying values or principles behind each argument
4. Map counter-arguments to their corresponding arguments
5. Note consensus points (where most actors agree)
6. Identify the most contentious points of disagreement
7. Track how arguments have evolved over time (if historical data is available)

Structure your output so that the debate can be visualized as a map of 
interconnected positions and arguments.
```

**Tools available**:
- `SubjectLookupTool` — Get subject context
- `ActorLookupTool` — Retrieve actor information
- `SubjectUpdateTool` — Add arguments and positions to the subject
- ChromaDB retriever — Get historical context

**Output**: `Argument` entities linked to `Actor` entities via `Position` records.

---

### 5. Subject Updater Agent

**Role**: Enrich existing subjects with new information, maintaining continuity and tracking evolution.

**When**: When a subject already exists in the database and new related news emerges.

**System Prompt** (draft):
```
You are responsible for maintaining and updating political subjects in our 
knowledge base. Given an existing subject and new information:

1. Determine what is genuinely new vs. already recorded
2. Update the subject summary to reflect the latest state
3. Add new timeline events with dates and significance ratings
4. Update actor positions if they have changed
5. Add new arguments that have emerged in the debate
6. Note if the subject's importance or urgency has changed
7. Identify new connections to other subjects in the knowledge base

Preserve existing information — append and enrich, do not replace.
Be precise about what has changed and when.
```

**Tools available**:
- `SubjectLookupTool` — Get current subject data
- `SubjectUpdateTool` — Modify the subject
- `ActorLookupTool` — Check actor information
- ChromaDB retriever — Compare with existing knowledge

**Output**: Updated `Subject` entity, new `TimelineEvent` entries, updated `Position` records.

---

### 6. User Query Agent (RAG)

**Role**: Answer user questions about political subjects using retrieval-augmented generation.

**When**: When a user submits a question through the chat interface.

**System Prompt** (draft):
```
You are a knowledgeable political analyst assistant. Users will ask you questions 
about political subjects, debates, actors, and media coverage.

Answer using the context provided from our knowledge base. When answering:

1. Be comprehensive but concise
2. Present multiple perspectives when relevant
3. Cite specific subjects, analyses, and sources from the knowledge base
4. If the knowledge base does not contain enough information, say so clearly
5. Never invent facts — only use information from the provided context
6. Suggest related subjects the user might want to explore

You have access to our full knowledge base of political subjects and analyses.
```

**Tools available**:
- ChromaDB retriever — Semantic search over the full knowledge base
- `SubjectLookupTool` — Get specific subject details
- `ActorLookupTool` — Get actor information

**Output**: Natural-language response streamed to a Live Component.

---

## Symfony AI Configuration (Planned)

```yaml
# config/packages/ai.yaml
ai:
    platform:
        perplexity:
            api_key: '%env(PERPLEXITY_API_KEY)%'

    agent:
        news_retriever:
            platform: 'ai.platform.perplexity'
            model: 'sonar-pro'
            prompt:
                file: '%kernel.project_dir%/config/ai/prompts/news_retriever.md'
            tools:
                services:
                    - { service: 'App\AI\Tool\SubjectLookupTool' }
                    - { service: 'App\AI\Tool\SubjectCreateTool' }
                    - { service: 'App\AI\Tool\SubjectUpdateTool' }

        subject_analyzer:
            platform: 'ai.platform.perplexity'
            model: 'sonar-pro'
            prompt:
                file: '%kernel.project_dir%/config/ai/prompts/subject_analyzer.md'
            tools:
                services:
                    - { service: 'App\AI\Tool\SubjectLookupTool' }
                    - { service: 'App\AI\Tool\ActorLookupTool' }

        tone_analyzer:
            platform: 'ai.platform.perplexity'
            model: 'sonar-pro'
            prompt:
                file: '%kernel.project_dir%/config/ai/prompts/tone_analyzer.md'
            tools:
                services:
                    - { service: 'App\AI\Tool\SubjectLookupTool' }
                    - { service: 'App\AI\Tool\MediaAnalysisTool' }

        debate_mapper:
            platform: 'ai.platform.perplexity'
            model: 'sonar-pro'
            prompt:
                file: '%kernel.project_dir%/config/ai/prompts/debate_mapper.md'
            tools:
                services:
                    - { service: 'App\AI\Tool\SubjectLookupTool' }
                    - { service: 'App\AI\Tool\ActorLookupTool' }
                    - { service: 'App\AI\Tool\SubjectUpdateTool' }

        subject_updater:
            platform: 'ai.platform.perplexity'
            model: 'sonar-pro'
            prompt:
                file: '%kernel.project_dir%/config/ai/prompts/subject_updater.md'
            tools:
                services:
                    - { service: 'App\AI\Tool\SubjectLookupTool' }
                    - { service: 'App\AI\Tool\SubjectUpdateTool' }
                    - { service: 'App\AI\Tool\ActorLookupTool' }

        user_query:
            platform: 'ai.platform.perplexity'
            model: 'sonar-pro'
            prompt:
                file: '%kernel.project_dir%/config/ai/prompts/user_query.md'
            tools:
                services:
                    - { service: 'App\AI\Tool\SubjectLookupTool' }
                    - { service: 'App\AI\Tool\ActorLookupTool' }

    store:
        chromadb:
            default:
                collection: 'democratic_analytics'

    vectorizer:
        default:
            platform: 'ai.platform.perplexity'
            model: 'sonar-pro'

    retriever:
        default:
            store: 'ai.store.chromadb.default'
```

## AI Tools (Symfony AI Tool Classes)

Tools are PHP classes that agents can invoke during their reasoning. They bridge the AI layer to the application's data layer.

### SubjectLookupTool
- **Purpose**: Search for existing subjects by keyword or semantic similarity
- **Input**: Search query string
- **Output**: List of matching subjects with summaries
- **Uses**: Doctrine repository + ChromaDB semantic search

### SubjectCreateTool
- **Purpose**: Create a new Subject entity in the database
- **Input**: Title, summary, importance, category, sources
- **Output**: Created subject ID
- **Uses**: Doctrine EntityManager

### SubjectUpdateTool
- **Purpose**: Update an existing Subject with new information
- **Input**: Subject ID, updated fields (summary, importance, new events, arguments)
- **Output**: Confirmation of updates applied
- **Uses**: Doctrine EntityManager

### ActorLookupTool
- **Purpose**: Find political actors by name or affiliation
- **Input**: Actor name or search query
- **Output**: Actor details, positions, affiliated subjects
- **Uses**: Doctrine repository

### MediaAnalysisTool
- **Purpose**: Record media coverage analysis for a subject
- **Input**: Subject ID, media source, tone classification, coverage notes
- **Output**: Created ArticleCoverage record
- **Uses**: Doctrine EntityManager

## Multi-Agent Orchestration (Phase 5)

In later phases, a **multi-agent system** using `symfony/ai-agent`'s multi-agent support will coordinate the specialized agents:

```yaml
ai:
    multi_agent:
        analysis_orchestrator:
            orchestrator: 'ai.agent.news_retriever'
            handoffs:
                news_retriever:
                    - 'ai.agent.subject_analyzer'
                    - 'ai.agent.tone_analyzer'
                subject_analyzer:
                    - 'ai.agent.debate_mapper'
                    - 'ai.agent.subject_updater'
            fallback: 'ai.agent.user_query'
```

This enables a full pipeline where the News Retriever identifies subjects, then hands off to specialized agents for deep analysis, tone evaluation, and debate mapping — all coordinated automatically.
