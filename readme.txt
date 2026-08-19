=== Selectrum Organization Schema ===
Contributors: selectrum
Tags: schema, seo, json-ld, acf, localbusiness
Requires at least: 6.0
Tested up to: 6.8
Requires PHP: 7.4
Stable tag: 2.1.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Adds an ACF PRO options page for Organization and LocalBusiness schema and outputs connected JSON-LD on the frontend.

== Description ==

* Creates an Organization Schema ACF options page and registers the fields automatically.
* Supports one Organization and multiple LocalBusiness locations.
* Connects each LocalBusiness to the Organization with `branchOf`.
* Outputs one Schema.org JSON-LD `@graph` in the document head.
* Disables Yoast SEO's frontend JSON-LD output when Yoast SEO is active.

== Requirements ==

* WordPress 6.0+
* PHP 7.4+
* Advanced Custom Fields PRO

== Changelog ==

= 2.1.0 =
* Activation is now blocked when ACF PRO is not active, with an explanatory message.
* The Plugins screen shows a dependency warning directly under this plugin's row.
* Settings are read through a single resolver instead of calling ACF from the output layer.
* Every save through ACF is mirrored into the selectrum_os_settings option, so the frontend keeps emitting the last saved values if ACF PRO is later deactivated.
* Added the selectrum_os_settings filter, for supplying or overriding values from code.
* ACF PRO detection no longer relies on the internal acf_pro class name.

= 2.0.0 =
* Breaking: every identifier moved to the Selectrum house naming convention. Stored settings are NOT migrated and must be re-entered on each site.
* Code namespaced under Selectrum\OrganizationSchema. Constants renamed from SOS_* to SELECTRUM_OS_*.
* ACF field names prefixed selectrum_os_*, so previously saved values are orphaned.
* Options page slug changed to selectrum-os-schema.

= 1.1.0 =
* No longer suppresses Yoast SEO's entire schema graph. Only Yoast's Organization piece is replaced, so its WebPage, Article, and Person entities are preserved.
* Added a Breadcrumb tab with an Active / Inactive toggle, active by default.
* Outputs BreadcrumbList JSON-LD for the trail leading to the current page.
* Added WebSite, WebPage, and primary ImageObject entities for sites without Yoast SEO.
* Removed the Organization Schema @ID field. The identifier is now always generated, so it cannot drift from the value Yoast's references point at.
* The per-location Schema @ID field is now optional and derives from the location name when left blank.

= 1.0.1 =
* Added self-hosted update support via GitHub releases.
* SOS_VERSION is now derived from the plugin header so it cannot drift.
