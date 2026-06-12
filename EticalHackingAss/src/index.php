<?php
session_start();
require_once 'db_connect.php';

$login_error = "";
$logged_in_user = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';

    $sql = "SELECT * FROM users WHERE username = '$username' AND password = '$password'";
    
    $result = $conn->query($sql);

    if ($result) {
        if ($result->num_rows > 0) {
            $logged_in_user = $result->fetch_assoc();
            $_SESSION['user'] = $logged_in_user;
            header("Location: dashboard.php");
            exit;
        } else {
            $login_error = "Invalid username or password.";
        }
    } else {
        $login_error = "Database Error: " . $conn->error . "<br>Executed Query: " . $sql;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MyEduConnect - Integrated Learning Portal</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        :root {
            --mmu-blue: #0A3663;
            --mmu-red: #D12B2B;
            --mmu-gold: #DAA520;
            --mmu-dark: #1E1E24;
        }
        body {
            background-color: #f8f9fa;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .navbar-brand-custom {
            font-weight: 700;
            color: var(--mmu-blue) !important;
        }
        .header-banner {
            background: linear-gradient(135deg, var(--mmu-blue) 0%, #1e5799 100%);
            color: white;
            padding: 40px 20px;
            border-bottom: 5px solid var(--mmu-gold);
            margin-bottom: 40px;
        }
        .card-login {
            border: none;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }
        .card-login-header {
            background-color: var(--mmu-blue);
            color: white;
            font-weight: 600;
            border-top-left-radius: 12px;
            border-top-right-radius: 12px;
        }
        .btn-mmu {
            background-color: var(--mmu-red);
            color: white;
            border: none;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        .btn-mmu:hover {
            background-color: #a72222;
            color: white;
        }
        .card-support {
            border: none;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.05);
        }
        .card-support-header {
            background-color: var(--mmu-dark);
            color: white;
            font-weight: 600;
            border-top-left-radius: 12px;
            border-top-right-radius: 12px;
        }
        footer {
            background-color: var(--mmu-dark);
            color: #d1d1d1;
            padding: 20px 0;
            font-size: 0.9rem;
            border-top: 3px solid var(--mmu-red);
        }
    </style>
</head>
<body class="d-flex flex-column min-vh-100">

    <div class="flex-grow-1">
        
        <nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm">
            <div class="container">
                <span class="navbar-brand navbar-brand-custom">
                    <span style="color: var(--mmu-red);">MyEduConnect</span>
                </span>
                <span class="navbar-text d-none d-sm-inline">
                    Integrated Learning Portal
                </span>
            </div>
        </nav>

        <div class="header-banner text-center">
            <h1 class="display-5">MyEduConnect</h1>
            <p class="lead">Integrated Learning Portal</p>
        </div>

        <div class="container">
            <div class="row">
                <div class="col-md-6 mb-4">
                    <div class="card card-login">
                        <div class="card-header card-login-header text-center py-3">
                            <h4 class="mb-0">Portal Login</h4>
                        </div>
                        <div class="card-body p-4">
                            <?php if ($logged_in_user): ?>
                                <div class="alert alert-success">
                                    <strong>Login Successful!</strong> Welcome, <?php echo htmlspecialchars($logged_in_user['username']); ?> (Role: <?php echo htmlspecialchars($logged_in_user['role']); ?>).
                                </div>
                                <div class="d-grid">
                                    <a href="dashboard.php" class="btn btn-primary">Go to Student Dashboard</a>
                                </div>
                            <?php else: ?>
                                <?php if (!empty($login_error)): ?>
                                    <div class="alert alert-danger">
                                        <?php echo $login_error; ?>
                                    </div>
                                <?php endif; ?>

                                <form action="index.php" method="POST">
                                    <div class="mb-3">
                                        <label for="username" class="form-label">Student / Staff ID</label>
                                        <input type="text" class="form-control" id="username" name="username" placeholder="e.g. 1181102345" required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="password" class="form-label">Portal Password</label>
                                        <input type="password" class="form-control" id="password" name="password" required>
                                    </div>
                                    
                                    <div class="row g-2">
                                        <div class="col-8">
                                            <button type="submit" class="btn btn-mmu w-100 py-2">Login</button>
                                        </div>
                                        <div class="col-4">
                                            <button type="reset" class="btn btn-secondary w-100 py-2">Clear</button>
                                        </div>
                                    </div>
                                </form>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <div class="col-md-6 mb-4">
                    <div class="card card-support h-100">
                        <div class="card-header card-support-header py-3">
                            <h4 class="mb-0">Support & Assistance</h4>
                        </div>
                        <div class="card-body p-4">
                            <p class="text-muted mb-3">Dear all,</p>
                            <p class="mb-3">
                                If you have any technical difficulty to login to the Student Portal, please drop us an email to <a href="mailto:helpdesk@myeduconnect.edu.my">helpdesk@myeduconnect.edu.my</a> with the following information:
                            </p>
                            <ol class="mb-4">
                                <li class="mb-1">Your name:</li>
                                <li class="mb-1">Your Student/Staff ID:</li>
                                <li class="mb-1">Your contact Number:</li>
                                <li class="mb-1">Type of problem:</li>
                                <li class="mb-1">Type of browser used (e.g. Chrome, Firefox):</li>
                                <li class="mb-1">Screenshots/screen dump of error (If available)</li>
                            </ol>
                            <p class="mb-0 text-end text-dark"><strong>MyEduConnect ITD</strong></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div> 

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
