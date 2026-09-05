<?php
require_once __DIR__.'/_header.php';
$id=(int)($_GET['id']??0);
$st=db()->prepare('SELECT * FROM videos WHERE id=?');$st->execute([$id]);$v=$st->fetch();if(!$v){http_response_code(404);exit('Not found');}
$cats=db()->query("SELECT * FROM categories WHERE status=1 ORDER BY name")->fetchAll();$msg='';
if($_SERVER['REQUEST_METHOD']==='POST'){
 verify_csrf();
 if(isset($_POST['stream_action'])){
   $action=$_POST['stream_action'];$sid=(int)($_POST['stream_id']??0);
   if($action==='add'){
     $label=trim($_POST['stream_label']??'Link');$url=trim($_POST['stream_url']??'');
     if($url===''||!filter_var($url,FILTER_VALIDATE_URL)){$msg='A valid external player URL is required.';}else{
       $mx=db()->prepare('SELECT COALESCE(MAX(sort_order),-1)+1 FROM stream_links WHERE video_id=?');$mx->execute([$id]);$order=(int)$mx->fetchColumn();
       db()->prepare('INSERT INTO stream_links(video_id,label,url,status,sort_order) VALUES(?,?,?,?,?)')->execute([$id,$label?:'Link',$url,1,$order]);log_action('stream.create','video_id='.$id);header('Location: video-edit.php?id='.$id.'#streams');exit;
     }
   } elseif($action==='update'){
     $label=trim($_POST['stream_label']??'Link');$url=trim($_POST['stream_url']??'');
     if(!$sid||$url===''||!filter_var($url,FILTER_VALIDATE_URL)){$msg='A valid external player URL is required.';}else{
       db()->prepare('UPDATE stream_links SET label=?,url=?,status=? WHERE id=? AND video_id=?')->execute([$label?:'Link',$url,isset($_POST['stream_status'])?1:0,$sid,$id]);log_action('stream.update','stream_id='.$sid);header('Location: video-edit.php?id='.$id.'#streams');exit;
     }
   } elseif($action==='delete'){
     db()->prepare('DELETE FROM stream_links WHERE id=? AND video_id=?')->execute([$sid,$id]);log_action('stream.delete','stream_id='.$sid);header('Location: video-edit.php?id='.$id.'#streams');exit;
   }
 } else {
   $title=trim($_POST['title']??'');$desc=trim($_POST['description']??'');$cat=(int)($_POST['category_id']??0);$status=$_POST['status']??'draft';$duration=max(0,(int)($_POST['duration_seconds']??0));
   if($title===''){$msg='Title is required.';}else{
     db()->prepare('UPDATE videos SET title=?,description=?,category_id=?,duration_seconds=?,status=? WHERE id=?')->execute([$title,$desc,$cat?:null,$duration?:null,$status,$id]);log_action('video.update','video_id='.$id);header('Location: video-edit.php?id='.$id);exit;
   }
 }
}
$streams=db()->prepare('SELECT * FROM stream_links WHERE video_id=? ORDER BY sort_order,id');$streams->execute([$id]);$streams=$streams->fetchAll();
$tok=$pdo??db();$ts=$tok->prepare('SELECT * FROM embed_tokens WHERE video_id=? AND status=1 LIMIT 1');$ts->execute([$id]);$token=$ts->fetch();
?>
<div class="page-head"><div><h1>Edit Video</h1><p>Manage metadata and multi-host external player links.</p></div></div>
<?php if($msg):?><p class="error"><?=e($msg)?></p><?php endif;?>
<form method="post" class="panel" style="padding:22px"><?=csrf_field()?>
<div class="grid"><label>Title<input name="title" value="<?=e($v['title'])?>" required></label><label>Duration (seconds)<input type="number" name="duration_seconds" value="<?=e((string)($v['duration_seconds']??''))?>"></label><label>Status<select name="status"><?php foreach(['draft','processing','published','private','failed'] as $s):?><option <?=$v['status']===$s?'selected':''?>><?=$s?></option><?php endforeach;?></select></label></div>
<label>Description<textarea name="description" rows="4"><?=e($v['description'])?></textarea></label>
<label>Category<select name="category_id"><option value="0">None</option><?php foreach($cats as $c):?><option value="<?=$c['id']?>" <?=$v['category_id']==$c['id']?'selected':''?>><?=e($c['name'])?></option><?php endforeach;?></select></label>
<button>Save Video</button>
</form>
<div id="streams" class="panel stream-panel"><div class="panel-head"><div><h2>Multi-Host Player Sources</h2><span class="panel-subtitle">Paste external player/embed URLs such as https://ustreamplay.online/#pniy53</span></div><button type="button" class="button" data-add-stream>+ Add Player</button></div>
<div class="stream-list">
<?php foreach($streams as $s):?>
<form method="post" class="stream-row"><?=csrf_field()?><input type="hidden" name="stream_action" value="update"><input type="hidden" name="stream_id" value="<?=$s['id']?>">
<div class="stream-heading"><b><?=e($s['label'])?></b><span>Requests : <?=number_format($s['requests'])?></span></div>
<div class="stream-grid"><input name="stream_label" value="<?=e($s['label'])?>" placeholder="Host / Link label"><input name="stream_url" value="<?=e($s['url'])?>" placeholder="https://host.example/#video-id"><span class="status-pill <?=$s['status']?'on':'off'?>">Status : <?=$s['status']?'Active':'Inactive'?></span></div>
<div class="stream-actions"><label class="check"><input type="checkbox" name="stream_status" <?=$s['status']?'checked':''?>> Active</label><button class="button" type="submit">Save</button><button class="button danger" type="submit" name="stream_action" value="delete" onclick="return confirm('Delete player source?')">Delete</button></div>
</form>
<?php endforeach;?>
<div id="stream-add" class="stream-add hidden"><form method="post"><?=csrf_field()?><input type="hidden" name="stream_action" value="add"><div class="stream-grid"><input name="stream_label" value="Host <?=count($streams)+1?>" placeholder="Host / Link label"><input name="stream_url" placeholder="https://host.example/#video-id" required></div><div class="stream-actions"><button class="button" type="submit">Add Player Source</button><button class="button secondary" type="button" data-cancel-stream>Cancel</button></div></form></div>
<?php if(!$streams):?><div class="empty">No player sources yet. Add an external player/embed URL.</div><?php endif;?>
</div></div>
<div class="panel" style="padding:22px"><h2>Embed URL</h2><code><?=e(app_url('embed/index.php?token='.($token['token']??'')))?></code><p><a class="button" href="embed.php?id=<?=$id?>">Open Embed</a></p></div>
<script>document.querySelector('[data-add-stream]')?.addEventListener('click',()=>document.getElementById('stream-add').classList.remove('hidden'));document.querySelector('[data-cancel-stream]')?.addEventListener('click',()=>document.getElementById('stream-add').classList.add('hidden'));</script>
<?php require __DIR__.'/_footer.php';