<?php
/**
 * Template part for social sharing buttons
 *
 * @package piratez_cyberpunk
 */

if (!is_singular()) {
    return;
}

$post_url = urlencode(get_permalink($id));
$post_title = urlencode(get_the_title($id));
$post_excerpt = urlencode(get_the_excerpt($id));
?>

<div class="social-sharing pz-share">
    <h4 class="share-label"><?php esc_html_e('Share this post', 'piratez-editorial'); ?></h4>
    <div class="share-buttons">
        <a href="https://twitter.com/intent/tweet?url=<?php echo $post_url; ?>&text=<?php echo $post_title; ?>" 
           target="_blank" 
           rel="noopener noreferrer" 
           class="share-button share-twitter"
           aria-label="<?php esc_attr_e('Share on Twitter', 'piratez-editorial'); ?>">
            <span class="share-icon">🐦</span>
            <span class="share-text"><?php esc_html_e('Twitter', 'piratez-editorial'); ?></span>
        </a>

        <a href="https://www.facebook.com/sharer/sharer.php?u=<?php echo $post_url; ?>" 
           target="_blank" 
           rel="noopener noreferrer" 
           class="share-button share-facebook"
           aria-label="<?php esc_attr_e('Share on Facebook', 'piratez-editorial'); ?>">
            <span class="share-icon">📘</span>
            <span class="share-text"><?php esc_html_e('Facebook', 'piratez-editorial'); ?></span>
        </a>

        <a href="https://www.linkedin.com/shareArticle?mini=true&url=<?php echo $post_url; ?>&title=<?php echo $post_title; ?>" 
           target="_blank" 
           rel="noopener noreferrer" 
           class="share-button share-linkedin"
           aria-label="<?php esc_attr_e('Share on LinkedIn', 'piratez-editorial'); ?>">
            <span class="share-icon">💼</span>
            <span class="share-text"><?php esc_html_e('LinkedIn', 'piratez-editorial'); ?></span>
        </a>

        <button class="share-button share-copy" 
                data-url="<?php echo esc_attr(get_permalink($id)); ?>"
                aria-label="<?php esc_attr_e('Copy link', 'piratez-editorial'); ?>">
            <span class="share-icon">🔗</span>
            <span class="share-text"><?php esc_html_e('Copy Link', 'piratez-editorial'); ?></span>
        </button>
    </div><span class="pz-copy-status" role="status"></span>
</div>

