<?php
namespace PirateZ\Editorial;
if (!defined('ABSPATH')) { exit; }

/** Read-only author reference. Examples are escaped, never executed as shortcodes. */
final class Guide {
    public static function url($anchor = '') {
        return admin_url((current_user_can('manage_options') ? 'admin.php' : 'edit.php') . '?page=piratez-editorial-guide') . ($anchor ? '#' . sanitize_key($anchor) : '');
    }

    public static function boot() {
        add_action('admin_menu', function () {
            $parent = current_user_can('manage_options') ? 'piratez-manager' : 'edit.php';
            $hook = add_submenu_page($parent, 'Editorial guide', 'Editorial guide', 'edit_posts', 'piratez-editorial-guide', [self::class, 'render']);
            if ($hook) {
                add_action('load-' . $hook, function () {
                    wp_enqueue_style('pz-editorial-guide', plugins_url('assets/guide.css', PIRATEZ_EDITORIAL_FILE), [], PIRATEZ_EDITORIAL_VERSION);
                    wp_enqueue_style('pz-editorial-guide-callouts', plugins_url('assets/content.css', PIRATEZ_EDITORIAL_FILE), [], PIRATEZ_EDITORIAL_VERSION);
                });
            }
        }, 20);
        // Run before WordPress rejects the now-unregistered legacy admin page.
        add_action('admin_menu', function () {
            if (($_GET['page'] ?? '') === 'piratez-shortcodes' && current_user_can('edit_posts')) {
                wp_safe_redirect(self::url('shortcodes'));
                exit;
            }
        });
    }

    public static function callouts() {
        return [
            'take' => ['PirateZ Take', 'Your interpretation, opinion or recommendation. Make the editorial judgement clear rather than presenting it as an established fact.', '[piratez_take]My recommendation and the reasoning behind it.[/piratez_take]'],
            'hard-truth' => ['Hard Truth', 'A blunt limitation or uncomfortable conclusion supported by the article. Use sparingly for points readers should not overlook.', '[piratez_hard_truth]Automation cannot repair an undefined process.[/piratez_hard_truth]'],
            'warning' => ['Warning', 'A practical risk, prerequisite or consequence readers should understand before acting.', '[piratez_warning title="Before you continue"]Back up the configuration before changing it.[/piratez_warning]'],
            'note' => ['Note', 'Helpful context, clarification or a side observation that supports the main text.', '[piratez_note title="Useful context"]These instructions use PHP 8.3.[/piratez_note]'],
        ];
    }

    public static function blocks() {
        return [
            ['PirateZ Callout', 'One block with Note, Take, Warning and Hard Truth variants. Choose the type in the block settings, edit its title separately, and add paragraphs or other blocks inside.', 'Text; always available'],
            ['PirateZ Code', 'A code listing with an optional filename, language label, basic token highlighting and copy button. Code is displayed as text and never executed. Enable Code for the post/page to activate the frontend enhancement.', 'Text; always available'],
            ['PirateZ Slideshow', 'An image carousel with captions, arrows, optional dots/thumbnails, aspect ratio, image fit and slide/fade choices. Autoplay is off by default. Add Image blocks inside; Gallery transforms preserve supported image content.', 'Media; Visual module'],
            ['PirateZ Before / After', 'Compare two images with a draggable or keyboard-controlled divider. First image is before, second is after; add meaningful captions and alt text.', 'Media; Visual module'],
            ['PirateZ Download Card', 'A labelled file download with description and known format/size for Media Library files. Choose a file or supply an external URL. It does not create private-file access control.', 'Media; Visual module'],
            ['PirateZ Product Card', 'An image, product name, summary, badge and details/contact link. Fill it manually on any page, or select an enabled catalogue product as its source.', 'Widgets; Products module'],
            ['PirateZ Specifications', 'A label/value specification table. Enter rows manually, select a catalogue product, or leave rows empty to use the current catalogue product.', 'Widgets; Products module'],
            ['PirateZ Product Collection', 'A grid of published catalogue products with category links and pagination. Set the initial category and products per page. The optional product catalogue must be enabled.', 'Widgets; Products module + catalogue'],
            ['PirateZ Product Comparison', 'Compare specifications for up to six published catalogue products. Different values are highlighted. Requires catalogue products with specification data.', 'Widgets; Products module + catalogue'],
            ['PirateZ Product Details / CTA', 'Displays the selected/current catalogue product’s model, specifications, contact/quote link and datasheet link. Configure that information on the product edit screen.', 'Widgets; Products module + catalogue'],
            ['PirateZ Article Series', 'An ordered overview of published articles sharing the current article’s Series taxonomy, plus previous/next links. Assign a series and position in the post editor.', 'Widgets; Maintenance module'],
            ['PirateZ Page Navigation', 'Lists published child pages or sibling pages. Choose the mode in the block. Private and password-protected pages are excluded.', 'Widgets; Maintenance module'],
            ['PirateZ Itinerary', 'A container for ordered travel stops. Add or reorder Itinerary Stop blocks inside; published output totals their overnight stays.', 'Widgets; Advanced module'],
            ['Itinerary Stop', 'One destination, optional arrival date, number of overnight stays and editable activity/transport content. Available inside an Itinerary rather than as a standalone block.', 'Inside Itinerary; Advanced module'],
            ['PirateZ Consent-aware Map', 'Choose OpenStreetMap or Google Maps, a title and coordinates. No map provider is contacted until the visitor chooses Load map. Unload removes it; Cookie Consent withdrawal also unloads it.', 'Embeds; Advanced module'],
            ['PirateZ Glossary', 'A visible list of terms and definitions. For an inline explanation, select text and use Glossary definition in the rich-text toolbar; readers can open it with keyboard or touch.', 'Text; Advanced module'],
        ];
    }

    public static function render() {
        if (!current_user_can('edit_posts')) {
            wp_die('You do not have permission to view the Editorial guide.', '', ['response' => 403]);
        }
        echo '<div class="wrap pz-editorial-guide"><h1>PirateZ Editorial guide</h1><p>Your shortcodes, callout cards, Gutenberg blocks and ready-made patterns.</p><nav aria-label="Editorial guide sections"><a href="#shortcodes">Shortcodes</a> · <a href="#callouts">Callout cards</a> · <a href="#blocks">Blocks</a> · <a href="#patterns">Patterns</a>';
        if (current_user_can('manage_options')) {
            echo ' · <a href="' . esc_url(admin_url('admin.php?page=piratez-editorial')) . '">Editorial settings</a>';
        }
        echo '</nav><h2 id="shortcodes">Shortcodes</h2><p>In Gutenberg, add a <strong>Shortcode</strong> block and paste one of these examples. In the Classic Editor, paste it into the article content. Existing saved shortcodes do not need conversion.</p>';
        foreach (self::callouts() as [$title, $description, $example]) {
            echo '<h3>' . esc_html($title) . '</h3><pre><code>' . esc_html($example) . '</code></pre>';
        }
        echo '<p>All four callouts accept an optional <code>title="Your heading"</code>. For example:</p><pre><code>' . esc_html('[piratez_take title="My verdict"]Your text here.[/piratez_take]') . '</code></pre><p>Titles support safe inline formatting. Use paragraphs inside the card for longer content. You can nest different PirateZ shortcodes; avoid nesting a shortcode inside itself.</p>';
        echo '<h3>Social profiles</h3><pre><code>' . esc_html('[piratez_social_links]') . "\n" . esc_html('[piratez_social_links class="my-custom-class"]') . '</code></pre><p>Displays the public social profiles configured in PirateZ → Editorial. If no profiles are configured, it returns no output. The optional <code>class</code> adds CSS classes; it does not set profile URLs.</p>';
        echo '<h3>Reading components</h3><pre><code>' . esc_html('[piratez_toc]') . "\n" . esc_html('[piratez_related]') . '</code></pre><p>These use the current content and respect its TOC/Related settings. TOC needs at least two H2–H4 headings; related posts need other published posts in shared categories. Automatic theme/plugin placement already supplies these components on supported views: do not insert a second copy there. Theme 1.3.x migration mode keeps the older theme’s renderers active; these two newer component shortcodes become available after the handoff.</p>';
        echo '<h2 id="callouts">The four editorial callout cards</h2><p>These are the same four variants whether you use shortcodes or the PirateZ Callout block. Their appearance can inherit the active theme’s colours.</p><div class="pz-guide-cards">';
        foreach (self::callouts() as $type => [$title, $description]) {
            echo '<aside class="piratez-callout piratez-' . esc_attr($type) . '"><p class="piratez-callout-title"><strong>' . esc_html($title) . '</strong></p><div class="piratez-callout-content"><p>' . esc_html($description) . '</p></div></aside>';
        }
        echo '</div><h2 id="blocks">Every PirateZ block</h2><p>Open the editor’s <strong>+ inserter → Blocks</strong> and search for <strong>PirateZ</strong>. Enable the corresponding authoring module in Editorial settings if its blocks are hidden. Hiding an authoring module does not remove existing renderers. A block is a component; a pattern is a ready-made arrangement of components.</p><table class="widefat striped"><thead><tr><th scope="col">Block</th><th scope="col">Purpose and use</th><th scope="col">Where / requirement</th></tr></thead><tbody>';
        foreach (self::blocks() as [$name, $description, $where]) {
            echo '<tr><th scope="row">' . esc_html($name) . '</th><td>' . esc_html($description) . '</td><td>' . esc_html($where) . '</td></tr>';
        }
        echo '</tbody></table><h2 id="patterns">Ready-made patterns</h2><p>Open <strong>+ inserter → Patterns → PirateZ</strong>. Insert a pattern, replace its placeholder text and media, then edit its individual blocks normally. Patterns do not supply researched facts, real testimonials, product records or contact-form handling.</p><table class="widefat striped"><thead><tr><th scope="col">Pattern</th><th scope="col">Use it for</th></tr></thead><tbody>';
        $purposes = [
            'tutorial'=>'A technical tutorial: overview, requirements, steps, verification, troubleshooting and sources.',
            'opinion'=>'An opinion article: question, position, evidence, counterarguments, take and sources.',
            'comparison'=>'An article comparing options, tradeoffs and recommendations.',
            'review'=>'A review structured around testing, strengths, limitations and verdict.',
            'travel'=>'A destination article with transport, activities, practical details and sources.',
            'about'=>'An About page with story, work and contact sections.',
            'contact'=>'A contact-page outline; add your details or your existing form plugin.',
            'services'=>'A service-page outline: offering, process and contact.',
            'faq'=>'A full FAQ-page outline.',
            'resources'=>'A resources/downloads/further-reading page outline.',
            'project'=>'A case study: problem, approach, screenshots, results and repository links.',
            'update'=>'An editable update/correction section; separate from the per-post automatic notice.',
            'sources'=>'An author-maintained sources section. Numbered references use native WordPress Footnotes.',
            'takeaways'=>'An author-written key-takeaways section; it does not generate a summary.',
            'steps'=>'An ordered instruction/checklist layout; not a persistent task tracker.',
            'faq-item'=>'One expandable question and answer using native Details.',
            'product-detail'=>'A gallery, overview, features and catalogue Product Details/CTA block. For an ordinary page without catalogue data, use manual cards/specifications and your own buttons.',
            'full-width-page'=>'A full-aligned section with constrained inner content. Select a supporting full-width page template for outer-page width.',
            'landing-page'=>'A full-aligned headline, introduction and contact button. Set its destination and choose the page template/title/chrome controls you want.',
            'feature-cards'=>'Three editable feature columns; these are native layout blocks rather than a separate card engine.',
            'timeline'=>'Editable milestone headings and descriptions; dates and outcomes are entered by the author.',
            'statistics'=>'Editable numerical callouts; supply real measurements and sources.',
            'testimonials'=>'A quote and attribution layout; replace placeholders with an authorised, truthful testimonial.',
            'comparison-table'=>'A native table starting point for manually entered comparisons.',
            'contact-section'=>'A reusable contact heading, text and button; set the destination.',
            'itinerary'=>'A starter itinerary with one editable stop.',
            'tabs'=>'A native Tabs arrangement for Linux/macOS instructions; replace labels and content as needed.',
        ];
        foreach (\WP_Block_Patterns_Registry::get_instance()->get_all_registered() as $pattern) {
            if (!str_starts_with($pattern['name'], 'piratez-editorial/')) { continue; }
            $key = substr($pattern['name'], strlen('piratez-editorial/'));
            echo '<tr><th scope="row">' . esc_html($pattern['title']) . '</th><td>' . esc_html($purposes[$key] ?? 'An editable PirateZ content layout.') . '</td></tr>';
        }
        echo '</tbody></table><h2>Native WordPress tools and page layouts</h2><p>Custom HTML, Table, Group, Row, Stack, Grid and Columns remain WordPress blocks. Editorial adds PirateZ table styles; it does not replace HTML permissions or sanitisation. Tabs, Footnotes and FAQ/Details build on native WordPress features.</p><p>With PirateZ theme 1.4.0 or later, select Default, Narrow reading, Wide, Full width or Landing in the page’s template settings. Optional title/header/footer visibility is in the PirateZ Editorial page panel. Other themes provide their own outer-page templates.</p><h3>What needs theme support?</h3><p>Only PirateZ’s outer page templates and the hide-title/header/footer controls require the layout adapter. Other themes receive automatic TOC, reading time/progress, sharing and related-post placement around the main singular content, according to Editorial settings. Shortcodes, content blocks, product showcases/catalogue and editorial tools work without the PirateZ theme or Manager. The active theme owns header/navigation/sidebar layout, fonts, colours and dark mode. Print and reading preferences cover article content; outer theme elements may need theme-specific styling.</p><h2>Tools that are not blocks</h2><p>Reading time/progress, sharing and related-post defaults are in Editorial settings with per-content overrides. Publishing checks and public update notices are in the post/page panel. Private notes, review dates and series position are in Editorial maintenance. Administrator link checks are in the maintenance dashboard. Reviewer assignment/actions require the optional review workflow; review records do not publish or unpublish content. Image alt editing remains in Image Optimize.</p></div>';
    }
}
Guide::boot();
