# AI PRD UI Generator MVP Design

## Goal

Build a simple authenticated Laravel + Livewire application that turns a meeting transcript into:

- PRD markdown
- User stories markdown
- Functional requirements markdown
- One single-page HTML output

The app must stay narrow:

- use existing auth
- accept paste text and `.txt` upload only
- use Gemini only
- validate transcript at a medium strictness level
- let users clarify extracted context before generation
- generate outputs sequentially in a background job
- tolerate partial failures
- allow regenerate of selected outputs after clarification edits

## Product Decisions

### In Scope

- authenticated access using the existing Fortify flow
- transcript intake via paste text or `.txt` upload
- AI context extraction
- sectioned clarification form
- AI recommendation for:
  - template family: `landing` or `app_shell`
  - design system: `minimal`, `modern`, `corporate`
- fixed-schema markdown generation for:
  - PRD
  - User Stories
  - Functional Requirements
- hybrid HTML generation using reusable Tailwind building blocks
- single session result page with per-output status
- regenerate selected outputs after clarification changes
- session ownership via `user_id`

### Out of Scope

- PDF upload
- multi-user collaboration
- session history index
- Notion, Jira, or Linear integration
- websockets
- prompt customization
- AI provider switching
- multi-language
- PDF export for MVP core

## UX Shape

### 1. Intake Page

- input transcript via textarea or `.txt` upload
- run medium transcript validation
- trigger AI extraction if valid

### 2. Clarification Page

- one page, split by sections
- editable context sections:
  - project context
  - goals
  - key features
- guided AI recommendation sections:
  - template family options
  - design system options
- no wizard

### 3. Result Page

- per-output generation status
- markdown rendering for the three text outputs
- HTML preview for the generated page
- edit clarification
- regenerate selected outputs

## Data Model

### transcript_sessions

- `user_id`
- `ulid`
- `source_type`
- `transcript_text`
- `status`
- extracted AI payloads:
  - `extracted_context`
  - `layout_recommendations`
  - `design_system_recommendations`
- clarification fields:
  - `project_name`
  - `project_summary`
  - `target_users`
  - `goals`
  - `key_features`
  - `template_family`
  - `design_system`

### generation_outputs

One row per artifact:

- `prd`
- `user_stories`
- `functional_requirements`
- `html_page`

Each output tracks:

- `status`
- `content`
- `error_message`
- `revision_number`
- `clarification_snapshot`

No dedicated `session_revisions` table for MVP.

## Generation Design

### Extraction

- one Gemini extraction pass after intake
- if extraction fails, show manual retry on the UI
- no aggressive retry logic here

### Output Generation

- use one orchestrator job: `GenerateSessionOutputs`
- sequential order:
  1. PRD
  2. User Stories
  3. Functional Requirements
  4. HTML Page

### Failure Handling

- if one output fails, continue the next output
- final session status:
  - `completed` if all complete
  - `partial` if mixed complete and failed
  - `failed` if all fail

### Revision Handling

- user edits clarification after first generation
- user selects outputs to regenerate
- only selected outputs are reset and regenerated
- non-selected outputs stay untouched

## Technical Shape

### Backend

- Laravel 13
- existing Fortify auth
- Livewire 4 pages/components
- database queue
- Laravel AI SDK with Gemini

### HTML Strategy

Do not let AI emit an unrestricted page.

Instead:

- app owns reusable Tailwind sections
- app owns template families
- app owns design system tokens
- AI provides structured content and layout choice

This keeps output stable and keeps the implementation small.

## Architecture Boundaries

Use a small service surface:

- `TranscriptValidationService`
- `TranscriptExtractionService`
- `SessionGenerationService`
- `MarkdownBlueprintService`
- `HtmlAssemblyService`

Use one generation job, not four separate jobs.

Do not add abstractions for future providers, real-time transport, or revision history tables yet.
