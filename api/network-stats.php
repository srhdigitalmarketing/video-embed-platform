<?php
require_once dirname(__DIR__).'/app/auth.php';
require_once dirname(__DIR__).'/app/ad_networks.php';
require_admin();
$from=preg_match('/^\d{4}-\d{2}-\d{2}$/',$_GET['from']??'')?$_GET['from']:date('Y-m-01');
$to=preg_match('/^\d{4}-\d{2}-\d{2}$/',$_GET['to']??'')?$_GET['to']:date('Y-m-d');
header('Content-Type: application/json');
$a=adsterra_stats($from,$to); $c=clickadu_stats($from,$to);
echo json_encode(['from'=>$from,'to'=>$to,'networks'=>[$a,$c],'total_revenue'=>(float)$a['revenue']+(float)$c['revenue'],'total_impressions'=>(int)$a['impressions']+(int)$c['impressions'],'total_clicks'=>(int)$a['clicks']+(int)$c['clicks']]);