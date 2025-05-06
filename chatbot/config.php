<?php
// Configuration for RAISE 2025 Chatbot

// Enable or disable streaming
$enable_streaming = true;

// Default language
$default_language = 'en';

// OpenAI API key - Replace with your actual API key in production
$api_key = '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';

// OpenAI model to use
$model = 'gpt-4o-mini';

// Temperature for generating responses (0.0 to 1.0)
$temperature = 0.3;

// Maximum number of tokens to generate
$max_tokens = 1000;

// Path to error log file
$error_log_file = __DIR__ . '/chatbot-error.log';

// Database settings
$db_type = 'sqlite'; // 'sqlite' or 'mysql'
$sqlite_db_file = __DIR__ . '/data/chatbot.db';
