<?php
require_once dirname(__DIR__).'/app/helpers.php';
header('Content-Type: application/json');
if($_SERVER['REQUEST_METHOD']!=='POST'){http_response_code(405);echo json_encode(['error'=>'method']);exit;}
$data=json_decode(file_get_contents('php://input'),true) ?: $_POST;
$token=preg_replace('/[^a-f0-9]/','',(string)($data['token']??''));$watch=max(0,(int)($data['watch_seconds']??0));
$st=db()->prepare('SELECT id,video_id FROM embed_tokens WHERE token=? AND status=1 LIMIT 1');$st->execute([$token]);$t=$st->fetch();if(!$t){http_response_code(404);echo json_encode(['error'=>'token']);exit;}
$ref=$_SERVER['HTTP_REFERER']??'';$host=parse_url($ref,PHP_URL_HOST)?:'';$ua=$_SERVER['HTTP_USER_AGENT']??'';$device=preg_match('/mobile/i',$ua)?'mobile':'desktop';
db()->prepare('INSERT INTO video_views(video_id,embed_token_id,domain,device,browser,watch_seconds) VALUES(?,?,?,?,?,?)')->execute([$t['video_id'],$t['id'],$host,$device,substr($ua,0,100),$watch]);
echo json_encode(['ok'=>true]);