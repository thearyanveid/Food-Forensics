<?php
// Put your NEW API key here
$apiKey = "AIzaSyBDki9C-H77Qr5iL1paeKLrqI5oswk5lPM"; 

$url = "https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash:generateContent?key=" . $apiKey;
$payload = json_encode([
    "contents" => [["parts" => [["text" => "Reply with exactly one word: 'Success'."]]]]
]);

$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); 

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "<h3>HTTP Status Code: " . $httpCode . "</h3>";
echo "<pre>Raw Response:\n" . print_r(json_decode($response, true), true) . "</pre>";
?>