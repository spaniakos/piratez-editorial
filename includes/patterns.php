<?php
namespace PirateZ\Editorial;
if (!defined('ABSPATH')) { exit; }
final class Patterns {
    public static function register() {
        register_block_pattern_category('piratez',['label'=>'PirateZ']);
        $extra=json_decode(file_get_contents(__DIR__.'/pattern-data.json'),true);foreach((array)$extra as$key=>$pattern)register_block_pattern('piratez-editorial/'.$key,['title'=>'PirateZ: '.$pattern['title'],'categories'=>['piratez'],'content'=>$pattern['content']]);
        $patterns=[
            'tutorial'=>['Technical tutorial',['Overview','Requirements','Steps','Verification','Troubleshooting','Sources']],
            'opinion'=>['Opinion article',['The question','My position','Evidence','Counterarguments','PirateZ take','Sources']],
            'comparison'=>['Comparison article',['Options','Comparison','Tradeoffs','Recommendation','Sources']],
            'review'=>['Review',['Overview','What I tested','What worked','Limitations','Verdict']],
            'travel'=>['Travel article',['Destination','Getting there','What to do','Practical details','Sources']],
            'about'=>['About page',['About','My story','What I do','Get in touch']],
            'contact'=>['Contact page',['Get in touch','Contact details','Other ways to connect']],
            'services'=>['Services page',['How I can help','Services','How we work','Contact']],
            'faq'=>['FAQ page',['Frequently asked questions','Further help']],
            'resources'=>['Resources page',['Recommended resources','Downloads','Further reading']],
            'project'=>['Project showcase',['The problem','The approach','Screenshots','Results','Repository and resources']],
            'update'=>['Update note',['Update / correction']],
            'sources'=>['Sources section',['Sources']],
            'takeaways'=>['Key takeaways',['Key takeaways']],
        ];
        register_block_pattern('piratez-editorial/steps',['title'=>'PirateZ: Steps / checklist','categories'=>['piratez'],'content'=>'<!-- wp:list {"ordered":true} --><ol class="wp-block-list"><!-- wp:list-item --><li>First step</li><!-- /wp:list-item --><!-- wp:list-item --><li>Verify the result</li><!-- /wp:list-item --></ol><!-- /wp:list -->']);
        register_block_pattern('piratez-editorial/faq-item',['title'=>'PirateZ: FAQ item','categories'=>['piratez'],'content'=>'<!-- wp:details --><details class="wp-block-details"><summary>Question</summary><!-- wp:paragraph --><p>Answer</p><!-- /wp:paragraph --></details><!-- /wp:details -->']);
        register_block_pattern('piratez-editorial/product-detail',['title'=>'PirateZ: Product showcase','categories'=>['piratez'],'content'=>'<!-- wp:heading --><h2 class="wp-block-heading">Product overview</h2><!-- /wp:heading --><!-- wp:paragraph --><p></p><!-- /wp:paragraph --><!-- wp:gallery --><figure class="wp-block-gallery has-nested-images columns-default is-cropped"></figure><!-- /wp:gallery --><!-- wp:heading --><h2 class="wp-block-heading">Features</h2><!-- /wp:heading --><!-- wp:paragraph --><p></p><!-- /wp:paragraph --><!-- wp:piratez-editorial/product-details /-->']);
        register_block_pattern('piratez-editorial/full-width-page',['title'=>'PirateZ: Full-width page','categories'=>['piratez'],'content'=>'<!-- wp:group {"align":"full","layout":{"type":"constrained"}} --><div class="wp-block-group alignfull"><!-- wp:heading --><h2 class="wp-block-heading">Full-width section</h2><!-- /wp:heading --><!-- wp:paragraph --><p></p><!-- /wp:paragraph --></div><!-- /wp:group -->']);
        register_block_pattern('piratez-editorial/landing-page',['title'=>'PirateZ: Landing page','categories'=>['piratez'],'content'=>'<!-- wp:group {"align":"full","layout":{"type":"constrained"}} --><div class="wp-block-group alignfull"><!-- wp:heading {"level":1} --><h1 class="wp-block-heading">Your headline</h1><!-- /wp:heading --><!-- wp:paragraph --><p>Your introduction.</p><!-- /wp:paragraph --><!-- wp:buttons --><div class="wp-block-buttons"><!-- wp:button --><div class="wp-block-button"><a class="wp-block-button__link wp-element-button">Contact us</a></div><!-- /wp:button --></div><!-- /wp:buttons --></div><!-- /wp:group -->']);
        foreach($patterns as$key=>[$title,$sections]){
            $content='';foreach($sections as$heading)$content.='<!-- wp:heading --><h2 class="wp-block-heading">'.esc_html($heading).'</h2><!-- /wp:heading --><!-- wp:paragraph --><p></p><!-- /wp:paragraph -->';
            register_block_pattern('piratez-editorial/'.$key,['title'=>'PirateZ: '.$title,'categories'=>['piratez'],'content'=>$content]);
        }
    }
}
