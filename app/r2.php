<?php
require_once __DIR__.'/config.php';
require_once dirname(__DIR__).'/vendor/autoload.php';
use Aws\S3\S3Client;
function r2_client(): S3Client {
    static $client; if ($client) return $client;
    $client = new S3Client(['version'=>'latest','region'=>env('R2_REGION','auto'),'endpoint'=>env('R2_ENDPOINT'),'use_path_style_endpoint'=>true,'credentials'=>['key'=>env('R2_ACCESS_KEY_ID'),'secret'=>env('R2_SECRET_ACCESS_KEY')]]);
    return $client;
}
function r2_put(string $key, string $source, string $contentType='application/octet-stream'): array { return r2_client()->putObject(['Bucket'=>env('R2_BUCKET'),'Key'=>$key,'SourceFile'=>$source,'ContentType'=>$contentType]); }
function r2_delete(string $key): void { r2_client()->deleteObject(['Bucket'=>env('R2_BUCKET'),'Key'=>$key]); }
function r2_presigned_url(string $key, int $minutes=10): string { $cmd=r2_client()->getCommand('GetObject',['Bucket'=>env('R2_BUCKET'),'Key'=>$key]); $req=r2_client()->createPresignedRequest($cmd, '+'.$minutes.' minutes'); return (string)$req->getUri(); }