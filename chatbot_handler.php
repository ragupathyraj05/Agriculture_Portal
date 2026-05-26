<?php
require_once 'includes/config.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $input = json_decode(file_get_contents('php://input'), true);
    $userMessage = isset($input['message']) ? trim($input['message']) : '';
    $type = isset($input['type']) ? $input['type'] : 'chat';

    if (empty($userMessage) && $type !== 'quote') {
        echo json_encode(['error' => 'Empty message']);
        exit;
    }

    $apiKey = GEMINI_API_KEY;

    // If API key missing
    if (empty($apiKey)) {
        echo json_encode([
            'reply' => 'AI service is currently unavailable.'
        ]);
        exit;
    }

    // Use lower quota model
    $apiUrl = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash:generateContent?key=' . $apiKey;

    $systemInstruction = "You are a helpful agricultural assistant for Tamil Nadu farmers. Give short, practical farming advice.";

    if ($type === 'quote') {
        $userMessage = "Give ONE short motivational farming quote. Format: \"Quote\" - Author";
    } else {
        $userMessage = $systemInstruction . "\n\nUser: " . $userMessage;
    }

    $data = [
        'contents' => [
            [
                'parts' => [
                    ['text' => $userMessage]
                ]
            ]
        ]
    ];

    $ch = curl_init($apiUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json'
    ]);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

    $response = curl_exec($ch);

    if ($response === false) {
        echo json_encode([
            'reply' => 'AI service temporarily unavailable.'
        ]);
    } else {

        $responseData = json_decode($response, true);

        // Successful reply
        if (isset($responseData['candidates'][0]['content']['parts'][0]['text'])) {

            echo json_encode([
                'reply' => $responseData['candidates'][0]['content']['parts'][0]['text']
            ]);

        } else {

            $errorMessage = isset($responseData['error']['message']) 
                ? $responseData['error']['message'] 
                : 'Unknown API Error';

            // Handle quota exceeded gracefully
            if (stripos($errorMessage, 'quota') !== false) {

                echo json_encode([
                    'reply' => 'AI quota limit reached. Please try again later.'
                ]);

            } else {

                echo json_encode([
                    'reply' => 'AI service is currently unavailable.'
                ]);
            }
        }
    }

    curl_close($ch);

} else {
    echo json_encode(['error' => 'Invalid request method']);
}
?>