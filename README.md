# Selectrum Organization Schema

This WordPress plugin:

- Creates an Organization Schema ACF options page.
- Registers the ACF fields automatically.
- Supports one Organization and multiple LocalBusiness locations.
- Connects each LocalBusiness to the Organization with `branchOf`.
- Outputs one Schema.org JSON-LD `@graph` in the document head.
- Disables Yoast SEO's frontend JSON-LD schema output when Yoast SEO is active.
- Supports additional JSON-LD properties.
- Sets every non-tab ACF field wrapper to 33% width.

## Requirements

- WordPress 6.0+
- PHP 7.4+
- Advanced Custom Fields PRO

## Installation

1. Upload and activate the ZIP under Plugins → Add New → Upload Plugin.
2. Activate ACF PRO.
3. Open Organization Schema in the WordPress admin.
4. Complete and enable the required entities.
