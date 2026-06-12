<?php
session_start();
require_once 'db_connect.php';

if (!isset($_SESSION['user'])){
    header("Location: index.php");
    exit;
}

$user = $_SESSION['user'];
$user_id = $user['id'];
$search = $_GET['search'] ?? '';

$courses = [];
$table_exists = false;
$check_table = $conn->query("SHOW TABLES LIKE 'courses'");
if ($check_table && $check_table->num_rows > 0){
    $table_exists = true;
}

if ($table_exists){
    if ($search !== '') {
        $stmt = $conn->prepare("SELECT * FROM courses WHERE course_name LIKE ?");
        $search_term = "%" . $search . "%";
        $stmt->bind_param("s", $search_term);
        $stmt->execute();
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()){
            $courses[] = $row;
        }
        $stmt->close();
    } else {
        $result = $conn->query("SELECT * FROM courses");
        if ($result) {
            while ($row = $result->fetch_assoc()){
                $courses[] = $row;
            }
        }
    }
} else {
    $all_mock_courses = [
        ['id' => 1, 'course_name' => 'Introduction to Python Programming', 'price' => 120.00, 'category' => 'Technology'],
        ['id' => 2, 'course_name' => 'SPM Physics Revision Masterclass', 'price' => 95.00, 'category' => 'Science'],
        ['id' => 3, 'course_name' => 'Principles of Financial Accounting', 'price' => 110.00, 'category' => 'Business'],
        ['id' => 4, 'course_name' => 'Web Development Fundamentals (HTML/CSS)', 'price' => 130.00, 'category' => 'Technology'],
        ['id' => 5, 'course_name' => 'Introduction to Cybersecurity & Networks', 'price' => 150.00, 'category' => 'Security'],
        ['id' => 6, 'course_name' => 'Creative Multimedia Design Basics', 'price' => 105.00, 'category' => 'Arts']
    ];

    if ($search !== '') {
        foreach ($all_mock_courses as $mc) {
            if (stripos($mc['course_name'], $search) !== false){
                $courses[] = $mc;
            }
        }
    } else {
        $courses = $all_mock_courses;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Academic Courses - MyEduConnect</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        :root{
            --mmu-dark: #1E1E24;
            --slate-bg: #2f3542;
            --slate-hover: #57606f;
            --slate-text: #ced6e0;
            --header-blue: #0A3663;
            --header-teal: #079992;
        }
        body{
            background-color: #f8f9fa;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        .sidebar-slate{
            background-color: var(--slate-bg);
            color: #ffffff;
        }
        .sidebar-slate .sidebar-heading{
            font-weight: 700;
            color: #ffffff;
            border-bottom: 1px solid var(--slate-hover);
        }
        .sidebar-slate .list-group-item{
            color: var(--slate-text);
            background: transparent;
            border: none;
            transition: all 0.2s ease;
        }
        .sidebar-slate .list-group-item:hover{
            color: #ffffff;
            background-color: var(--slate-hover);
        }
        .sidebar-slate .list-group-item.active{
            color: #ffffff;
            background-color: var(--header-blue);
            font-weight: 600;
        }
        
        .card-custom{
            border: none;
            border-radius: 10px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.05);
            margin-bottom: 25px;
            overflow: hidden;
            transition: transform 0.2s ease;
        }
        .card-custom:hover{
            transform: translateY(-3px);
        }
        .card-header-custom{
            background-color: var(--header-blue);
            color: white;
            font-weight: 600;
            font-size: 1.1rem;
            padding: 15px 20px;
        }
        
        footer{
            background-color: var(--mmu-dark);
            color: #d1d1d1;
            padding: 20px 0;
            font-size: 0.9rem;
            border-top: 3px solid #D12B2B;
        }
    </style>
</head>
<body class="d-flex flex-column min-vh-100">

    <div class="flex-grow-1">
        
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

                <div class="col-md-9 col-lg-10 py-4 px-4 bg-light">
                    
                    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4">
                        <h2 class="text-dark">Explore Educational Courses</h2>
                        
                        <form action="courses.php" method="GET" class="d-flex mt-2 mt-md-0 col-md-5 col-lg-4 px-0">
                            <input class="form-control me-2" type="search" name="search" placeholder="Search courses..." aria-label="Search" value="<?php echo htmlspecialchars($search); ?>">
                            <button class="btn btn-outline-dark" type="submit">Search</button>
                        </form>
                    </div>

                    <?php if ($search !== ''): ?>
                        <div class="alert alert-info mb-4 py-2">
                            Showing results for "<strong><?php echo htmlspecialchars($search); ?></strong>"
                            <a href="courses.php" class="float-end text-decoration-none text-secondary">Clear Search</a>
                        </div>
                    <?php endif; ?>

                    <div class="row">
                        <?php if (count($courses) > 0): ?>
                            <?php foreach ($courses as $course): ?>
                                <div class="col-md-6 col-lg-4">
                                    <div class="card card-custom h-100 bg-white">
                                        <div class="card-header card-header-custom">
                                            <?php echo htmlspecialchars($course['course_name']); ?>
                                        </div>
                                        <div class="card-body d-flex flex-column p-4">
                                            <span class="badge bg-light text-secondary mb-3 align-self-start">
                                                <?php echo htmlspecialchars($course['category'] ?? 'Academic'); ?>
                                            </span>
                                            
                                            <h3 class="text-dark mb-4">RM <?php echo number_format($course['price'], 2); ?></h3>
                                            
                                            <div class="mt-auto">
                                                <a href="payment.php?course_id=<?php echo urlencode($course['id']); ?>&price=<?php echo urlencode($course['price']); ?>" class="btn btn-dark w-100 py-2">
                                                    Enroll Now
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="col-12 text-center py-5">
                                <p class="text-muted">No courses found matching your query.</p>
                            </div>
                        <?php endif; ?>
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
