<?php
/**
 * Password Hash Generator for VolunteerTrack
 * Place this file in your project root and access via browser
 * Copy the generated hashes and update your SQL file
 */

$passwords = [
    'admin123' => 'Admin password',
    'coord123' => 'Coordinator password', 
    'volunteer123' => 'Volunteer password'
];

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Password Hash Generator</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            max-width: 800px;
            margin: 50px auto;
            padding: 20px;
            background: #f5f5f5;
        }
        .container {
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        h1 {
            color: #667eea;
            margin-bottom: 20px;
        }
        .password-item {
            background: #f8f9fa;
            padding: 15px;
            margin: 15px 0;
            border-radius: 5px;
            border-left: 4px solid #667eea;
        }
        .password-item strong {
            color: #333;
            display: block;
            margin-bottom: 8px;
        }
        .hash {
            font-family: monospace;
            font-size: 12px;
            color: #666;
            word-break: break-all;
            background: white;
            padding: 10px;
            border-radius: 3px;
            margin-top: 5px;
        }
        .sql-section {
            margin-top: 30px;
            padding: 20px;
            background: #e7f3ff;
            border-radius: 5px;
        }
        .sql-section h2 {
            color: #333;
            margin-bottom: 15px;
        }
        .sql-code {
            background: white;
            padding: 15px;
            border-radius: 5px;
            font-family: monospace;
            font-size: 12px;
            overflow-x: auto;
            white-space: pre-wrap;
        }
        .instruction {
            background: #fff3cd;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
            border-left: 4px solid #ffc107;
        }
        .success {
            background: #d4edda;
            color: #155724;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔐 VolunteerTrack Password Hash Generator</h1>
        
        <div class="instruction">
            <strong>📋 Instructions:</strong><br>
            1. Copy the SQL statements below<br>
            2. Run them in phpMyAdmin after creating the database tables<br>
            3. Or update the INSERT statements in your volunteertrack.sql file
        </div>

        <div class="success">
            ✅ Password hashes generated successfully!
        </div>

        <h2>Generated Password Hashes:</h2>
        
        <?php foreach ($passwords as $password => $description): ?>
            <div class="password-item">
                <strong><?php echo $description; ?>: <?php echo $password; ?></strong>
                <div class="hash"><?php echo password_hash($password, PASSWORD_DEFAULT); ?></div>
            </div>
        <?php endforeach; ?>

        <div class="sql-section">
            <h2>Complete SQL Insert Statements:</h2>
            <div class="sql-code">-- Delete existing demo users (if any)
DELETE FROM users WHERE username IN ('admin', 'coordinator1', 'volunteer1');

-- Insert Admin User (username: admin, password: admin123)
INSERT INTO users (username, email, password, full_name, role, status) VALUES
('admin', 'admin@volunteertrack.com', '<?php echo password_hash('admin123', PASSWORD_DEFAULT); ?>', 'System Administrator', 'admin', 'active');

-- Insert Coordinator (username: coordinator1, password: coord123)
INSERT INTO users (username, email, password, full_name, role, status) VALUES
('coordinator1', 'coordinator@volunteertrack.com', '<?php echo password_hash('coord123', PASSWORD_DEFAULT); ?>', 'John Coordinator', 'coordinator', 'active');

-- Insert Volunteer (username: volunteer1, password: volunteer123)
INSERT INTO users (username, email, password, full_name, role, status) VALUES
('volunteer1', 'volunteer@volunteertrack.com', '<?php echo password_hash('volunteer123', PASSWORD_DEFAULT); ?>', 'Jane Volunteer', 'volunteer', 'active');</div>
        </div>

        <div class="instruction" style="margin-top: 20px; background: #d1ecf1; border-left-color: #0c5460;">
            <strong>🔑 Login Credentials:</strong><br>
            <strong>Admin:</strong> admin / admin123<br>
            <strong>Coordinator:</strong> coordinator1 / coord123<br>
            <strong>Volunteer:</strong> volunteer1 / volunteer123
        </div>
    </div>
</body>
</html>