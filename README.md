# Selectrum Organization Schema

This WordPress plugin:

- Creates an Organization Schema ACF options page.
- Registers the ACF fields automatically.
- Supports one Organization and multiple LocalBusiness locations.
- Connects each LocalBusiness to the Organization with `branchOf`.
- Outputs one Schema.org JSON-LD `@graph` in the document head.
- Adds a `BreadcrumbList` for the current page, generated from the page hierarchy and toggled from the Breadcrumb tab.
- Adds `WebSite`, `WebPage`, and a primary `ImageObject` when no other plugin owns the page-level graph.
- Generates canonical `@id` values, using Yoast's fragment conventions so both graphs interlink.
- The Organization `@id` is generated, not configurable, so it always matches what Yoast's references resolve against.
- Supports additional JSON-LD properties.
- Sets every non-tab ACF field wrapper to 33% width.

## Interaction with Yoast SEO

When Yoast SEO is active it keeps ownership of the page-level graph — `WebPage`,
`Article`, `Person`, `BreadcrumbList` — and this plugin removes only Yoast's
`Organization` piece, replacing it with its own. Because both use the same
`#organization` identifier, Yoast's `publisher` and `author` references resolve
to this plugin's Organization with no further rewriting.

When Yoast SEO is not active, this plugin emits the page-level entities itself.

This means the Breadcrumb tab has no effect on a Yoast site. Yoast's own
breadcrumb output covers it, and emitting a second `BreadcrumbList` would put
two conflicting trails on the page.

## Requirements

- WordPress 6.0+
- PHP 7.4+
- Advanced Custom Fields PRO

## Installation

1. Upload and activate the ZIP under Plugins → Add New → Upload Plugin.
2. Activate ACF PRO.
3. Open Organization Schema in the WordPress admin.
4. Complete and enable the required entities.
