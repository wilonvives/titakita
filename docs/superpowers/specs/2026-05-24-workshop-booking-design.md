# Workshop / Appointment Booking — Design Spec

- **Date:** 2026-05-24
- **Status:** Approved in principle (pending final spec review)
- **Project:** TitaKita (Hi.Events fork — Laravel backend, React/Vite SSR frontend, DDD)

## 1. Goal & Context

TitaKita currently supports **events** (a single dated happening that sells tickets). This spec adds a NEW, parallel offering type: **workshop / appointment booking**, where a customer books a specific **time slot (session)** — as intuitive as buying a movie ticket — and the organizer sets up sessions as easily as a cinema sets showtimes.

The differentiator is the **organizer experience**. Incumbent ticketing tools force the organizer to hand-create a ticket type per slot, re-enter dates/times, and re-adjust downstream reminder automation whenever slots change — error-prone and painful. Here, the organizer defines a **schedule** once and the system **auto-generates** every bookable session. Lifecycle automation (reminders, follow-up, lead nurturing) is **not** built here — it is delegated to the operator's existing CRM via **webhooks**.

### Hard constraints
- **Additive only.** Existing `event` behavior MUST NOT change. New code paths are gated by a new `event_type` field defaulting to `event`.
- **Reuse the engine.** Sessions reuse the existing Product → Order → OrderItem → Attendee → Capacity → QR / check-in machinery unchanged.

## 2. Non-goals (YAGNI)
- No built-in reminder / CRM / follow-up engine. Delegated to the operator's CRM via webhooks; only a basic booking-confirmation email is retained.
- No unlimited recurrence (capped at ≤ 3 months, renewable).
- No change to creation/checkout when `event_type = event`.

## 3. Key decisions (from brainstorming)

| Topic | Decision |
|---|---|
| Recurrence | Flexible: **single day** (most common), **recurring weekly** (≤ 3 months, renewable), or **specific chosen dates**. |
| Slot definition | Per-session **duration** + a list of **start times** (cinema-style). Uneven gaps allowed. |
| Capacity | Per-session max headcount; one value applied to all generated sessions, editable. |
| Booking unit | Party size → N attendees; **each attendee provides full info** (name/email/phone), reusing the existing per-attendee checkout step. |
| Notifications | **Webhook-first**: emit rich webhooks to the operator's CRM on booking events; keep only a basic confirmation email. |
| Payment | Reuse existing pricing (free / paid). Launch free; paid works once Stripe is connected. |
| Architecture | **Approach A**: a session is a first-class bookable unit that reuses the existing ticket engine; a Schedule generates sessions. |

## 4. Architecture (Approach A)

### 4.1 New field `events.event_type`
- Add enum column `event_type` to `events`: `event` (default) | `booking`.
- All existing events default to `event`; existing logic untouched. Booking-specific UI/flows branch on `event_type = booking`.

### 4.2 New entity: Schedule
`schedules` table (one row per booking event) holding generator config:
- `event_id`
- `session_duration_minutes` (e.g. 120)
- `start_times` (jsonb array of `"HH:MM"`, e.g. `["10:00","13:00","15:30"]`)
- `capacity_per_session` (int, null = unlimited)
- `scope_type`: `single_day` | `recurring_weekly` | `specific_dates`
- `weekdays` (jsonb, recurring mode), `range_start_date`, `range_end_date` (recurring; ≤ 3 months), `specific_dates` (jsonb, specific_dates mode)
- Timezone derived from the event.

### 4.3 Sessions = "session-products" (reuse)
Each generated session is a **Product** row (reusing existing `products` + `product_prices`) with new nullable columns:
- `schedule_id` (FK → `schedules`; NULL for normal event products)
- `session_start_at`, `session_end_at` (datetime; NULL for normal products)

Per-session capacity = existing product/price `quantity_available`. Price = existing `ProductPrice` (free/paid). Because a session **is** a product, checkout, capacity, attendee generation, QR, check-in, and existing product/order webhooks all work with **zero changes**. Normal event products keep these columns NULL and are completely unaffected.

### 4.4 Generation & regeneration
`ScheduleGenerationService` turns a Schedule config into the set of `(date × start_time)` session-products. Rules:
- Idempotent upsert: regenerating reconciles the set.
- **Never delete a session-product that has bookings** — only add new ones or remove empty future ones.
- "Renew" = extend `range_end_date` and regenerate forward.
- Follows DDD: Action → Handler → ScheduleGenerationService → repositories. No Eloquent outside repositories.

### 4.5 Webhooks (CRM integration — the lifecycle backbone)
- Reuse the existing Hi.Events webhook system + the Plan 1 webhook infra.
- Add booking event types: `booking.created`, `booking.cancelled` (plus reuse existing order/attendee hooks).
- Payload includes: workshop info; **session start/end + location**; order; and **each attendee's name/email/phone**; booking status.
- Delivered reliably through the existing queued-job pattern.

### 4.6 Payment
- Reuse `ProductPrice` types: a session can be free or paid via existing logic. Paid requires Stripe connected (existing flow). MVP launches free — no new payment code.

## 5. Organizer UX ("排片" / showtime-setting)
On create, the organizer picks the offering type: **Event** or **Workshop/Booking**. For Booking:
1. Basic info: name, description, location, images (reuse existing).
2. **Schedule panel** (the core new UI):
   - Session duration (e.g. 2h)
   - Capacity per session (e.g. 8)
   - Start times: add one or more (`10:00`, `13:00`, `15:30`; uneven gaps allowed)
   - Scope (one of): **Single day** (pick a date) · **Recurring weekly** (pick weekdays + start/end date, ≤ 3 months; one-click renew near expiry) · **Specific dates** (pick several days)
   - Live preview: "will generate N sessions" + calendar/list preview
3. Publish → live.

Later edits (add a start time / add days / change capacity) regenerate sessions; **existing bookings are preserved**; changes fire webhooks so the CRM's reminders auto-realign — no manual re-setup.

## 6. Customer UX (movie-ticket flow)
1. Open the booking page → workshop info / images / location.
2. **Pick a date** — calendar shows only days that have sessions.
3. Pick that day → **session list** with remaining capacity (`10:00–12:00 · 5 left` | `13:00–15:00 · full` | `15:30–17:30 · 8 left`).
4. Pick a session + party size → fill each attendee's info (reuse checkout) → pay if paid → done; each attendee gets a confirmation + QR.
5. Booking fires a webhook with full data (session + every attendee) to the CRM.

## 7. Frontend
- New offering-type choice on event creation (Event vs Booking).
- New organizer **Schedule** panel for booking events (replaces the per-product UI): duration, capacity, start times, scope, live preview.
- New public **booking page** for `event_type = booking`: date picker (only days with sessions) → session list with remaining capacity → existing checkout wizard (per-attendee info).
- Normal product-management UI hides session-products (filter `schedule_id IS NOT NULL`).

## 8. Error handling & edge cases
- Capacity enforced by existing product capacity logic (no double-booking; checkout-time enforcement handles concurrency).
- Regeneration preserves any session with bookings.
- Timezone: generate and display in the event's timezone.
- Recurrence capped at ≤ 3 months; renewal surfaced via the CRM (webhook) and an organizer prompt.

## 9. Reused vs New
- **Reused unchanged:** Order / OrderItem / Attendee, capacity, QR, check-in, checkout wizard, product/order webhooks, ProductPrice.
- **New:** `events.event_type`; `schedules` table; `schedule_id` + `session_start_at` + `session_end_at` on products; `ScheduleGenerationService` + Actions/Handlers; booking webhook event types; organizer Schedule UI; customer date/session picker UI.
- **Untouched (guarantee):** every code path for `event_type = event`.

## 10. Testing
- Unit tests (`DatabaseTransactions`, per repo conventions) for: schedule generation (config → expected sessions, all 3 scope types), capacity, regeneration preserving booked sessions, timezone correctness, webhook payload shape.
- Regression: existing event test suite must pass unchanged — this is the proof of additivity.

## 11. Open questions / future
- Calendar-picker UX polish.
- Paid-session display nuances.
- Auto renewal-reminder timing.
- Per-session capacity overrides (vary capacity by slot) — future, not MVP.
