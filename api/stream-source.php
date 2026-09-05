<?php
require_once dirname(__DIR__).'/app/helpers.php';
require_once dirname(__DIR__).'/app/database.php';
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: private, no-store');

$key = preg_replace('/[^A-Za-z0-9_-]/','',(string)($_GET['key']??''));
$streamId = (int)($_GET['stream']??0);
if($key==='' || !$streamId){ http_response_code(400); echo json_encode(['error'=>'invalid_request']); exit; }

$st=db()->prepare("SELECT t.id token_id,t.video_id FROM embed_tokens t WHERE t.play_key=? AND t.status=1 AND (t.expires_at IS NULL OR t.expires_at>NOW()) LIMIT 1");
$st->execute([$key]); $owner=$st->fetch();
if(!$owner){ http_response_code(404); echo json_encode(['error'=>'player_not_found']); exit; }

$st=db()->prepare("SELECT id,label,url FROM stream_links WHERE id=? AND video_id=? AND status=1 LIMIT 1");
$st->execute([$streamId,$owner['video_id']]); $stream=$st->fetch();
if(!$stream){ http_response_code(404); echo json_encode(['error'=>'stream_not_found']); exit; }

// The raw host URL is deliberately returned only from this authenticated player request,
// so it does not appear in the initial HTML/View Source. It can still be observed in browser
// network tools because the browser must ultimately request the external host.
db()->prepare('UPDATE stream_links SET requests=requests+1 WHERE id=?')->execute([$stream['id']]);

echo json_encode(['id'=>(int)$stream['id'],'label'=>$stream['label'],'url'=>$stream['url']], JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE);
