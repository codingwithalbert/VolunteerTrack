<?php
require_once '../../../../includes/auth.php';
require_once '../../../../config/dbconfig.php';

requireRole(['admin', 'coordinator']);

$database = new Database();
$db = $database->getConnection();

// Handle verify/reject actions
if (isset($_GET['action']) && isset($_GET['id'])) {
    $hour_id = $_GET['id'];
    $action = $_GET['action'];
    
    if ($action == 'verify') {
        $query = "UPDATE hours SET status = 'verified', verified_by = :verified_by, verified_at = NOW() 
                  WHERE hour_id = :hour_id";
        $stmt = $db->prepare($query);
        $verified_by = getUserId();
        $stmt->bindParam(':verified_by', $verified_by);
        $stmt->bindParam(':hour_id', $hour_id);
        
        if ($stmt->execute()) {
            $success = "Hours verified successfully";
        }
    } elseif ($action == 'reject') {
        $query = "UPDATE hours SET status = 'rejected', verified_by = :verified_by, verified_at = NOW() 
                  WHERE hour_id = :hour_id";
        $stmt = $db->prepare($query);
        $verified_by = getUserId();
        $stmt->bindParam(':verified_by', $verified_by);
        $stmt->bindParam(':hour_id', $hour_id);
        
        if ($stmt->execute()) {
            $success = "Hours rejected";
        }
    }
}

// Handle delete
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $hour_id = $_GET['delete'];
    $query = "DELETE FROM hours WHERE hour_id = :hour_id";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':hour_id', $hour_id);
    
    if ($stmt->execute()) {
        $success = "Hour entry deleted successfully";
    } else {
        $error = "Failed to delete hour entry";
    }
}

// Get all hours
$query = "SELECT h.*, u.full_name as volunteer_name, a.activity_name, v.full_name as verifier_name
          FROM hours h
          JOIN users u ON h.volunteer_id = u.user_id
          JOIN activities a ON h.activity_id = a.activity_id
          LEFT JOIN users v ON h.verified_by = v.user_id
          ORDER BY h.created_at DESC";
$stmt = $db->prepare($query);
$stmt->execute();
$hours = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Hours - <?php echo SITE_NAME; ?></title>
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
            max-width: 1400px;
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
            max-width: 1400px;
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
            font-size: 14px;
        }
        
        .btn-primary {
            background: #667eea;
            color: white;
        }
        
        .btn-success {
            background: #28a745;
            color: white;
        }
        
        .btn-warning {
            background: #ffc107;
            color: #333;
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
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #dee2e6;
            font-size: 14px;
        }
        
        th {
            font-weight: 600;
            color: #495057;
        }
        
        tbody tr:hover {
            background: #f8f9fa;
        }
        
        .badge {
            padding: 4px 10px;
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
            flex-wrap: wrap;
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="header-content">
            <h1><?php echo SITE_NAME; ?> - <?php echo getUserRole() == 'admin' ? 'Admin' : 'Coordinator'; ?> Panel</h1>
            <div class="header-links">
                <a href="../../<?php echo getUserRole() == 'admin' ? 'a' : 'c'; ?>../../a_dashboard.php">Dashboard</a>
                <a href="../../../../logout.php">Logout</a>
            </div>
        </div>
    </div>
    
    <div class="container">
        <div class="page-header">
            <h2>Manage Volunteer Hours</h2>
            <a href="add_hours.php" class="btn btn-primary">Add Hours Entry</a>
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
                            <th>ID</th>
                            <th>Volunteer</th>
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
                                <td><?php echo $hour['hour_id']; ?></td>
                                <td><?php echo htmlspecialchars($hour['volunteer_name']); ?></td>
                                <td><?php echo htmlspecialchars($hour['activity_name']); ?></td>
                                <td><?php echo $hour['hours_worked']; ?></td>
                                <td><?php echo date('M d, Y', strtotime($hour['work_date'])); ?></td>
                                <td><?php echo htmlspecialchars(substr($hour['description'], 0, 30)) . (strlen($hour['description']) > 30 ? '...' : ''); ?></td>
                                <td>
                                    <span class="badge badge-<?php echo $hour['status']; ?>">
                                        <?php echo ucfirst($hour['status']); ?>
                                    </span>
                                </td>
                                <td><?php echo $hour['verifier_name'] ? htmlspecialchars($hour['verifier_name']) : '-'; ?></td>
                                <td>
                                    <div class="actions">
                                        <?php if ($hour['status'] == 'pending'): ?>
                                            <a href="?action=verify&id=<?php echo $hour['hour_id']; ?>" 
                                               class="btn btn-success btn-sm"
                                               onclick="return confirm('Verify these hours?')">Verify</a>
                                            <a href="?action=reject&id=<?php echo $hour['hour_id']; ?>" 
                                               class="btn btn-warning btn-sm"
                                               onclick="return confirm('Reject these hours?')">Reject</a>
                                        <?php endif; ?>
                                        <a href="edit_hours.php?id=<?php echo $hour['hour_id']; ?>" 
                                           class="btn btn-primary btn-sm">Edit</a>
                                        <a href="?delete=<?php echo $hour['hour_id']; ?>" 
                                           class="btn btn-danger btn-sm"
                                           onclick="return confirm('Delete this entry?')">Delete</a>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="9" style="text-align: center; padding: 30px; color: #666;">
                                    No hour entries found.
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