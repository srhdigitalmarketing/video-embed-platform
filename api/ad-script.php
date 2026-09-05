<?php
require_once dirname(__DIR__).'/app/helpers.php';
require_once dirname(__DIR__).'/app/database.php';
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: private, no-store');

$key=preg_replace('/[^A-Za-z0-9_-]/','',(string)($_GET['key']??''));
if($key===''){echo json_encode(['ok'=>false,'error'=>'missing_player_key']);exit;}
$st=db()->prepare("SELECT t.video_id FROM embed_tokens t JOIN videos v ON v.id=t.video_id WHERE t.play_key=? AND t.status=1 AND (t.expires_at IS NULL OR t.expires_at>NOW()) AND v.status='published' LIMIT 1");
$st->execute([$key]);$video=$st->fetch();
if(!$video){http_response_code(404);echo json_encode(['ok'=>false,'error'=>'invalid_player']);exit;}
$rows=db()->query("SELECT * FROM ad_campaigns WHERE status=1 AND trigger_event='player_click' ORDER BY priority DESC,id DESC")->fetchAll();
if(!$rows){echo json_encode(['ok'=>false,'error'=>'no_player_click_campaign']);exit;}
$max=max(array_column($rows,'priority'));$pool=array_values(array_filter($rows,fn($r)=>(int)$r['priority']===(int)$max));
$total=array_sum(array_map(fn($r)=>max(1,(int)$r['weight']),$pool));$pick=random_int(1,max(1,$total));$chosen=$pool[0];$acc=0;
foreach($pool as $r){$acc+=max(1,(int)$r['weight']);if($pick<=$acc){$chosen=$r;break;}}
$ref=$_SERVER['HTTP_REFERER']??'';$domain=parse_url($ref,PHP_URL_HOST)?:'';$device=preg_match('/mobile/i',$_SERVER['HTTP_USER_AGENT']??'')?'mobile':'desktop';
db()->prepare('INSERT INTO ad_rotation_events(campaign_id,video_id,domain,device) VALUES(?,?,?,?)')->execute([$chosen['id'],$video['video_id'],$domain,$device]);
echo json_encode(['ok'=>true,'type'=>$chosen['ad_type'],'code'=>(string)$chosen['ad_code'],'campaign_id'=>(int)$chosen['id']],JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE);
