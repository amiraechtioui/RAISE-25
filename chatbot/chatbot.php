<?php
// Clear all output buffers
while (ob_get_level()) {
    ob_end_clean();
}

// Include config file to get streaming preferences
require_once 'config.php';
require_once 'database.php';

/**
 * Estimate tokens in a string more accurately
 * Based on GPT tokenization principles
 */
function estimate_tokens($string)
{
    // Count words, numbers and punctuation
    $word_count = preg_match_all('/\b\w+\b|\d+|[^\w\s]/', $string, $matches);

    // Add estimated token count for special characters and spaces
    $special_chars_count = strlen(preg_replace('/[a-zA-Z0-9\s]/', '', $string));

    // Count spaces
    $space_count = substr_count($string, ' ');

    // GPT tokenizers tend to tokenize at a rate of roughly 0.75 tokens per word
    // for English text, plus additional tokens for spaces and special characters
    $estimated_tokens = ($word_count * 0.75) + ($special_chars_count * 0.5) + ($space_count * 0.25);

    // Ensure minimum of 1 token for non-empty strings
    return $string ? max(1, intval($estimated_tokens)) : 0;
}

// Disable output buffering and compression
ini_set('output_buffering', 'off');
ini_set('implicit_flush', true);
ini_set('zlib.output_compression', 'off'); // Explicitly disable zlib compression
ob_implicit_flush(true);

// Error logging setup
ini_set('error_log', $error_log_file);
error_reporting(E_ALL);
ini_set('display_errors', 'off');

// Set headers for Server-Sent Events (SSE)
header('Content-Type: text/event-stream');
header('Cache-Control: no-cache, no-store, must-revalidate');
header('Pragma: no-cache');
header('Expires: 0');
header('Connection: keep-alive');
header('X-Accel-Buffering: no'); // Disable nginx buffering
header('Content-Encoding: none'); // Prevent compression interference

// Send initial padding to bypass proxy buffering (helps with some environments)
echo ':' . str_repeat(' ', 2048) . "\n\n";
flush();

// Send an initial heartbeat ping to keep the connection alive
echo "event: ping\ndata: " . json_encode(['time' => time()]) . "\n\n";
flush();

// Check if the request has a message parameter
if (!isset($_GET['message'])) {
    echo "data: " . json_encode(['error' => 'No message provided']) . "\n\n";
    echo "event: done\ndata: {}\n\n";
    flush();
    exit;
}

// Get language parameter, default to English
$lang = isset($_GET['lang']) ? $_GET['lang'] : $default_language;
$isRTL = ($lang === 'ar');
$direction = $isRTL ? 'rtl' : 'ltr';

// Get database instance
$db = Database::getInstance();

// Ensure logs directory exists
$logsDir = __DIR__ . '/logs';
if (!is_dir($logsDir)) {
    mkdir($logsDir, 0755, true);
}

// System prompt with RAISE 2025 information
$system_prompt = "You are the official AI assistant for RAISE 2025, a summer school on Robotics and Artificial Intelligence in Systems Engineering. 
Here are important details about the event:
- Event dates: July 14-16, 2025
- Location: Centre de recherche Sfax, Tunisia
- Co-organized by: CES Lab, LASEM, SMART, Centre de Recherche, REGIM Lab, University of Sfax
- Key topics: ROS (Robot Operating System), Artificial Intelligence, Robotics Competition
- Sponsors: SCALEXI, MUST
- Organizers: 
  * General Chair: Omar Cheikhrouhou (ENET'Com, Sfax, Tunisia)
  * Technical Program Lead: Anis Koubaa (Alfaisal University/ScaleX Innovation)
  * Scientific Committee: Hatem Bentaher (ISGIS, University of Sfax), Houssam Chouikhi (ISSIG, University of Gabès), Nidhal Ayadi (University of Sfax), Mohamed Bahloul (Alfaisal University), Yassine Bouteraa (Prince Sattam Bin Abdulaziz University)
- Registration Fees:
  * Students: 300 DT
  * Academics: 400 DT
  * Industry: 500 DT
  * Early offer: 100 DT for 1st cycle students (cash payment before July 15, 2025)
- Competition Tracks:
  * Track 1: Autonomous Robot - Follow a 10-meter black path autonomously
  * Track 2: Industrial Robot - Carry a 5cm³ box and follow a black path for 10 meters
  * Track 3: Agriculture Robot - Read QR codes and perform specified actions
- Program Highlights:
  * Day 1: ROS workshops and hands-on implementation
  * Day 2: Generative AI, Computer Vision, and Large Language Models
  * Day 3: Competition and Industrial sessions
- Rewards for Competition:
  * Monetary prizes for each track winner
  * Potential industrial partnerships
  * Recognition certificates
- Contact: raise2025@enetcom.usf.tn

Never add backticks inlcuding ```html or ``` to the response.
Respond in the same language as the question. If Arabic, add rtl in each HTML tag and add style='font-family: DinNext, Arial, sans-serif;' to ensure proper font display.
Your role is to provide helpful, accurate, and concise information about the RAISE 2025 summer school. Be friendly and professional.
Output format must be in HTML with emojis to make it engaging, and links where appropriate. Use <h5 dir='$direction'> for headings and <p dir='$direction'> for paragraphs, <ul dir='$direction'> for lists, <a dir='$direction'> for links, and <b dir='$direction'> to highlight important keywords in the answer in the same language of the question.
If asked about topics outside of the event scope, politely redirect to RAISE 2025-related information.

Current language: " . ($lang === 'ar' ? 'Arabic (RTL)' : 'English/French (LTR)');

$message = $_GET['message'];

// Set a time limit for the script execution
set_time_limit(120); // 2 minutes should be plenty for most responses

try {
    // OpenAI API endpoint
    $url = 'https://api.openai.com/v1/chat/completions';

    // Prepare the request data
    $request_data = [
        'model' => $model,
        'messages' => [
            [
                'role' => 'system',
                'content' => $system_prompt
            ],
            [
                'role' => 'user',
                'content' => $message
            ]
        ],
        'temperature' => $temperature,
        'max_tokens' => $max_tokens,
        'stream' => $enable_streaming
    ];

    // Initialize cURL session
    $ch = curl_init($url);

    if ($enable_streaming) {
        // STREAMING MODE
        // Set cURL options
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($request_data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $api_key
        ]);

        // Variables to collect the full response
        $full_content = '';
        $input_tokens = null;
        $output_tokens = null;
        $total_tokens = null;

        // Set up streaming callback
        curl_setopt($ch, CURLOPT_WRITEFUNCTION, function ($curl, $data) use (&$full_content) {
            $lines = explode("\n", $data);
            foreach ($lines as $line) {
                if (strlen(trim($line)) > 0) {
                    if (str_starts_with($line, 'data: ')) {
                        $jsonData = substr($line, 6); // Remove 'data: ' prefix
                        if ($jsonData === '[DONE]') {
                            echo "event: done\ndata: {}\n\n";
                        } else {
                            echo $line . "\n\n";

                            // Try to parse JSON data and accumulate content for database logging
                            try {
                                $parsed = json_decode($jsonData, true);
                                if (isset($parsed['choices'][0]['delta']['content'])) {
                                    $full_content .= $parsed['choices'][0]['delta']['content'];
                                }
                            } catch (Exception $e) {
                                // Silently ignore parsing errors
                            }
                        }
                        flush();
                    }
                }
            }
            return strlen($data);
        });

        // Execute the cURL request
        $response = curl_exec($ch);

        // Check for cURL errors
        if (curl_errno($ch)) {
            echo "data: " . json_encode(['error' => curl_error($ch)]) . "\n\n";
            flush();
        } else {
            // Estimate token counts using our estimation function
            $estimated_input_tokens = estimate_tokens($system_prompt) + estimate_tokens($message);
            $estimated_output_tokens = estimate_tokens($full_content);
            $estimated_total_tokens = $estimated_input_tokens + $estimated_output_tokens;

            // Log the complete interaction to the database
            $apiMetrics = [
                'model' => $model,
                'temperature' => $temperature,
                'max_output_tokens' => $max_tokens,
                'input_tokens' => $estimated_input_tokens,
                'output_tokens' => $estimated_output_tokens,
                'total_tokens' => $estimated_total_tokens
            ];

            // Log only if we have content
            if (!empty($full_content)) {
                $db->logChatInteraction($message, $full_content, $lang, $apiMetrics);
            }
        }
    } else {
        // NON-STREAMING MODE: Standard request
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);  // Return response as string
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($request_data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $api_key
        ]);
        curl_setopt($ch, CURLOPT_TIMEOUT, 60);

        // Execute the cURL request
        $response = curl_exec($ch);

        // Check for cURL errors
        if (!$response) {
            $curl_error = curl_error($ch);
            error_log("cURL Error: " . $curl_error);
            echo "data: " . json_encode(['error' => $curl_error]) . "\n\n";
            echo "event: done\ndata: {}\n\n";
            flush();
        } else {
            // Process and return the complete response
            $response_data = json_decode($response, true);

            if (isset($response_data['error'])) {
                error_log("API Error: " . json_encode($response_data['error']));
                echo "data: " . json_encode(['error' => $response_data['error']['message']]) . "\n\n";
            } else if (isset($response_data['choices'][0]['message']['content'])) {
                // Format the complete response as a single data chunk for SSE
                $content = $response_data['choices'][0]['message']['content'];

                // Create a fake delta structure to match streaming format
                $fake_delta = [
                    'choices' => [
                        [
                            'delta' => [
                                'content' => $content
                            ],
                            'finish_reason' => 'stop'
                        ]
                    ]
                ];

                echo "data: " . json_encode($fake_delta) . "\n\n";

                // Extract API metrics
                $apiMetrics = [
                    'created_at' => $response_data['created'] ?? $response_data['created_at'] ?? null,
                    'model' => $response_data['model'] ?? null,
                    'status' => $response_data['object'] ?? $response_data['status'] ?? null,
                    'temperature' => $request_data['temperature'] ?? $response_data['temperature'] ?? null,
                    'max_output_tokens' => $request_data['max_tokens'] ?? $response_data['max_output_tokens'] ?? null,
                    'instructions' => $response_data['instructions'] ?? null,
                ];

                // Extract token usage if available - handle both standard OpenAI format and custom format
                if (isset($response_data['usage'])) {
                    // Standard OpenAI format
                    $apiMetrics['input_tokens'] = $response_data['usage']['prompt_tokens'] ?? null;
                    $apiMetrics['output_tokens'] = $response_data['usage']['completion_tokens'] ?? null;
                    $apiMetrics['total_tokens'] = $response_data['usage']['total_tokens'] ?? null;
                }

                // Log the chat interaction with metrics to the database
                $db->logChatInteraction($message, $content, $lang, $apiMetrics);
            }

            echo "event: done\ndata: {}\n\n";
            flush();
        }
    }

    curl_close($ch);
} catch (Exception $e) {
    $error_message = 'Error calling OpenAI API: ' . $e->getMessage();
    error_log($error_message);
    echo "data: " . json_encode(['error' => $error_message]) . "\n\n";
    echo "event: done\ndata: {}\n\n";
    flush();
}
