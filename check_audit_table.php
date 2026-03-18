<?php
// Load environment variables
$dotenv_path = __DIR__ . '/.env';
if (file_exists($dotenv_path)) {
    $lines = file($dotenv_path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos($line, '=') !== false && strpos($line, '#') !== 0) {
            list($key, $value) = explode('=', $line, 2);
            $key = trim($key);
            $value = trim($value);
            if (!empty($key)) {
                $_ENV[$key] = $value;
            }
        }
    }
}

// Connect to database
$driver = $_ENV['database.default.DBDriver'] ?? 'MySQLi';
$hostname = $_ENV['database.default.hostname'] ?? 'localhost';
$username = $_ENV['database.default.username'] ?? 'root';
$password = $_ENV['database.default.password'] ?? '';
$database = $_ENV['database.default.database'] ?? 'emb_rms';
$port = $_ENV['database.default.port'] ?? 3306;

try {
    $conn = new mysqli($hostname, $username, $password, $database, $port);
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
    
    echo "✓ Connected to database: $database\n\n";
    
    // Check if audit_logs table exists
    $result = $conn->query("SHOW TABLES LIKE 'audit_logs'");
    if ($result->num_rows > 0) {
        echo "✓ audit_logs table exists\n\n";
        
        // Get table columns
        $result = $conn->query("DESCRIBE audit_logs");
        echo "Current columns in audit_logs table:\n";
        while ($row = $result->fetch_assoc()) {
            $null = $row['Null'] == 'YES' ? 'nullable' : 'NOT NULL';
            $default = $row['Default'] !== null ? " DEFAULT {$row['Default']}" : '';
            $key = $row['Key'] ? " ({$row['Key']})" : '';
            echo "  - {$row['Field']} ({$row['Type']}) $null$default$key\n";
        }
    } else {
        echo "✗ audit_logs table does NOT exist\n";
    }
    
    $conn->close();
} catch (Exception $e) {
    die("Error: " . $e->getMessage());
}
