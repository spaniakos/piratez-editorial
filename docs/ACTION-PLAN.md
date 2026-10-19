# PirateZ Editorial — implementation plan

Version 1.3 • 19 October 2026 • Milestones through 1.4.0 released; 1.4.1 guide released; separation audit fixes prepared as 1.4.2. Repository: https://github.com/spaniakos/piratez-editorial.

## Purpose

A standalone WordPress plugin for writing, presenting and maintaining posts, pages and product showcases. It extracts PirateZ's existing editorial features, then adds reusable Gutenberg content tools. Manager and the PirateZ theme remain optional. Commerce is a future separate plugin.

This document expands the Editorial scope in PirateZ-Modules-Migration-Plan.md. It does not change the other plugins' ownership or their migration requirements. The user has confirmed completion of the Image Optimize patch, staging validation and retirement of the old Image Alt Text plugin; Editorial is the next project.

## Release sequence

| Milestone | Scope | Exit condition |
| --- | --- | --- |
| Preparation | Inspect current main branches, inventory theme features/settings/content, map native WordPress capabilities, establish fixtures and migration contract | Approved scope is represented in repository docs and a concrete extraction checklist |
| 1.0.0 — extraction and writing foundation | Existing shortcode/block compatibility; TOC, reading/progress, sharing, related content and profiles; plugin settings; article/page patterns; publishing checks; sources/footnotes; update notes; code tools; page controls | Existing content passes regression checks; standalone plugin and paired theme release are ready |
| 1.1.0 — visual content | Slideshow, before/after comparison, download cards, steps/checklists, summaries, native tabs/FAQ styling, richer page patterns | Editor/frontend accessibility and performance checks pass for every interactive component |
| 1.2.0 — product showcase | Product cards, specifications, showcase patterns, optional catalogue, collections, comparison tables, datasheet/contact CTAs | Works both as blocks on ordinary pages and as an optional structured catalogue |
| 1.3.0 — maintenance and navigation | Maintenance dashboard, editorial notes, series, child/sibling-page navigation, content review/expiry notices, controlled link checking | Background work is bounded; private editorial data remains private |
| 1.4.0 — advanced editorial | Itinerary tools, consent-aware maps, glossary, print styles, reading preferences and multi-author review workflow | Each feature has a bounded specification before implementation |

These are proposed minor-release boundaries, not deadlines. Patch releases fix defects; feature additions increment the minor version. The complete scope below remains on the roadmap even when a feature is scheduled after 1.0.

## 1. Existing theme extraction

- Preserve `[piratez_take]`, `[piratez_hard_truth]`, `[piratez_warning]`, `[piratez_note]` and `[piratez_social_links]`, including supported attributes, nested content and safe rendering. Existing posts require no conversion.
- Move existing native editorial block registrations, content styles and necessary runtime code after inspecting the current theme. Preserve saved block names and serialization; provide block deprecations where markup changes are necessary.
- Extract TOC heading collection, anchors and navigation; preserve existing author-supplied IDs and avoid duplicate IDs. Handle nested blocks and Greek headings.
- Extract the reading-time/progress behavior, related-post selection and cache invalidation, share controls and public social-profile settings.
- Provide portable default styles and theme integration hooks. The theme keeps layout, typography, palette, dark mode and component placement. Do not move or change render-blocking optimization during this extraction.
- Import applicable theme settings with an explicit preview/report and idempotent, versioned migration. Preserve false/off values and existing plugin settings. Keep source settings for rollback.
- Establish one runtime owner. The paired theme release removes duplicate engines and uses guarded plugin adapters; it does not contain a second Editorial implementation.

## 2. Native Gutenberg first

The supplied WordPress 7.1.3 source is the development/integration baseline. Its block metadata confirms native Tabs, Footnotes and Time to Read blocks. Evaluate those implementations before adding wrappers or extensions; retain the existing reading-time behavior where native behavior does not match the required contract.

Use core blocks, styles, variations and patterns for features already served adequately by WordPress: layouts, tables, lists, buttons, file links, social links, synced sections and applicable FAQ/accordion controls. Verify each capability in the supplied source and actual editor. Create a custom block only when behavior or structured data requires it.

Patterns should remain editable Gutenberg content. Synced sections use native synced patterns rather than a parallel storage system. No page-builder engine or replacement editor.

## 3. Posts and pages

| Area | Planned behavior |
| --- | --- |
| Article patterns | Technical tutorial, opinion, comparison, review and travel article |
| Page patterns | About, Contact, Services, FAQ, Resources and project showcase |
| Reusable sections | Author profile, disclosure, contact details and CTA patterns |
| Publishing checks | Missing excerpt/featured image/categories/tags where applicable; heading issues, placeholders and alt-text review links |
| Sources | Native footnotes plus a styled sources section; add reusable citation metadata only where core cannot satisfy the requirement |
| Updates/corrections | Explicit public update/correction notices and optional author-maintained change history; keep private revision history private |
| Code | Core code content with progressive syntax highlighting, language/filename labels and copy button; no code execution |
| Layout patterns | Feature cards, timelines, statistics, testimonials and structured comparisons |
| Per-content controls | Inherit/on/off overrides for TOC, progress, sharing, related content and other plugin-owned components |
| Page navigation | Section anchors, optional sticky navigation, child and sibling links with permission-aware public results |

Posts and pages have separate configurable defaults. On fresh installations, pages default to reading progress, sharing and related posts off. Migration preserves existing intentional settings. Excerpt and featured-image checks are optional for pages. Title/author/date visibility requires a supporting theme adapter; clearly indicate when the active theme cannot apply a control.

Publishing checks are advisory by default and must not silently block publishing or rewrite content. External link checking arrives with the maintenance milestone, rather than being implied by the initial checklist.

## 4. Visual content tools

### Slideshow

Media Library selection and reordering; captions; arrows; optional dots/thumbnails; touch swipe; responsive aspect ratio; contain/crop; slide/fade choices. Autoplay is off by default. If enabled, provide a visible pause control and pause on interaction. Respect reduced motion and support labelled keyboard navigation without focus traps.

Offer explicit Gallery-to-Slideshow and reverse transforms preserving images, per-occurrence alt text, captions and supported links. Report any unsupported attributes instead of silently losing them. Use responsive image sizes, stable dimensions and conditional assets. Do not lazy-load a likely first-screen hero indiscriminately; defer later slides appropriately. Without JavaScript, images/captions remain readable.

### Other tools

- Before/after comparison: labelled images, keyboard-adjustable divider and readable fallback.
- Tabs: styles/patterns around native tabs where sufficient; no duplicate tab engine.
- Download cards: attachment/external link, description, format and known file size. Keep private downloads private; a display block is not an access-control system.
- Key takeaways: author-written summary pattern, with no assumed AI service.
- Steps/checklists: semantic ordered steps or lists; optional accessible interaction, without pretending reader state is shared server data.
- FAQ/accordion: use suitable native controls and proper headings; structured data belongs to SEO.

## 5. Product showcases

Two supported modes: insert showcase blocks/patterns into any post/page, or enable an optional structured catalogue. The catalogue is off by default and enabled explicitly.

| Component | Fields/behavior |
| --- | --- |
| Product card | Image, name, summary, optional truthful badge and details link |
| Product detail pattern | Gallery/slideshow, introduction, features, specifications, downloads and CTA |
| Specifications | Ordered label/value rows with optional groups; preserve units and meaningful display values |
| Collection | Responsive product grid, category filters and pagination; public/published content only |
| Comparison | Selected product attributes, explicit differences and accessible table headers |
| CTAs | Request quote/contact destination, datasheet download or external link |

Use a namespaced custom post type (proposed `piratez_product`) and taxonomy (proposed `piratez_product_category`), with versioned registered metadata, REST permissions and revisions where supported. Inspect existing post types and routes before choosing public slugs. Never register the generic WooCommerce `product` type or take over its data.

Keep identity, specifications, media references and CTA destinations separate from presentation. Ordinary page showcases remain valid without enabling the catalogue. Dynamic cards should link to a single source of truth rather than duplicate editable product records.

No cart, checkout, payment, orders, stock or account system. A future shop plugin will need an explicit adapter/migration contract; data reuse is a design goal, not a promise of automatic WooCommerce compatibility. Contact CTAs link to a contact page or existing form; Editorial does not introduce a forms/SMTP system.

Editorial exposes product data to SEO integration. SEO owns public schema output; never fabricate offers, ratings, reviews or availability. Existing SEO providers must not produce duplicate schema.

## 6. Maintenance and later enhancements

- Dashboard for missing editorial information, assigned review dates and stale-content candidates. Age alone does not automatically mean inaccurate content.
- Private per-post notes/checklists with permissions; exclude them from public REST responses, frontend HTML, feeds and search.
- Ordered article series with overview and previous/next navigation.
- Review/expiry dates with author-configured public notices. Do not automatically unpublish or redirect content.
- Link checker: scheduled/on-demand batches, timeouts, queue limits, caching and retries. Protect outbound requests against private-network targets and unsafe redirect chains. Report uncertain/time-limited failures for review; never auto-rewrite links.
- Itinerary blocks/patterns for dates, destinations, nights and activities.
- Optional provider-based maps with a useful link/fallback before consent; coordinate with Cookie Consent before loading third-party resources.
- Accessible glossary/tooltips, print styles and article-scoped reading preferences.
- Reviewer assignment/workflow only as an explicitly enabled later module, preserving WordPress roles and publishing permissions.

## 7. Plugin architecture and integrations

- Proposed repository/slug: `piratez-editorial`; PHP namespace `PirateZ\Editorial`; new block namespace `piratez-editorial`, while preserving any extracted legacy names.
- Shared PirateZ admin parent works with Manager absent. Editorial screen: modules/settings, patterns/help and maintenance when available. Product administration appears only when enabled.
- Bundle the established independent GitHub updater: native WordPress update registration, canonical runtime ZIP, manifest/integrity checks, cached release lookup, requirements checks and graceful GitHub failures. Manager is optional for install/update management.
- Individual modules can be enabled independently. Disabling an authoring tool should not silently erase existing content or break saved static markup. Clearly warn before disabling required dynamic renderers/catalogue routes.
- Image Optimize owns attachment alt auditing and image generation. Editorial preserves explicit block-level alt text and links editors to that tool; it does not bulk-fill alt text.
- SEO owns metadata/schema/indexing, Performance owns global loading policy, Cookie Consent owns consent, Mail owns transport and Access owns account controls.
- Load local frontend assets only where used, including nested/synced patterns and template placement. No default external fonts, CDN libraries, tracking or required AI provider.
- Prefix styles, support theme tokens and RTL, provide portable defaults, and respect editor permissions. Admin-only settings use capability checks/nonces; REST/meta writes require object-level authorization.
- Deactivation preserves content/settings. Static content remains readable; shortcode/dynamic features require the plugin. Uninstall does not delete authored posts, pages or products by default.

## 8. Migration and delivery

1. Receive the repository URL; read current main branches and applicable repository instructions.
2. Inventory extraction paths, registered blocks/shortcodes, template calls, settings and actual content fixtures. Record minimum PHP and supported WordPress versions explicitly; test every declared target. Do not infer that other plugins' compatibility declarations automatically apply.
3. Implement on a branch, with repository scope/migration/testing docs and a coordinated theme branch.
4. Build migration preview and standalone rendering first, then add 1.0 writing tools. Leave later milestones in the backlog.
5. Validate old/new theme/plugin combinations or block unsafe combinations with an actionable admin notice. Avoid any period with duplicate components.
6. Open plugin and paired theme PRs with reproducible packages and release-order instructions. Register the ready module in Manager's catalogue as needed.
7. Stage installation, settings preview/import, ownership handoff, cache purge and frontend/editor acceptance. No production cutover occurs as part of this planning task.
8. Publish coordinated releases; retain source settings and compatible packages for rollback. Do not blindly restore a database over newer editorial changes.

## 9. Acceptance gates

| Area | Required evidence |
| --- | --- |
| Content compatibility | Existing shortcodes and saved blocks render correctly; nested content, Greek, quotes, long code and deliberate off settings survive |
| Editor | Save/reopen without block-validation errors; undo/revisions; transformations preserve content; author/admin permissions enforced |
| Portability | PirateZ and a standard theme; Manager absent/present; no theme-owned script dependency in portable components |
| Interaction | Keyboard/touch, reduced motion, focus, mobile layout and no-JavaScript fallbacks for appropriate features |
| Performance | No unused module assets or frontend release checks; responsive images; no regression of render-blocking optimization |
| Migration | Preview, repeat import, later plugin edits protected, no duplicates, documented release order and rollback |
| Catalogue | Optional registration, route collisions, private/draft filtering, pagination, safe specifications and future data export |
| Integration | No competing image edits, SEO outputs, consent bypass or duplicate theme/plugin UI |
| Release | Declared PHP lint/runtime checks, supplied WP baseline integration, deterministic package identity and independent updater validation |

## Next input

Repository supplied. Proceed in milestone order through 1.4, retaining this document as the single action plan. Record completed work and actual validation below. No production credentials are required for local implementation.

## Execution log

- [x] Repository supplied; current theme main fetched.
- [x] Initial empty-repository write returned HTTP 403. Access was resolved on 17 October; all five milestone commits are now uploaded.
- [x] 1.0 extraction/foundation and paired theme validation.
- [x] 1.1 visual content validation.
- [x] 1.2 catalogue validation.
- [x] 1.3 maintenance validation.
- [x] 1.4 advanced tools validation.
- [x] Publish the milestone branch and Editorial PR #1. GitHub Package checks are attached to the PR; use its current check status before merging.

## Implemented page layouts and native HTML tools

The paired theme provides Default, Narrow reading, Wide, Full width and Landing page templates. Editorial exposes optional page title/header/footer visibility when the theme advertises support. Full width is genuine outer-page width; the mobile integration check verifies there is no horizontal overflow. Other themes retain their own native template choices.

Native Custom HTML, Table, Group, Row, Stack, Grid and Columns remain available. Editorial adds specification/comparison/compact table styles and reusable layout patterns. WordPress permissions and sanitization continue to govern raw HTML. We reuse native Tabs and Footnotes rather than creating competing engines.

## Completed implementation and validation

- Plugin package: 1.4.0, requiring WordPress 7.1.3 and PHP 8.3. Developed sequentially in milestone commits for 1.0, 1.1, 1.2, 1.3 and 1.4.
- Catalogue and reviewer workflow are off by default. Visual/product/maintenance/advanced inserter tools can be hidden independently without removing existing renderers. Review actions never publish/unpublish or send messages; assigned reviewers require editorial permissions, stale approval requests are rejected, and content edits invalidate approvals.
- Maps require one-time permission per map/page. Analytics/advertising consent never substitutes for external-map permission. Cookie Consent withdrawal unloads the frame; the user can also unload it explicitly.
- Link checks are administrator-triggered, limited to 50 links per post, a 200-item queue and three destinations per cron batch. Public-IP validation, bounded redirects, caching and request timeouts apply. Series navigation is bounded to 200 articles; page navigation to 100 pages; product comparisons to six products.
- Local integration passed with the supplied WordPress 7.1.3 source, PHP 8.3 and Chromium. Covered legacy theme migration mode, Manager active/absent, settings false values/idempotence, safe nested shortcodes, Greek text, related cache invalidation, block serialization, gallery transforms, slideshow/comparison controls, code preservation, optional catalogue/private-product exclusion, private notes excluded from REST, navigation, review authorization/staleness, maps before/after permission and withdrawal, glossary keyboard controls, reading preferences, print and responsive page templates.
- PHP syntax checks and runtime package builds passed. Packages are built deterministically with explicit runtime allowlists and verified manifests. GitHub checks validate PHP syntax/package builds; the supplied WordPress integration suite runs locally and is documented in the repository.
- Browser network access is intercepted in integration tests; remote map documents use fixtures. Live provider behavior, production cache/CDN output and staging acceptance remain deployment checks. This is not an accessibility certification or a measured performance benchmark.

## Coordinated GitHub work

- Manager 1.1.0: https://github.com/spaniakos/piratez-manager/pull/3 — adds Editorial to the optional catalogue; updater unchanged.
- Theme 1.4.0: https://github.com/spaniakos/Piratez.gr-Theme/pull/4 — draft handoff, page variants and guarded adapters; render-blocking optimization and updater retained.
- Editorial 1.4.0: https://github.com/spaniakos/piratez-editorial/pull/1 — five ordered milestone commits uploaded with trees verified against the tested local source. The access blocker is resolved; merge and verify the immutable release/package before the theme handoff.

Publish Editorial's reviewed package before merging/publishing the theme handoff. Activate Editorial on theme 1.3.x, import settings from the preview, then update the theme. Manager's catalogue addition can be published independently. No production installation, activation, migration or deactivation was performed by this implementation task.

## Separation audit — 1.4.2

Released baseline: Editorial 1.4.1, theme 1.4.0 and Manager 1.1.0. The earlier PRs above have merged; their descriptions record the original handoff.

| Confirmed inconsistency | Correction | Regression evidence |
| --- | --- | --- |
| Theme legacy TOC CSS hides the extracted button | Plugin-scoped visible-state and panel rules | Computed visibility, keyboard open/close, panel bounds |
| Reading progress sits below the sticky header/admin toolbar | Explicit plugin layer and visible-toolbar offset | Mid-article progress, hit testing, desktop/mobile and anonymous readers |
| Synced/template content can miss assets when article tools are off | Enqueue from actual block/shortcode rendering as well as queried content | Synced download/glossary with tools/modules off on a standard theme |
| Query Loop product previews use the edited page rather than the loop context | Pass block postId to server previews | Actual editor product query and specification response |
| Page reading time/sharing settings lack theme placements | Guarded page placements in theme 1.4.1, including related content | All five page variants; defaults remain off |

Validate all 27 native/custom patterns with Gutenberg, theme operation without Editorial, preserved per-post off settings, and unchanged render-blocking/updater ownership. Publish the Editorial patch and paired theme patch; the Manager navigation fix is independent. Production installation and cache/CDN acceptance remain deployment steps.
