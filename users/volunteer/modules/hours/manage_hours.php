<?php
require_once '../../../../includes/auth.php';
require_once '../../../../config/dbconfig.php';

requireRole('volunteer');

$database = new Database();
$db = $database->getConnection();
$user_id = getUserId();

// Handle delete (only if pending)
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $hour_id = $_GET['delete'];
    $query = "DELETE FROM hours WHERE hour_id = :hour_id AND volunteer_id = :user_id AND status = 'pending'";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':hour_id', $hour_id);
    $stmt->bindParam(':user_id', $user_id);
    
    if ($stmt->execute() && $stmt->rowCount() > 0) {
        $success = "Hour entry deleted successfully";
    } else {
        $error = "Cannot delete this entry (only pending entries can be deleted)";
    }
}

// Get volunteer's hours
$query = "SELECT h.*, a.activity_name, v.full_name as verifier_name
          FROM hours h
          JOIN activities a ON h.activity_id = a.activity_id
          LEFT JOIN users v ON h.verified_by = v.user_id
          WHERE h.volunteer_id = :user_id
          ORDER BY h.work_date DESC, h.created_at DESC";
$stmt = $db->prepare($query);
$stmt->bindParam(':user_id', $user_id);
$stmt->execute();
$hours = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Hours - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="<?php echo SITE_URL; ?>/assets/css/modern-style.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <!--<style>
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
            max-width: 1200px;
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
        
        .btn-primary {
            background: #667eea;
            color: white;
        }
        
        .btn-secondary {
            background: #6c757d;
            color: white;
        }
        
        .btn-danger {
            background: #dc3545;
            color: white;
        }
        
        .btn-sm {
            padding: 6px 12px;
            font-size: 13px;
        }
        
        .alert {
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        
        .alert-success {
            background: #d4edda;
            color: #155724;
        }
        
        .alert-error {
            background: #f8d7da;
            color: #721c24;
        }
        
        .card {
            background: white;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            overflow: hidden;
        }
        
        .table-responsive {
            overflow-x: auto;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
        }
        
        thead {
            background: #f8f9fa;
        }
        
        th, td {
            padding: 15px;
            text-align: left;
            border-bottom: 1px solid #dee2e6;
        }
        
        th {
            font-weight: 600;
            color: #495057;
        }
        
        tbody tr:hover {
            background: #f8f9fa;
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
        
        .actions {
            display: flex;
            gap: 5px;
        }
        
        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: #666;
        }
        
        .empty-state h3 {
            color: #333;
            margin-bottom: 10px;
        }
    </style> -->
</head>
<body>
    <div class="header">
        <div class="header-content">
            <h1><span class="logo-emoji">🤝</span><?php echo SITE_NAME; ?> - Volunteer</h1>
            <div class="header-links">
                <a href="../../v_dashboard.php">Dashboard</a>
                <a href="../../../../logout.php">Logout</a>
            </div>
        </div>
    </div>
    
    <div class="container">
        <div class="page-header">
            <h2>My Volunteer Hours</h2>
            <a href="add_hours.php" class="btn btn-primary">Log New Hours</a>
        </div>
        
        <?php if (isset($success)): ?>
            <div class="alert alert-success"><?php echo $success; ?></div>
        <?php endif; ?>
        
        <?php if (isset($error)): ?>
            <div class="alert alert-error"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <div class="card">
            <div class="table-responsive">
                <table>
                    <thead>
                        <tr>
                            <th>Activity</th>
                            <th>Hours</th>
                            <th>Date</th>
                            <th>Description</th>
                            <th>Status</th>
                            <th>Verified By</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($hours) > 0): ?>
                            <?php foreach ($hours as $hour): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($hour['activity_name']); ?></td>
                                <td><?php echo $hour['hours_worked']; ?></td>
                                <td><?php echo date('M d, Y', strtotime($hour['work_date'])); ?></td>
                                <td><?php echo htmlspecialchars(substr($hour['description'], 0, 40)) . (strlen($hour['description']) > 40 ? '...' : ''); ?></td>
                                <td>
                                    <span class="badge badge-<?php echo $hour['status']; ?>">
                                        <?php echo ucfirst($hour['status']); ?>
                                    </span>
                                </td>
                                <td><?php echo $hour['verifier_name'] ? htmlspecialchars($hour['verifier_name']) : '-'; ?></td>
                                <td>
                                    <div class="actions">
                                        <?php if ($hour['status'] == 'pending'): ?>
                                            <a href="edit_hours.php?id=<?php echo $hour['hour_id']; ?>" 
                                               class="btn btn-primary btn-sm">Edit</a>
                                            <a href="?delete=<?php echo $hour['hour_id']; ?>" 
                                               class="btn btn-danger btn-sm"
                                               onclick="return confirm('Are you sure you want to delete this entry?')">Delete</a>
                                        <?php else: ?>
                                            <span style="color: #999; font-size: 12px;">No actions</span>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="7">
                                    <div class="empty-state">
                                        <h3>No hours logged yet</h3>
                                        <p>Start tracking your volunteer hours by clicking the button above</p>
                                        <a href="add_hours.php" class="btn btn-primary" style="margin-top: 20px;">Log Your First Hours</a>
                                    </div>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>
</html>