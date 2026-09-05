<?php
require_once dirname(__DIR__).'/app/auth.php';
if (is_logged_in()) { header('Location: '.app_url('admin/index.php')); exit; }
$error='';
if ($_SERVER['REQUEST_METHOD']==='POST') { verify_csrf(); if(!login_admin(trim($_POST['email']??''),$_POST['password']??'')) $error='Invalid credentials.'; else { header('Location: '.app_url('admin/index.php')); exit; } }
?><!doctype html><html><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Admin Login</title><link rel="stylesheet" href="<?=e(app_url('assets/css/admin.css'))?>"></head><body class="login"><form method="post" class="card"><h1>Admin Login</h1><?=csrf_field()?><?php if($error):?><p class="error"><?=e($error)?></p><?php endif;?><input type="email" name="email" placeholder="Email" required><input type="password" name="password" placeholder="Password" required><button>Login</button></form></body></html>