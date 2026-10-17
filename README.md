# PirateZ Editorial

Standalone content tools for WordPress 7.1.3 and PHP 8.3+. Developed sequentially through milestones 1.0–1.4.

- Existing PirateZ callouts/shortcodes, TOC, reading time/progress, related posts, sharing and profiles.
- Gutenberg patterns for articles, pages, layouts, sources, code and product showcases.
- Slideshows, before/after comparison, downloads, specifications, collections and product comparisons.
- Optional structured product catalogue; no commerce dependency.
- Content maintenance, private notes, review dates, series/page navigation and bounded link checking.
- Itineraries, explicitly loaded maps, glossary, print, reading preferences and optional reviewer workflow.
- Shared PirateZ menu and independent native WordPress GitHub updater; Manager/theme optional.

The [single action plan](docs/ACTION-PLAN.md) is the source of truth for scope, delivery and migration. [Validation](docs/VALIDATION.md) describes the integration harness and its practical limits.

## Build and migration

Run `python3 tools/build_release.py`. Only runtime files enter the installable ZIP. Merging the reviewed implementation to main triggers the existing immutable release/package workflow.

With PirateZ theme 1.3.x, activate Editorial in migration mode, preview/import settings, then install theme 1.4.0. Other themes receive portable content tools; outer width/chrome controls require theme support. Do not publish the paired theme before the plugin release is available.

Catalogue and multi-author review workflow start disabled. Source settings and authored content survive deactivation. Static block markup remains readable; dynamic blocks and legacy shortcodes need the plugin. Link checking uses WP-Cron; review actions send no messages and leave publication to WordPress.
