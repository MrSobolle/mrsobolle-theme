<?php
/** SEO safety and structured-data regression tests without a database. */
declare(strict_types=1);
namespace MrSobolle\Data {
    final class Site_Data {
        public static function home_url(): string { return 'https://example.test/ru/'; }
        public static function owner(): array { return ['name'=>'Test Author']; }
        public static function content_page_url(string $slug): string { return 'https://example.test/ru/'.$slug.'/'; }
    }
    final class Product_Data {
        public static function key(int $id): string { return $GLOBALS['case']['product']??''; }
        public static function stage(int $id): string { return 'pre-alpha'; }
        public static function stage_label(string $value): string { return 'Pre-alpha'; }
    }
    final class Directory_Data {
        public static function children(int $id): array { return []; }
    }
}
namespace {
define('ABSPATH',__DIR__);
class WP_Post {
    public int $ID=42; public string $post_type='page'; public string $post_name='example';
    public string $post_excerpt='A useful page summary.'; public string $post_content='';
}
$GLOBALS['case']=['role'=>'about','product'=>'','type'=>'page','front'=>false,'404'=>false,'search'=>false,'archive'=>false,'template'=>''];
$GLOBALS['post']=new WP_Post();
$GLOBALS['title']='Example';
function __(string $s,string $d=''):string{return $s;}
function get_option($key,$default=null){return $key==='home'?'https://example.test':'1';}
function trailingslashit($s){return rtrim($s,'/').'/';}
function get_locale(){return 'ru_RU';}
function get_queried_object(){return $GLOBALS['post'];}
function get_queried_object_id(){return 42;}
function get_post_meta($id,$key,$single=true){return $key==='_studio_page_role'?$GLOBALS['case']['role']:'';}
function get_page_template_slug($id){return $GLOBALS['case']['template'];}
function is_page_template($name){return $name===$GLOBALS['case']['template'];}
function is_front_page(){return $GLOBALS['case']['front'];}
function is_singular($type=null){return !$GLOBALS['case']['404']&&!$GLOBALS['case']['search']&&(!$type||$type===$GLOBALS['case']['type']);}
function is_search(){return $GLOBALS['case']['search'];}
function is_404(){return $GLOBALS['case']['404'];}
function is_archive(){return $GLOBALS['case']['archive'];}
function is_category(){return false;}function is_tag(){return false;}function is_tax(){return false;}
function post_password_required($p=null){return false;}
function get_permalink($id){return 'https://example.test/ru/example/';}
function get_the_title($p=null){return $GLOBALS['title'];}
function get_post_ancestors($p){return [];}
function wp_get_post_parent_id($p){return 0;}
function get_post_status($id){return 'publish';}
function get_the_date($format,$post){return '2026-11-05T00:00:00+00:00';}
function get_the_modified_date($format,$post){return '2026-11-05T01:00:00+00:00';}
function get_the_post_thumbnail_url($post,$size){return false;}
function wp_strip_all_tags($s){return strip_tags($s);}
function strip_shortcodes($s){return $s;}
function get_query_var($key){return 0;}
function has_site_icon(){return false;}
function get_template_directory_uri(){return 'https://example.test/theme';}
function esc_attr($s){return htmlspecialchars((string)$s,ENT_QUOTES,'UTF-8');}
function esc_url($s){return esc_attr($s);}
function wp_json_encode($v,$flags=0){return json_encode($v,$flags);}
require dirname(__DIR__,2).'/inc/class-seo.php';
use MrSobolle\Seo;
$checks=0;
function check(bool $ok,string $why):void{global $checks;if(!$ok)throw new \RuntimeException($why);$checks++;}
check(Seo::locked(),'Noindex must remain locked even if WP is accidentally set public.');
$seo=new Seo();$robots=$seo->robots(['index'=>true,'follow'=>true]);
check(isset($robots['noindex'],$robots['nofollow'])&&!isset($robots['index'],$robots['follow']),'Robots contradictions.');
$graph=Seo::graph();$types=array_column($graph,'@type');
check(in_array('ProfilePage',$types,true),'About must be ProfilePage.');
check(count(array_column($graph,'@id'))===count(array_unique(array_column($graph,'@id'))),'Graph IDs unique.');
check(count(Seo::breadcrumbs())===2,'Home/current breadcrumbs.');
$GLOBALS['case']['role']='';$GLOBALS['case']['product']='widecrop';
$graph=Seo::graph();$app=array_values(array_filter($graph,fn($n)=>$n['@type']==='SoftwareApplication'))[0];
check($app['softwareRequirements']==='Google Chrome'&&!isset($app['operatingSystem']),'Browser requirement is not an unverified OS support claim.');
$GLOBALS['case']['product']='ai-pc-assistant';
$conceptGraph=Seo::graph();
check(!in_array('SoftwareApplication',array_column($conceptGraph,'@type'),true)&&in_array('CreativeWork',array_column($conceptGraph,'@type'),true),'An early concept is not a released application.');
check(!isset($app['offers'],$app['aggregateRating'],$app['downloadUrl']),'No invented commercial claims.');
$GLOBALS['case']['product']='';$GLOBALS['case']['type']='post';$GLOBALS['post']->post_type='post';
$graph=Seo::graph();check(in_array('BlogPosting',array_column($graph,'@type'),true),'Article graph.');
check(count(Seo::breadcrumbs())===3,'Article has Journal breadcrumb.');
$GLOBALS['title']='A </script><script>alert("x")</script>';
ob_start();$seo->head();$html=ob_get_clean();
check(substr_count($html,'<script')===1,'Titles cannot inject script elements.');
preg_match('~<script type="application/ld\+json">(.*?)</script>~s',$html,$match);
json_decode($match[1],true,512,JSON_THROW_ON_ERROR);check(true,'Safe JSON roundtrip.');
$GLOBALS['case']['404']=true;
check(Seo::canonical()===''&&Seo::graph()===[],'404 has no false canonical or graph.');
$GLOBALS['case']['404']=false;$GLOBALS['case']['search']=true;
check(Seo::canonical()===''&&Seo::graph()===[],'Search is not canonicalized as content.');
echo "SEO safety assertions: $checks passed\n";
}
