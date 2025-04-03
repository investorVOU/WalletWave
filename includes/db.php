<?php
/**
 * Database Connection
 * Handles connection to PostgreSQL database
 */

// Use environment variables for database configuration
$host = getenv('PGHOST') ?: 'localhost';
$dbname = getenv('PGDATABASE') ?: 'cryptofund';
$username = getenv('PGUSER') ?: 'postgres';
$password = getenv('PGPASSWORD') ?: '';
$port = getenv('PGPORT') ?: '5432';

// PDO options
$options = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES => false,
];

// Create PDO instance
try {
    $pdo = new PDO("pgsql:host=$host;port=$port;dbname=$dbname", $username, $password, $options);
} catch (PDOException $e) {
    // Log error but don't expose details to user
    error_log("Database connection error: " . $e->getMessage());
    
    // Fail gracefully
    die("Sorry, we're experiencing technical difficulties. Please try again later.");
}

/**
 * Helper function to execute SQL queries
 * @param string $sql SQL query with placeholders
 * @param array $params Parameters to bind to the query
 * @return array|null Result set or null on failure
 */
function executeQuery($sql, $params = []) {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    } catch (PDOException $e) {
        error_log("Query execution error: " . $e->getMessage());
        return null;
    }
}

/**
 * Helper function to get a single record
 * @param string $sql SQL query with placeholders
 * @param array $params Parameters to bind to the query
 * @return array|null Single record or null on failure/no results
 */
function getRecord($sql, $params = []) {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetch();
    } catch (PDOException $e) {
        error_log("Query execution error: " . $e->getMessage());
        return null;
    }
}

/**
 * Helper function to insert data and return the ID
 * @param string $table Table name
 * @param array $data Associative array of column => value
 * @return int|null Last inserted ID or null on failure
 */
function insertRecord($table, $data) {
    global $pdo;
    
    try {
        $columns = implode(', ', array_keys($data));
        $placeholders = implode(', ', array_fill(0, count($data), '?'));
        
        $sql = "INSERT INTO $table ($columns) VALUES ($placeholders)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute(array_values($data));
        
        return $pdo->lastInsertId();
    } catch (PDOException $e) {
        error_log("Insert error: " . $e->getMessage());
        return null;
    }
}

/**
 * Helper function to update data
 * @param string $table Table name
 * @param array $data Associative array of column => value
 * @param string $where WHERE clause
 * @param array $params Parameters for WHERE clause
 * @return bool Success or failure
 */
function updateRecord($table, $data, $where, $params = []) {
    global $pdo;
    
    try {
        $setParts = [];
        $updateParams = [];
        
        foreach ($data as $column => $value) {
            $setParts[] = "$column = ?";
            $updateParams[] = $value;
        }
        
        $setClause = implode(', ', $setParts);
        $updateParams = array_merge($updateParams, $params);
        
        $sql = "UPDATE $table SET $setClause WHERE $where";
        $stmt = $pdo->prepare($sql);
        $stmt->execute($updateParams);
        
        return true;
    } catch (PDOException $e) {
        error_log("Update error: " . $e->getMessage());
        return false;
    }
}
