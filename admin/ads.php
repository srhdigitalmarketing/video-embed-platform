<?php
require_once __DIR__.'/_header.php';
if($_SERVER['REQUEST_METHOD']==='POST'){
 verify_csrf();
 $network=in_array($_POST['network']??'', ['adsterra','clickadu','custom'],true)?$_POST['network']:'custom';
 $type=in_array($_POST['ad_type']??'', ['javascript','html','external'],true)?$_POST['ad_type']:'javascript';
 $trigger=in_array($_POST['trigger_event']??'', ['player_load','player_click','video_start','video_end','manual'],true)?$_POST['trigger_event']:'player_click';
 db()->prepare('INSERT INTO ad_campaigns(name,network,ad_type,ad_code,status,priority,weight,frequency_minutes,trigger_event) VALUES(?,?,?,?,?,?,?,?,?)')
   ->execute([trim($_POST['name']),$network,$type,$_POST['ad_code']??'',isset($_POST['status'])?1:0,(int)$_POST['priority'],max(1,(int)$_POST['weight']),(int)$_POST['frequency_minutes'],$trigger]);
}
$rows=db()->query('SELECT * FROM ad_campaigns ORDER BY priority DESC,id DESC')->fetchAll();
?><h1>Advertisements & Auto Rotation</h1>
<div class="card"><h2>Network earnings</h2><p id="earnings">Loading API statistics…</p><small>API keys are server-side and never sent to the browser.</small></div>
<div class="card"><h2>Add campaign</h2>
<form method="post"><?=csrf_field()?>
<label>Name<input name="name" placeholder="Adsterra Player Popup" required></label>
<label>Network<select name="network"><option value="adsterra">Adsterra</option><option value="clickadu">Clickadu</option><option value="custom">Custom</option></select></label>
<label>Type<select name="ad_type"><option value="javascript">JavaScript</option><option value="html">HTML</option><option value="external">External Script URL</option></select></label>
<label>Trigger<select name="trigger_event"><option value="player_click">Player click</option><option value="player_load">Player load</option><option value="video_start">Video start</option><option value="video_end">Video end</option><option value="manual">Manual</option></select></label>
<label>JavaScript / Ad code<textarea name="ad_code" rows="7" placeholder="Paste the ad network JavaScript code here. Do not use a plain link."></textarea></label>
<div class="grid"><label>Priority<input type="number" name="priority" value="1"></label><label>Weight<input type="number" name="weight" value="100" min="1"></label><label>Cooldown (minutes)<input type="number" name="frequency_minutes" value="30" min="0"></label></div>
<label><input type="checkbox" name="status" checked> Active</label><button>Add Campaign</button>
</form></div>
<div class="card"><h2>Rotation</h2><p>The player chooses an active campaign using priority + weighted rotation and respects the campaign cooldown per browser session.</p></div>
<table><tr><th>Name</th><th>Network</th><th>Type</th><th>Status</th><th>Priority</th><th>Weight</th><th>Cooldown</th></tr>
<?php foreach($rows as $r):?><tr><td><?=e($r['name'])?></td><td><?=e($r['network'])?></td><td><?=e($r['ad_type'])?></td><td><?=$r['status']?'Active':'Off'?></td><td><?=$r['priority']?></td><td><?=$r['weight']?></td><td><?=$r['frequency_minutes']?>m</td></tr><?php endforeach;?></table>
<script>
fetch('<?=e(app_url('api/network-stats.php'))?>').then(r=>r.json()).then(x=>{
 const fmt=n=>'$'+Number(n||0).toFixed(4);
 document.getElementById('earnings').textContent=
  'Adsterra: '+fmt(x.networks[0].revenue)+' | Clickadu: '+fmt(x.networks[1].revenue)+' | Total: '+fmt(x.total_revenue);
}).catch(()=>document.getElementById('earnings').textContent='Unable to load API statistics.');
</script>
<?php require __DIR__.'/_footer.php';