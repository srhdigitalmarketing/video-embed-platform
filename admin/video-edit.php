<?php
require_once __DIR__.'/_header.php';
$id=(int)($_GET['id']??0);
$st=db()->prepare('SELECT * FROM videos WHERE id=?');$st->execute([$id]);$v=$st->fetch();if(!$v){http_response_code(404);exit('Not found');}
$cats=db()->query("SELECT * FROM categories WHERE status=1 ORDER BY name")->fetchAll();
if($_SERVER['REQUEST_METHOD']==='POST'){
 verify_csrf();
 $title=trim($_POST['title']??'');$desc=trim($_POST['description']??'');$cat=(int)($_POST['category_id']??0);$status=$_POST['status']??'draft';$duration=max(0,(int)($_POST['duration_seconds']??0));
 if($title===''){exit('Title is required');}
 db()->prepare('UPDATE videos SET title=?,description=?,category_id=?,duration_seconds=?,status=? WHERE id=?')->execute([$title,$desc,$cat?:null,$duration?:null,$status,$id]);
 if(isset($_POST['stream_action'])){
   $action=$_POST['stream_action'];$sid=(int)($_POST['stream_id']??0);
   if($action==='add'){ $label=trim($_POST['stream_label']??'Stream');$url=trim($_POST['stream_url']??'');if($url!==''){db()->prepare('INSERT INTO stream_links(video_id,label,url,status,sort_order) VALUES(?,?,?,?,?)')->execute([$id,$label,$url,1,999]);} }
   if($action==='update'){ $label=trim($_POST['stream_label']??'Stream');$url=trim($_POST['stream_url']??'');db()->prepare('UPDATE stream_links SET label=?,url=?,status=? WHERE id=? AND video_id=?')->execute([$label,$url,isset($_POST['stream_status'])?1:0,$sid,$id]); }
   if($action==='delete'){db()->prepare('DELETE FROM stream_links WHERE id=? AND video_id=?')->execute([$sid,$id]);}
 }
 if(!isset($_POST['stream_action'])){log_action('video.update','video_id='.$id);header('Location: video-edit.php?id='.$id);exit;}
}
$streams=db()->prepare('SELECT * FROM stream_links WHERE video_id=? ORDER BY sort_order,id');$streams->execute([$id]);$streams=$streams->fetchAll();
$tok=db()->prepare('SELECT * FROM embed_tokens WHERE video_id=? AND status=1 LIMIT 1');$tok->execute([$id]);$token=$tok->fetch();
?>
<div class="page-head"><div><h1>Edit Video</h1><p>Manage metadata, stream sources and embed settings.</p></div></div>
<form method="post" class="panel" style="padding:22px"><?=csrf_field()?><div class="grid"><label>Title<input name="title" value="<?=e($v['title'])?>" required></label><label>Duration (seconds)<input type="number" name="duration_seconds" value="<?=e((string)($v['duration_seconds']??''))?>"></label><label>Status<select name="status"><?php foreach(['draft','processing','published','private','failed'] as $s):?><option <?=$v['status']===$s?'selected':''?>><?=$s?></option><?php endforeach;?></select></label></div><label>Description<textarea name="description" rows="4"><?=e($v['description'])?></textarea></label><label>Category<select name="category_id"><option value="0">None</option><?php foreach($cats as $c):?><option value="<?=$c['id']?>" <?=$v['category_id']==$c['id']?'selected':''?>><?=e($c['name'])?></option><?php endforeach;?></select></label><button>Save Video</button></form>
<div class="panel stream-panel"><div class="panel-head"><h2>Stream Links</h2><button type="button" class="button" data-add-stream>+ Add Stream</button></div><div class="stream-list">
<?php foreach($streams as $s):?><form method="post" class="stream-row"><?=csrf_field()?><input type="hidden" name="stream_action" value="update"><input type="hidden" name="stream_id" value="<?=$s['id']?>"><div class="stream-title"><b><?=e($s['label'])?></b><small>Requests : <?=number_format($s['requests'])?></small></div><input name="stream_label" value="<?=e($s['label'])?>"><input name="stream_url" value="<?=e($s['url'])?>"><div class="stream-status">Status : <b class="<?= $s['status']?'status-active':'status-off' ?>"><?= $s['status']?'Active':'Inactive' ?></b></div><label class="check"><input type="checkbox" name="stream_status" <?=$s['status']?'checked':''?>> Active</label><button class="button" type="submit">Save</button><button class="danger button" type="submit" name="stream_action" value="delete" onclick="return confirm('Delete stream link?')">Delete</button></form><?php endforeach;?>
<div id="stream-add" class="stream-row hidden"><form method="post"><?=csrf_field()?><input type="hidden" name="stream_action" value="add"><div class="grid"><label>Label<input name="stream_label" value="Link <?=count($streams)+1?>"></label><label>Stream URL<input name="stream_url" placeholder="https://.../video.m3u8" required></label></div><button class="button" type="submit">Add Stream Link</button><button class="button secondary" type="button" data-cancel-stream>Cancel</button></form></div>
<?php if(!$streams):?><div class="empty">No stream links yet. Add an HLS (.m3u8) or MP4 stream URL.</div><?php endif;?></div></div>
<div class="panel" style="padding:22px"><h2>Embed</h2><code><?=e(app_url('embed/index.php?token='.($token['token']??'')))?></code><p><a class="button" href="embed.php?id=<?=$id?>">Open Embed Settings</a></p></div>
<script>document.querySelector('[data-add-stream]')?.addEventListener('click',()=>document.getElementById('stream-add').classList.remove('hidden'));document.querySelector('[data-cancel-stream]')?.addEventListener('click',()=>document.getElementById('stream-add').classList.add('hidden'));</script>
<?php require __DIR__.'/_footer.php';