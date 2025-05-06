<?php

/**
 * Database handler class for RAISE 2025 Chatbot
 */
class Database
{
    private static $instance = null;
    private $conn = null;

    // SQLite database file path
    private $dbFile = null;

    /**
     * Private constructor to prevent direct instantiation
     */
    private function __construct()
    {
        // Set up SQLite database file path
        $this->dbFile = __DIR__ . '/data/chatbot.db';

        // Ensure the data directory exists
        if (!is_dir(dirname($this->dbFile))) {
            if (!mkdir(dirname($this->dbFile), 0755, true)) {
                error_log("Failed to create data directory");
                $this->conn = null;
                return;
            }
        }

        try {
            // Try creating SQLite connection
            $this->conn = new PDO(
                "sqlite:" . $this->dbFile,
                null,
                null,
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false,
                ]
            );

            // Create tables if they don't exist
            $this->initTables();
        } catch (PDOException $e) {
            // Log error but don't stop script execution
            error_log("Database connection error: " . $e->getMessage());

            // Fall back to file-based logging if database connection fails
            $this->conn = null;
        }
    }

    /**
     * Get singleton instance
     */
    public static function getInstance(): Database
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Initialize database tables
     */
    private function initTables(): void
    {
        if ($this->conn === null) return;

        // Chat interactions table
        $chatLogsTableSql = "
        CREATE TABLE IF NOT EXISTS chat_logs (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            user_message TEXT NOT NULL,
            ai_response TEXT NOT NULL,
            language TEXT DEFAULT 'en',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            input_tokens INTEGER DEFAULT NULL,
            output_tokens INTEGER DEFAULT NULL,
            total_tokens INTEGER DEFAULT NULL,
            model TEXT DEFAULT NULL,
            ip_address TEXT DEFAULT NULL,
            user_agent TEXT DEFAULT NULL
        );
        ";

        try {
            $this->conn->exec($chatLogsTableSql);
        } catch (PDOException $e) {
            error_log("Error creating tables: " . $e->getMessage());
        }
    }

    /**
     * Log chat interaction to database or file
     */
    public function logChatInteraction(string $userMessage, string $aiResponse, string $language, array $metrics = []): void
    {
        // If no database connection, log to file instead
        if ($this->conn === null) {
            $this->logToFile($userMessage, $aiResponse, $language, $metrics);
            return;
        }

        try {
            $sql = "INSERT INTO chat_logs 
                   (user_message, ai_response, language, input_tokens, output_tokens, total_tokens, model, ip_address, user_agent) 
                   VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";

            $stmt = $this->conn->prepare($sql);

            $stmt->execute([
                $userMessage,
                $aiResponse,
                $language,
                $metrics['input_tokens'] ?? null,
                $metrics['output_tokens'] ?? null,
                $metrics['total_tokens'] ?? null,
                $metrics['model'] ?? null,
                $_SERVER['REMOTE_ADDR'] ?? null,
                $_SERVER['HTTP_USER_AGENT'] ?? null
            ]);
        } catch (PDOException $e) {
            error_log("Error logging chat interaction: " . $e->getMessage());
            $this->logToFile($userMessage, $aiResponse, $language, $metrics);
        }
    }

    /**
     * Fallback method to log to file if database is unavailable
     */
    private function logToFile(string $userMessage, string $aiResponse, string $language, array $metrics = []): void
    {
        $logDir = __DIR__ . '/logs';
        if (!is_dir($logDir)) {
            mkdir($logDir, 0755, true);
        }

        $logFile = $logDir . '/chat_logs.txt';

        $logEntry = [
            'timestamp' => date('Y-m-d H:i:s'),
            'user_message' => $userMessage,
            'ai_response' => $aiResponse,
            'language' => $language,
            'metrics' => $metrics,
            'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown'
        ];

        file_put_contents(
            $logFile,
            json_encode($logEntry, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n---\n",
            FILE_APPEND
        );
    }
}
