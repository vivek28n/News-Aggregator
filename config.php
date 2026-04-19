<?php
define('NEWSAPI_KEY',  'b9fbaffe08524cf39817fcd088539ac2');
define('NEWSAPI_BASE', 'https://newsapi.org/v2/');

define('VALID_CATEGORIES', [
    'technology',
    'sports',
    'business',
    'entertainment',
    'health',
    'science',
]);

define('PAGE_SIZE',       30);
define('REQUEST_TIMEOUT', 10);
define('NEWS_COUNTRY',   'in');
define('NEWS_LANGUAGE',  'en');

define('ENABLE_CACHE', true);
define('CACHE_TTL',    300);
define('CACHE_DIR',    __DIR__ . '/cache/');
