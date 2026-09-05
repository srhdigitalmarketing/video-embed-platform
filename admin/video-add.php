<?php
require_once __DIR__.'/_header.php';
$cats=db()->query("SELECT * FROM categories WHERE status=1 ORDER BY name")->fetchAll();
$msg='';
if($_SERVER['REQUEST_METHOD']==='POST'){
 verify_csrf();
 $title=trim($_POST['title']??'');
 $desc=trim($_POST['description']??'');
 $cat=(int)($_POST['category_id']??0);
 $status=in_array($_POST['status']??'', ['draft','private','published'],true)?$_POST['status']:'draft';
 $streamUrl=trim($_POST['stream_url']??'');
 $streamLabel=trim($_POST['stream_label']??'Stream 1');
 if(!$title){$msg='Title is required.';}
 elseif($streamUrl!=='' && !filter_var($streamUrl,FILTER_VALIDATE_URL)){$msg='Stream URL is invalid.';}
 else{
  $slug=slugify($title);
  $st=db()->prepare('INSERT INTO videos(title,slug,description,category_id,status) VALUES(?,?,?,?,?)');
  $st->execute([$title,$slug,$desc,$cat?:null,$status]);
  $id=(int)db()->lastInsertId();
  db()->prepare('INSERT INTO embed_tokens(video_id,token) VALUES(?,?)')->execute([$id,random_token()]);
  if($streamUrl!=='') db()->prepare('INSERT INTO stream_links(video_id,label,url,status,sort_order) VALUES(?,?,?,?,?)')->execute([$id,$streamLabel?:'Stream 1',$streamUrl,1,0]);
  log_action('video.create','video_id='.$id);
  header('Location: video-edit.php?id='.$id); exit;
 }
}
?><div class="page-head"><div><h1>Add Video</h1><p>Create the video metadata and optional first stream link.</p></div></div>
<form method="post" class="panel" style="padding:22px"><?=csrf_field()?>
<label>Title<input name="title" required></label>
<label>Description<textarea name="description" rows="5"></textarea></label>
<label>Category<select name="category_id"><option value="0">None</option><?php foreach($cats as $c):?><option value="<?=$c['id']?>"><?=e($c['name'])?></option><?php endforeach;?></select></label>
<div class="grid"><label>First Stream Label<input name="stream_label" value="Stream 1"></label><label>First Stream URL<input name="stream_url" placeholder="https://example.com/embed/abc123"></label><label>Status<select name="status"><option>draft</option><option>published</option><option>private</option></select></label></div>
<p class="muted">Cloudflare/R2 fields are intentionally not part of the video editor. Add one or more stream URLs instead.</p>
<button>Create Video</button> <a class="button secondary" href="videos.php">Cancel</a>
</form><?php require __DIR__.'/_footer.php';