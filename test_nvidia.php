<?php

// Quick standalone test for NVIDIA NIM API
// Run with: php test_nvidia.php

$apiKey = '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';

$payload = json_encode([
    'model'       => 'meta/llama-3.3-70b-instruct',
    'messages'    => [
        ['role' => 'user', 'content' => 'Reply with only this exact JSON, no extra text: {"status":"ok","value":42}']
    ],
    'max_tokens'  => 100,
    'temperature' => 0,
    'stream'      => false,
]);

$ch = curl_init('https://integrate.api.nvidia.com/v1/chat/completions');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
curl_setopt($ch, CURLOPT_TIMEOUT, 60);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Accept: application/json',
    "Authorization: Bearer $apiKey",
]);

echo "Sending request to NVIDIA NIM...\n";
$start    = microtime(true);
$response = curl_exec($ch);
$elapsed  = round(microtime(true) - $start, 2);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curlErr  = curl_error($ch);
curl_close($ch);

echo "HTTP Status : $httpCode\n";
echo "Time taken  : {$elapsed}s\n";

if ($curlErr) {
    echo "cURL Error  : $curlErr\n";
    exit(1);
}

$data = json_decode($response, true);
echo "Raw response:\n";
echo json_encode($data, JSON_PRETTY_PRINT) . "\n";

$content = $data['choices'][0]['message']['content'] ?? null;
echo "\nModel reply : $content\n";
