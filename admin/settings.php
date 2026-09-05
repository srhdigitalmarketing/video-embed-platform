<?php
require_once __DIR__.'/_header.php';
$msg='';
$keys=['APP_NAME','APP_URL'];
if($_SERVER['REQUEST_METHOD']==='POST'){
 verify_csrf();
 foreach($keys as $k){db()->prepare('INSERT INTO settings(setting_key,setting_value) VALUES(?,?) ON DUPLICATE KEY UPDATE setting_value=VALUES(setting_value)')->execute([$k,trim($_POST[$k]??'')]);}
 $color=trim($_POST['PLAYER_PLAY_COLOR']??'#2F80ED'); if(!preg_match('/^#[0-9A-Fa-f]{6}$/',$color))$color='#2F80ED';
 $timeout=max(3000,min(30000,(int)($_POST['PLAYER_FAILOVER_TIMEOUT']??8000)));
 db()->prepare("INSERT INTO settings(setting_key,setting_value) VALUES('PLAYER_PLAY_COLOR',?) ON DUPLICATE KEY UPDATE setting_value=VALUES(setting_value)")->execute([$color]);
 db()->prepare("INSERT INTO settings(setting_key,setting_value) VALUES('PLAYER_FAILOVER_TIMEOUT',?) ON DUPLICATE KEY UPDATE setting_value=VALUES(setting_value)")->execute([(string)$timeout]);
 $msg='Settings saved.';
}
function setting_value($k){$s=db()->prepare('SELECT setting_value FROM settings WHERE setting_key=?');$s->execute([$k]);return $s->fetchColumn()?:env($k,'');}
?><div class="page-head"><div><h1>Settings</h1><p>General platform and player configuration.</p></div></div><?php if($msg):?><p class="ok"><?=e($msg)?></p><?php endif;?><form method="post" class="panel" style="padding:22px"><?=csrf_field()?><label>Application Name<input name="APP_NAME" value="<?=e(setting_value('APP_NAME'))?>"></label><label>Application URL<input name="APP_URL" value="<?=e(setting_value('APP_URL'))?>"></label><h2>Player Skin</h2><div class="grid"><label>Play Button Color<input type="color" name="PLAYER_PLAY_COLOR" value="<?=e(setting_value('PLAYER_PLAY_COLOR')?:'#2F80ED')?>"></label><label>Host Failover Timeout (ms)<input type="number" min="3000" max="30000" step="500" name="PLAYER_FAILOVER_TIMEOUT" value="<?=e(setting_value('PLAYER_FAILOVER_TIMEOUT')?:'8000')?>"></label></div><p class="muted">The player shows a central play button. When an external host does not load within the configured timeout, it automatically tries the next active host.</p><button>Save Settings</button></form><div class="panel" style="padding:22px"><h2>Stream Architecture</h2><p class="muted">Video delivery uses external Stream Links. Cloudflare R2 is not part of the application UI or delivery path.</p></div><?php require __DIR__.'/_footer.php';