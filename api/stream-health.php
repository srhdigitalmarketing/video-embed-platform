<?php
require_once dirname(__DIR__).'/app/helpers.php';
require_once dirname(__DIR__).'/app/database.php';
header('Content-Type: application/json; charset=utf-8');
if($_SERVER['REQUEST_METHOD']!=='POST'){http_response_code(405);echo json_encode(['error'=>'method']);exit;}
$data=json_decode(file_get_contents('php://input'),true) ?: $_POST;
$key=preg_replace('/[^A-Za-z0-9_-]/','',(string)($data['key']??''));
$token=preg_replace('/[^a-f0-9]/','',(string)($data['token']??''));
$streamId=(int)($data['stream_id']??0);$type=(string)($data['event_type']??'timeout');
$allowed=['timeout','load_error','manual_switch','play_failed'];
if(!in_array($type,$allowed,true)||!$streamId||($key===''&&$token==='')){http_response_code(400);echo json_encode(['error'=>'invalid']);exit;}
if($key!==''){$st=db()->prepare("SELECT t.id token_id,t.video_id FROM embed_tokens t WHERE t.play_key=? AND t.status=1 LIMIT 1");$st->execute([$key]);}
else{$st=db()->prepare("SELECT t.id token_id,t.video_id FROM embed_tokens t WHERE t.token=? AND t.status=1 LIMIT 1");$st->execute([$token]);}
$owner=$st->fetch();if(!$owner){http_response_code(403);echo json_encode(['error'=>'player']);exit;}
$chk=db()->prepare('SELECT id,label,url FROM stream_links WHERE id=? AND video_id=? LIMIT 1');$chk->execute([$streamId,$owner['video_id']]);$stream=$chk->fetch();if(!$stream){http_response_code(404);echo json_encode(['error'=>'stream']);exit;}
$ref=$_SERVER['HTTP_REFERER']??'';$domain=parse_url($ref,PHP_URL_HOST)?:'';$ua=substr($_SERVER['HTTP_USER_AGENT']??'',0,255);
db()->prepare('INSERT INTO stream_health_events(stream_id,video_id,event_type,client_domain,user_agent) VALUES(?,?,?,?,?)')->execute([$streamId,$owner['video_id'],$type,$domain,$ua]);
db()->prepare("INSERT INTO system_logs(user_id,action,details,ip_hash) VALUES(NULL,?,?,NULL)")->execute(['stream.'.$type,'video_id='.$owner['video_id'].'; stream_id='.$streamId.'; host='.$stream['label']]);
echo json_encode(['ok'=>true]);