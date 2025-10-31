<?php
require_once 'includes/auth.php';
require_once 'config/dbconfig.php';

requireRole('admin');

$database = new Database();
$db = $database->getConnection();

// Test direct update
if (isset($_GET['test_id']) && isset($_GET['new_role'])) {
    $user_id = $_GET['test_id'];
    $new_role = $_GET['new_role'];
    
    echo "<h2>Direct Update Test</h2>";
    echo "<p>Attempting to update user ID: $user_id to role: $new_role</p>";
    
    // Get current value
    $query = "SELECT user_id, username, role FROM users WHERE user_id = ?";
    $stmt = $db->prepare($query);
    $stmt->execute([$user_id]);
    $before = $stmt->fetch(PDO::FETCH_ASSOC);
    
    echo "<h3>Before Update:</h3>";
    echo "<pre>" . print_r($before, true) . "</pre>";
    
    // Direct update
    $query = "UPDATE users SET role = ? WHERE user_id = ?";
    $stmt = $db->prepare($query);
    $result = $stmt->execute([$new_role, $user_id]);
    
    echo "<h3>Update Result:</h3>";
    echo "Execute: " . ($result ? "TRUE" : "FALSE") . "<br>";
    echo "Rows affected: " . $stmt->rowCount() . "<br>";
    
    // Get after value
    $query = "SELECT user_id, username, role FROM users WHERE user_id = ?";
    $stmt = $db->prepare($query);
    $stmt->execute([$user_id]);
    $after = $stmt->fetch(PDO::FETCH_ASSOC);
    
    echo "<h3>After Update:</h3>";
    echo "<pre>" . print_r($after, true) . "</pre>";
    
    if ($before['role'] !== $after['role']) {
        echo "<p style='color: green; font-weight: bold;'>✓ SUCCESS! Role changed from {$before['role']} to {$after['role']}</p>";
    } else {
        echo "<p style='color: red; font-weight: bold;'>✗ FAILED! Role did not change (still {$after['role']})</p>";
        
        // Check table structure
        echo "<h3>Table Structure Check:</h3>";
        $query = "DESCRIBE users";
        $stmt = $db->query($query);
        $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo "<pre>" . print_r($columns, true) . "</pre>";
    }
    
    echo "<hr>";
}

// List all users with quick test links
echo "<h2>All Users - Quick Test</h2>";
$query = "SELECT user_id, username, role, status FROM users ORDER BY user_id";
$stmt = $db->query($query);
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo "<table border='1' cellpadding='10' style='border-collapse: collapse;'>";
echo "<tr><th>ID</th><th>Username</th><th>Current Role</th><th>Status</th><th>Test Update</th></tr>";

foreach ($users as $user) {
    echo "<tr>";
    echo "<td>{$user['user_id']}</td>";
    echo "<td>{$user['username']}</td>";
    echo "<td><strong>{$user['role']}</strong></td>";
    echo "<td>{$user['status']}</td>";
    echo "<td>";
    
    // Show role change options
    if ($user['role'] !== 'volunteer') {
        echo "<a href='?test_id={$user['user_id']}&new_role=volunteer'>→ Volunteer</a> | ";
    }
    if ($user['role'] !== 'coordinator') {
        echo "<a href='?test_id={$user['user_id']}&new_role=coordinator'>→ Coordinator</a> | ";
    }
    if ($user['role'] !== 'admin') {
        echo "<a href='?test_id={$user['user_id']}&new_role=admin'>→ Admin</a>";
    }
    
    echo "</td>";
    echo "</tr>";
}

echo "</table>";

echo "<hr>";
echo "<p><a href='manage_users.php'>← Back to Manage Users</a></p>";
?>

<!DOCTYPE html>
<html>
<head>
    <title>Direct Update Test</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            padding: 20px;
            max-width: 1200px;
            margin: 0 auto;
        }
        table {
            width: 100%;
            margin: 20px 0;
        }
        th {
            background: #667eea;
            color: white;
            padding: 10px;
        }
        td {
            padding: 10px;
        }
        a {
            color: #667eea;
            text-decoration: none;
            padding: 5px 10px;
            background: #e7f3ff;
            border-radius: 3px;
        }
        a:hover {
            background: #667eea;
            color: white;
        }
        pre {
            background: #f5f5f5;
            padding: 10px;
            border-radius: 5px;
            overflow-x: auto;
        }
    </style>
</head>
<body>
</body>
</html>