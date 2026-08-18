=== Selectrum Organization Schema ===
Contributors: selectrum
Tags: schema, seo, json-ld, acf, localbusiness
Requires at least: 6.0
Tested up to: 6.8
Requires PHP: 7.4
Stable tag: 1.1.0
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

= 1.1.0 =
* No longer suppresses Yoast SEO's entire schema graph. Only Yoast's Organization piece is replaced, so its WebPage, Article, and Person entities are preserved.
* Added a Breadcrumb tab with an Active / Inactive toggle, active by default.
* Outputs BreadcrumbList JSON-LD for the trail leading to the current page.
* Added WebSite, WebPage, and primary ImageObject entities for sites without Yoast SEO.
* Schema @ID fields are now optional. Canonical identifiers are generated when they are left blank.

= 1.0.1 =
* Added self-hosted update support via GitHub releases.
* SOS_VERSION is now derived from the plugin header so it cannot drift.
