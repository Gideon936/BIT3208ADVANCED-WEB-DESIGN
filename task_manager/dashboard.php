<?php
session_start();
require 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Handle dynamic user input (Adding a task)
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['task_name'])) {
    $task_name = $_POST['task_name'];
    
    // Prevent basic SQL errors
    $task_name = mysqli_real_escape_string($conn, $task_name);
    
    $query = "INSERT INTO tasks (user_id, task_name) VALUES ('$user_id', '$task_name')";
    mysqli_query($conn, $query);
}

// Fetch Tasks
$tasks_query = "SELECT * FROM tasks WHERE user_id = '$user_id' ORDER BY created_at DESC";
$tasks_result = mysqli_query($conn, $tasks_query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Task Manager</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f4f4f9; margin: 0; padding: 20px; }
        .dashboard-container { max-width: 800px; margin: auto; background: white; padding: 20px; border-radius: 8px; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
        
        /* Input Form Styles */
        .form-group { display: flex; gap: 10px; margin-bottom: 20px; }
        input[type="text"] { flex-grow: 1; padding: 10px; border: 1px solid #ccc; border-radius: 4px; }
        button { padding: 10px 15px; background: #28a745; color: white; border: none; border-radius: 4px; cursor: pointer; font-weight: bold; }
        button:hover { background: #218838; }
        
        .logout { background: #dc3545; float: right; }
        .logout:hover { background: #c82333; }

        /* Table Styles */
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { padding: 12px; text-align: left; border-bottom: 1px solid #ddd; }
        th { background-color: #f8f9fa; font-weight: bold; }
        tr:hover { background-color: #f1f1f1; }
        
        .status-pending { color: #d39e00; font-weight: bold; }
        .status-completed { color: #28a745; font-weight: bold; }
        .empty-state { text-align: center; font-style: italic; color: #888; }
    </style>
</head>
<body>

<div class="dashboard-container">
    <button class="logout" onclick="window.location.href='logout.php'">Logout</button>
    
    <!-- Welcome Note -->
    <h1>Welcome, William Butcher!</h1>
    <p style="color: gray;">Manage your daily tasks below.</p>

    <!-- Task Input Form -->
    <form method="POST" id="taskForm" class="form-group">
        <input type="text" id="task_name" name="task_name" placeholder="Enter a new task..." required>
        <button type="submit">Save Task</button>
    </form>

    <!-- Task Table -->
    <table>
        <thead>
            <tr>
                <th>Task Name</th>
                <th>Status</th>
                <th>Date Added</th>
            </tr>
        </thead>
        <tbody>
            <?php while($row = mysqli_fetch_assoc($tasks_result)): ?>
                <tr>
                    <td><?php echo htmlspecialchars($row['task_name']); ?></td>
                    <td class="status-<?php echo $row['status']; ?>"><?php echo ucfirst($row['status']); ?></td>
                    <td><?php echo date('M d, Y h:i A', strtotime($row['created_at'])); ?></td>
                </tr>
            <?php endwhile; ?>
            
            <?php if(mysqli_num_rows($tasks_result) == 0): ?>
                <tr>
                    <td colspan="3" class="empty-state">No tasks available. Insert a task to get started!</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

</body>
</html>