# AI PRD UI Generator MVP Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Build the approved MVP flow for transcript-to-docs generation with existing auth, Gemini extraction, guided clarification, fixed-schema markdown outputs, and one generated HTML page.

**Architecture:** Use a small Livewire flow with three authenticated pages backed by `transcript_sessions` and per-artifact `generation_outputs`. Run extraction once up front, then run sequential generation in one queue job while keeping HTML generation constrained to reusable Tailwind templates and fixed design-system choices.

**Tech Stack:** Laravel 13, Livewire 4, Fortify, Laravel AI SDK, database queue, Pest, Tailwind CSS v4, Flux UI free components.

---

## File Map

### Create

- `app/Models/TranscriptSession.php`
- `app/Models/GenerationOutput.php`
- `database/migrations/2026_06_12_000001_create_transcript_sessions_table.php`
- `database/migrations/2026_06_12_000002_create_generation_outputs_table.php`
- `app/Policies/TranscriptSessionPolicy.php`
- `app/Services/Transcripts/TranscriptValidationService.php`
- `app/Services/Transcripts/TranscriptExtractionService.php`
- `app/Services/Generation/SessionGenerationService.php`
- `app/Services/Generation/MarkdownBlueprintService.php`
- `app/Services/Generation/HtmlAssemblyService.php`
- `app/Services/Generation/PromptBuilder.php`
- `app/Jobs/GenerateSessionOutputs.php`
- `app/Livewire/Transcripts/CreateTranscriptSession.php`
- `app/Livewire/Transcripts/EditTranscriptClarification.php`
- `app/Livewire/Transcripts/ShowTranscriptSession.php`
- `resources/views/livewire/transcripts/create-transcript-session.blade.php`
- `resources/views/livewire/transcripts/edit-transcript-clarification.blade.php`
- `resources/views/livewire/transcripts/show-transcript-session.blade.php`
- `resources/views/components/generated-pages/landing.blade.php`
- `resources/views/components/generated-pages/app-shell.blade.php`
- `tests/Feature/Transcripts/CreateTranscriptSessionTest.php`
- `tests/Feature/Transcripts/EditTranscriptClarificationTest.php`
- `tests/Feature/Transcripts/ShowTranscriptSessionTest.php`
- `tests/Feature/Jobs/GenerateSessionOutputsTest.php`
- `tests/Unit/Services/TranscriptValidationServiceTest.php`
- `tests/Unit/Services/MarkdownBlueprintServiceTest.php`
- `tests/Unit/Services/HtmlAssemblyServiceTest.php`

### Modify

- `routes/web.php`
- `config/services.php`
- `app/Providers/AppServiceProvider.php`
- `resources/views/dashboard.blade.php`
- `tests/Feature/DashboardTest.php`

---

### Task 1: Lock The Domain Model

**Files:**
- Create: `database/migrations/2026_06_12_000001_create_transcript_sessions_table.php`
- Create: `database/migrations/2026_06_12_000002_create_generation_outputs_table.php`
- Create: `app/Models/TranscriptSession.php`
- Create: `app/Models/GenerationOutput.php`
- Test: `tests/Feature/Transcripts/CreateTranscriptSessionTest.php`

- [ ] **Step 1: Write the first failing feature test for session creation shape**

Cover:
- authenticated user can create a transcript session draft
- session belongs to the user
- four output rows are not created yet during intake

- [ ] **Step 2: Run the targeted test and confirm it fails**

Run:
```bash
php artisan test --compact tests/Feature/Transcripts/CreateTranscriptSessionTest.php
```

- [ ] **Step 3: Create the migrations**

`transcript_sessions` columns:
- `id`
- `user_id`
- `ulid`
- `source_type`
- `transcript_text`
- `status`
- `extracted_context` JSON nullable
- `layout_recommendations` JSON nullable
- `design_system_recommendations` JSON nullable
- `project_name` nullable
- `project_summary` nullable
- `target_users` nullable
- `goals` JSON nullable
- `key_features` JSON nullable
- `template_family` nullable
- `design_system` nullable
- timestamps

`generation_outputs` columns:
- `id`
- `transcript_session_id`
- `type`
- `status`
- `content` LONGTEXT nullable
- `error_message` TEXT nullable
- `revision_number` unsigned integer default `1`
- `clarification_snapshot` JSON nullable
- timestamps

- [ ] **Step 4: Add model relationships, casts, and ownership-safe fillable fields**

Use casts for:
- arrays/json
- timestamps

- [ ] **Step 5: Run the targeted test again**

Run:
```bash
php artisan test --compact tests/Feature/Transcripts/CreateTranscriptSessionTest.php
```

- [ ] **Step 6: Commit**

Use a Lore commit message after the task passes.

---

### Task 2: Add Session Authorization And Routes

**Files:**
- Create: `app/Policies/TranscriptSessionPolicy.php`
- Modify: `app/Providers/AppServiceProvider.php`
- Modify: `routes/web.php`
- Test: `tests/Feature/Transcripts/ShowTranscriptSessionTest.php`

- [ ] **Step 1: Write the failing access test**

Cover:
- owner can open a transcript session
- another authenticated user cannot open it
- guest is redirected to login

- [ ] **Step 2: Run the targeted test and confirm it fails**

Run:
```bash
php artisan test --compact tests/Feature/Transcripts/ShowTranscriptSessionTest.php
```

- [ ] **Step 3: Register the policy and add authenticated transcript routes**

Routes:
- `GET /transcripts/create`
- `GET /transcripts/{transcriptSession}/clarify`
- `GET /transcripts/{transcriptSession}`

Keep all routes inside the existing `auth` and `verified` middleware group.

- [ ] **Step 4: Implement simple owner-only policy methods**

Needed actions:
- `view`
- `update`

- [ ] **Step 5: Run the targeted test again**

Run:
```bash
php artisan test --compact tests/Feature/Transcripts/ShowTranscriptSessionTest.php
```

- [ ] **Step 6: Commit**

Use a Lore commit message after the task passes.

---

### Task 3: Build Transcript Intake Validation

**Files:**
- Create: `app/Services/Transcripts/TranscriptValidationService.php`
- Create: `tests/Unit/Services/TranscriptValidationServiceTest.php`
- Create: `app/Livewire/Transcripts/CreateTranscriptSession.php`
- Create: `resources/views/livewire/transcripts/create-transcript-session.blade.php`
- Test: `tests/Feature/Transcripts/CreateTranscriptSessionTest.php`

- [ ] **Step 1: Write the failing unit test for medium validation**

Cover:
- rejects too-short transcript
- rejects transcript without product/problem/feature signals
- accepts transcript with product goal and feature context

- [ ] **Step 2: Run the validation unit test and confirm it fails**

Run:
```bash
php artisan test --compact tests/Unit/Services/TranscriptValidationServiceTest.php
```

- [ ] **Step 3: Implement `TranscriptValidationService`**

Rules:
- accept pasted text or parsed `.txt` content
- enforce medium signal checks
- return structured validation result for UI errors

- [ ] **Step 4: Build the intake Livewire component**

Behavior:
- textarea input
- `.txt` upload only
- clear validation messages
- persist session as owned by current user
- send valid session into extraction flow

- [ ] **Step 5: Add the intake page feature tests**

Cover:
- authenticated user sees intake page
- invalid transcript shows message
- `.txt` upload is accepted
- non-`.txt` upload is rejected

- [ ] **Step 6: Run both tests**

Run:
```bash
php artisan test --compact tests/Unit/Services/TranscriptValidationServiceTest.php tests/Feature/Transcripts/CreateTranscriptSessionTest.php
```

- [ ] **Step 7: Commit**

Use a Lore commit message after the task passes.

---

### Task 4: Add Gemini Extraction And Clarification Entry

**Files:**
- Create: `app/Services/Transcripts/TranscriptExtractionService.php`
- Create: `app/Services/Generation/PromptBuilder.php`
- Modify: `config/services.php`
- Test: `tests/Feature/Transcripts/EditTranscriptClarificationTest.php`

- [ ] **Step 1: Write the failing clarification entry test**

Cover:
- successful extract redirects to clarification page
- clarification page shows extracted defaults
- clarification page shows AI recommendations for template family and design system

- [ ] **Step 2: Run the targeted test and confirm it fails**

Run:
```bash
php artisan test --compact tests/Feature/Transcripts/EditTranscriptClarificationTest.php
```

- [ ] **Step 3: Add Gemini configuration**

Add only what is needed:
- API key env mapping
- default model setting for extraction/generation

- [ ] **Step 4: Implement extraction service using Laravel AI SDK**

Requirements:
- one extraction call
- structured output for:
  - context fields
  - template family options
  - design system options
- best-effort failure message returned to UI

- [ ] **Step 5: Persist extracted payloads on the session**

Status transition:
- `draft` -> `extracting` -> `clarifying`

- [ ] **Step 6: Run the targeted test again**

Run:
```bash
php artisan test --compact tests/Feature/Transcripts/EditTranscriptClarificationTest.php
```

- [ ] **Step 7: Commit**

Use a Lore commit message after the task passes.

---

### Task 5: Build The Sectioned Clarification Form

**Files:**
- Create: `app/Livewire/Transcripts/EditTranscriptClarification.php`
- Create: `resources/views/livewire/transcripts/edit-transcript-clarification.blade.php`
- Test: `tests/Feature/Transcripts/EditTranscriptClarificationTest.php`

- [ ] **Step 1: Extend the failing feature test for guided edit rules**

Cover:
- user may edit context sections
- template family must be one of the AI-offered options
- design system must be one of the AI-offered options
- save moves session into generation-ready state

- [ ] **Step 2: Run the test and confirm it fails**

Run:
```bash
php artisan test --compact tests/Feature/Transcripts/EditTranscriptClarificationTest.php
```

- [ ] **Step 3: Implement the Livewire form state**

Sections:
- project context
- goals
- key features
- UI direction
- generation scope summary

- [ ] **Step 4: Build the Blade view with Flux components where they fit**

Use:
- `flux:field`
- `flux:input`
- `flux:textarea`
- `flux:radio` or `flux:select`
- `flux:button`

Avoid custom UI unless Flux does not cover the control.

- [ ] **Step 5: Save clarification back to the session**

Status stays `clarifying` until generation is requested.

- [ ] **Step 6: Run the clarification test again**

Run:
```bash
php artisan test --compact tests/Feature/Transcripts/EditTranscriptClarificationTest.php
```

- [ ] **Step 7: Commit**

Use a Lore commit message after the task passes.

---

### Task 6: Create Fixed Markdown Blueprints

**Files:**
- Create: `app/Services/Generation/MarkdownBlueprintService.php`
- Create: `tests/Unit/Services/MarkdownBlueprintServiceTest.php`

- [ ] **Step 1: Write the failing blueprint unit test**

Cover:
- PRD markdown always contains fixed sections
- User Stories markdown always contains fixed sections
- Functional Requirements markdown always contains fixed sections

- [ ] **Step 2: Run the targeted unit test and confirm it fails**

Run:
```bash
php artisan test --compact tests/Unit/Services/MarkdownBlueprintServiceTest.php
```

- [ ] **Step 3: Implement one fixed schema per output**

Include stable headings and normalized ordering. Let AI fill content, not document shape.

- [ ] **Step 4: Add simple helper methods for prompt-ready section payloads**

Keep the service focused on schema and formatting only.

- [ ] **Step 5: Run the targeted unit test again**

Run:
```bash
php artisan test --compact tests/Unit/Services/MarkdownBlueprintServiceTest.php
```

- [ ] **Step 6: Commit**

Use a Lore commit message after the task passes.

---

### Task 7: Build Reusable HTML Template Assembly

**Files:**
- Create: `app/Services/Generation/HtmlAssemblyService.php`
- Create: `resources/views/components/generated-pages/landing.blade.php`
- Create: `resources/views/components/generated-pages/app-shell.blade.php`
- Create: `tests/Unit/Services/HtmlAssemblyServiceTest.php`

- [ ] **Step 1: Write the failing HTML assembly unit test**

Cover:
- `landing` template renders expected section skeleton
- `app_shell` template renders expected shell structure
- selected design system changes token classes or attributes in a predictable way

- [ ] **Step 2: Run the targeted test and confirm it fails**

Run:
```bash
php artisan test --compact tests/Unit/Services/HtmlAssemblyServiceTest.php
```

- [ ] **Step 3: Implement design-system token mapping**

Support only:
- `minimal`
- `modern`
- `corporate`

- [ ] **Step 4: Implement both reusable HTML template families**

Keep them simple:
- landing page sections
- app shell header/sidebar/content shell

- [ ] **Step 5: Implement HTML assembly from structured content**

Do not accept arbitrary full-page HTML from AI.

- [ ] **Step 6: Run the targeted unit test again**

Run:
```bash
php artisan test --compact tests/Unit/Services/HtmlAssemblyServiceTest.php
```

- [ ] **Step 7: Commit**

Use a Lore commit message after the task passes.

---

### Task 8: Add The Sequential Generation Job

**Files:**
- Create: `app/Services/Generation/SessionGenerationService.php`
- Create: `app/Jobs/GenerateSessionOutputs.php`
- Create: `tests/Feature/Jobs/GenerateSessionOutputsTest.php`

- [ ] **Step 1: Write the failing job feature test**

Cover:
- creates and updates per-output statuses in order
- continues after one output fails
- marks session `partial` on mixed results
- marks session `completed` when all succeed

- [ ] **Step 2: Run the job test and confirm it fails**

Run:
```bash
php artisan test --compact tests/Feature/Jobs/GenerateSessionOutputsTest.php
```

- [ ] **Step 3: Implement `SessionGenerationService`**

Responsibilities:
- choose target outputs
- build prompts from clarification snapshot
- call markdown or HTML generators
- persist output status transitions

- [ ] **Step 4: Implement the orchestrator job**

Behavior:
- accepts a session id and optional target output list
- runs outputs sequentially
- catches and stores per-output failures
- continues to remaining outputs

- [ ] **Step 5: Add conservative queue settings**

Keep it simple:
- one job
- explicit timeout
- explicit backoff
- no parallel fan-out

- [ ] **Step 6: Run the job test again**

Run:
```bash
php artisan test --compact tests/Feature/Jobs/GenerateSessionOutputsTest.php
```

- [ ] **Step 7: Commit**

Use a Lore commit message after the task passes.

---

### Task 9: Build The Result Page And Regenerate Flow

**Files:**
- Create: `app/Livewire/Transcripts/ShowTranscriptSession.php`
- Create: `resources/views/livewire/transcripts/show-transcript-session.blade.php`
- Modify: `tests/Feature/Transcripts/ShowTranscriptSessionTest.php`

- [ ] **Step 1: Extend the failing result-page test**

Cover:
- owner sees per-output status
- markdown outputs render in the page
- HTML output preview is shown
- selected-output regenerate resets only chosen artifacts

- [ ] **Step 2: Run the targeted test and confirm it fails**

Run:
```bash
php artisan test --compact tests/Feature/Transcripts/ShowTranscriptSessionTest.php
```

- [ ] **Step 3: Implement the result Livewire component**

Behavior:
- authorize owner
- poll output status
- show session summary
- expose regenerate action

- [ ] **Step 4: Build the result view**

Use:
- per-output cards
- markdown rendering area
- HTML preview area
- selected regenerate controls

- [ ] **Step 5: Wire regeneration to create a fresh clarification snapshot for chosen outputs**

Only selected outputs should be reset to `pending`.

- [ ] **Step 6: Run the result-page test again**

Run:
```bash
php artisan test --compact tests/Feature/Transcripts/ShowTranscriptSessionTest.php
```

- [ ] **Step 7: Commit**

Use a Lore commit message after the task passes.

---

### Task 10: Connect Navigation And Replace Placeholder Dashboard

**Files:**
- Modify: `resources/views/dashboard.blade.php`
- Modify: `tests/Feature/DashboardTest.php`

- [ ] **Step 1: Extend the dashboard test**

Cover:
- authenticated user sees a clear entry point into transcript generation

- [ ] **Step 2: Run the targeted test and confirm it fails**

Run:
```bash
php artisan test --compact tests/Feature/DashboardTest.php
```

- [ ] **Step 3: Replace the placeholder dashboard with a simple launch surface**

Keep it narrow:
- start new transcript session
- short explanation of supported flow
- no history list

- [ ] **Step 4: Run the targeted test again**

Run:
```bash
php artisan test --compact tests/Feature/DashboardTest.php
```

- [ ] **Step 5: Commit**

Use a Lore commit message after the task passes.

---

### Task 11: Verify End To End And Polish Minimal Edges

**Files:**
- Modify only as needed from earlier tasks
- Test: `tests/Feature/Transcripts/*.php`
- Test: `tests/Feature/Jobs/GenerateSessionOutputsTest.php`
- Test: `tests/Unit/Services/*.php`

- [ ] **Step 1: Run focused transcript feature tests**

Run:
```bash
php artisan test --compact tests/Feature/Transcripts
```

- [ ] **Step 2: Run focused service and job tests**

Run:
```bash
php artisan test --compact tests/Feature/Jobs/GenerateSessionOutputsTest.php tests/Unit/Services
```

- [ ] **Step 3: Run formatting**

Run:
```bash
vendor/bin/pint --dirty --format agent
```

- [ ] **Step 4: Run the broader safety net**

Run:
```bash
php artisan test --compact
```

- [ ] **Step 5: Run static analysis if the new code touches shared contracts broadly**

Run:
```bash
vendor/bin/phpstan analyse
```

- [ ] **Step 6: Commit final polish**

Use a Lore commit message after all verification passes.

---

## Simplifications To Preserve

- No PDF upload
- No session history index
- No multi-provider AI abstraction
- No session revisions table
- No websocket progress transport
- No full freeform AI HTML generation
- No parallel multi-job orchestration

## Implementation Notes

- Prefer Laravel AI SDK structured output for extraction and generation payloads.
- Keep markdown output deterministic by fixing headings in code.
- Keep HTML deterministic by assembling trusted Blade templates from structured content.
- Follow existing auth middleware and app layout patterns already present in the starter.
- Use Flux components first for forms and actions before hand-rolling controls.

## Verification Checklist

- Intake rejects weak transcripts and invalid file types
- Extraction persists recommendations and opens clarification
- Clarification only accepts AI-offered template/design choices
- Generation continues after a failed artifact
- Result page shows partial success correctly
- Regenerate selected outputs does not wipe untouched outputs
- Session URL is owner-only
