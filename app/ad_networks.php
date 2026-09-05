<?php
require_once __DIR__.'/config.php';
require_once __DIR__.'/database.php';

function http_json(string $url, array $headers=[]): array {
    $ch=curl_init($url);
    curl_setopt_array($ch,[CURLOPT_RETURNTRANSFER=>true,CURLOPT_FOLLOWLOCATION=>true,CURLOPT_CONNECTTIMEOUT=>10,CURLOPT_TIMEOUT=>20,CURLOPT_HTTPHEADER=>$headers,CURLOPT_USERAGENT=>'VideoEmbedPlatform/1.0']);
    $body=curl_exec($ch); $err=curl_error($ch); $code=(int)curl_getinfo($ch,CURLINFO_HTTP_CODE); curl_close($ch);
    if($body===false || $err) throw new RuntimeException('HTTP error: '.$err);
    if($code<200 || $code>=300) throw new RuntimeException('API HTTP '.$code);
    $json=json_decode($body,true); if(!is_array($json)) throw new RuntimeException('Invalid JSON from network API'); return $json;
}
function cache_get(string $key): ?array {
    try { $st=db()->prepare('SELECT payload FROM api_cache WHERE cache_key=? AND expires_at>NOW()'); $st->execute([$key]); $p=$st->fetchColumn(); return $p ? json_decode($p,true) : null; } catch(Throwable $e) { return null; }
}
function cache_put(string $key,array $payload,int $seconds): void {
    try { db()->prepare('INSERT INTO api_cache(cache_key,payload,expires_at) VALUES(?,?,DATE_ADD(NOW(),INTERVAL ? SECOND)) ON DUPLICATE KEY UPDATE payload=VALUES(payload),expires_at=VALUES(expires_at)')->execute([$key,json_encode($payload),$seconds]); } catch(Throwable $e) {}
}
function adsterra_stats(string $from,string $to): array {
    $key=(string)env('ADSTERRA_API_KEY','');
    if(!$key) return ['configured'=>false,'network'=>'Adsterra','revenue'=>0,'impressions'=>0,'clicks'=>0,'rows'=>[],'error'=>'API key not configured'];
    $cacheKey='adsterra:'.$from.':'.$to.':'.env('ADSTERRA_DOMAIN_ID','').':'.env('ADSTERRA_PLACEMENT_ID',''); if($c=cache_get($cacheKey)) return $c;
    $q=['start_date'=>$from,'finish_date'=>$to,'group_by[]'=>'date']; if(env('ADSTERRA_DOMAIN_ID')) $q['domain']=env('ADSTERRA_DOMAIN_ID'); if(env('ADSTERRA_PLACEMENT_ID')) $q['placement']=env('ADSTERRA_PLACEMENT_ID');
    try { $j=http_json('https://api3.adsterratools.com/publisher/stats.json?'.http_build_query($q),['Accept: application/json','X-API-Key: '.$key]); $rows=$j['stats']??$j['data']??[]; $total=$j['total']??[]; $out=['configured'=>true,'network'=>'Adsterra','revenue'=>(float)($total['revenue']??$total['money']??0),'impressions'=>(int)($total['impressions']??0),'clicks'=>(int)($total['clicks']??0),'rows'=>$rows]; if(!$out['revenue']&&is_array($rows)) foreach($rows as $r) $out['revenue']+=(float)($r['revenue']??$r['money']??0); cache_put($cacheKey,$out,(int)env('NETWORK_STATS_CACHE_SECONDS','600')); return $out; } catch(Throwable $e) { return ['configured'=>true,'network'=>'Adsterra','revenue'=>0,'impressions'=>0,'clicks'=>0,'rows'=>[],'error'=>$e->getMessage()]; }
}
function clickadu_stats(string $from,string $to): array {
    $key=(string)env('CLICKADU_API_TOKEN','');
    if(!$key) return ['configured'=>false,'network'=>'Clickadu','revenue'=>0,'impressions'=>0,'clicks'=>0,'rows'=>[],'error'=>'API token not configured'];
    $cacheKey='clickadu:'.$from.':'.$to.':'.env('CLICKADU_SITE_ID','').':'.env('CLICKADU_ZONE_ID',''); if($c=cache_get($cacheKey)) return $c;
    $q=['token'=>$key,'dateFrom'=>$from,'dateTo'=>$to,'groupBy'=>'day','format'=>'json']; if(env('CLICKADU_SITE_ID')) $q['siteId']=env('CLICKADU_SITE_ID'); if(env('CLICKADU_ZONE_ID')) $q['zoneId']=env('CLICKADU_ZONE_ID');
    try { $j=http_json('https://v2.api.clickadu.com/partner/stats?'.http_build_query($q),['Accept: application/json']); $total=$j['total']??[]; $rows=$j['stats']??[]; $out=['configured'=>true,'network'=>'Clickadu','revenue'=>(float)($total['money']??$total['revenue']??0),'impressions'=>(int)($total['impressions']??0),'clicks'=>(int)($total['clicks']??0),'rows'=>$rows]; if(!$out['revenue']&&is_array($rows)) foreach($rows as $r) $out['revenue']+=(float)($r['money']??$r['revenue']??0); cache_put($cacheKey,$out,(int)env('NETWORK_STATS_CACHE_SECONDS','600')); return $out; } catch(Throwable $e) { return ['configured'=>true,'network'=>'Clickadu','revenue'=>0,'impressions'=>0,'clicks'=>0,'rows'=>[],'error'=>$e->getMessage()]; }
}