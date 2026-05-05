<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

$hostname = 'localhost';
$username = 'root';
$password = '';
$database = 'emb_rms';

try {
    $pdo = new PDO("mysql:host=$hostname;dbname=$database", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    echo "<h1>Database Snapshot</h1>";

    // List all tables
    $stmt = $pdo->query("SHOW TABLES");
    echo "<h2>Tables Found</h2><ul>";
    while ($row = $stmt->fetch(PDO::FETCH_NUM)) {
        echo "<li>{$row[0]}</li>";
    }
    echo "</ul>";

    // Roles
    echo "<h2>Roles</h2>";
    $stmt = $pdo->query("SELECT * FROM roles");
    $roles = $stmt->fetchAll(PDO::FETCH_ASSOC);
    if (empty($roles)) echo "<p>No roles found.</p>";
    else {
        echo "<table border='1'><tr><th>ID</th><th>Name</th></tr>";
        foreach ($roles as $row) {
            echo "<tr><td>{$row['role_id']}</td><td>{$row['role_name']}</td></tr>";
        }
        echo "</table>";
    }

    // Users and their roles
    echo "<h2>Users and assigned roles</h2>";
    $stmt = $pdo->query("SELECT u.user_id, u.username, r.role_name FROM users u LEFT JOIN user_roles ur ON u.user_id = ur.user_id LEFT JOIN roles r ON r.role_id = ur.role_id");
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
    if (empty($users)) echo "<p>No users found.</p>";
    else {
        echo "<table border='1'><tr><th>ID</th><th>User</th><th>Role</th></tr>";
        foreach ($users as $row) {
            echo "<tr><td>{$row['user_id']}</td><td>{$row['username']}</td><td>" . ($row['role_name'] ?? '<i>No Role</i>') . "</td></tr>";
        }
        echo "</table>";
    }

    // User Permissions
    echo "<h2>User Permissions (Custom overrides)</h2>";
    $stmt = $pdo->query("SELECT * FROM user_permissions");
    $perms = $stmt->fetchAll(PDO::FETCH_ASSOC);
    if (empty($perms)) echo "<p>No custom permissions found.</p>";
    else {
        echo "<table border='1'><tr><th>User ID</th><th>Permission</th></tr>";
        foreach ($perms as $row) {
            echo "<tr><td>{$row['user_id']}</td><td>{$row['permission_key']}</td></tr>";
        }
        echo "</table>";
    }

} catch (PDOException $e) {
    echo "Connection failed: " . $e->getMessage();
}
