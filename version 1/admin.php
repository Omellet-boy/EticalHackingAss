<?php
// admin.php
session_start();
require_once 'db_connect.php';

// Redirect back to index.php if not logged in
if (!isset($_SESSION['user'])) {
    header("Location: index.php");
    exit;
}

$user = $_SESSION['user'];
$user_id = $user['id'];

// Migration check: Ensure user_id column exists in feedback table to support the JOIN query
$col_check = $conn->query("SHOW COLUMNS FROM feedback LIKE 'user_id'");
if ($col_check && $col_check->num_rows == 0) {
    $conn->query("ALTER TABLE feedback ADD user_id INT DEFAULT NULL");
    // Default existing feedback to the student01 account (id: 2)
    $conn->query("UPDATE feedback SET user_id = 2 WHERE user_id IS NULL");
}

// VULNERABLE BROKEN ACCESS CONTROL:
// Omitted check validating if the user's role is 'admin', allowing any student to access this portal.
/*
if ($user['role'] !== 'admin') {
    header("Location: dashboard.php");
    exit;
}
*/

// Fetch dynamic count of pending feedbacks for Card 3
$feedback_count = 0;
$count_result = $conn->query("SELECT COUNT(*) as cnt FROM feedback");
if ($count_result) {
    $row_count = $count_result->fetch_assoc();
    $feedback_count = $row_count['cnt'];
}

// Query student feedback using JOIN to display in the moderation queue table
$sql_select = "SELECT f.id, f.message, u.username FROM feedback f JOIN users u ON f.user_id = u.id ORDER BY f.id DESC";
$result_feedback = $conn->query($sql_select);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Administrative Console - MyEduConnect</title>
    <!-- Bootstrap 5 CDN -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        :root {
            --admin-dark-bg: #0f172a;       /* Dark Slate Body Background */
            --admin-sidebar-bg: #0b0f19;    /* Darker Slate Sidebar */
            --admin-card-bg: #1e293b;       /* Card Background */
            --admin-border: #334155;        /* Card Border */
            
            /* Text Colors */
            --text-title: #ffffff;
            --text-body: #94a3b8;
            
            /* Theme/Category Colors */
            --teal-accent: #0f766e;
            --green-accent: #15803d;
            --orange-accent: #c2410c;
            --red-accent: #b91c1c;
            --slate-hover: #1e293b;
        }
        
        body {
            background-color: var(--admin-dark-bg);
            color: var(--text-body);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        h1, h2, h3, h4, h5, h6, .card-title {
            color: var(--text-title);
        }
        
        /* Left-Sidebar Styling */
        .sidebar-slate {
            background-color: var(--admin-sidebar-bg);
            border-right: 1px solid var(--admin-border);
        }
        .sidebar-slate .sidebar-heading {
            font-weight: 700;
            color: var(--text-title);
            border-bottom: 1px solid var(--admin-border);
        }
        .sidebar-slate .list-group-item {
            color: var(--text-body);
            background: transparent;
            border: none;
            transition: all 0.2s ease;
        }
        .sidebar-slate .list-group-item:hover {
            color: var(--text-title);
            background-color: var(--slate-hover);
        }
        .sidebar-slate .list-group-item.active {
            color: var(--text-title);
            background-color: var(--orange-accent);
            font-weight: 600;
        }
        
        /* Stats Cards */
        .card-stat {
            background-color: var(--admin-card-bg);
            border: 1px solid var(--admin-border);
            border-radius: 8px;
            padding: 20px;
        }
        .stat-val {
            font-size: 2rem;
            font-weight: 700;
            color: var(--text-title);
        }
        .stat-desc {
            font-size: 0.85rem;
            color: var(--text-body);
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        /* Admin Dark Layout Cards */
        .card-admin {
            background-color: var(--admin-card-bg);
            border: 1px solid var(--admin-border);
            border-radius: 10px;
            margin-bottom: 25px;
            overflow: hidden;
        }
        .card-header-admin {
            font-weight: 600;
            font-size: 1.05rem;
            padding: 15px 20px;
            border-bottom: 1px solid var(--admin-border);
        }
        
        /* Color-Coded Header Variants */
        .header-feedback {
            border-top: 4px solid #f97316 !important;
            background-color: #2e1d11 !important;
            color: #ffffff !important;
        }
        .header-stats {
            border-top: 4px solid #0d9488 !important;
            background-color: #0f272a !important;
            color: #ffffff !important;
        }
        .header-logs {
            border-top: 4px solid #ef4444 !important;
            background-color: #2d161a !important;
            color: #ffffff !important;
        }
        
        /* Text Readability Styles */
        .text-light-silver {
            color: #94a3b8;
        }
        
        /* Table Styles */
        .table-dark-custom {
            color: var(--text-body);
            background-color: transparent;
        }
        .table-dark-custom th {
            color: var(--text-title);
            border-bottom: 2px solid var(--admin-border);
        }
        .table-dark-custom td {
            border-bottom: 1px solid var(--admin-border);
            vertical-align: middle;
        }
        
        /* Mock Audit Logs widget */
        .log-container {
            font-family: 'Courier New', Courier, monospace;
            background-color: #0b0f19;
            border-radius: 6px;
            padding: 15px;
            max-height: 250px;
            overflow-y: auto;
            border: 1px solid var(--admin-border);
        }
        .log-item {
            font-size: 0.82rem;
            margin-bottom: 8px;
            line-height: 1.4;
        }
        .log-time {
            color: #57606f;
        }
        .log-warn {
            color: #ff4757;
        }
        .log-info {
            color: #2ed573;
        }
        
        footer {
            background-color: var(--admin-sidebar-bg);
            color: var(--text-body);
            padding: 20px 0;
            font-size: 0.9rem;
            border-top: 1px solid var(--admin-border);
        }
    </style>
</head>
<body class="d-flex flex-column min-vh-100">

    <!-- Wrapper for flex-grow to push footer down -->
    <div class="flex-grow-1">
        
        <!-- Top Navbar -->
        <nav class="navbar navbar-dark bg-dark px-3 py-2 shadow-sm border-bottom border-secondary">
            <div class="container-fluid">
                <span class="navbar-brand mb-0 h1 text-white">MyEduConnect Control Console</span>
                <span class="navbar-text text-white d-none d-sm-inline">
                    Logged in as: <strong><?php echo htmlspecialchars($user['username']); ?></strong> (Role: <span class="badge bg-danger"><?php echo htmlspecialchars($user['role']); ?></span>)
                </span>
            </div>
        </nav>

        <div class="container-fluid">
            <div class="row">
                
                <!-- Left Sidebar Column -->
                <div class="col-md-3 col-lg-2 sidebar-slate py-4 px-3 d-flex flex-column" style="min-height: calc(100vh - 56px);">
                    <h5 class="sidebar-heading px-3 pb-3 mb-3">Navigation</h5>
                    <div class="list-group list-group-flush flex-grow-1">
                        <a href="dashboard.php" class="list-group-item list-group-item-action rounded mb-1">Dashboard</a>
                        <a href="profile.php?id=<?php echo $user_id; ?>" class="list-group-item list-group-item-action rounded mb-1">Student Directory</a>
                        <a href="courses.php" class="list-group-item list-group-item-action rounded mb-1">Courses</a>
                        <a href="upload.php" class="list-group-item list-group-item-action rounded mb-1">Coursework</a>
                        <a href="feedback.php" class="list-group-item list-group-item-action rounded mb-1">Feedback</a>
                        <a href="admin.php" class="list-group-item list-group-item-action active rounded mb-1">Admin Panel</a>
                        <a href="logout.php" class="list-group-item list-group-item-action rounded text-danger mt-4">Logout</a>
                    </div>
                </div>

                <!-- Main Content Column -->
                <div class="col-md-9 col-lg-10 py-4 px-4">
                    
                    <!-- Title row -->
                    <div class="mb-4">
                        <h2>Administrative Dashboard</h2>
                        <p class="text-light-silver mb-0">Manage university modules, course configurations, and view logged student concerns.</p>
                    </div>

                    <!-- Top Row Statistics (3 Cards) -->
                    <div class="row g-4 mb-4">
                        <!-- Card 1: Total Enrolled Students (Teal accent) -->
                        <div class="col-md-4">
                            <div class="card-stat border-start border-4" style="border-color: var(--teal-accent) !important;">
                                <div class="stat-desc">Total Enrolled Students</div>
                                <div class="stat-val mt-1">82,415</div>
                            </div>
                        </div>
                        <!-- Card 2: Premium Subscriptions (Green accent) -->
                        <div class="col-md-4">
                            <div class="card-stat border-start border-4" style="border-color: var(--green-accent) !important;">
                                <div class="stat-desc">Premium Subscriptions</div>
                                <div class="stat-val mt-1">12,480</div>
                            </div>
                        </div>
                        <!-- Card 3: Pending Feedbacks (Orange accent) -->
                        <div class="col-md-4">
                            <div class="card-stat border-start border-4" style="border-color: var(--orange-accent) !important;">
                                <div class="stat-desc">Pending Feedback Tickets</div>
                                <div class="stat-val mt-1"><?php echo $feedback_count; ?></div>
                            </div>
                        </div>
                    </div>

                    <!-- Middle Content Rows -->
                    <div class="row">
                        
                        <!-- Middle Left Column: Pending Moderation Queue -->
                        <div class="col-lg-8">
                            <div class="card card-admin">
                                <div class="card-header-admin header-feedback">
                                    Pending Student Feedback Queue
                                </div>
                                <div class="card-body p-4 text-light">
                                    <p class="small text-light-silver mb-4">
                                        Submissions below require review. Stored HTML layouts are allowed in message logs.
                                    </p>
                                    
                                    <div class="table-responsive">
                                        <table class="table table-dark-custom">
                                            <thead>
                                                <tr>
                                                    <th style="width: 10%;">ID</th>
                                                    <th style="width: 25%;">Student</th>
                                                    <th style="width: 45%;">Message</th>
                                                    <th style="width: 20%;" class="text-center">Actions</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php if ($result_feedback && $result_feedback->num_rows > 0): ?>
                                                    <?php while ($row = $result_feedback->fetch_assoc()): ?>
                                                        <tr>
                                                            <td><strong>#<?php echo $row['id']; ?></strong></td>
                                                            <td><span class="text-info"><?php echo htmlspecialchars($row['username']); ?></span></td>
                                                            
                                                            <!-- VULNERABLE RENDER: Rendering DB outputs raw to allow Stored XSS -->
                                                            <td><?php echo $row['message']; ?></td>
                                                            
                                                            <td class="text-center">
                                                                <button class="btn btn-sm btn-success me-1 py-1 px-2">Approve</button>
                                                                <button class="btn btn-sm btn-danger py-1 px-2">Delete</button>
                                                            </td>
                                                        </tr>
                                                    <?php endwhile; ?>
                                                <?php else: ?>
                                                    <tr>
                                                        <td colspan="4" class="text-center py-4 text-muted">No pending feedback logs discovered in the queue database.</td>
                                                    </tr>
                                                <?php endif; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Middle Right Column: System Info & Security Logs -->
                        <div class="col-lg-4">
                            <!-- Card 1: Server System Statistics -->
                            <div class="card card-admin mb-4">
                                <div class="card-header-admin header-stats">
                                    Server System Statistics
                                </div>
                                <div class="card-body p-4">
                                    <div class="row mb-2">
                                        <div class="col-6 text-light small" style="color: #cbd5e1 !important;">System OS:</div>
                                        <div class="col-6 text-end text-white small">Linux/Docker</div>
                                    </div>
                                    <div class="row mb-2">
                                        <div class="col-6 text-light small" style="color: #cbd5e1 !important;">DB Connection:</div>
                                        <div class="col-6 text-end text-success fw-bold small">Connected</div>
                                    </div>
                                    <div class="row mb-2">
                                        <div class="col-6 text-light small" style="color: #cbd5e1 !important;">PHP Engine:</div>
                                        <div class="col-6 text-end text-white small">PHP 8.x</div>
                                    </div>
                                </div>
                            </div>

                            <!-- Card 2: Recent Security & Audit Logs (Red Accent Border) -->
                            <div class="card card-admin">
                                <div class="card-header-admin header-logs">
                                    Recent Security & Audit Logs
                                </div>
                                <div class="card-body p-4">
                                    <div class="log-container">
                                        <div class="log-item">
                                            <span class="log-time">[02:30:11]</span> 
                                            <span class="log-warn">unauthorized access attempt blocked on admin.php from IP 192.168.1.102</span>
                                        </div>
                                        <div class="log-item">
                                            <span class="log-time">[02:15:45]</span> 
                                            <span class="log-info">successful database backup created</span>
                                        </div>
                                        <div class="log-item">
                                            <span class="log-time">[01:58:22]</span> 
                                            <span class="log-info">session expired for user 'student01'</span>
                                        </div>
                                        <div class="log-item">
                                            <span class="log-time">[01:45:00]</span> 
                                            <span class="log-info">routine cron job cleanup executed</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>

                </div>
            </div>
        </div>

    </div> <!-- End flex-grow-1 wrapper -->

    <!-- Footer placed outside main wrapper to cling to viewport bottom -->
    <footer class="text-center py-4 mt-auto">
        <div class="container">
            <p class="mb-2"><strong>MyEduConnect &copy; 2026</strong></p>
            <p class="mb-0 text-muted" style="font-size: 0.8rem;">
                Authorized access only. Subject to the Personal Data Protection Act (PDPA) 2010 of Malaysia.
            </p>
        </div>
    </footer>

</body>
</html>
