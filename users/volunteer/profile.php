<?php
require_once '../../includes/auth.php';
require_once '../../config/dbconfig.php';

requireRole('volunteer');

$database = new Database();
$db = $database->getConnection();
$user_id = getUserId();

// Get user info
$query = "SELECT * FROM users WHERE user_id = :user_id";
$stmt = $db->prepare($query);
$stmt->bindParam(':user_id', $user_id);
$stmt->execute();
$user = $stmt->fetch(PDO::FETCH_ASSOC);

// Get volunteer hours statistics
$query = "SELECT 
    COUNT(*) as total_entries,
    SUM(CASE WHEN status='verified' THEN hours_worked ELSE 0 END) as verified_hours,
    SUM(CASE WHEN status='pending' THEN hours_worked ELSE 0 END) as pending_hours,
    SUM(CASE WHEN status='rejected' THEN hours_worked ELSE 0 END) as rejected_hours
    FROM hours WHERE volunteer_id = :user_id";
$stmt = $db->prepare($query);
$stmt->bindParam(':user_id', $user_id);
$stmt->execute();
$stats = $stmt->fetch(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile - <?php echo SITE_NAME; ?></title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f5f5f5;
        }
        
        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .header-content {
            max-width: 1200px;
            margin: 0 auto;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .header h1 {
            font-size: 24px;
        }
        
        .header-links a {
            color: white;
            text-decoration: none;
            margin-left: 20px;
            padding: 8px 16px;
            border-radius: 5px;
            background: rgba(255,255,255,0.2);
        }
        
        .container {
            max-width: 800px;
            margin: 30px auto;
            padding: 0 20px;
        }
        
        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
        }
        
        .page-header h2 {
            color: #333;
        }
        
        .btn {
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            transition: all 0.3s;
        }
        
        .btn-secondary {
            background: #6c757d;
            color: white;
        }
        
        .btn-primary {
            background: #667eea;
            color: white;
        }
        
        .card {
            background: white;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            padding: 30px;
            margin-bottom: 20px;
        }
        
        .card h3 {
            color: #333;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid #f0f0f0;
        }
        
        .profile-info {
            display: grid;
            gap: 20px;
        }
        
        .info-item {
            display: flex;
            padding: 15px;
            background: #f8f9fa;
            border-radius: 8px;
            border-left: 4px solid #17a2b8;
        }
        
        .info-item strong {
            width: 150px;
            color: #495057;
        }
        
        .info-item span {
            color: #212529;
            flex: 1;
        }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 15px;
            margin-bottom: 20px;
        }
        
        .stat-box {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 20px;
            border-radius: 8px;
            text-align: center;
        }
        
        .stat-box h4 {
            font-size: 28px;
            margin-bottom: 5px;
        }
        
        .stat-box p {
            font-size: 13px;
            opacity: 0.9;
        }
        
        .actions {
            display: flex;
            gap: 10px;
            margin-top: 20px;
        }
        
        .badge {
            display: inline-block;
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 14px;
            font-weight: 600;
        }
        
        .badge-volunteer {
            background: #17a2b8;
            color: white;
        }
        
        .badge-active {
            background: #d4edda;
            color: #155724;
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="header-content">
            <h1><?php echo SITE_NAME; ?> - Volunteer</h1>
            <div class="header-links">
                <a href="v_dashboard.php">Dashboard</a>
                <a href="../../logout.php">Logout</a>
            </div>
        </div>
    </div>
    
    <div class="container">
        <div class="page-header">
            <h2>My Profile</h2>
            <a href="v_dashboard.php" class="btn btn-secondary">Back to Dashboard</a>
        </div>
        
        <div class="card">
            <h3>Personal Information</h3>
            <div class="profile-info">
                <div class="info-item">
                    <strong>Username:</strong>
                    <span><?php echo htmlspecialchars($user['username']); ?></span>
                </div>
                
                <div class="info-item">
                    <strong>Email:</strong>
                    <span><?php echo htmlspecialchars($user['email']); ?></span>
                </div>
                
                <div class="info-item">
                    <strong>Full Name:</strong>
                    <span><?php echo htmlspecialchars($user['full_name']); ?></span>
                </div>
                
                <div class="info-item">
                    <strong>Role:</strong>
                    <span><span class="badge badge-volunteer"><?php echo ucfirst($user['role']); ?></span></span>
                </div>
                
                <div class="info-item">
                    <strong>Status:</strong>
                    <span><span class="badge badge-active"><?php echo ucfirst($user['status']); ?></span></span>
                </div>
                
                <div class="info-item">
                    <strong>Phone:</strong>
                    <span><?php echo htmlspecialchars($user['phone'] ?: 'Not provided'); ?></span>
                </div>
                
                <div class="info-item">
                    <strong>Address:</strong>
                    <span><?php echo htmlspecialchars($user['address'] ?: 'Not provided'); ?></span>
                </div>
                
                <div class="info-item">
                    <strong>Member Since:</strong>
                    <span><?php echo date('F d, Y', strtotime($user['created_at'])); ?></span>
                </div>
            </div>
        </div>
        
        <div class="card">
            <h3>My Volunteer Statistics</h3>
            <div class="stats-grid">
                <div class="stat-box">
                    <h4><?php echo number_format((float)($stats['verified_hours'] ?? 0), 1); ?></h4>
                    <p>Verified Hours</p>
                </div>
                <div class="stat-box">
                    <h4><?php echo number_format((float)($stats['pending_hours'] ?? 0), 1); ?></h4>
                    <p>Pending Hours</p>
                </div>
                <div class="stat-box">
                    <h4><?php echo $stats['total_entries']; ?></h4>
                    <p>Total Entries</p>
                </div>
                <div class="stat-box">
                    <h4><?php echo number_format((float)($stats['rejected_hours'] ?? 0), 1); ?></h4>
                    <p>Rejected Hours</p>
                </div>
            </div>
        </div>
        
        <div class="actions">
            <a href="change_password.php" class="btn btn-primary">Change Password</a>
            <a href="v_dashboard.php" class="btn btn-secondary">Back to Dashboard</a>
        </div>
    </div>
</body>
</html>