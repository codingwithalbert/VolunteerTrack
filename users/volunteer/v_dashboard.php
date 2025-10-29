<?php
require_once '../../includes/auth.php';
require_once '../../config/dbconfig.php';

requireRole('volunteer');

$database = new Database();
$db = $database->getConnection();
$user_id = getUserId();

// Get volunteer statistics
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

// Get recent submissions
$query = "SELECT h.*, a.activity_name 
          FROM hours h
          JOIN activities a ON h.activity_id = a.activity_id
          WHERE h.volunteer_id = :user_id
          ORDER BY h.created_at DESC
          LIMIT 5";
$stmt = $db->prepare($query);
$stmt->bindParam(':user_id', $user_id);
$stmt->execute();
$recent_hours = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get available activities
$query = "SELECT COUNT(*) as total FROM activities WHERE status = 'active'";
$stmt = $db->prepare($query);
$stmt->execute();
$total_activities = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Volunteer Dashboard - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="<?php echo SITE_URL; ?>/assets/css/modern-style.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
   <!-- <style>
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
        
        .user-info {
            display: flex;
            align-items: center;
            gap: 15px;
        }
        
        .header-links a {
            color: white;
            text-decoration: none;
            margin-left: 15px;
            padding: 8px 16px;
            border-radius: 5px;
            background: rgba(255,255,255,0.2);
            transition: background 0.3s;
        }
        
        .header-links a:hover {
            background: rgba(255,255,255,0.3);
        }
        
        .container {
            max-width: 1200px;
            margin: 30px auto;
            padding: 0 20px;
        }
        
        .welcome {
            background: white;
            padding: 30px;
            border-radius: 10px;
            margin-bottom: 30px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .welcome h2 {
            color: #333;
            margin-bottom: 10px;
        }
        
        .welcome p {
            color: #666;
        }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .stat-card {
            background: white;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        
        .stat-info h3 {
            font-size: 32px;
            color: #667eea;
            margin-bottom: 5px;
        }
        
        .stat-info p {
            color: #666;
            font-size: 14px;
        }
        
        .stat-icon {
            font-size: 40px;
            opacity: 0.3;
        }
        
        .quick-actions {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin-bottom: 30px;
        }
        
        .action-btn {
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            text-align: center;
            text-decoration: none;
            color: #333;
            transition: transform 0.3s, box-shadow 0.3s;
        }
        
        .action-btn:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 20px rgba(0,0,0,0.15);
        }
        
        .action-btn h4 {
            margin-top: 10px;
            color: #667eea;
        }
        
        .recent-section {
            background: white;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .recent-section h3 {
            color: #333;
            margin-bottom: 20px;
        }
        
        .recent-item {
            padding: 15px;
            border-bottom: 1px solid #e0e0e0;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .recent-item:last-child {
            border-bottom: none;
        }
        
        .recent-info {
            flex: 1;
        }
        
        .recent-info h4 {
            color: #333;
            margin-bottom: 5px;
        }
        
        .recent-info p {
            color: #666;
            font-size: 13px;
        }
        
        .badge {
            padding: 5px 12px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: 600;
        }
        
        .badge-pending {
            background: #fff3cd;
            color: #856404;
        }
        
        .badge-verified {
            background: #d4edda;
            color: #155724;
        }
        
        .badge-rejected {
            background: #f8d7da;
            color: #721c24;
        }
    </style> -->
</head>
<body>
    <div class="header">
        <div class="header-content">
            <h1><span class="logo-emoji">🤝</span><?php echo SITE_NAME; ?></h1>
            <div class="user-info">
                <span>Welcome, <?php echo getUserFullName(); ?></span>
                <div class="header-links">
                    <a href="profile.php">Profile</a>
                    <a href="../../logout.php">Logout</a>
                </div>
            </div>
        </div>
    </div>
    
    <div class="container">
        <div class="welcome">
            <h2>Volunteer Dashboard</h2>
            <p>Track your volunteer hours and contributions</p>
        </div>
        
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-info">
                    <h3><?php echo number_format((float)($stats['verified_hours'] ?? 0), 1); ?></h3>
                    <p>Verified Hours</p>
                </div>
                <div class="stat-icon"></div>
            </div>
            
            <div class="stat-card">
                <div class="stat-info">
                    <h3><?php echo number_format((float)($stats['pending_hours'] ?? 0), 1); ?></h3>
                    <p>Pending Hours</p>
                </div>
                <div class="stat-icon"></div>
            </div>
            
            <div class="stat-card">
                <div class="stat-info">
                    <h3><?php echo $stats['total_entries']; ?></h3>
                    <p>Total Entries</p>
                </div>
                <div class="stat-icon"></div>
            </div>
            
            <div class="stat-card">
                <div class="stat-info">
                    <h3><?php echo $total_activities; ?></h3>
                    <p>Available Activities</p>
                </div>
                <div class="stat-icon"></div>
            </div>
        </div>
        
        <h3 style="color: #333; margin-bottom: 15px;">Quick Actions</h3>
        <div class="quick-actions">
            <a href="modules/hours/add_hours.php" class="action-btn">
                <div style="font-size: 32px;">➕</div>
                <h4>Log Hours</h4>
            </a>
            <a href="modules/hours/manage_hours.php" class="action-btn">
                <div style="font-size: 32px;">⏰</div>
                <h4>View My Hours</h4>
            </a>
            <a href="profile.php" class="action-btn">
                <div style="font-size: 32px;">👤</div>
                <h4>My Profile</h4>
            </a>
            <a href="change_password.php" class="action-btn">
                <div style="font-size: 32px;">🔒</div>
                <h4>Change Password</h4>
            </a>
        </div>
        
        <div class="recent-section">
            <h3>My Recent Submissions</h3>
            <?php if (count($recent_hours) > 0): ?>
                <?php foreach ($recent_hours as $hour): ?>
                    <div class="recent-item">
                        <div class="recent-info">
                            <h4><?php echo htmlspecialchars($hour['activity_name']); ?></h4>
                            <p><?php echo $hour['hours_worked']; ?> hours on <?php echo date('M d, Y', strtotime($hour['work_date'])); ?></p>
                        </div>
                        <span class="badge badge-<?php echo $hour['status']; ?>">
                            <?php echo ucfirst($hour['status']); ?>
                        </span>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p style="color: #666; text-align: center; padding: 20px;">
                    No submissions yet. <a href="modules/hours/add_hours.php">Log your first hours!</a>
                </p>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>