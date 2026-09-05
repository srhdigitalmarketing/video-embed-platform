<?php
require_once dirname(__DIR__).'/app/helpers.php';
require_once dirname(__DIR__).'/app/database.php';
$key = preg_replace('/[^A-Za-z0-9_-]/','',(string)($_GET['key']??''));
$st=db()->prepare("SELECT t.token,t.id token_id,v.id,v.title FROM embed_tokens t JOIN videos v ON v.id=t.video_id WHERE t.play_key=? AND t.status=1 AND (t.expires_at IS NULL OR t.expires_at>NOW()) AND v.status='published' LIMIT 1");
$st->execute([$key]);
$row=$st->fetch();
if(!$row){http_response_code(404);exit('Video not found');}
header('Content-Type: text/html; charset=utf-8');
$_GET['token']=$row['token'];
require dirname(__DIR__).'/embed/index.php';
