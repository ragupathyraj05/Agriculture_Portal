<?php
require_once 'includes/config.php';

echo "Testing OpenAI API...\n";
echo "API Key: " . substr(OPENAI_API_KEY, 0, 5) . "...\n";

$apiKey = OPENAI_API_KEY;
$apiUrl = 'https://api.openai.com/v1/chat/completions';

$data = [
    'model' => 'gpt-4o-mini',  // Updated model
    'messages' => [
        ['role' => 'user', 'content' => 'Hello']
    ],
    'temperature' => 0.7
];

$ch = curl_init($apiUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Authorization: Bearer ' . $apiKey
]);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

$response = curl_exec($ch);

if ($response === false) {
    echo "cURL Error: " . curl_error($ch) . "\n";
} else {
    echo "Response: " . $response . "\n";
}

curl_close($ch);
?>