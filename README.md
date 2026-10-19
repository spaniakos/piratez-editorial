# PirateZ Editorial

Standalone editorial tools for **WordPress 7.1.3 and PHP 8.3+**: callout cards, writing tools, page patterns, visual blocks, product showcases and content maintenance.

Editorial works independently of PirateZ Manager and the PirateZ theme. It has its own GitHub updater registered with WordPress. Manager is an optional installation/update interface; the theme provides optional placement and page-layout integration.

## Start here

1. Download `piratez-editorial.zip` from a published [GitHub release](https://github.com/spaniakos/piratez-editorial/releases). Use the installable plugin asset, rather than GitHub's source-code ZIP.
2. Install it through **Plugins → Add New → Upload Plugin**, then activate it.
3. Open **PirateZ → Editorial** to configure post/page defaults, public social profiles and authoring modules.
4. In Gutenberg, open **+ → Blocks** and search for **PirateZ**, or choose **+ → Patterns → PirateZ** for ready-made layouts.
5. From version **1.4.1**, open **PirateZ → Editorial guide** for examples and explanations. Authors/editors without settings access use **Posts → Editorial guide**. Editorial settings also link to the guide; the former `piratez-shortcodes` admin bookmark redirects there.

The product catalogue and review workflow are **off by default**. Authoring modules can be hidden independently without removing the renderers needed by existing content.

## The four editorial callout cards

Use a callout to draw attention to one point in an article or page. The same four variants are available through shortcodes and the **PirateZ Callout** block.

| Card | Use it for | Shortcode |
| --- | --- | --- |
| **PirateZ Take** | Your interpretation, opinion or recommendation, with reasoning. | `[piratez_take]…[/piratez_take]` |
| **Hard Truth** | A blunt limitation or uncomfortable conclusion supported by the article. | `[piratez_hard_truth]…[/piratez_hard_truth]` |
| **Warning** | A risk, prerequisite or consequence readers should understand before acting. | `[piratez_warning]…[/piratez_warning]` |
| **Note** | Supporting context, clarification or a useful side observation. | `[piratez_note]…[/piratez_note]` |

### Shortcode examples

In Gutenberg, add a native **Shortcode** block and paste an example. In the Classic Editor, paste it into the article content. Existing saved shortcodes do not need conversion.

```text
[piratez_take]My recommendation and the reasoning behind it.[/piratez_take]

[piratez_hard_truth]Automation cannot repair an undefined process.[/piratez_hard_truth]

[piratez_warning]Back up the configuration before changing it.[/piratez_warning]

[piratez_note]These instructions use PHP 8.3.[/piratez_note]
```

All four support an optional custom `title`:

```text
[piratez_warning title="Before you continue"]
Back up your configuration before making changes.
[/piratez_warning]
```

Titles support safe inline formatting. For longer content, use paragraphs inside the card. Different PirateZ shortcodes may be nested; avoid nesting a shortcode inside itself.

For visual editing, insert **PirateZ Callout**, choose its type in the block settings, edit its heading separately, and add paragraphs or other blocks inside. The active theme can influence its colours and presentation.

### Social-profile shortcode

```text
[piratez_social_links]

[piratez_social_links class="my-custom-class"]
```

This displays the public profiles configured under **PirateZ → Editorial → Public social profiles**. With no configured profiles, it produces no output. The optional `class` adds CSS classes; it does not configure the URLs.

### Reading-component shortcodes

```text
[piratez_toc]

[piratez_related]
```

| Shortcode | Behaviour and requirements |
| --- | --- |
| `[piratez_toc]` | Provides the current content's TOC component when TOC is enabled. Needs at least two H2–H4 headings to build navigation. |
| `[piratez_related]` | Displays related published posts from shared categories when Related is enabled and matching posts exist. |

Automatic theme/plugin placement already supplies these components on supported views. Avoid inserting a second copy there. These two newer component shortcodes become available after the handoff from PirateZ theme 1.3.x migration mode; the older theme keeps its own existing renderers during that mode.

## All 16 Gutenberg blocks

A **block** is an individual content component. Find these through the block inserter by searching for PirateZ. The Itinerary Stop is available inside an Itinerary.

| Block | What it does | Requirement / controls |
| --- | --- | --- |
| **PirateZ Callout** | An editorial card with Note, Take, Warning or Hard Truth presentation. | Choose the type, edit the heading and add inner blocks. Always available. |
| **PirateZ Code** | Displays a code listing with optional filename, language label, basic token highlighting and a copy button. | Enable Code for the post/page for frontend enhancement. Code is displayed as text and never executed. Always available. |
| **PirateZ Slideshow** | Image carousel with captions, navigation, optional dots/thumbnails, aspect ratio, contain/crop and slide/fade settings. | Visual module. Add Image blocks inside. Autoplay is off by default. Gallery transforms preserve supported image content. |
| **PirateZ Before / After** | Two-image comparison with a draggable and keyboard-adjustable divider. | Visual module. First image is before; second is after. Supply meaningful captions and alt text. |
| **PirateZ Download Card** | A file link with label, description and known format/size for Media Library files. | Visual module. Choose a file or enter an external URL. This is a presentation block, not private-file access control. |
| **PirateZ Product Card** | Product image, name, summary, badge and details/contact link. | Products module. Enter information manually on any page, or select a catalogue product. |
| **PirateZ Specifications** | A label/value specification table. | Products module. Enter rows manually, select a catalogue product, or leave rows empty to use the current catalogue product. |
| **PirateZ Product Collection** | Published catalogue products in a grid, with category links and pagination. | Products module and enabled catalogue. Choose an initial category and products per page. |
| **PirateZ Product Comparison** | Compares specifications and highlights differing values. | Products module and enabled catalogue. Select up to six published products with specification data. |
| **PirateZ Product Details / CTA** | Product model, specifications, contact/quote button and datasheet link. | Products module and enabled catalogue. Select a product or use the current one; configure its data on the product edit screen. |
| **PirateZ Article Series** | Ordered series overview and previous/next article links. | Maintenance module. Assign articles to the same Series taxonomy and set their positions. |
| **PirateZ Page Navigation** | Lists published child or sibling pages. | Maintenance module. Choose children or siblings. Private and password-protected pages are excluded. |
| **PirateZ Itinerary** | Container for ordered travel stops; published output totals their overnight stays. | Advanced module. Add and reorder Itinerary Stop blocks inside. |
| **Itinerary Stop** | Destination, optional arrival date, overnight stays and activity/transport content. | Advanced module; available inside an Itinerary. |
| **PirateZ Consent-aware Map** | An OpenStreetMap or Google Maps location loaded after an explicit visitor choice. | Advanced module. Set title and coordinates. No map provider is contacted until Load map is chosen. Unload and Cookie Consent withdrawal remove the frame. |
| **PirateZ Glossary** | Visible list of terms and definitions. | Advanced module. For inline explanations, select text and use Glossary definition in the rich-text toolbar. Readers can open these with keyboard or touch. |

## All 27 patterns

A **pattern** inserts a ready-made arrangement of editable blocks. Find them under **+ → Patterns → PirateZ**, replace the placeholders, and edit the resulting blocks normally.

| Pattern | Starting point |
| --- | --- |
| **Technical tutorial** | Overview, requirements, steps, verification, troubleshooting and sources. |
| **Opinion article** | Question, position, evidence, counterarguments, take and sources. |
| **Comparison article** | Options, comparison, tradeoffs, recommendation and sources. |
| **Review** | Overview, testing, strengths, limitations and verdict. |
| **Travel article** | Destination, transport, activities, practical details and sources. |
| **About page** | About, story, work and contact sections. |
| **Contact page** | Contact details and ways to connect. Add your existing form plugin if needed. |
| **Services page** | Offering, services, working process and contact. |
| **FAQ page** | A full FAQ-page outline. |
| **Resources page** | Resources, downloads and further reading. |
| **Project showcase** | Problem, approach, screenshots, results and repository/resources. |
| **Update note** | An author-maintained correction/update section, separate from the automatic per-post notice. |
| **Sources section** | An editable sources section. Numbered references use native WordPress Footnotes. |
| **Key takeaways** | An author-written summary section; it does not generate a summary. |
| **Steps / checklist** | An ordered instruction layout; it is not a persistent task tracker. |
| **FAQ item** | One expandable question and answer using native Details. |
| **Product showcase** | Overview, gallery, features and catalogue Product Details/CTA. Without catalogue data, use manual cards/specifications and your own buttons. |
| **Full-width page** | A full-aligned section with constrained inner content. Outer-page width also needs a supporting page template. |
| **Landing page** | Headline, introduction and contact button. Set the destination and choose template/title/chrome options. |
| **Feature cards** | Three editable feature columns using native layout blocks. |
| **Timeline** | Milestone headings and descriptions; enter your own dates and outcomes. |
| **Statistics** | Numerical callouts; supply real measurements and sources. |
| **Testimonial** | Quote and attribution layout; replace placeholders with an authorised, truthful testimonial. |
| **Comparison table** | A native table starting point for manually entered comparisons. |
| **Contact section** | Contact heading, text and button; set its destination. |
| **Travel itinerary** | Starter itinerary with one editable stop. |
| **Platform instructions** | Native Tabs with Linux/macOS starter labels and instructions. |

Patterns provide structure. They do not supply researched facts, genuine testimonials, catalogue records or contact-form handling.

## Theme support boundary

| Capability | With a theme that has no PirateZ adapter |
| --- | --- |
| TOC, reading time/progress, sharing and related posts | Editorial places them around the main singular content, according to settings |
| Shortcodes, content blocks/patterns, catalogue, maintenance and reviews | Available independently of PirateZ theme and Manager |
| PirateZ Narrow/Wide/Full width/Landing outer templates | Use the active theme’s native templates instead |
| Hide page title/header/footer controls | Hidden unless the theme advertises the layout adapter |
| Header, navigation, sidebars, fonts, palette and dark mode | Belong to the active theme |
| Print and reading preferences | Cover article content; outer theme elements may need theme-specific styling |

The active theme must render normal WordPress content and asset hooks. Product pages inherit that theme’s single/archive layout; product information and showcase blocks belong to Editorial. Related posts still require shared categories and TOC requires at least two H2–H4 headings.

## HTML, tables and page layouts

Native **Custom HTML, Table, Group, Row, Stack, Grid and Columns** remain available. Editorial adds specification/comparison/compact table styles and patterns while retaining WordPress permissions and sanitisation for raw HTML. Tabs, Footnotes and FAQ/Details build on native WordPress capabilities.

With **PirateZ theme 1.4.0+**, select a page template:

| Template | Purpose |
| --- | --- |
| **Default** | The theme's standard page layout. |
| **Narrow reading** | A narrower text-focused page. |
| **Wide** | More space for layouts and tables. |
| **Full width** | Outer content can span the page width. |
| **Landing page** | A full-width starting point for a landing layout. |

Optional page-title/header/footer visibility controls are in the **PirateZ Editorial** page panel when the theme supports them. Other themes supply their own outer templates; full-aligned blocks alone cannot override every theme's page container.

## Settings and maintenance tools

| Tool | Where / behaviour |
| --- | --- |
| Post/page defaults | PirateZ → Editorial. Configure TOC, reading time/progress, sharing, related content, code, publishing checks, reading preferences and print. |
| Per-content overrides | PirateZ Editorial post/page panel: inherit the site default, enable or disable a feature. |
| Publishing checklist | Advisory checks for applicable missing information, headings, placeholders and image-alt review. It does not silently block publishing. |
| Public update/correction | Post/page panel. Displays the author-written notice above the content. |
| Private notes | Editorial maintenance panel; not public article content or public REST metadata. |
| Review dates/notices | Maintenance tools identify due reviews; an optional public notice can appear after the configured review date. Content is not automatically unpublished. |
| Link checks | Administrator maintenance dashboard. WP-Cron processes bounded batches, validates destinations and reports uncertain results for manual review. Links are not automatically rewritten. |
| Review workflow | Enable in settings, assign reviewers in the editor, then use Posts → Editorial reviews. Decisions are recorded; stale approval requests are refused and edits invalidate approvals. No messages are sent; native WordPress publishing permissions still apply. |
| Image alt text | Managed by PirateZ Image Optimize. Editorial links authors toward review rather than bulk-filling descriptions. |

## Product catalogue and map permission

The optional catalogue stores product information separately from its layout. It supports specifications, categories, product pages, collections, comparisons and contact/datasheet links. It does not provide cart, checkout, payments, orders or stock management. Manual Product Card and Specifications blocks also work on ordinary pages without enabling the catalogue.

Map permission applies to a particular map on the current page. Cookie Consent's analytics/advertising choices do not substitute for map permission. Visitors can unload a map; withdrawing Cookie Consent also unloads an open frame. No provider map is loaded in the editor.

## Theme migration and data retention

With PirateZ theme **1.3.x**, activate Editorial in migration mode, preview/import the relevant settings, then update the theme to **1.4.0+**. The older theme retains its engines during migration mode; the newer theme hands off to Editorial without duplicate engines.

Publish and verify Editorial's release package before the paired theme handoff. Source theme settings are retained for rollback. Re-running the settings import does not overwrite later Editorial edits.

Deactivation preserves settings and authored content. Static block markup remains readable; dynamic blocks and legacy shortcodes require the plugin for their functionality. Other themes receive portable content tools, with layout/chrome integration dependent on theme support.

## Development and releases

```bash
python3 tools/build_release.py
```

The build creates a deterministic installable ZIP, `release.json` manifest and `SHA256SUMS` from an explicit runtime allowlist. Tests, documentation, tools and git state are excluded. Merging a reviewed version to `main` triggers the immutable release workflow; existing release versions/tags are not overwritten.

- [Action plan](docs/ACTION-PLAN.md): milestones, ownership and coordinated delivery.
- [Validation](docs/VALIDATION.md): integration setup, coverage and practical limits.
- [Paired theme](https://github.com/spaniakos/Piratez.gr-Theme).
- [Optional Manager](https://github.com/spaniakos/piratez-manager).

The authoritative development/integration baseline is the supplied WordPress **7.1.3** source. Local integration uses PHP **8.3** and Chromium. CI validates PHP syntax and runtime packaging; the supplied WordPress integration harness is documented separately. Remote map documents use fixtures in tests; live provider behaviour and production caches remain staging checks.

### Separation fixes (1.4.2)

Editorial scopes its TOC control styles so the theme’s older hidden-state rules cannot hide the extracted controls. Reading progress is layered above the PirateZ sticky header and follows the visible bottom of the WordPress admin toolbar, including when the mobile toolbar scrolls away. Feature defaults and explicit per-post off settings remain respected.

Assets also load when Editorial content is rendered through synced patterns, templates, widgets or archive loops, even with automatic article tools disabled. Hiding an authoring module keeps existing blocks styled and interactive. Product previews receive the block’s current post context, including products inside Query Loops. The card picker distinguishes a manual card from current-product details/specifications.

The paired theme 1.4.1 places enabled reading time, sharing and related content on pages as well as articles. Page defaults remain unchanged. Editorial retains its independent native GitHub updater and needs no Manager installation.
