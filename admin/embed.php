<?php
require_once __DIR__.'/_header.php';
$id=(int)($_GET['id']??0);
$st=db()->prepare('SELECT v.title,t.id token_id,t.token,t.play_key FROM videos v JOIN embed_tokens t ON t.video_id=v.id AND t.status=1 WHERE v.id=? LIMIT 1');
$st->execute([$id]);$x=$st->fetch();if(!$x)exit('No active embed token');
if(empty($x['play_key'])){
  do{$playKey='tt'.random_int(100000000,999999999);$q=db()->prepare('SELECT id FROM embed_tokens WHERE play_key=?');$q->execute([$playKey]);}while($q->fetch());
  db()->prepare('UPDATE embed_tokens SET play_key=? WHERE id=?')->execute([$playKey,$x['token_id']]);$x['play_key']=$playKey;
}
$src=app_url('play/'.$x['play_key']);
$code='<iframe src="'.$src.'" style="width:100%;aspect-ratio:16/9;border:0" allow="autoplay; fullscreen; picture-in-picture" allowfullscreen loading="lazy"></iframe>';
?><div class="page-head"><div><h1>Embed Player</h1><p>Use the clean public player URL on external websites.</p></div></div>
<div class="panel" style="padding:22px"><h2><?=e($x['title'])?></h2>
<div class="card" style="background:#f8fafc"><small>Player URL</small><div style="font-size:18px;font-weight:700;margin-top:6px;word-break:break-all"><?=e($src)?></div></div>
<div style="aspect-ratio:16/9;background:#000;margin:18px 0;border-radius:8px;overflow:hidden"><iframe src="<?=e($src)?>" style="width:100%;height:100%;border:0" allow="autoplay;fullscreen;picture-in-picture" allowfullscreen></iframe></div>
<label>Embed Code<textarea rows="5" readonly id="embed-code"><?=e($code)?></textarea></label><button onclick="navigator.clipboard.writeText(document.getElementById('embed-code').value)">Copy Embed Code</button></div>
<?php require __DIR__.'/_footer.php';