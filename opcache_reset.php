<?php
/**
 * opcache_reset.php — One-time OPcache flush helper
 * Access: http://localhost/news-aggregator/php/opcache_reset.php
 * DELETE THIS FILE after running it once.
 */
if (function_exists('opcache_reset')) {
    opcache_reset();
    echo json_encode(['status' => 'ok', 'message' => 'OPcache cleared. All PHP files will be freshly compiled on next request.']);
} else {
    echo json_encode(['status' => 'skip', 'message' => 'OPcache is not enabled. Files will be served directly from disk — no action needed.']);
}
