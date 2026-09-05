<?php
require_once __DIR__.'/_header.php';
$msg='';
$keys=['APP_NAME','APP_URL'];
if($_SERVER['REQUEST_METHOD']==='POST'){
 verify_csrf();
 foreach($keys as $k){db()->prepare('INSERT INTO settings(setting_key,setting_value) VALUES(?,?) ON DUPLICATE KEY UPDATE setting_value=VALUES(setting_value)')->execute([$k,trim($_POST[$k]??'')]);}
 $msg='Settings saved.';
}
function setting_value($k){$s=db()->prepare('SELECT setting_value FROM settings WHERE setting_key=?');$s->execute([$k]);return $s->fetchColumn()?:env($k,'');}
?><div class="page-head"><div><h1>Settings</h1><p>General platform configuration.</p></div></div><?php if($msg):?><p class="ok"><?=e($msg)?></p><?php endif;?><form method="post" class="panel" style="padding:22px"><?=csrf_field()?><label>Application Name<input name="APP_NAME" value="<?=e(setting_value('APP_NAME'))?>"></label><label>Application URL<input name="APP_URL" value="<?=e(setting_value('APP_URL'))?>"></label><button>Save Settings</button></form><div class="panel" style="padding:22px"><h2>Stream Architecture</h2><p class="muted">Video delivery uses the Stream Links configured per video. Cloudflare R2 settings are no longer exposed in the admin UI.</p></div><?php require __DIR__.'/_footer.php';