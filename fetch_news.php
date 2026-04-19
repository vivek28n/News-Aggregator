<?php
if (function_exists('opcache_invalidate')) {
    opcache_invalidate(__FILE__, true);
    opcache_invalidate(__DIR__ . '/config.php', true);
}

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Cache-Control: no-store');

require_once __DIR__ . '/config.php';

$category = isset($_GET['category']) ? strtolower(trim($_GET['category'])) : '';
$query    = isset($_GET['q'])        ? strip_tags(mb_substr(trim($_GET['q']), 0, 100)) : '';

if ($category && !in_array($category, VALID_CATEGORIES, true)) {
    $category = '';
}

function curlGet(string $url): array
{
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT        => REQUEST_TIMEOUT,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_USERAGENT      => 'NewsHub-Aggregator/2.0',
        CURLOPT_FOLLOWLOCATION => true,
    ]);
    $raw  = curl_exec($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $err  = curl_error($ch);
    curl_close($ch);
    return [$code, ($raw ? json_decode($raw, true) : null), $err];
}

if ($query) {
    $url = NEWSAPI_BASE . 'everything?' . http_build_query([
        'q'        => $query,
        'language' => NEWS_LANGUAGE,
        'pageSize' => PAGE_SIZE,
        'sortBy'   => 'publishedAt',
        'apiKey'   => NEWSAPI_KEY,
    ]);
} else {
    $params = [
        'country'  => NEWS_COUNTRY,
        'pageSize' => PAGE_SIZE,
        'apiKey'   => NEWSAPI_KEY,
    ];
    if ($category) {
        $params['category'] = $category;
    }
    $url = NEWSAPI_BASE . 'top-headlines?' . http_build_query($params);
}

$cacheFile = null;
$source    = 'newsapi';

if (ENABLE_CACHE) {
    if (!is_dir(CACHE_DIR)) {
        @mkdir(CACHE_DIR, 0755, true);
    }
    $cacheKey  = md5($url);
    $cacheFile = CACHE_DIR . $cacheKey . '.json';
    if (file_exists($cacheFile) && (time() - filemtime($cacheFile)) < CACHE_TTL) {
        $cached = file_get_contents($cacheFile);
        if ($cached) {
            $d = json_decode($cached, true);
            if ($d && $d['status'] === 'ok' && !empty($d['articles'])) {
                echo json_encode(['status' => 'ok', 'source' => 'cache', 'articles' => $d['articles']]);
                exit;
            }
        }
    }
}

$articles   = [];
$apiError   = false;
$apiErrCode = '';
$apiErrMsg  = '';

if (NEWSAPI_KEY !== 'YOUR_NEWSAPI_KEY_HERE' && !empty(NEWSAPI_KEY)) {

    [$httpCode, $data, $curlErr] = curlGet($url);

    if (
        !$curlErr
        && $data
        && ($data['status'] ?? '') !== 'ok'
        && in_array($data['code'] ?? '', ['parameterInvalid', 'parametersMissing'], true)
        && !$query
    ) {
        $fallbackParams = ['pageSize' => PAGE_SIZE, 'apiKey' => NEWSAPI_KEY];
        if ($category) $fallbackParams['category'] = $category;
        $fallbackUrl = NEWSAPI_BASE . 'top-headlines?' . http_build_query($fallbackParams);
        error_log('[NewsHub] Retrying without country= param.');
        [$httpCode, $data, $curlErr] = curlGet($fallbackUrl);
    }

    if ($curlErr) {
        error_log('[NewsHub] cURL error: ' . $curlErr);
        $apiError   = true;
        $apiErrCode = 'curlError';
        $apiErrMsg  = $curlErr;

    } elseif ($data && ($data['status'] ?? '') === 'ok') {
        $articles = $data['articles'] ?? [];
        $articles = array_values(array_filter($articles, function ($a) {
            return !empty($a['title']) && $a['title'] !== '[Removed]';
        }));

        if (ENABLE_CACHE && $cacheFile) {
            file_put_contents($cacheFile, json_encode(['status' => 'ok', 'articles' => $articles]));
        }

    } else {
        $apiErrCode = $data['code']    ?? ('http_' . $httpCode);
        $apiErrMsg  = $data['message'] ?? 'Unknown API error';
        error_log('[NewsHub] API error ' . $httpCode . ' – ' . $apiErrCode . ': ' . $apiErrMsg);
        $apiError = true;
    }

} else {
    $apiError   = true;
    $apiErrCode = 'noKey';
    $apiErrMsg  = 'No API key configured in php/config.php';
    $source     = 'demo';
    error_log('[NewsHub] No API key set. Serving demo data.');
}

if (!$apiError && empty($articles) && NEWS_COUNTRY === 'in' && !$query) {
    $retryTopic = $category ?: 'India';
    $retryUrl   = NEWSAPI_BASE . 'everything?' . http_build_query([
        'q'        => $retryTopic,
        'language' => NEWS_LANGUAGE,
        'pageSize' => PAGE_SIZE,
        'sortBy'   => 'publishedAt',
        'apiKey'   => NEWSAPI_KEY,
    ]);
    error_log('[NewsHub] country=in returned 0 articles, retrying via /everything?q=' . $retryTopic);
    [$httpCode2, $data2, $curlErr2] = curlGet($retryUrl);
    if (!$curlErr2 && $data2 && ($data2['status'] ?? '') === 'ok') {
        $articles = array_values(array_filter($data2['articles'] ?? [], function ($a) {
            return !empty($a['title']) && $a['title'] !== '[Removed]';
        }));
        if (!empty($articles) && ENABLE_CACHE && $cacheFile) {
            file_put_contents($cacheFile, json_encode(['status' => 'ok', 'articles' => $articles]));
        }
    }
}

if ($apiError || empty($articles)) {
    $articles = getDemoArticles($category ?: ($query ?: 'general'));
    $source   = 'demo';
}

$response = [
    'status'   => 'ok',
    'source'   => $source,
    'articles' => array_values($articles),
];

if ($apiError && $apiErrCode) {
    $response['api_error'] = ['code' => $apiErrCode, 'message' => $apiErrMsg];
}

echo json_encode($response, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);


function getDemoArticles(string $tag): array
{
    $images = [
        'https://picsum.photos/seed/tech1/600/400',
        'https://picsum.photos/seed/sport1/600/400',
        'https://picsum.photos/seed/biz1/600/400',
        'https://picsum.photos/seed/ent1/600/400',
        'https://picsum.photos/seed/health1/600/400',
        'https://picsum.photos/seed/sci1/600/400',
        'https://picsum.photos/seed/news7/600/400',
        'https://picsum.photos/seed/news8/600/400',
        'https://picsum.photos/seed/news9/600/400',
        'https://picsum.photos/seed/news10/600/400',
        'https://picsum.photos/seed/news11/600/400',
        'https://picsum.photos/seed/news12/600/400',
    ];

    $pool = [
        [
            'title'       => 'AI Breakthrough: New Model Surpasses Human Performance on Reasoning Tasks',
            'description' => 'Researchers unveiled a model capable of solving complex multi-step reasoning problems with remarkable accuracy, setting a new benchmark.',
            'category'    => 'technology', 'source' => 'TechCrunch', 'url' => 'https://techcrunch.com',
        ],
        [
            'title'       => 'Global Stock Markets Rally After Central Bank Policy Announcement',
            'description' => 'Major indices climbed sharply as investors welcomed the central bank\'s decision to hold interest rates steady.',
            'category'    => 'business', 'source' => 'Financial Times', 'url' => 'https://ft.com',
        ],
        [
            'title'       => 'Champions League Semi-Finals: Dramatic Comeback Stuns Football World',
            'description' => 'The underdogs overturned a two-goal deficit to book their place in the final, sending fans into ecstasy.',
            'category'    => 'sports', 'source' => 'ESPN', 'url' => 'https://espn.com',
        ],
        [
            'title'       => 'New Study Links Mediterranean Diet to Improved Cognitive Health',
            'description' => 'A decade-long study of 12,000 participants found significantly lower rates of cognitive decline among adherents.',
            'category'    => 'health', 'source' => 'WebMD', 'url' => 'https://webmd.com',
        ],
        [
            'title'       => 'James Webb Telescope Reveals Stunning New Images of Distant Galaxies',
            'description' => 'NASA released images revealing previously unseen structures in galaxies billions of light-years away.',
            'category'    => 'science', 'source' => 'NASA', 'url' => 'https://nasa.gov',
        ],
        [
            'title'       => 'Award Season Preview: Blockbuster Films Dominate Oscar Nominations',
            'description' => 'The Academy unveiled its nominee list, with record-breaking films competing across multiple categories.',
            'category'    => 'entertainment', 'source' => 'Variety', 'url' => 'https://variety.com',
        ],
        [
            'title'       => 'Cybersecurity Alert: Critical Zero-Day Vulnerability Found in Widely-Used Library',
            'description' => 'Security researchers disclosed a vulnerability affecting millions of applications; patches are being fast-tracked.',
            'category'    => 'technology', 'source' => 'Wired', 'url' => 'https://wired.com',
        ],
        [
            'title'       => 'Electric Vehicle Sales Hit Record High as Battery Costs Plummet',
            'description' => 'Global EV sales surged 38% year-over-year, buoyed by falling battery prices and expanding charging infrastructure.',
            'category'    => 'business', 'source' => 'Reuters', 'url' => 'https://reuters.com',
        ],
        [
            'title'       => 'Olympic Contender Breaks World Record in Track and Field',
            'description' => 'A rising star shattered the long-standing world record, drawing worldwide attention ahead of the Olympics.',
            'category'    => 'sports', 'source' => 'BBC Sport', 'url' => 'https://bbc.co.uk/sport',
        ],
        [
            'title'       => 'Breakthrough Cancer Vaccine Shows 90% Effectiveness in Phase 3 Trial',
            'description' => 'A personalized mRNA-based cancer vaccine demonstrated unprecedented results, raising hopes for a new era in oncology.',
            'category'    => 'health', 'source' => 'MedPage Today', 'url' => 'https://medpagetoday.com',
        ],
        [
            'title'       => 'Scientists Discover 30 New Deep-Sea Species in the Pacific Trench',
            'description' => 'Marine biologists catalogued previously unknown creatures including a bioluminescent jellyfish with unusual feeding behavior.',
            'category'    => 'science', 'source' => 'Nature', 'url' => 'https://nature.com',
        ],
        [
            'title'       => 'Streaming Wars Intensify as Major Platform Announces $2B Content Fund',
            'description' => 'A leading streaming service pledged $2 billion toward exclusive originals, escalating competition in the space.',
            'category'    => 'entertainment', 'source' => 'Hollywood Reporter', 'url' => 'https://hollywoodreporter.com',
        ],
    ];

    $validCategory = in_array($tag, VALID_CATEGORIES, true) ? $tag : null;
    if ($validCategory) {
        usort($pool, function ($a, $b) use ($validCategory) {
            return ($b['category'] === $validCategory) - ($a['category'] === $validCategory);
        });
    } else {
        shuffle($pool);
    }

    $now    = time();
    $result = [];
    foreach ($pool as $i => $item) {
        $result[] = [
            'title'       => $item['title'],
            'description' => $item['description'],
            'url'         => $item['url'],
            'urlToImage'  => $images[$i % count($images)],
            'publishedAt' => date('c', $now - ($i * 3600)),
            'source'      => ['name' => $item['source']],
            'content'     => $item['description'],
            'category'    => $item['category'],
        ];
    }
    return $result;
}
