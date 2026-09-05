<?php
require_once __DIR__.'/_header.php';
$pdo=db();
$totalVideos=(int)$pdo->query("SELECT COUNT(*) FROM videos WHERE status!='deleted'")->fetchColumn();
$totalViews=(int)$pdo->query("SELECT COALESCE(SUM(views),0) FROM videos")->fetchColumn();
$storage=(int)$pdo->query("SELECT COALESCE(SUM(file_size),0) FROM videos WHERE status!='deleted'")->fetchColumn();
$activeEmbeds=(int)$pdo->query("SELECT COUNT(*) FROM embed_tokens WHERE status=1")->fetchColumn();
$published=(int)$pdo->query("SELECT COUNT(*) FROM videos WHERE status='published'")->fetchColumn();
$processing=(int)$pdo->query("SELECT COUNT(*) FROM videos WHERE status='processing'")->fetchColumn();
$events=(int)$pdo->query("SELECT COUNT(*) FROM video_views WHERE created_at>=DATE_SUB(NOW(),INTERVAL 7 DAY)")->fetchColumn();
$recent=$pdo->query("SELECT v.*,c.name category FROM videos v LEFT JOIN categories c ON c.id=v.category_id WHERE v.status!='deleted' ORDER BY v.created_at DESC LIMIT 5")->fetchAll();
$daily=$pdo->query("SELECT DATE(created_at) d,COUNT(*) n FROM video_views WHERE created_at>=DATE_SUB(CURDATE(),INTERVAL 6 DAY) GROUP BY DATE(created_at) ORDER BY d")->fetchAll();
$days=[];$vals=[];for($i=6;$i>=0;$i--){$d=date('Y-m-d',strtotime("-$i day"));$days[]=date('M j',strtotime($d));$vals[]=0;foreach($daily as $r)if($r['d']===$d)$vals[count($vals)-1]=(int)$r['n'];}
$max=max(1,max($vals));
?>
<div class="page-head"><div><h1>Welcome back, Admin!</h1><p>Here's what's happening with your video platform today.</p></div><div class="date"><?=e(date('l, F j, Y'))?></div></div>

<section class="stats-grid">
 <div class="stat-card blue"><div class="stat-icon">▶</div><div><span>Total Videos</span><strong><?=number_format($totalVideos)?></strong><small>Published <?=number_format($published)?></small></div></div>
 <div class="stat-card green"><div class="stat-icon">♟</div><div><span>Total Views</span><strong><?=number_format($totalViews)?></strong><small>7-day events <?=number_format($events)?></small></div></div>
 <div class="stat-card amber"><div class="stat-icon">▤</div><div><span>Storage Used</span><strong><?=number_format($storage/1073741824,1)?> GB</strong><small>R2 tracked storage</small></div></div>
 <div class="stat-card purple"><div class="stat-icon">↗</div><div><span>Active Embeds</span><strong><?=number_format($activeEmbeds)?></strong><small>Allowed tokens</small></div></div>
</section>

<section class="dashboard-grid">
 <div class="panel chart-panel"><div class="panel-head"><h2>Views Overview</h2><span class="select-like">Last 7 days⌄</span></div>
  <div class="chart"><div class="y-axis"><span><?=number_format($max)?></span><span><?=number_format($max*.75)?></span><span><?=number_format($max*.5)?></span><span><?=number_format($max*.25)?></span><span>0</span></div><div class="bars"><?php foreach($vals as $i=>$v):?><div class="bar-wrap"><div class="bar" style="height:<?=max(4,($v/$max)*100)?>%" title="<?=number_format($v)?> views"></div><span><?=e($days[$i])?></span></div><?php endforeach;?></div></div>
 </div>
 <div class="panel"><div class="panel-head"><h2>Platform Status</h2></div>
   <div class="status-list"><div><span class="dot green-dot"></span>Published <b><?=$published?></b></div><div><span class="dot amber-dot"></span>Processing <b><?=$processing?></b></div><div><span class="dot gray-dot"></span>Draft / Private <b><?=max(0,$totalVideos-$published-$processing)?></b></div></div>
   <div class="quick-card"><span>↗</span><div><b>Embed-first architecture</b><small>No public homepage or video catalog.</small></div></div>
 </div>
</section>

<section class="dashboard-grid lower">
 <div class="panel"><div class="panel-head"><h2>Latest Videos</h2><a href="videos.php">View All →</a></div>
 <div class="table-wrap"><table><thead><tr><th>#</th><th>Thumbnail</th><th>Title</th><th>Status</th><th>Views</th><th>Created At</th><th>Actions</th></tr></thead><tbody>
 <?php foreach($recent as $i=>$v):?><tr><td><?=($i+1)?></td><td><div class="thumb"><?=e(strtoupper(substr($v['title'],0,1)))?></div></td><td><b><?=e($v['title'])?></b><small class="muted"><?=e($v['category']??'Uncategorized')?></small></td><td><span class="badge <?=e($v['status'])?>"><?=e(ucfirst($v['status']))?></span></td><td><?=number_format((int)$v['views'])?></td><td><?=e(date('Y-m-d',strtotime($v['created_at'])))?></td><td class="actions"><a href="video-edit.php?id=<?=$v['id']?>">✎</a><a href="analytics.php?video=<?=$v['id']?>">▥</a></td></tr><?php endforeach;?>
 <?php if(!$recent):?><tr><td colspan="7" class="empty">No videos yet.</td></tr><?php endif;?></tbody></table></div></div>
 <div class="panel activity"><div class="panel-head"><h2>Recent Activity</h2></div>
 <?php $logs=$pdo->query("SELECT action,details,created_at FROM system_logs ORDER BY created_at DESC LIMIT 5")->fetchAll();foreach($logs as $l):?><div class="activity-item"><span class="activity-icon">●</span><div><b><?=e(ucwords(str_replace('.',' ', $l['action'])))?></b><small><?=e($l['details']??'')?> · <?=e(date('M j, H:i',strtotime($l['created_at'])))?></small></div></div><?php endforeach;if(!$logs):?><div class="empty">No activity recorded.</div><?php endif;?>
 <a class="activity-link" href="logs.php">View All Logs →</a></div>
</section>
<?php require __DIR__.'/_footer.php';