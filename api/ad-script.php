<?php
require_once dirname(__DIR__).'/app/helpers.php';
require_once dirname(__DIR__).'/app/database.php';
header('Content-Type: application/javascript; charset=utf-8');
header('Cache-Control: private, no-store');

$key=preg_replace('/[^A-Za-z0-9_-]/','',(string)($_GET['key']??''));
if($key===''){ echo '/* missing player key */'; exit; }
$st=db()->prepare("SELECT t.video_id FROM embed_tokens t JOIN videos v ON v.id=t.video_id WHERE t.play_key=? AND t.status=1 AND (t.expires_at IS NULL OR t.expires_at>NOW()) AND v.status='published' LIMIT 1");
$st->execute([$key]);$video=$st->fetch();
if(!$video){echo '/* invalid player */';exit;}
$rows=db()->query("SELECT * FROM ad_campaigns WHERE status=1 ORDER BY priority DESC,id DESC")->fetchAll();
if(!$rows){echo '/* no active campaigns */';exit;}
$max=max(array_column($rows,'priority'));$pool=array_values(array_filter($rows,fn($r)=>(int)$r['priority']===(int)$max));
$total=array_sum(array_map(fn($r)=>max(1,(int)$r['weight']),$pool));$pick=random_int(1,max(1,$total));$chosen=$pool[0];$acc=0;
foreach($pool as $r){$acc+=max(1,(int)$r['weight']);if($pick<=$acc){$chosen=$r;break;}}
$ref=$_SERVER['HTTP_REFERER']??'';$domain=parse_url($ref,PHP_URL_HOST)?:'';$device=preg_match('/mobile/i',$_SERVER['HTTP_USER_AGENT']??'')?'mobile':'desktop';
db()->prepare('INSERT INTO ad_rotation_events(campaign_id,video_id,domain,device) VALUES(?,?,?,?)')->execute([$chosen['id'],$video['video_id'],$domain,$device]);
$code=(string)$chosen['ad_code'];
if($chosen['ad_type']==='external'){
 $src=filter_var($code,FILTER_VALIDATE_URL)?$code:'';
 echo $src ? "(function(){var s=document.createElement('script');s.src=".json_encode($src).";document.head.appendChild(s);})();" : '/* invalid external ad script */';
}elseif($chosen['ad_type']==='html'){
 echo "(function(){var w=document.createElement('div');w.innerHTML=".json_encode($code).";document.body.appendChild(w);})();";
}else{
 echo "(function(){try{".$code."}catch(e){console.error('Ad script error',e);}})();";
}
