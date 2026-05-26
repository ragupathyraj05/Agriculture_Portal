<?php
// Function to load .env file
function loadEnv($path)
{
    if (!file_exists($path)) {
        return;
    }

    $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0 || empty(trim($line))) {
            continue;
        }

        $parts = explode('=', $line, 2);
        if (count($parts) === 2) {
            $name = trim($parts[0]);
            $value = trim($parts[1]);

            if (!array_key_exists($name, $_SERVER) && !array_key_exists($name, $_ENV)) {
                putenv(sprintf('%s=%s', $name, $value));
                $_ENV[$name] = $value;
                $_SERVER[$name] = $value;
            }
        }
    }
}

// Load .env variables
loadEnv(__DIR__ . '/../.env');

// Database Configuration
define('DB_SERVER', $_ENV['DB_SERVER'] ?? '127.0.0.1');
define('DB_USERNAME', $_ENV['DB_USERNAME'] ?? 'root');
define('DB_PASSWORD', $_ENV['DB_PASSWORD'] ?? '');
define('DB_NAME', $_ENV['DB_NAME'] ?? 'agriculture_portal');

// API Keys
define('NEWS_API_KEY', $_ENV['NEWS_API_KEY'] ?? '');
define('OPENWEATHER_API_KEY', $_ENV['OPENWEATHER_API_KEY'] ?? '');
define('GEMINI_API_KEY', $_ENV['GEMINI_API_KEY'] ?? '');
define('OPENAI_API_KEY', $_ENV['OPENAI_API_KEY'] ?? '');

// Base URL (Update if hosted elsewhere)
define('BASE_URL', $_ENV['BASE_URL'] ?? 'http://localhost/Agriculture-Portal/');
?>
