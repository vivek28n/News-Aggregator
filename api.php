<?php
/**
 * api.php
 * ─────────────────────────────────────────────────────────────────────────────
 * Clean alias for fetch_news.php.
 * The frontend can call either:
 *   php/fetch_news.php?category=technology
 *   php/api.php?category=technology       ← this file
 *
 * Both routes are identical in behaviour.
 * ─────────────────────────────────────────────────────────────────────────────
 */
require_once __DIR__ . '/fetch_news.php';
