# ZionStream — Project Overview

## Purpose
A dedicated live commerce streaming platform for a single client. Solves the core problem with FB Live selling: product claims ("mine") get buried in hundreds of chat messages. ZionStream separates claims from chat and organizes them per product.

## User Roles

| Role | Access | Key Responsibility |
|---|---|---|
| admin | `/zpanel` full access | Platform management, user management, product catalog |
| streamer | Own channel + stream controls | Goes live, presents products |
| moderator | Admin panel stream tools | Activates products, confirms claims, messages customers |
| customer | Frontend | Watches streams, claims products, subscribes, chats |

## Feature Map

### Live Streaming
- Powered by **Agora RTC** (video/audio)
- Each stream has a unique `agora_channel_name` used as the Agora channel identifier
- Stream statuses: `scheduled → live → ended`
- After ending, recordings are uploaded and published as VOD

### Real-Time Chat
- Powered by **Laravel Reverb** (WebSockets)
- All roles can chat during a live stream
- System messages (e.g. "User joined") use `type = system`
- Chat messages are persisted in `stream_chat_messages`

### Live Commerce — Claim System
```
Moderator activates product (stream_products.is_active = true)
    ↓
Customer types "mine" (or any configured keyword) in chat
    ↓
System detects keyword → creates product_claim linked to:
  - active stream_product
  - customer's user
  - the triggering chat message
    ↓
Moderator sees clean claim list in admin panel
Moderator manually confirms or cancels each claim
```

- Claim keywords are per-stream (`streams.claim_keywords` JSON array, default `["mine"]`)
- No system stock enforcement — admin decides who gets the item
- Multiple claims per product are allowed (customers can change their mind)

### Subscriptions
- Plans defined in `subscription_plans` (name, price, billing period, features JSON)
- Customers subscribe via `subscriptions`
- Statuses: `trialing → active → cancelled / expired`
- Keep simple — one client, basic tiers only

### Messenger (Customer ↔ Moderator)
- FB Messenger style — private threaded conversations
- Customer initiates or moderator opens a conversation
- `conversations.moderator_id` is nullable (unassigned until a moderator picks it up)
- Statuses: `pending → open → closed`
- Real-time message delivery via Reverb

### VOD (Video on Demand)
- After a stream ends, recordings are uploaded and stored in `stream_recordings`
- `is_published` controls visibility to customers
- Duration stored in seconds

## Product Catalog

### Images
- Managed via Filament's native `FileUpload` (multiple files)
- Stored as JSON array of paths in `products.images`
- Disk: `public`
- Do NOT use Spatie Media Library

### Stock
- `products.stock_quantity` is display-only
- Admin manages actual fulfillment manually

### Live Commerce Tables
| Table | Purpose |
|---|---|
| `products` | Master product catalog (sku, name, price, images JSON, stock) |
| `stream_products` | Products queued for a specific stream — moderator picks from here |
| `product_claims` | Auto-created when customer types a claim keyword in stream chat |

## Landscape Features (schema exists, no UI yet)

| Feature | Tables | When to build |
|---|---|---|
| Donations / Tips | `donations` | Future milestone |
| Categories / Tags | `categories`, `stream_category` | Future milestone |
| Channel Follows | `channel_follows` | Future milestone |
| Ecommerce | `orders`, `order_items`, `order_addresses` | Future milestone — claims convert to orders |

## Database — 18 New Tables (+ existing system tables)

### Active (build UI for these)
| # | Table | Group |
|---|---|---|
| 1 | `channels` | Streaming |
| 2 | `streams` | Streaming |
| 3 | `stream_recordings` | Streaming |
| 4 | `stream_chat_messages` | Streaming |
| 5 | `products` | Live Commerce |
| 6 | `stream_products` | Live Commerce |
| 7 | `product_claims` | Live Commerce |
| 8 | `subscription_plans` | Subscriptions |
| 9 | `subscriptions` | Subscriptions |
| 10 | `conversations` | Messenger |
| 11 | `conversation_messages` | Messenger |

### Landscape Only (schema exists, no UI yet)
| # | Table | Future Feature |
|---|---|---|
| 12 | `donations` | Tips / Donations |
| 13 | `categories` | Stream Categories |
| 14 | `stream_category` | Stream Categories (pivot) |
| 15 | `channel_follows` | Follow System |
| 16 | `orders` | Ecommerce |
| 17 | `order_items` | Ecommerce |
| 18 | `order_addresses` | Ecommerce |

### Existing System Tables (managed by packages)
`users`, `roles`, `permissions`, `model_has_roles`, `model_has_permissions`, `role_has_permissions`, `sessions`, `cache`, `jobs`, `notifications`, `activity_log`, `media`, `pulse_aggregates`, `pulse_entries`, `pulse_values`

> See `docs/04-database-schema.md` for full column-level documentation of all tables.

## Tech Stack Summary

| Concern | Solution |
|---|---|
| Video streaming | Agora RTC SDK (JS) + PHP token builder |
| WebSockets | Laravel Reverb |
| Admin panel | Filament 5 at `/zpanel` |
| Reactive UI | Livewire 4 + Alpine.js 3 |
| Roles & Permissions | Spatie Laravel Permission |
| Real-time events | Laravel Echo + pusher-js (Reverb protocol) |
| CSS | Tailwind CSS 4 |
| Queue | Redis |
| Testing | Pest 4 |
