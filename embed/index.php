<?php
require_once dirname(__DIR__).'/app/helpers.php';
require_once dirname(__DIR__).'/app/database.php';
$token=preg_replace('/[^a-f0-9]/','',(string)($_GET['token']??''));
$st=db()->prepare("SELECT v.*,t.id token_id FROM embed_tokens t JOIN videos v ON v.id=t.video_id WHERE t.token=? AND t.status=1 AND (t.expires_at IS NULL OR t.expires_at>NOW()) AND v.status='published' LIMIT 1");$st->execute([$token]);$v=$st->fetch();if(!$v){http_response_code(404);exit('Video unavailable');}
$ref=$_SERVER['HTTP_REFERER']??'';$host=strtolower(parse_url($ref,PHP_URL_HOST)?:'');
if($host){$ok=db()->prepare("SELECT id FROM allowed_domains WHERE status=1 AND (domain=? OR ? LIKE CONCAT('%.',domain)) LIMIT 1");$ok->execute([$host,$host]);if(!$ok->fetch()){http_response_code(403);exit('Embedding domain not allowed');}}
$ls=db()->prepare('SELECT id,url FROM stream_links WHERE video_id=? AND status=1 ORDER BY sort_order,id');$ls->execute([$v['id']]);$streams=$ls->fetchAll();
if(!$streams){http_response_code(404);exit('No active stream link');}
$chosen=$streams[0];
db()->prepare('UPDATE stream_links SET requests=requests+1 WHERE id=?')->execute([$chosen['id']]);
db()->prepare('UPDATE videos SET views=views+1 WHERE id=?')->execute([$v['id']]);
$source=$chosen['url'];
?>
<!doctype html><html><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><meta name="referrer" content="origin-when-cross-origin"><title><?=e($v['title'])?></title><style>html,body{margin:0;background:#000;width:100%;height:100%;overflow:hidden;font-family:system-ui}.player{position:relative;width:100%;height:100%;display:grid;place-items:center}.video{width:100%;height:100%;object-fit:contain;background:#000}</style></head><body><div class="player"><video id="video" class="video" controls playsinline preload="metadata"></video></div>
<script>window.VEP={token:<?=json_encode($token)?>,source:<?=json_encode($source)?>,analytics:<?=json_encode(app_url('api/analytics.php'))?>,adUrl:<?=json_encode(app_url('api/ad-script.php'))?>};</script><script src="<?=e(app_url('assets/js/player.js'))?>"></script></body></html>