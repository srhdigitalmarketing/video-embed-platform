<?php
require_once __DIR__.'/_header.php';
$pdo=db();$msg='';
if($_SERVER['REQUEST_METHOD']==='POST'){
 verify_csrf();$action=$_POST['action']??'';$id=(int)($_POST['id']??0);$name=trim($_POST['name']??'');
 if($action==='add'&&$name!==''){
  $slug=slugify($name);$st=$pdo->prepare('INSERT INTO categories(name,slug) VALUES(?,?)');$st->execute([$name,$slug]);log_action('category.create','category_id='.$pdo->lastInsertId());$msg='Category added.';
 }elseif($action==='update'&&$id>0&&$name!==''){
  $slug=slugify($name);$st=$pdo->prepare('UPDATE categories SET name=?,slug=? WHERE id=?');$st->execute([$name,$slug,$id]);log_action('category.update','category_id='.$id);$msg='Category updated.';
 }elseif($action==='delete'&&$id>0){
  $pdo->prepare('UPDATE videos SET category_id=NULL WHERE category_id=?')->execute([$id]);$pdo->prepare('DELETE FROM categories WHERE id=?')->execute([$id]);log_action('category.delete','category_id='.$id);$msg='Category deleted.';
 }
}
$rows=$pdo->query('SELECT c.*,COUNT(v.id) video_count FROM categories c LEFT JOIN videos v ON v.category_id=c.id AND v.status!=\'deleted\' GROUP BY c.id ORDER BY c.name')->fetchAll();
?><div class="page-head"><div><h1>Categories</h1><p>Organize videos with editable categories.</p></div></div>
<?php if($msg):?><p class="ok"><?=e($msg)?></p><?php endif;?>
<div class="panel" style="padding:22px"><h2>Add Category</h2><form method="post" class="grid"><input type="hidden" name="action" value="add"><?=csrf_field()?><label>Name<input name="name" placeholder="Category name" required></label><div style="display:flex;align-items:end"><button>Add Category</button></div></form></div>
<div class="panel"><div class="panel-head"><h2>Category List</h2><span class="select-like"><?=count($rows)?> categories</span></div><div class="table-wrap"><table><thead><tr><th>Name</th><th>Slug</th><th>Videos</th><th>Actions</th></tr></thead><tbody><?php foreach($rows as $r):?><tr><td><b><?=e($r['name'])?></b></td><td><?=e($r['slug'])?></td><td><?=number_format($r['video_count'])?></td><td class="actions"><a href="category-edit.php?id=<?=$r['id']?>">✎ Edit</a><form method="post" action="categories.php" style="display:inline"><?=csrf_field()?><input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="<?=$r['id']?>"><button type="submit" class="danger link-button" onclick="return confirm('Delete this category? Videos will become uncategorized.')">Delete</button></form></td></tr><?php endforeach;?><?php if(!$rows):?><tr><td colspan="4" class="empty">No categories yet.</td></tr><?php endif;?></tbody></table></div></div>
<?php require __DIR__.'/_footer.php';