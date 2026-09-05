<?php
require_once __DIR__.'/_header.php';
$id=(int)($_GET['id']??0);
if($_SERVER['REQUEST_METHOD']!=='POST'){http_response_code(405);exit('Method not allowed');}
verify_csrf();
$st=db()->prepare('DELETE FROM ad_campaigns WHERE id=?');$st->execute([$id]);
log_action('ad.delete','campaign_id='.$id);
header('Location: ads.php');exit;