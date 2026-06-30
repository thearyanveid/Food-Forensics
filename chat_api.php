<?php
header('Content-Type: application/json');
error_reporting(0);

include __DIR__ . "/db.php"; 

$input = json_decode(file_get_contents('php://input'), true);
$message = $input['message'] ?? '';

if (empty($message)) {
    echo json_encode(['reply' => 'Error: Blank query received.']);
    exit;
}

// 1. Fetch the latest Context
$productQ = $conn->query("SELECT * FROM products ORDER BY id DESC LIMIT 1");
$product = $productQ->fetch_assoc();
$userQ = $conn->query("SELECT * FROM user_profiles ORDER BY id DESC LIMIT 1");
$user = $userQ->fetch_assoc();

$productContext = $product ? "Current Product: " . $product['product_name'] . " (Ingredients: " . $product['ingredients'] . ")" : "No product scanned.";
$userContext = $user ? "User Health Profile: Has the following conditions/constraints: " . $user['diseases'] : "No known health conditions.";

// 2. API Setup
$apiKey = "AIzaSyDH4sWJKqP2X9hcOiXh3QsXeQt6wdLezDI"; 

// FIX: Switched to gemini-1.5-flash
$url = "https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash:generateContent?key=" . trim($apiKey);

// 3. Dynamic System Prompt
$systemPrompt = "You are FoodForensics, an advanced, highly capable AI health and nutrition assistant. 
Here is the current context for the user you are speaking to:
- $userContext
- $productContext

Instructions:
1. If the user asks about the product or their health, tailor your answer strictly to their health profile and the scanned ingredients.
2. If the user asks a general question unrelated to food, you are fully authorized to answer it accurately and comprehensively.
3. Keep formatting clean and readable.

User Query: " . $message;

$payload = json_encode([
    "contents" => [["parts" => [["text" => $systemPrompt]]]],
    "generationConfig" => [
        "temperature" => 0.6 
    ]
]);

$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); 
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

// FIX: Graceful High Demand handling in Chat
if ($httpCode === 503 || $httpCode === 429 || stripos($response, 'high demand') !== false) {
    echo json_encode(['reply' => '<i>[System: The neural network is experiencing high traffic. Please try asking again in a few seconds.]</i>']);
    exit;
}

$data = json_decode($response, true);

// Extract AI response safely
$reply = $data['candidates']['content']['parts']['text'] ?? null;

if (!$reply) {
    $errorMsg = $data['error']['message'] ?? 'Check API connectivity.';
    echo json_encode(['reply' => "System Error: " . $errorMsg]);
    exit;
}

// Return response formatting new lines for HTML
echo json_encode(['reply' => nl2br(htmlspecialchars($reply))]);
?>