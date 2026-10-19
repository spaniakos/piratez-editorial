<?php
/**
 * Template part for related posts
 *
 * @package piratez_cyberpunk
 */

$related_posts = \PirateZ\Editorial\Content::related($id, 3);

if (!$related_posts || !$related_posts->have_posts()) {
    return;
}
?>

<div class="related-posts">
    <h3 class="related-posts-title"><?php esc_html_e('Related Posts', 'piratez-editorial'); ?></h3>
    <div class="related-posts-grid">
        <?php while ($related_posts->have_posts()) : $related_posts->the_post(); ?>
            <article class="related-post-card">
                <div class="related-post-thumbnail-slot">
                    <?php if (has_post_thumbnail()) : ?>
                        <div class="related-post-thumbnail">
                            <a href="<?php the_permalink(); ?>">
                                <?php the_post_thumbnail('piratez-thumbnail'); ?>
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
                <div class="related-post-content">
                    <h4 class="related-post-title">
                        <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
                    </h4>
                    <div class="related-post-meta">
                        <time datetime="<?php echo esc_attr(get_the_date('c')); ?>">
                            <?php echo esc_html(get_the_date()); ?>
                        </time>
                    </div>
                    <div class="related-post-footer">
                        <a href="<?php the_permalink(); ?>" class="read-more">
                            <?php esc_html_e('Read More', 'piratez-editorial'); ?> →
                        </a>
                    </div>
                </div>
            </article>
        <?php endwhile; ?>
    </div>
</div>

<?php
wp_reset_postdata();
?>
