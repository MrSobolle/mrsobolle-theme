<?php
/**
 * Read-only HTTP verification against an exported public route inventory.
 * php tests/verify-site.php base-url inventory.json output.json
 */
declare(strict_types=1);
$base = rtrim( $argv[1] ?? '', '/' );
$inventory = json_decode( file_get_contents( $argv[2] ), true, 512, JSON_THROW_ON_ERROR );
if ( ! preg_match( '~^https?://(?:127\.0\.0\.1:18866|mrsobolle\.com)$~', $base ) ) { throw new RuntimeException( 'Unexpected target.' ); }
$failures=[]; $results=[]; $links=[]; $assets=[]; $types=[]; $titles=[];
function request( string $url ): array {
    $c=curl_init($url); $headers=[];
    curl_setopt_array($c,[CURLOPT_RETURNTRANSFER=>true,CURLOPT_FOLLOWLOCATION=>true,CURLOPT_MAXREDIRS=>4,CURLOPT_TIMEOUT=>15,CURLOPT_CONNECTTIMEOUT=>8,CURLOPT_USERAGENT=>'Sobolle-Release-QA/1.0',CURLOPT_HEADERFUNCTION=>static function($c,$line)use(&$headers){$p=explode(':',$line,2);if(count($p)===2)$headers[strtolower(trim($p[0]))]=trim($p[1]);return strlen($line);}]);
    $html=curl_exec($c);$status=curl_getinfo($c,CURLINFO_RESPONSE_CODE);$error=curl_error($c);curl_close($c);
    return [$status,(string)$html,$headers,$error];
}
function dom( string $html ): DOMXPath {
    $doc=new DOMDocument();libxml_use_internal_errors(true);$doc->loadHTML('<?xml encoding="UTF-8">'.$html,LIBXML_NONET);libxml_clear_errors();return new DOMXPath($doc);
}
function absolute( string $href,string $url,string $base ): string {
    if(str_starts_with($href,'/'))return $base.$href;
    if(preg_match('~^https?://~',$href))return $href;
    if(str_starts_with($href,'#')||preg_match('~^[a-z]+:~i',$href))return '';
    return rtrim(dirname($url),'/').'/'.$href;
}
foreach($inventory as $item){
    $path=parse_url($item['url'],PHP_URL_PATH)?:'/';$url=$base.$path;
    [$status,$html,$headers,$error]=request($url);$bad=[];
    if($status!==200)$bad[]='HTTP '.$status.' '.$error;
    $x=dom($html);
    foreach(['//h1'=>1,'//meta[@name="description"]'=>1,'//link[@rel="canonical"]'=>1,'//script[@type="application/ld+json"]'=>1,'//link[@hreflang]'=>5] as $query=>$count){if($x->query($query)->length!==$count)$bad[]=$query.' count='.$x->query($query)->length;}
    if(!str_contains($headers['x-robots-tag']??'','noindex'))$bad[]='Missing X-Robots-Tag';
    $robot=$x->query('//meta[@name="robots"]')->item(0);
    if(!$robot||!str_contains($robot->getAttribute('content'),'noindex'))$bad[]='Missing robots meta';
    $canonical=$x->query('//link[@rel="canonical"]')->item(0)?->getAttribute('href');
    if($canonical!==$url)$bad[]='Canonical mismatch '.$canonical;
    $title=$x->query('//title')->item(0)?->textContent??'';
    if(!$title||str_contains($title,'My Title'))$bad[]='Invalid title';
    $titles[$item['lang'].' | '.$title][]=$url;
    $seen=[];
    foreach($x->query('//link[@hreflang]') as $n){$seen[]=$n->getAttribute('hreflang');$links[$n->getAttribute('href')]=true;}
    if(count(array_unique($seen))!==5)$bad[]='Duplicate language';
    try{
        $json=json_decode($x->query('//script[@type="application/ld+json"]')->item(0)?->textContent??'',true,512,JSON_THROW_ON_ERROR);
        $graph=$json['@graph']??[];$ids=array_column($graph,'@id');
        if(count($ids)!==count(array_unique($ids)))$bad[]='Duplicate graph IDs';
        foreach($graph as $node){$types[$node['@type']]=($types[$node['@type']]??0)+1;}
        if(!$graph||($json['@context']??'')!=='https://schema.org')$bad[]='Invalid graph';
        if(str_contains(json_encode($graph),'schema-author'))$bad[]='Stale plugin graph';
        if($base==='https://mrsobolle.com'&&str_contains(json_encode($graph),'127.0.0.1'))$bad[]='Local URL leaked';
    }catch(Throwable $e){$bad[]='JSON-LD: '.$e->getMessage();}
    foreach($x->query('//a[@href]') as $a){
        $href=html_entity_decode($a->getAttribute('href'));
        if($href==='#')$bad[]='Empty anchor';
        if(str_starts_with($href,'#')&&strlen($href)>1){$id=substr($href,1);if(!$x->query('//*[@id="'.str_replace('"','',$id).'"]')->length)$bad[]='Missing anchor '.$id;continue;}
        $target=absolute($href,$url,$base);
        if(str_starts_with($target,$base.'/'))$links[explode('#',$target)[0]]=true;
    }
    foreach($x->query('//script[@src]|//link[@rel="stylesheet"]|//link[@rel="icon"]|//img[@src]|//meta[@property="og:image"]') as $a){
        $href=$a->getAttribute('src')?:($a->getAttribute('href')?:$a->getAttribute('content'));
        $target=absolute($href,$url,$base);if(str_starts_with($target,$base.'/'))$assets[$target]=true;
        if(str_contains($href,'jquery'))$bad[]='Unneeded jQuery';
    }
    if(preg_match('/(?:Fatal error|Warning:|Parse error):?/',$html))$bad[]='PHP error text';
    $results[]=['url'=>$url,'status'=>$status,'failures'=>$bad];
    foreach($bad as $message)$failures[]=$url.' '.$message;
}
$checked=array_column($results,'url');
foreach(array_keys($links+$assets) as $url){
    if(in_array($url,$checked,true))continue;
    [$status,$body,$headers,$error]=request($url);
    if($status!==200)$failures[]='Linked URL '.$status.' '.$url.' '.$error;
}
foreach(['/this-page-does-not-exist-qa/','/sample-page/','/hello-world/'] as $path){
    [$status,$html]=request($base.$path);$x=dom($html);
    if($status!==404||$x->query('//h1')->length!==1||!$x->query('//form[@role="search"]')->length)$failures[]='404 template '.$path.' status='.$status;
}
foreach(['/?s=WideCrop','/?s=nonexistentqa123','/journal/?topic=engineering','/journal/?topic=products','/journal/?topic=creative-lab','/journal/?journal_page=999','/journal/?topic%5B%5D=products'] as $path){
    [$status,$html,$headers]=request($base.$path);$x=dom($html);
    if($status!==200||$x->query('//h1')->length!==1||!str_contains($headers['x-robots-tag']??'','noindex'))$failures[]='Dynamic route '.$path.' status='.$status;
}
[$status,$robots]=request($base.'/robots.txt');
if($status!==200||preg_match('~Disallow:\s*/\s~',$robots."\n"))$failures[]='robots.txt must allow crawlers to read noindex';
foreach($titles as $title=>$urls){if(count($urls)>1)$failures[]='Duplicate title: '.$title.' '.implode(',',$urls);}
$report=['base'=>$base,'routes'=>count($results),'links'=>count($links),'assets'=>count($assets),'schema_types'=>$types,'failures'=>$failures,'results'=>$results];
file_put_contents($argv[3],json_encode($report,JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES));
echo json_encode(array_diff_key($report,['results'=>true]),JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES)."\n";
exit($failures?1:0);
