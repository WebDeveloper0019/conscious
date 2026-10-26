# Conscious Indi website guide

The public-site assistant is a deterministic website guide. It does not call an LLM, an AI API or a chatbot service. Messages and personal information are not persisted; conversation filters live only in the current page's JavaScript memory and are discarded on reload.

## Data and behaviour

- Product results come from `api/public/assistant.php?kind=products` and are limited to published product snapshots belonging to active, approved vendors, with current live variant inventory and public, clean media.
- Event results come from the same endpoint with `kind=events` and are limited to published events that have not ended. Public event details and active tickets are read through `api/public/events.php`.
- Search combines explicit filters with a small reviewed synonym/category map and bounded Levenshtein typo matching. Visitor text is never interpolated into SQL.
- Follow-up filters are kept in memory for the current page only. The restart control clears them.
- Policy answers are reviewed static templates in `assets/js/site-assistant.js`. Unsupported questions receive an honest fallback and links to appropriate help routes.
- The guide never creates carts, bookings or payments. Product and event actions lead into existing site/account flows.

## Integration and safeguards

`assets/js/bharatcraft.js` loads the guide on public pages. It deliberately excludes admin, vendor, influencer and customer portal paths. Checkout gets a separate placement class so the launcher stays clear of essential controls; small-screen positioning also reserves space for fixed navigation.

The discovery endpoint is GET-only, validates length/range/date inputs, uses parameterised statements, returns only public listing fields and applies a per-IP request limit. Account/order data is not queried.

## Useful local URLs

- Public page with assistant: `http://localhost/conscious/`
- Product query: `http://localhost/conscious/api/public/assistant.php?kind=products&q=ceramics&max_price=2000&availability=in_stock`
- Event query: `http://localhost/conscious/api/public/assistant.php?kind=events`
- Event detail: `http://localhost/conscious/event-detail-live.php?id=EVENT_UUID`

The local database may contain no upcoming published events. That is rendered as an empty state, not replaced with sample events.
