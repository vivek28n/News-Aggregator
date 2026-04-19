<?php
/**
 * debug.php — NewsAPI Diagnostic Tool
 * Access: http://localhost/news-aggregator/php/debug.php
 * DELETE THIS FILE after diagnosis is complete.
 */
header('Content-Type: text/html; charset=utf-8');

require_once __DIR__ . '/config.php';

echo '<style>body{font-family:monospace;padding:20px;background:#111;color:#eee;}
.ok{color:#4ade80;}.err{color:#f87171;}.warn{color:#fbbf24;}.box{background:#1e1e2e;padding:12px;border-radius:8px;margin:10px 0;}</style>';
echo '<h2>🔍 NewsAPI Debug Report</h2>';

// ── 1. Key check ─────────────────────────────────────────────────────────────
echo '<div class="box">';
echo '<b>1. API Key:</b> ';
if (NEWSAPI_KEY === 'YOUR_NEWSAPI_KEY_HERE' || empty(NEWSAPI_KEY)) {
    echo '<span class="err">❌ Not set</span>';
} else {
    $masked = substr(NEWSAPI_KEY, 0, 4) . str_repeat('*', strlen(NEWSAPI_KEY) - 8) . substr(NEWSAPI_KEY, -4);
    echo '<span class="ok">✅ Set → ' . htmlspecialchars($masked) . '</span>';
}
echo '</div>';

// ── 2. cURL check ────────────────────────────────────────────────────────────
echo '<div class="box">';
echo '<b>2. cURL Extension:</b> ';
if (function_exists('curl_init')) {
    echo '<span class="ok">✅ Enabled</span>';
} else {
    echo '<span class="err">❌ DISABLED — Enable extension=curl in php.ini</span>';
}
echo '</div>';

// ── 3. allow_url_fopen check ─────────────────────────────────────────────────
echo '<div class="box">';
echo '<b>3. allow_url_fopen:</b> ';
echo ini_get('allow_url_fopen')
    ? '<span class="ok">✅ On</span>'
    : '<span class="warn">⚠️ Off (cURL will be used anyway)</span>';
echo '</div>';

// ── 4. Live API call ──────────────────────────────────────────────────────────
echo '<div class="box">';
echo '<b>4. Live API Test (top-headlines, country=us):</b><br><br>';

$url = NEWSAPI_BASE . 'top-headlines?' . http_build_query([
    'country'  => NEWS_COUNTRY,
    'pageSize' => 3,
    'apiKey'   => NEWSAPI_KEY,
]);

echo 'URL: <span style="color:#93c5fd">' . htmlspecialchars(str_replace(NEWSAPI_KEY, '***KEY***', $url)) . '</span><br><br>';

if (function_exists('curl_init')) {
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT        => 15,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_USERAGENT      => 'NewsHub-Debug/1.0',
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_VERBOSE        => false,
    ]);
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlErr  = curl_error($ch);
    curl_close($ch);

    echo 'HTTP Status: ';
    if ($httpCode === 200) {
        echo '<span class="ok">✅ 200 OK</span><br>';
    } elseif ($httpCode === 0) {
        echo '<span class="err">❌ 0 — No response. cURL error: ' . htmlspecialchars($curlErr) . '</span><br>';
    } else {
        echo '<span class="err">❌ ' . $httpCode . '</span><br>';
    }

    if ($curlErr) {
        echo 'cURL Error: <span class="err">' . htmlspecialchars($curlErr) . '</span><br>';
    }

    if ($response) {
        $data = json_decode($response, true);
        echo 'API Status: ';
        if (isset($data['status'])) {
            if ($data['status'] === 'ok') {
                $count = count($data['articles'] ?? []);
                echo '<span class="ok">✅ ok — ' . $count . ' articles returned</span><br>';
            } else {
                echo '<span class="err">❌ ' . htmlspecialchars($data['status']) . '</span><br>';
                echo 'Message: <span class="err">' . htmlspecialchars($data['message'] ?? 'none') . '</span><br>';
                echo 'Code: <span class="err">' . htmlspecialchars($data['code'] ?? 'none') . '</span><br>';
            }
        } else {
            echo '<span class="warn">⚠️ Unexpected response body</span><br>';
            echo '<pre>' . htmlspecialchars(substr($response, 0, 500)) . '</pre>';
        }
    }
} else {
    echo '<span class="err">❌ cURL not available — cannot test</span>';
}
echo '</div>';

// ── 5. Search endpoint test ───────────────────────────────────────────────────
echo '<div class="box">';
echo '<b>5. Search API Test (?q=technology):</b><br><br>';

$url2 = NEWSAPI_BASE . 'everything?' . http_build_query([
    'q'        => 'technology',
    'language' => NEWS_LANGUAGE,
    'pageSize' => 3,
    'sortBy'   => 'publishedAt',
    'apiKey'   => NEWSAPI_KEY,
]);

if (function_exists('curl_init')) {
    $ch2 = curl_init($url2);
    curl_setopt_array($ch2, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT        => 15,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_USERAGENT      => 'NewsHub-Debug/1.0',
    ]);
    $resp2    = curl_exec($ch2);
    $code2    = curl_getinfo($ch2, CURLINFO_HTTP_CODE);
    $curlErr2 = curl_error($ch2);
    curl_close($ch2);

    echo 'HTTP Status: ';
    if ($code2 === 200) {
        $d2 = json_decode($resp2, true);
        $cnt = count($d2['articles'] ?? []);
        echo '<span class="ok">✅ 200 OK — ' . $cnt . ' articles</span><br>';
    } else {
        echo '<span class="err">❌ ' . $code2 . '</span><br>';
        if ($curlErr2) echo 'cURL Error: ' . htmlspecialchars($curlErr2) . '<br>';
        if ($resp2) {
            $d2 = json_decode($resp2, true);
            echo 'Message: <span class="err">' . htmlspecialchars($d2['message'] ?? $resp2) . '</span><br>';
        }
    }
}
echo '</div>';

// ── 6. PHP error log tail ────────────────────────────────────────────────────
echo '<div class="box">';
echo '<b>6. PHP Error Log (last 10 lines):</b><br><br>';
$logFile = ini_get('error_log');
if ($logFile && file_exists($logFile)) {
    $lines = array_slice(file($logFile), -10);
    foreach ($lines as $line) {
        $class = strpos($line, 'NewsHub') !== false ? 'warn' : '';
        echo '<span class="' . $class . '">' . htmlspecialchars(rtrim($line)) . '</span><br>';
    }
} else {
    echo '<span class="warn">Log file not found or not configured: ' . htmlspecialchars($logFile ?: 'none') . '</span>';
}
echo '</div>';

echo '<hr><p style="color:#666">⚠️ Delete php/debug.php after you\'re done diagnosing.</p>';
