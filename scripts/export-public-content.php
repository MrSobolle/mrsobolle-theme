<?php
if ( ! defined( 'WP_CLI' ) || ! WP_CLI ) { exit; }
$out = [];
foreach ( get_posts( [ 'post_type'=>['page','post'], 'post_status'=>'publish', 'numberposts'=>-1, 'suppress_filters'=>true ] ) as $p ) {
$out[] = ['id'=>$p->ID,'type'=>$p->post_type,'slug'=>$p->post_name,'parent'=>$p->post_parent,'title'=>$p->post_title,'content'=>$p->post_content,'excerpt'=>$p->post_excerpt,'hash'=>hash('sha256',$p->post_content),'lang'=>pll_get_post_language($p->ID),'url'=>get_permalink($p),'template'=>get_page_template_slug($p->ID),'product_key'=>get_post_meta($p->ID,'_studio_product_key',true),'seed_key'=>get_post_meta($p->ID,'_studio_seed_key',true),'editorial_status'=>get_post_meta($p->ID,'_studio_editorial_status',true)];
}
echo wp_json_encode($out,JSON_PRETTY_PRINT);
