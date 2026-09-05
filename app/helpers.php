<?php
require_once __DIR__.'/database.php';
function e(?string $s): string { return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }
function slugify(string $s): string { $s=preg_replace('/[^a-z0-9]+/i','-',trim($s)); return strtolower(trim($s,'-')) ?: bin2hex(random_bytes(4)); }
function random_token(): string { return bin2hex(random_bytes(32)); }
function base_path(string $p=''): string { return dirname(__DIR__).'/'.ltrim($p,'/'); }