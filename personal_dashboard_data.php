<?php
// Errors hidden for production so the JSON response stays clean
error_reporting(0); 
ini_set('display_errors', 0); 

header('Content-Type: application/json');

include __DIR__ . "/db.php"; 

/* 1. LOCAL DATA ACQUISITION */
$productQ = $conn->query("SELECT * FROM products ORDER BY id DESC LIMIT 1");
$product = $productQ->fetch_assoc();
$userQ = $conn->query("SELECT * FROM user_profiles ORDER BY id DESC LIMIT 1");
$user = $userQ->fetch_assoc();

if(!$product || !$user){
    echo json_encode(["error" => "Database empty. Scan a barcode or enter ingredients, and ensure user profile exists."]);
    exit;
}

$barcode = $product['barcode'] ?? '';
$localIngredients = $product['ingredients'] ?? '';

if (empty($barcode) && empty($localIngredients)) {
    echo json_encode(["error" => "No barcode or ingredients found in the latest database entry."]);
    exit;
}

/* 2. DETERMINE DATA SOURCE (OpenFoodFacts vs. Local DB) */
$productName = 'Custom Analysis';
$ingredientsText = '';
$imageUrl = $product['image_path'] ?? ''; 

if (!empty($barcode)) {
    $offUrl = "https://world.openfoodfacts.org/api/v0/product/" . urlencode($barcode) . ".json";
    $offResponse = @file_get_contents($offUrl);
    $offData = json_decode($offResponse, true);

    if ($offData && isset($offData['status']) && $offData['status'] === 1) {
        $offProduct = $offData['product'];
        $productName = $offProduct['product_name'] ?? 'Barcode Product';
        $ingredientsText = $offProduct['ingredients_text'] ?? '';
        $imageUrl = $offProduct['image_url'] ?? $imageUrl;
    }
}

if (empty($ingredientsText) && !empty($localIngredients)) {
    $ingredientsText = $localIngredients;
    
    if (!empty($product['image_path'])) {
        $filename = pathinfo($product['image_path'], PATHINFO_FILENAME);
        $filename = preg_replace('/^[0-9]+[_\-\s]*/', '', $filename);
        $productName = ucwords(str_replace(['_', '-'], ' ', $filename));
    } else {
        $productName = "Custom Ingredient Analysis"; 
    }
}

if (empty($ingredientsText)) {
    echo json_encode(["error" => "Could not find ingredients from barcode, and no manual ingredients were provided."]);
    exit;
}

/* 3. GEMINI AI CALL (Contextual Analysis) */
$apiKey ="AIzaSyDH4sWJKqP2X9hcOiXh3QsXeQt6wdLezDI"; 

// Switched strictly to gemini-1.5-flash to avoid 2.5 rate limit bugs
$url = "https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash:generateContent?key=" . trim($apiKey);

// FIX: Force clean UTF-8 to prevent json_encode from silently failing on bad DB characters
$cleanProductName = mb_convert_encoding($productName, 'UTF-8', 'UTF-8');
$cleanIngredients = mb_convert_encoding($ingredientsText, 'UTF-8', 'UTF-8');
$cleanDiseases = mb_convert_encoding($user['diseases'] ?? 'None', 'UTF-8', 'UTF-8');

$prompt = "Analyze the following food product: '$cleanProductName' with ingredients: [" . $cleanIngredients . "]. 
Consider the user's specific health profile constraints: [" . $cleanDiseases . "]. 
Return ONLY a valid JSON object.
{
  \"risk_score\": \"Integer between 1 and 10\", 
  \"risk_level\": \"String (e.g., Low, Moderate, High, Critical)\",
  \"nutrition\": {
     \"calories\": \"Integer between 0 and 100 representing percentage of daily value\", 
     \"sugar\": \"Integer between 0 and 100\", 
     \"sodium\": \"Integer between 0 and 100\", 
     \"fat\": \"Integer between 0 and 100\"
  },
  \"ingredients_list\": [
    {\"name\": \"String\", \"category\": \"String\", \"risk\": \"String\"}
  ],
  \"warnings\": [\"String\"],
  \"explanation\": \"String\"
}";

$payload = json_encode([
    "contents" => [["parts" => [["text" => $prompt]]]],
    "generationConfig" => [
        "temperature" => 0.1, 
        "response_mime_type" => "application/json" 
    ]
]);

// FIX: Check if JSON encoding failed due to database character issues
if ($payload === false) {
    echo json_encode(["error" => "System Error: The database ingredients contain invalid characters that cannot be processed. (" . json_last_error_msg() . ")"]);
    exit;
}

$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); 
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curlError = curl_error($ch);
curl_close($ch);

if ($curlError) {
    echo json_encode(["error" => "Network Error connecting to AI: " . $curlError]);
    exit;
}

if ($httpCode === 503 || $httpCode === 429 || stripos($response, 'high demand') !== false) {
    echo json_encode(["error" => "The AI servers are currently experiencing high traffic. Please wait 10 seconds and refresh."]);
    exit;
}

/* 4. ROBUST PARSING */
$data = json_decode($response, true);

function getAiText($arr) {
    if (!is_array($arr)) return null;
    if (isset($arr['text'])) return $arr['text'];
    foreach ($arr as $v) {
        $res = getAiText($v);
        if ($res) return $res;
    }
    return null;
}

$rawText = getAiText($data);

if (!$rawText) {
    $errorMessage = $data['error']['message'] ?? 'Unknown API Error.';
    echo json_encode(["error" => "API Failure: " . $errorMessage]);
    exit;
}

// FIX: Strip Markdown Formatting if the AI accidentally wrapped the JSON
$rawText = preg_replace('/^```json\s*/i', '', $rawText);
$rawText = preg_replace('/\s*```$/', '', $rawText);
$rawText = trim($rawText);

$ai = json_decode($rawText, true);

// FIX: Catch JSON decoding errors from the AI response
if (json_last_error() !== JSON_ERROR_NONE) {
    echo json_encode(["error" => "AI Data Error: The neural network returned improperly formatted data. (" . json_last_error_msg() . ")"]);
    exit;
}

/* 5. DATA NORMALIZATION */
$ai['risk_score'] = max(1, min(10, (int)($ai['risk_score'] ?? 0)));
$riskLevel = $ai['risk_level'] ?? "N/A";

foreach(['calories', 'sugar', 'sodium', 'fat'] as $key) {
    $val = $ai['nutrition'][$key] ?? 0;
    $val = (int) preg_replace('/[^0-9]/', '', (string)$val);
    $ai['nutrition'][$key] = max(0, min(100, $val));
}

/* 6. SAVE TO DATABASE (USING PREPARED STATEMENTS) */
$dbPayload = json_encode([
    "product_name" => $productName,
    "risk_score" => $ai['risk_score'],
    "risk_level" => $riskLevel,
    "nutrition" => $ai['nutrition'],
    "ingredients_list" => $ai['ingredients_list'] ?? [],
    "warnings" => $ai['warnings'] ?? [],
    "explanation" => $ai['explanation'] ?? ""
], JSON_UNESCAPED_UNICODE);

$productId = (int)$product['id'];

$stmt = $conn->prepare("UPDATE products SET ai_analysis = ? WHERE id = ?");
if ($stmt) {
    $stmt->bind_param("si", $dbPayload, $productId);
    $stmt->execute();
    $stmt->close();
}

/* 7. FINAL OUTPUT TO DASHBOARD */
echo json_encode([
    "product_name" => $productName,
    "image" => $imageUrl, 
    "risk_score" => $ai['risk_score'],
    "risk_level" => $riskLevel,
    "nutrition" => $ai['nutrition'],
    "ingredients_list" => $ai['ingredients_list'] ?? [],
    "warnings" => $ai['warnings'] ?? [],
    "explanation" => $ai['explanation'] ?? ""
]);
?>