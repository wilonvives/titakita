# Workshop / Appointment Booking Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Add an additive "workshop/appointment booking" offering type where organizers define a schedule once and the system auto-generates bookable time-slot sessions, while customers book a slot like a movie ticket — reusing the existing ticket/order/capacity engine and leaving `event_type=event` behavior untouched.

**Architecture:** Approach A. A session IS a Product (`schedule_id`, `session_start_at`, `session_end_at` added as nullable columns) so checkout/capacity/QR/check-in/webhooks are reused unchanged. A new `schedules` table + `ScheduleGenerationService` generate session-products from `(duration + start_times + scope)`. New `events.event_type` enum gates all new behavior. Lifecycle automation is delegated to the operator CRM via webhooks.

**Tech Stack:** Laravel/PHP (DDD: Action→Handler→Service→Repository; Spatie Laravel Data DTOs; PHPUnit with DatabaseTransactions), React/Vite SSR + Mantine + React Query, PostgreSQL.

**Spec:** `docs/superpowers/specs/2026-05-24-workshop-booking-design.md`

---

## File structure (what gets created/modified)

**Backend (create):**
- `backend/database/migrations/*_add_event_type_to_events.php`
- `backend/database/migrations/*_create_schedules_table.php`
- `backend/database/migrations/*_add_session_columns_to_products.php`
- `backend/app/DomainObjects/ScheduleDomainObject.php` (+ generate via `generate-domain-objects` if used; else hand-write)
- `backend/app/DomainObjects/Enums/EventType.php`, `ScheduleScopeType.php`
- `backend/app/Models/Schedule.php`
- `backend/app/Repository/Eloquent/ScheduleRepository.php` (+ interface)
- `backend/app/Services/Domain/Booking/ScheduleGenerationService.php`
- `backend/app/Services/Application/Handlers/Booking/{UpsertScheduleHandler,GetBookingSessionsHandler}.php` (+ DTOs)
- `backend/app/Http/Actions/Booking/{UpsertScheduleAction,GetPublicBookingSessionsAction}.php`
- `backend/app/Jobs/.../Webhook/` booking webhook event constants/payload
- Tests under `backend/tests/Unit/...`

**Frontend (create):**
- `frontend/src/components/routes/event/schedule.tsx` (organizer schedule panel)
- `frontend/src/components/routes/product-widget/SessionPicker/` (customer date+session picker)
- queries/mutations: `useGetSchedule`, `useUpsertSchedule`, `useGetBookingSessions`

**Modify (additive, gated by event_type):**
- `EventDomainObject` (+ getEventType()), event resource, creation form (offering-type choice), products page (hide session-products), public event homepage (render SessionPicker when booking).

---

## Phase 0 — Environment & additivity baseline

### Task 0: Bring up dev stack and capture baseline
- [ ] **Step 1:** Start dev stack (Windows/Git-Bash gotchas: prefix `MSYS_NO_PATHCONV=1`; if Docker socket broken, rename `%LOCALAPPDATA%\Docker\run`). Run: `cd docker/development && MSYS_NO_PATHCONV=1 bash start-dev.sh`
- [ ] **Step 2:** Confirm app at https://localhost:8443 responds (accept self-signed cert).
- [ ] **Step 3:** Run existing backend unit suite to establish green baseline. Run: `docker compose -f docker-compose.dev.yml exec -T backend php artisan test --testsuite=Unit`. Expected: all pass (this is the additivity reference — must stay green after every phase).
- [ ] **Step 4:** Note the test count/result in the plan as the baseline.

---

## Phase 1 — Backend data model (additive migrations + domain objects)

### Task 1: `events.event_type` enum
- [ ] **Step 1 (test):** `backend/tests/Unit/.../EventTypeTest.php` — assert a freshly built Event defaults to `EventType::EVENT`, and existing factory events have type `event`.
- [ ] **Step 2:** Run test → FAIL.
- [ ] **Step 3:** Create `EventType` enum (`EVENT`, `BOOKING`). Migration: add `event_type varchar default 'event' not null` to `events`. Add `getEventType()` to `EventDomainObject` + cast on `Event` model.
- [ ] **Step 4:** Migrate + run test → PASS. Run full Unit suite → still green.
- [ ] **Step 5:** Commit `feat(booking): add event_type to events (default event)`.

### Task 2: `schedules` table + Schedule domain/model/repo
- [ ] **Step 1 (test):** `ScheduleRepositoryTest` — create a schedule for an event, read it back, assert fields (`session_duration_minutes`, `start_times` json, `capacity_per_session`, `scope_type`, `weekdays`, `range_start_date`, `range_end_date`, `specific_dates`).
- [ ] **Step 2:** FAIL.
- [ ] **Step 3:** Migration `create_schedules_table` (cols above + `event_id` FK + timestamps). `ScheduleScopeType` enum (`single_day`,`recurring_weekly`,`specific_dates`). `Schedule` model (casts: start_times/weekdays/specific_dates = array, dates = date). `ScheduleDomainObject`. `ScheduleRepository` + interface, bind in a service provider following existing repo bindings.
- [ ] **Step 4:** PASS + full suite green.
- [ ] **Step 5:** Commit `feat(booking): schedules table + repository`.

### Task 3: session columns on products
- [ ] **Step 1 (test):** assert a Product can persist `schedule_id`, `session_start_at`, `session_end_at`, and that products with these NULL behave exactly as before (load an existing factory product, assert getters return null, `isAvailable()` unchanged).
- [ ] **Step 2:** FAIL.
- [ ] **Step 3:** Migration adds nullable `schedule_id` (FK), `session_start_at`, `session_end_at` to `products`. Add getters to `ProductDomainObject` + casts on `Product` model. Do NOT change any existing product method.
- [ ] **Step 4:** PASS + full suite green (proves existing products unaffected).
- [ ] **Step 5:** Commit `feat(booking): add session columns to products`.

---

## Phase 2 — Schedule generation (the heart)

### Task 4: ScheduleGenerationService — single_day
- [ ] **Step 1 (test):** `ScheduleGenerationServiceTest::test_single_day_generates_one_product_per_start_time` — schedule(duration=120, start_times=["10:00","13:00","15:30"], capacity=8, scope=single_day, range_start=2026-06-06). Call `generate($schedule)`. Assert: 3 session-products created on event, each TICKET product, free price, quantity_available=8, `session_start_at`/`session_end_at` correct (10:00–12:00, 13:00–15:00, 15:30–17:30 in event TZ), `schedule_id` set. Title = formatted slot label.
- [ ] **Step 2:** FAIL.
- [ ] **Step 3:** Implement `ScheduleGenerationService::generate(Schedule)`: compute target `(date,start)` pairs for the scope; for each, **upsert** a session-product (match on schedule_id+session_start_at). Create via existing product creation path/repository so it's a normal Product (TICKET type, free ProductPrice, quantity_available = capacity, or unlimited if null). Use event timezone.
- [ ] **Step 4:** PASS + full suite green.
- [ ] **Step 5:** Commit.

### Task 5: recurring_weekly + specific_dates + ≤3-month cap
- [ ] **Step 1 (test):** (a) recurring weekly: weekdays=[Sat,Sun], range 4 weeks, 2 start_times → assert correct count (#matching days × 2) and dates. (b) specific_dates=[d1,d2] → 2×start_times. (c) recurring range >3 months → throws/clamps validation error.
- [ ] **Step 2:** FAIL.
- [ ] **Step 3:** Extend service: weekday expansion within range; specific date expansion; validate recurring `range_end - range_start ≤ 3 months`.
- [ ] **Step 4:** PASS + full suite green.
- [ ] **Step 5:** Commit.

### Task 6: regeneration preserves booked sessions
- [ ] **Step 1 (test):** generate; simulate a booking on one session-product (create an order item / set sold>0 via existing flow or repository); change start_times (remove one that has a booking, add a new one); regenerate. Assert: the booked session-product is NOT deleted; the removed-but-unbooked future one is removed; the new one is added.
- [ ] **Step 2:** FAIL.
- [ ] **Step 3:** Add reconciliation: never delete a session-product with attendees/sold>0; only soft-remove empty future ones (e.g., set hidden/sold-out or delete if zero usage). Match existing product soft-delete/visibility patterns.
- [ ] **Step 4:** PASS + full suite green.
- [ ] **Step 5:** Commit.

---

## Phase 3 — Organizer API (schedule CRUD)

### Task 7: UpsertSchedule handler + action
- [ ] **Step 1 (test):** Handler test — given a Create/UpdateScheduleDTO for an event, it persists the schedule and triggers generation; returns the schedule with generated session count. Authorization: only event organizer.
- [ ] **Step 2:** FAIL.
- [ ] **Step 3:** `UpsertScheduleDTO` (Spatie Data, extends BaseDataObject). `UpsertScheduleHandler` (validate, save schedule via repo, call ScheduleGenerationService). `UpsertScheduleAction` extends BaseAction, route `POST/PUT /api/events/{event}/schedule`, `isActionAuthorized`. Also set the event's `event_type=booking` when a schedule is created. Register route.
- [ ] **Step 4:** PASS + full suite green.
- [ ] **Step 5:** Commit.

### Task 8: GetSchedule action (organizer reads current schedule)
- [ ] Standard read action + resource + test. Commit.

---

## Phase 4 — Public booking API

### Task 9: GetPublicBookingSessions (grouped by date)
- [ ] **Step 1 (test):** for a booking event, returns sessions grouped by date with each session's start/end + remaining capacity (only future, on-sale, non-hidden). Excludes past sessions.
- [ ] **Step 2:** FAIL.
- [ ] **Step 3:** Handler queries session-products for the event (schedule_id not null), groups by date(session_start_at), maps to a `BookingSessionResource` (date, sessions[{product_id, start, end, capacity_remaining, sold_out}]). Public action `GET /api/public/events/{event}/sessions`. Reuse existing product availability/remaining logic.
- [ ] **Step 4:** PASS + full suite green.
- [ ] **Step 5:** Commit.

(Booking checkout itself = existing public order flow with the chosen session-product as the order item. No new checkout code; verify in Task 12.)

---

## Phase 5 — Webhook (CRM integration)

### Task 10: booking webhook events
- [ ] **Step 1 (test):** when an order completes for a `booking` event, a webhook payload is dispatched containing event/workshop info, session start/end+location, order, and each attendee (name/email/phone). Test the payload builder shape. Add event type constants `booking.created`, `booking.cancelled`.
- [ ] **Step 2:** FAIL.
- [ ] **Step 3:** Extend the existing webhook system: add the new event-type constants; when an order's event is `booking`, enrich/emit the booking payload (reuse existing order/attendee webhook dispatch + add session fields). If existing order webhooks already cover the data, just add the booking event-type tagging + session fields.
- [ ] **Step 4:** PASS + full suite green.
- [ ] **Step 5:** Commit.

---

## Phase 6 — Frontend: organizer schedule panel

### Task 11: Schedule UI + offering-type choice
- [ ] **Step 1:** Add offering-type選擇 to the create-event modal (Event vs Workshop/Booking). For Booking, after create, route to the new Schedule panel instead of products.
- [ ] **Step 2:** Build `schedule.tsx`: form (duration, capacity, start-times repeater, scope radio → conditional fields: single date / weekdays+range / multi-date), live "will generate N sessions" preview (call a dry-run or compute client-side), Save → `useUpsertSchedule`. Mantine components; SCSS modules; translations via Lingui (add zh-Hans + en).
- [ ] **Step 3:** In products page, hide session-products (filter `schedule_id != null`) so booking events show the Schedule panel, not raw slots.
- [ ] **Step 4:** `npx tsc --noEmit` clean. Commit.

---

## Phase 7 — Frontend: customer session picker

### Task 12: Public booking page
- [ ] **Step 1:** When public event `event_type=booking`, render `SessionPicker` instead of the flat product list: date picker (only days from GetPublicBookingSessions) → that day's sessions with remaining capacity → selecting a session + party size feeds the existing checkout (set the session-product as the selected product/quantity).
- [ ] **Step 2:** Reuse the existing checkout wizard (per-attendee info) unchanged.
- [ ] **Step 3:** `npx tsc --noEmit` clean; add translations. Commit.

---

## Phase 8 — Integration, deploy, verify

### Task 13: End-to-end local verification
- [ ] Create a booking event via UI, set a schedule (single day, 3 start times, cap 8), confirm sessions generate; open public page, book 2 people on a session as a guest, confirm capacity decremented, confirm webhook fired (check delivery log / a test endpoint). Confirm an existing normal `event` still creates/sells tickets unchanged. Full Unit suite green.

### Task 14: Deploy to live instance and verify
- [ ] Push master; redeploy in Coolify (http://159.69.6.83:8000); after build, verify on http://159.69.6.83:8123: create a workshop booking event end-to-end as the admin, book as a guest, confirm the movie-ticket flow works. Confirm the earlier perfume-workshop demo event (normal event) still works.

---

## Self-review notes
- Spec coverage: event_type (T1), schedule model (T2), session-products (T3), generation all 3 scopes + cap (T4–5), regen preserves bookings (T6), organizer CRUD (T7–8), public sessions (T9), webhook (T10), organizer UI (T11), customer UI (T12), payment = reuse free/paid ProductPrice (T4 free default; paid is existing), additivity guaranteed by keeping full Unit suite green every task + T3/T13 explicit regression. All spec sections mapped.
- No placeholders for the critical backend logic; frontend tasks lean on established Mantine/React-Query/Lingui patterns in the repo (follow existing `products.tsx` and checkout components).
- Type consistency: `EventType`, `ScheduleScopeType`, `ScheduleGenerationService::generate(Schedule)`, `UpsertScheduleDTO`, `BookingSessionResource` used consistently across tasks.
