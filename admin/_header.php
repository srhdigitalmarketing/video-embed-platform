<?php
require_once dirname(__DIR__).'/app/auth.php';
require_admin();
$page = basename($_SERVER['PHP_SELF']);
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<meta name="robots" content="noindex,nofollow">
<title><?= e(env('APP_NAME','Video Embed Platform')) ?> — Admin</title>
<link rel="stylesheet" href="../assets/css/admin.css?v=1.1.1">
<script defer src="../assets/js/admin.js?v=1.1.1"></script>
</head>
<body>
<aside class="sidebar">
  <div class="brand"><span class="brand-icon">▶</span><span>Video Embed<br>Platform</span></div>
  <nav>
    <a class="<?= $page==='index.php'?'active':'' ?>" href="<?=e(app_url('admin/index.php'))?>"><span>⌂</span>Dashboard</a>
    <a class="<?= in_array($page,['videos.php','video-add.php','video-edit.php'])?'active':'' ?>" href="<?=e(app_url('admin/videos.php'))?>"><span>▣</span>Videos</a>
    <a class="<?= $page==='categories.php'?'active':'' ?>" href="<?=e(app_url('admin/categories.php'))?>"><span>■</span>Categories</a>
    <a class="<?= $page==='embed.php'?'active':'' ?>" href="<?=e(app_url('admin/embed.php'))?>"><span>‹/›</span>Embed</a>
    <a class="<?= $page==='ads.php'?'active':'' ?>" href="<?=e(app_url('admin/ads.php'))?>"><span>Ad</span>Advertisements</a>
    <a class="<?= $page==='analytics.php'?'active':'' ?>" href="<?=e(app_url('admin/analytics.php'))?>"><span>▥</span>Analytics</a>
    <a class="<?= $page==='domains.php'?'active':'' ?>" href="<?=e(app_url('admin/domains.php'))?>"><span>◎</span>Domains</a>
    <a class="<?= $page==='settings.php'?'active':'' ?>" href="<?=e(app_url('admin/settings.php'))?>"><span>⚙</span>Settings</a>
    <a href="<?=e(app_url('admin/logs.php'))?>"><span>▤</span>System Logs</a>
  </nav>
  <a class="logout" href="<?=e(app_url('admin/logout.php'))?>"><span>⇥</span>Logout</a>
</aside>
<div class="app-shell">
<header class="topbar">
  <button class="menu-toggle" data-sidebar-toggle>☰</button>
  <div class="topbar-spacer"></div>
  <button class="icon-btn" title="Notifications">♧<i>3</i></button>
  <div class="profile"><span class="avatar"><?=e(strtoupper(substr((string)($_SESSION['admin_email']??'A'),0,1)))?></span><span><?=e($_SESSION['admin_email']??'admin')?></span><span>⌄</span></div>
</header>
<main class="content">