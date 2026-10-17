<?php
namespace PirateZ\Editorial;
if (!defined('ABSPATH')) { exit; }
final class Patterns {
    public static function register() {
        register_block_pattern_category('piratez',['label'=>'PirateZ']);
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
        foreach($patterns as$key=>[$title,$sections]){
            $content='';foreach($sections as$heading)$content.='<!-- wp:heading --><h2 class="wp-block-heading">'.esc_html($heading).'</h2><!-- /wp:heading --><!-- wp:paragraph --><p></p><!-- /wp:paragraph -->';
            register_block_pattern('piratez-editorial/'.$key,['title'=>'PirateZ: '.$title,'categories'=>['piratez'],'content'=>$content]);
        }
    }
}
