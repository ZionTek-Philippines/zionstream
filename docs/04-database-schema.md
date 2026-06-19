# ZionStream — Database Schema

## Entity Relationship Overview

```
users
 ├── channels (1:1 per streamer)
 │    └── streams (1:many)
 │         ├── stream_recordings (1:many)
 │         ├── stream_chat_messages (1:many)  ←── triggers product_claims
 │         ├── stream_products (1:many)        ←── product_claims link here
 │         └── [landscape] donations, stream_category
 │
 ├── subscriptions (many — one active at a time)
 │    └── subscription_plans (many:1)
 │
 ├── conversations (as customer — 1:many)
 │    └── conversation_messages (1:many)
 │
 ├── conversations (as moderator — 1:many)
 │
 ├── product_claims (as customer — 1:many)
 │
 └── [landscape] channel_follows, orders

products
 ├── stream_products (many — can be queued in many streams)
 └── product_claims (many)

stream_products
 └── product_claims (1:many — claims link to the active stream_product)
```

---

## Streaming Group

### `channels`
One channel per streamer. Created when a user is assigned the `streamer` role.

| Column | Type | Nullable | Default | Description |
|---|---|---|---|---|
| id | bigint unsigned | — | auto | Primary key |
| user_id | bigint unsigned | — | — | FK → users (streamer) |
| name | varchar(255) | — | — | Display name of the channel |
| slug | varchar(255) | — | — | URL-friendly unique identifier |
| description | text | ✓ | — | Channel bio / about |
| thumbnail | varchar(255) | ✓ | — | Profile image path |
| banner | varchar(255) | ✓ | — | Banner image path |
| is_active | tinyint(1) | — | 1 | Soft on/off for the channel |
| created_at | timestamp | ✓ | — | — |
| updated_at | timestamp | ✓ | — | — |

**Indexes:** `slug` (unique), `is_active`
**Relationships:** `belongsTo User`, `hasMany Stream`, `hasMany ChannelFollow`

---

### `streams`
A single live stream session. Created by admin/streamer, goes live via Agora.

| Column | Type | Nullable | Default | Description |
|---|---|---|---|---|
| id | bigint unsigned | — | auto | Primary key |
| channel_id | bigint unsigned | — | — | FK → channels |
| title | varchar(255) | — | — | Stream title shown to viewers |
| description | text | ✓ | — | Optional description |
| thumbnail | varchar(255) | ✓ | — | Stream cover image |
| agora_channel_name | varchar(255) | — | — | Unique name used as Agora channel |
| agora_uid | bigint unsigned | ✓ | — | Host UID in Agora |
| status | enum | — | `scheduled` | `scheduled` / `live` / `ended` |
| claim_keywords | json | ✓ | — | Array of trigger words, e.g. `["mine","+1"]`. Default set in model: `["mine"]` |
| peak_viewer_count | int unsigned | — | 0 | Highest concurrent viewer count recorded |
| scheduled_at | timestamp | ✓ | — | When the stream is planned to start |
| started_at | timestamp | ✓ | — | When `status` became `live` |
| ended_at | timestamp | ✓ | — | When `status` became `ended` |
| created_at | timestamp | ✓ | — | — |
| updated_at | timestamp | ✓ | — | — |
| deleted_at | timestamp | ✓ | — | Soft delete |

**Indexes:** `status`, `agora_channel_name` (unique)
**Note:** `claim_keywords` has no DB default (MySQL restriction on JSON columns). The `Stream` model sets `$attributes['claim_keywords'] = '["mine"]'`.
**Relationships:** `belongsTo Channel`, `hasMany StreamRecording`, `hasMany StreamChatMessage`, `hasMany StreamProduct`, `hasMany ProductClaim`, `hasMany Donation`, `belongsToMany Category`

---

### `stream_recordings`
VOD files uploaded after a stream ends.

| Column | Type | Nullable | Default | Description |
|---|---|---|---|---|
| id | bigint unsigned | — | auto | Primary key |
| stream_id | bigint unsigned | — | — | FK → streams |
| title | varchar(255) | — | — | Recording title |
| url | varchar(255) | — | — | Storage path or CDN URL |
| duration_seconds | int unsigned | ✓ | — | Length of the recording |
| file_size | bigint unsigned | ✓ | — | File size in bytes |
| is_published | tinyint(1) | — | 0 | Controls customer visibility |
| created_at | timestamp | ✓ | — | — |
| updated_at | timestamp | ✓ | — | — |

**Indexes:** `[stream_id, is_published]`
**Relationships:** `belongsTo Stream`

---

### `stream_chat_messages`
All chat messages sent during a live stream, including system events.

| Column | Type | Nullable | Default | Description |
|---|---|---|---|---|
| id | bigint unsigned | — | auto | Primary key |
| stream_id | bigint unsigned | — | — | FK → streams |
| user_id | bigint unsigned | — | — | FK → users (sender) |
| message | text | — | — | The chat message content |
| type | enum | — | `text` | `text` / `system` (system = join/leave events) |
| created_at | timestamp | ✓ | — | — |
| updated_at | timestamp | ✓ | — | — |

**Indexes:** `[stream_id, created_at]`
**Relationships:** `belongsTo Stream`, `belongsTo User`, `hasOne ProductClaim` (if it triggered a claim)

---

## Live Commerce Group

### `products`
The master product catalog. Products are pre-loaded by admin before a stream.

| Column | Type | Nullable | Default | Description |
|---|---|---|---|---|
| id | bigint unsigned | — | auto | Primary key |
| sku | varchar(255) | — | — | Stock keeping unit (unique) |
| name | varchar(255) | — | — | Product display name |
| slug | varchar(255) | — | — | URL-friendly unique identifier |
| description | text | ✓ | — | Full product description |
| price | decimal(12,2) | — | — | Base price in PHP |
| images | json | ✓ | — | Array of file paths (Filament FileUpload, disk: `public`) |
| stock_quantity | int unsigned | — | 0 | **Display only** — not system-enforced |
| is_active | tinyint(1) | — | 1 | Controls visibility in product picker |
| created_at | timestamp | ✓ | — | — |
| updated_at | timestamp | ✓ | — | — |
| deleted_at | timestamp | ✓ | — | Soft delete |

**Indexes:** `sku` (unique), `slug` (unique), `is_active`
**Relationships:** `hasMany StreamProduct`, `hasMany ProductClaim`, `hasMany OrderItem`

---

### `stream_products`
Products queued by the moderator for a specific stream. Only **one** record per stream can have `is_active = true` at a time.

| Column | Type | Nullable | Default | Description |
|---|---|---|---|---|
| id | bigint unsigned | — | auto | Primary key |
| stream_id | bigint unsigned | — | — | FK → streams |
| product_id | bigint unsigned | — | — | FK → products |
| is_active | tinyint(1) | — | 0 | Currently being showcased on stream |
| featured_price | decimal(12,2) | ✓ | — | Stream-only price override (null = use product.price) |
| display_order | int unsigned | — | 0 | Order in the moderator's product queue |
| activated_at | timestamp | ✓ | — | When moderator last set this as active |
| created_at | timestamp | ✓ | — | — |
| updated_at | timestamp | ✓ | — | — |

**Indexes:** `[stream_id, is_active]`, `[stream_id, display_order]`
**Business rule:** When moderator activates a product, all other `stream_products` for that stream must have `is_active = false`. Enforced at application layer.
**Relationships:** `belongsTo Stream`, `belongsTo Product`, `hasMany ProductClaim`

---

### `product_claims`
Created automatically when a customer's chat message matches a `claim_keyword` and a stream product is active.

| Column | Type | Nullable | Default | Description |
|---|---|---|---|---|
| id | bigint unsigned | — | auto | Primary key |
| stream_id | bigint unsigned | — | — | FK → streams |
| stream_product_id | bigint unsigned | — | — | FK → stream_products (which product was active) |
| product_id | bigint unsigned | — | — | FK → products (denormalized for easy querying) |
| user_id | bigint unsigned | — | — | FK → users (the customer who claimed) |
| chat_message_id | bigint unsigned | ✓ | — | FK → stream_chat_messages (the "mine" message). Nullable: set to NULL if chat message is deleted |
| quantity | int unsigned | — | 1 | Default 1. Admin can adjust |
| status | enum | — | `pending` | `pending` / `confirmed` / `cancelled` |
| notes | text | ✓ | — | Moderator notes (e.g. "Customer changed mind") |
| created_at | timestamp | ✓ | — | — |
| updated_at | timestamp | ✓ | — | — |

**Indexes:** `[stream_id, status]`, `[stream_product_id, status]`, `[user_id, status]`
**Relationships:** `belongsTo Stream`, `belongsTo StreamProduct`, `belongsTo Product`, `belongsTo User`, `belongsTo StreamChatMessage`, `hasOne Order` (landscape)

---

## Subscriptions Group

### `subscription_plans`
Plan definitions (tiers). Admin creates and manages these.

| Column | Type | Nullable | Default | Description |
|---|---|---|---|---|
| id | bigint unsigned | — | auto | Primary key |
| name | varchar(255) | — | — | Plan name e.g. "Basic", "Premium" |
| slug | varchar(255) | — | — | URL-friendly unique identifier |
| description | text | ✓ | — | What the plan includes |
| price | decimal(10,2) | — | — | Price per billing period |
| billing_period | enum | — | — | `monthly` / `yearly` |
| features | json | ✓ | — | Array of feature strings for display |
| is_active | tinyint(1) | — | 1 | Hide discontinued plans |
| sort_order | int unsigned | — | 0 | Display order |
| created_at | timestamp | ✓ | — | — |
| updated_at | timestamp | ✓ | — | — |

**Indexes:** `slug` (unique), `is_active`
**Relationships:** `hasMany Subscription`

---

### `subscriptions`
A customer's subscription to a plan.

| Column | Type | Nullable | Default | Description |
|---|---|---|---|---|
| id | bigint unsigned | — | auto | Primary key |
| user_id | bigint unsigned | — | — | FK → users (customer) |
| plan_id | bigint unsigned | — | — | FK → subscription_plans |
| status | enum | — | `trialing` | `trialing` / `active` / `cancelled` / `expired` |
| trial_ends_at | timestamp | ✓ | — | When trial period ends |
| starts_at | timestamp | ✓ | — | When paid period starts |
| ends_at | timestamp | ✓ | — | When current period ends |
| cancelled_at | timestamp | ✓ | — | When customer cancelled |
| created_at | timestamp | ✓ | — | — |
| updated_at | timestamp | ✓ | — | — |
| deleted_at | timestamp | ✓ | — | Soft delete |

**Indexes:** `[user_id, status]`
**Relationships:** `belongsTo User`, `belongsTo SubscriptionPlan`

---

## Messenger Group

### `conversations`
A private thread between one customer and one moderator (FB Messenger style).

| Column | Type | Nullable | Default | Description |
|---|---|---|---|---|
| id | bigint unsigned | — | auto | Primary key |
| customer_id | bigint unsigned | — | — | FK → users (customer side) |
| moderator_id | bigint unsigned | ✓ | — | FK → users (moderator side). Nullable = unassigned |
| status | enum | — | `pending` | `pending` (unread/unassigned) / `open` / `closed` |
| created_at | timestamp | ✓ | — | — |
| updated_at | timestamp | ✓ | — | — |

**Indexes:** `[customer_id, status]`, `[moderator_id, status]`
**Relationships:** `belongsTo User (as customer)`, `belongsTo User (as moderator)`, `hasMany ConversationMessage`

---

### `conversation_messages`
Individual messages within a conversation thread.

| Column | Type | Nullable | Default | Description |
|---|---|---|---|---|
| id | bigint unsigned | — | auto | Primary key |
| conversation_id | bigint unsigned | — | — | FK → conversations |
| user_id | bigint unsigned | — | — | FK → users (sender — either customer or moderator) |
| message | text | — | — | Message content |
| type | enum | — | `text` | `text` / `image` |
| read_at | timestamp | ✓ | — | Null = unread. Set when recipient opens the message |
| created_at | timestamp | ✓ | — | — |
| updated_at | timestamp | ✓ | — | — |

**Indexes:** `[conversation_id, created_at]`
**Relationships:** `belongsTo Conversation`, `belongsTo User`

---

## Landscape Tables (schema only — no UI)

### `donations`
Tips sent by customers during a live stream.

| Column | Type | Nullable | Default | Description |
|---|---|---|---|---|
| id | bigint unsigned | — | auto | — |
| stream_id | bigint unsigned | — | — | FK → streams |
| user_id | bigint unsigned | — | — | FK → users (donor) |
| amount | decimal(10,2) | — | — | Donation amount |
| currency | varchar(3) | — | `PHP` | ISO currency code |
| message | text | ✓ | — | Optional message with donation |
| status | enum | — | `pending` | `pending` / `completed` / `failed` / `refunded` |
| created_at | timestamp | ✓ | — | — |
| updated_at | timestamp | ✓ | — | — |

---

### `categories`
Stream/product categories for browsing.

| Column | Type | Nullable | Default | Description |
|---|---|---|---|---|
| id | bigint unsigned | — | auto | — |
| name | varchar(255) | — | — | Category name |
| slug | varchar(255) | — | — | Unique slug |
| description | text | ✓ | — | — |
| icon | varchar(255) | ✓ | — | Icon name or path |
| is_active | tinyint(1) | — | 1 | — |
| sort_order | int unsigned | — | 0 | — |
| created_at | timestamp | ✓ | — | — |
| updated_at | timestamp | ✓ | — | — |

---

### `stream_category` (pivot)
Many-to-many between streams and categories.

| Column | Type | Description |
|---|---|---|
| stream_id | bigint unsigned | FK → streams |
| category_id | bigint unsigned | FK → categories |

**Primary key:** `[stream_id, category_id]`

---

### `channel_follows`
Customers following a channel.

| Column | Type | Nullable | Default | Description |
|---|---|---|---|---|
| id | bigint unsigned | — | auto | — |
| channel_id | bigint unsigned | — | — | FK → channels |
| user_id | bigint unsigned | — | — | FK → users (follower) |
| created_at | timestamp | ✓ | — | — |
| updated_at | timestamp | ✓ | — | — |

**Indexes:** `[channel_id, user_id]` (unique)

---

### `orders`
An order placed by a customer, optionally originating from a product claim.

| Column | Type | Nullable | Default | Description |
|---|---|---|---|---|
| id | bigint unsigned | — | auto | — |
| order_number | varchar(255) | — | — | Human-readable order reference (unique) |
| user_id | bigint unsigned | — | — | FK → users (buyer) |
| stream_id | bigint unsigned | ✓ | — | FK → streams (which stream it came from) |
| claim_id | bigint unsigned | ✓ | — | FK → product_claims (the claim that became this order) |
| total_amount | decimal(12,2) | — | — | Order total |
| status | enum | — | `pending` | `pending` / `processing` / `shipped` / `delivered` / `cancelled` / `refunded` |
| notes | text | ✓ | — | Admin notes |
| created_at | timestamp | ✓ | — | — |
| updated_at | timestamp | ✓ | — | — |
| deleted_at | timestamp | ✓ | — | Soft delete |

**Indexes:** `[user_id, status]`, `order_number` (unique)

---

### `order_items`
Line items within an order.

| Column | Type | Nullable | Default | Description |
|---|---|---|---|---|
| id | bigint unsigned | — | auto | — |
| order_id | bigint unsigned | — | — | FK → orders |
| product_id | bigint unsigned | — | — | FK → products |
| quantity | int unsigned | — | — | — |
| unit_price | decimal(12,2) | — | — | Price at time of order |
| subtotal | decimal(12,2) | — | — | quantity × unit_price |
| created_at | timestamp | ✓ | — | — |
| updated_at | timestamp | ✓ | — | — |

**Indexes:** `order_id`

---

### `order_addresses`
Shipping address captured at the time of order.

| Column | Type | Nullable | Default | Description |
|---|---|---|---|---|
| id | bigint unsigned | — | auto | — |
| order_id | bigint unsigned | — | — | FK → orders |
| user_id | bigint unsigned | — | — | FK → users |
| name | varchar(255) | — | — | Recipient name |
| phone | varchar(255) | — | — | Contact number |
| address_line_1 | varchar(255) | — | — | Street address |
| address_line_2 | varchar(255) | ✓ | — | Unit / floor / building |
| city | varchar(255) | — | — | — |
| state | varchar(255) | ✓ | — | Province / region |
| postal_code | varchar(255) | — | — | — |
| country | varchar(2) | — | `PH` | ISO 3166-1 alpha-2 |
| created_at | timestamp | ✓ | — | — |
| updated_at | timestamp | ✓ | — | — |

---

## Migration Run Order

```
031125  channels
031125  subscription_plans
031125  subscriptions          → subscription_plans
031125  streams                → channels
031126  products
031126  stream_chat_messages   → streams, users
031126  stream_recordings      → streams
031127  conversations          → users (×2)
031127  stream_products        → streams, products
031128  categories
031128  donations              → streams, users
031128  stream_category        → streams, categories
031129  channel_follows        → channels, users
031130  conversation_messages  → conversations, users
031130  product_claims         → streams, stream_products, products, users, stream_chat_messages
031131  orders                 → users, streams, product_claims
031132  order_addresses        → orders, users
031132  order_items            → orders, products
```

---

## Key Design Decisions

| Decision | Reason |
|---|---|
| `streams.claim_keywords` is nullable JSON (no DB default) | MySQL does not allow defaults on JSON columns |
| `product_claims.product_id` is denormalized (also in stream_products) | Faster queries — avoids a join through stream_products to get to products |
| `product_claims.chat_message_id` is nullable with `nullOnDelete` | If a chat message is deleted, the claim record is preserved |
| `stream_products.is_active` is a boolean, not a unique constraint | Allows flexibility; uniqueness enforced at app layer when moderator activates |
| `products.stock_quantity` is informational only | Admin manually decides fulfilment — no system-level stock gate |
| `subscriptions` uses soft deletes | Preserve history for billing records |
| `orders` uses soft deletes | Preserve order history even after cancellations |
| All prices use `decimal(12,2)` | Supports up to ₱9,999,999,999.99 — safe for luxury goods |
