<?php
require_once dirname(__DIR__).'/app/database.php';
$token=preg_replace('/[^a-f0-9]/','',(string)($_GET['token']??''));
$st=db()->prepare("SELECT v.*,t.id token_id FROM embed_tokens t JOIN videos v ON v.id=t.video_id WHERE t.token=? AND t.status=1 AND (t.expires_at IS NULL OR t.expires_at>NOW()) AND v.status='published' LIMIT 1");
$st->execute([$token]);$v=$st->fetch();if(!$v){http_response_code(404);exit('Video unavailable');}
$ref=$_SERVER['HTTP_REFERER']??'';$host=strtolower(parse_url($ref,PHP_URL_HOST)?:'');
if($host){$ok=db()->prepare("SELECT id FROM allowed_domains WHERE status=1 AND (domain=? OR ? LIKE CONCAT('%%.',domain)) LIMIT 1");$ok->execute([$host,$host]);if(!$ok->fetch()){http_response_code(403);exit('Embedding domain not allowed');}}
db()->prepare('UPDATE videos SET views=views+1 WHERE id=?')->execute([$v['id']]);
$source='';
if(!empty($v['hls_master_key'])) $source=rtrim((string)env('R2_PUBLIC_BASE_URL',''),'/').'/'.ltrim($v['hls_master_key'],'/');
elseif(!empty($v['r2_key'])) $source=rtrim((string)env('R2_PUBLIC_BASE_URL',''),'/').'/'.ltrim($v['r2_key'],'/');
?>
<!doctype html><html><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title><?=e($v['title'])?></title><style>html,body{margin:0;background:#000;width:100%;height:100%;overflow:hidden;font-family:system-ui}.player{position:relative;width:100%;height:100%;display:grid;place-items:center}.video{width:100%;height:100%;object-fit:contain;background:#000}.empty{color:#fff;text-align:center}.ad-layer{position:absolute;inset:0;pointer-events:none}.ad-layer>*{pointer-events:auto}</style></head><body><div class="player"><video id="video" class="video" controls playsinline preload="metadata" <?php if($source):?>src="<?=e($source)?>"<?php endif;?>></video><div id="ad-layer" class="ad-layer"></div><?php if(!$source):?><div class="empty">Video source is not configured.</div><?php endif;?></div>
<script>window.VEP={token:<?=json_encode($token)?>,adUrl:<?=json_encode(app_url('api/ad-script.php'))?>,analytics:<?=json_encode(app_url('api/analytics.php'))?>};</script><script src="<?=e(app_url('assets/js/player.js'))?>"></script></body></html>