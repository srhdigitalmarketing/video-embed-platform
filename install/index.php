<?php
require_once dirname(__DIR__).'/app/config.php';
$done=false;$error='';
if($_SERVER['REQUEST_METHOD']==='POST'){
 try{
  $dsn='mysql:host='.trim($_POST['db_host']).';port='.trim($_POST['db_port']).';dbname='.trim($_POST['db_name']).';charset=utf8mb4';
  $pdo=new PDO($dsn,trim($_POST['db_user']),$_POST['db_pass'],[PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION]);
  $sql=file_get_contents(dirname(__DIR__).'/database.sql'); foreach(array_filter(array_map('trim',preg_split('/;[\r\n]+/',$sql))) as $q){if($q)$pdo->exec($q);}
  $email=trim($_POST['admin_email']);$pass=$_POST['admin_password'];$hash=password_hash($pass,PASSWORD_DEFAULT);
  $st=$pdo->prepare('INSERT INTO users(email,password_hash) VALUES(?,?) ON DUPLICATE KEY UPDATE password_hash=VALUES(password_hash),status=1');$st->execute([$email,$hash]);
  $env="APP_NAME=\"Video Embed Platform\"\nAPP_URL=\"".rtrim(trim($_POST['app_url']),'/')."\"\nAPP_KEY=\"".bin2hex(random_bytes(32))."\"\nDB_HOST=\"".trim($_POST['db_host'])."\"\nDB_PORT=\"".trim($_POST['db_port'])."\"\nDB_DATABASE=\"".trim($_POST['db_name'])."\"\nDB_USERNAME=\"".trim($_POST['db_user'])."\"\nDB_PASSWORD=\"".trim($_POST['db_pass'])."\"\nR2_ACCOUNT_ID=\"\"\nR2_ACCESS_KEY_ID=\"\"\nR2_SECRET_ACCESS_KEY=\"\"\nR2_BUCKET=\"\"\nR2_ENDPOINT=\"\"\nR2_PUBLIC_BASE_URL=\"\"\nR2_REGION=\"auto\"\n";
  if(!file_put_contents(dirname(__DIR__).'/.env',$env)) throw new Exception('Could not write .env. Create it manually from .env.example.');
  $done=true;
 }catch(Throwable $e){$error=$e->getMessage();}
}
?><!doctype html><html><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Installer</title><style>body{font-family:system-ui;background:#f5f5f5;padding:30px}.card{max-width:650px;margin:auto;background:white;padding:25px;border-radius:12px}input{display:block;width:100%;box-sizing:border-box;padding:11px;margin:7px 0 15px}button{padding:12px 18px}.error{color:#b00}</style></head><body><div class="card"><h1>Video Embed Platform Installer</h1><?php if($done):?><p>Installation completed. Delete/disable the <code>install</code> directory, then login at <a href="../admin/login.php">Admin Login</a>.</p><?php else:?><?php if($error):?><p class="error"><?=htmlspecialchars($error)?></p><?php endif;?><form method="post"><label>App URL<input name="app_url" value="https://player.example.com" required></label><h3>Database</h3><input name="db_host" value="127.0.0.1"><input name="db_port" value="3306"><input name="db_name" placeholder="Database name" required><input name="db_user" placeholder="Database user" required><input type="password" name="db_pass" placeholder="Database password"><h3>Admin</h3><input type="email" name="admin_email" placeholder="Admin email" required><input type="password" name="admin_password" placeholder="Admin password" required><button>Install</button></form><?php endif;?></div></body></html>