<?php
/**
 * clear_cache.php — One-time cache clearing utility.
 * Access: http://localhost/news-aggregator/php/clear_cache.php
 * DELETE THIS FILE after running it once.
 */
$cacheDir = __DIR__ . '/cache/';
$count = 0;

if (is_dir($cacheDir)) {
    foreach (glob($cacheDir . '*.json') as $file) {
        unlink($file);
        $count++;
    }
    echo "✅ Cache cleared! Deleted {$count} cached file(s).<br>";
    echo "You can now delete this file (php/clear_cache.php).";
} else {
    echo "ℹ️ No cache directory found — nothing to clear. (It will be created automatically on next request.)";
}
