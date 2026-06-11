<?php
// payment.php
session_start();
require_once 'db_connect.php';

// Redirect back to index.php if not logged in
if (!isset($_SESSION['user'])) {
    header("Location: index.php");
    exit;
}

$user = $_SESSION['user'];
$user_id = $user['id'];

// Get course data from URL GET parameters
$course_id = intval($_GET['course_id'] ?? 0);
$price = $_GET['price'] ?? '0.00';

// Mock list of courses matching courses.php for display lookup
$mock_courses = [
    1 => 'Introduction to Python Programming',
    2 => 'SPM Physics Revision Masterclass',
    3 => 'Principles of Financial Accounting',
    4 => 'Web Development Fundamentals (HTML/CSS)',
    5 => 'Introduction to Cybersecurity & Networks',
    6 => 'Creative Multimedia Design Basics'
];

$course_name = $mock_courses[$course_id] ?? 'Selected Academic Course';

$payment_status = "";
$processed = false;

// Handle POST processing for payment
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // VULNERABLE LOGIC: The backend takes the transaction amount directly from $_POST['amount'].
    // This value is passed via a hidden input field and can be altered in the client-side DOM.
    $amount = $_POST['amount'] ?? '0.00';
    $post_course_id = intval($_POST['course_id'] ?? 0);
    $course_name = $mock_courses[$post_course_id] ?? 'Academic Course';
    
    // Mock processing logic
    $card_number = $_POST['card_number'] ?? '';
    $expiry = $_POST['expiry_date'] ?? '';
    $cvv = $_POST['cvv'] ?? '';
    
    if (!empty($card_number) && !empty($expiry) && !empty($cvv)) {
        $payment_status = "<div class='alert alert-success'>
            <h4>Payment Successful!</h4>
            <p class='mb-0'>A total transaction of <strong>RM " . htmlspecialchars($amount) . "</strong> has been processed.</p>
            <p class='mb-0'>You have successfully enrolled in <strong>" . htmlspecialchars($course_name) . "</strong>.</p>
        </div>";
        $processed = true;
    } else {
        $payment_status = "<div class='alert alert-danger'>Error: Please fill in all payment details.</div>";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Course Payment - MyEduConnect</title>
    <!-- Bootstrap 5 CDN -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        :root {
            --mmu-dark: #1E1E24;
            --slate-bg: #2f3542;
            --slate-hover: #57606f;
            --slate-text: #ced6e0;
            --header-blue: #0A3663;
            --header-teal: #079992;
        }
        body {
            background-color: #f8f9fa;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        /* Left-Sidebar Styling */
        .sidebar-slate {
            background-color: var(--slate-bg);
            color: #ffffff;
        }
        .sidebar-slate .sidebar-heading {
            font-weight: 700;
            color: #ffffff;
            border-bottom: 1px solid var(--slate-hover);
        }
        .sidebar-slate .list-group-item {
            color: var(--slate-text);
            background: transparent;
            border: none;
            transition: all 0.2s ease;
        }
        .sidebar-slate .list-group-item:hover {
            color: #ffffff;
            background-color: var(--slate-hover);
        }
        .sidebar-slate .list-group-item.active {
            color: #ffffff;
            background-color: var(--header-blue);
            font-weight: 600;
        }
        
        .card-custom {
            border: none;
            border-radius: 10px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.05);
            margin-bottom: 25px;
            overflow: hidden;
        }
        .card-header-custom {
            background-color: var(--header-blue);
            color: white;
            font-weight: 600;
            font-size: 1.1rem;
            padding: 15px 20px;
        }
        
        footer {
            background-color: var(--mmu-dark);
            color: #d1d1d1;
            padding: 20px 0;
            font-size: 0.9rem;
            border-top: 3px solid #D12B2B;
        }
    </style>
</head>
<body class="d-flex flex-column min-vh-100">

    <!-- Wrapper for flex-grow to push footer down -->
    <div class="flex-grow-1">
        
        <!-- Top Navbar -->
        <nav class="navbar navbar-dark bg-dark px-3 py-2 shadow-sm">
            <div class="container-fluid">
                <span class="navbar-brand mb-0 h1">MyEduConnect Student Portal</span>
                <span class="navbar-text text-white d-none d-sm-inline">
                    Welcome, <strong><?php echo htmlspecialchars($user['username']); ?></strong>
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
                        <a href="courses.php" class="list-group-item list-group-item-action active rounded mb-1">Courses</a>
                        <a href="upload.php" class="list-group-item list-group-item-action rounded mb-1">Coursework</a>
                        <a href="feedback.php" class="list-group-item list-group-item-action rounded mb-1">Feedback</a>
                        <?php if (isset($_SESSION['user']) && $_SESSION['user']['role'] === 'admin'): ?>
                            <a href="admin.php" class="list-group-item list-group-item-action rounded mb-1">Admin Panel</a>
                        <?php endif; ?>
                        <a href="logout.php" class="list-group-item list-group-item-action rounded text-danger mt-4">Logout</a>
                    </div>
                </div>

                <!-- Main Content Column -->
                <div class="col-md-9 col-lg-10 py-4 px-4 bg-light">
                    
                    <div class="row justify-content-center">
                        <div class="col-xl-8 col-lg-10 col-md-12">
                            
                            <!-- Payment Processing Card -->
                            <div class="card card-custom">
                                <div class="card-header card-header-custom">
                                    Secure Course Enrollment Checkout
                                </div>
                                <div class="card-body p-4">
                                    
                                    <?php echo $payment_status; ?>

                                    <?php if (!$processed): ?>
                                        <h4 class="mb-4">Invoice Summary</h4>
                                        <div class="row mb-3 bg-light p-3 rounded mx-0 border">
                                            <div class="col-sm-8">
                                                <strong>Course Name:</strong><br>
                                                <?php echo htmlspecialchars($course_name); ?>
                                            </div>
                                            <div class="col-sm-4 text-sm-end mt-2 mt-sm-0">
                                                <strong>Price:</strong><br>
                                                <span class="text-primary h5">RM <?php echo htmlspecialchars(number_format(floatval($price), 2)); ?></span>
                                            </div>
                                        </div>

                                        <form action="payment.php" method="POST" class="mt-4">
                                            
                                            <!-- VULNERABLE HIDDEN PARAMETER: Handed to client-side DOM -->
                                            <!-- Attack: Tamper with value="0.01" to process transaction at lower cost -->
                                            <input type="hidden" name="course_id" value="<?php echo htmlspecialchars($course_id); ?>">
                                            <input type="hidden" name="amount" value="<?php echo htmlspecialchars($price); ?>">
                                            
                                            <h5 class="mb-3">Billing Information</h5>
                                            
                                            <div class="mb-3">
                                                <label for="card_number" class="form-label">Credit Card Number</label>
                                                <input type="text" class="form-control" id="card_number" name="card_number" placeholder="4111 2222 3333 4444" required>
                                            </div>
                                            
                                            <div class="row">
                                                <div class="col-md-6 mb-3">
                                                    <label for="expiry_date" class="form-label">Expiry Date</label>
                                                    <input type="text" class="form-control" id="expiry_date" name="expiry_date" placeholder="MM/YY" required>
                                                </div>
                                                <div class="col-md-6 mb-3">
                                                    <label for="cvv" class="form-label">CVV</label>
                                                    <input type="password" class="form-control" id="cvv" name="cvv" placeholder="123" maxlength="3" required>
                                                </div>
                                            </div>

                                            <div class="d-flex justify-content-between mt-4">
                                                <a href="courses.php" class="btn btn-outline-secondary">Back to Courses</a>
                                                <button type="submit" class="btn btn-primary" style="background-color: var(--header-blue); border: none;">Submit Payment</button>
                                            </div>
                                        </form>
                                    <?php else: ?>
                                        <div class="text-center mt-3">
                                            <a href="courses.php" class="btn btn-primary" style="background-color: var(--header-blue); border: none;">Return to Courses Directory</a>
                                        </div>
                                    <?php endif; ?>

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
