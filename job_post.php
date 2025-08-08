<?php
session_start();
include 'db.php';
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'client') {
    echo "<script>navigate('login.php')</script>";
    exit;
}
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $title = $_POST['title'] ?? '';
    $description = $_POST['description'] ?? '';
    $budget = $_POST['budget'] ?? '';
    $deadline = $_POST['deadline'] ?? '';
    $category = $_POST['category'] ?? '';
    $rate_type = $_POST['rate_type'] ?? 'fixed';
    $client_id = $_SESSION['user_id'];
    if (empty($title) || empty($description) || empty($budget) || empty($deadline) || empty($category)) {
        $error = "All fields are required.";
    } else {
        $stmt = $conn->prepare("INSERT INTO jobs (title, description, budget, deadline, category, rate_type, client_id) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssdsssi", $title, $description, $budget, $deadline, $category, $rate_type, $client_id);
        if ($stmt->execute()) {
            echo "<script>navigate('job_list.php')</script>";
            exit;
        } else {
            $error = "Job posting failed: " . $conn->error;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Post a Job</title>
    <style>
        body { background: #f5f7fa; font-family: Arial, sans-serif; }
        .job-post-container { max-width: 800px; margin: 20px auto; background: #fff; padding: 40px; border-radius: 10px; box-shadow: 0 4px 10px rgba(0,0,0,0.2); }
        h2 { color: #1a73e8; margin-bottom: 20px; }
        .form-group { margin-bottom: 15px; }
        label { display: block; margin-bottom: 5px; font-weight: bold; }
        input, textarea, select { width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 5px; }
        button { background: #1a73e8; color: white; padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer; }
        button:hover { background: #0d47a1; }
        .error { color: red; text-align: center; }
        a { color: #1a73e8; text-decoration: none; }
        a:hover { text-decoration: underline; }
        @media (max-width: 768px) { .job-post-container { padding: 20px; } }
    </style>
</head>
<body>
    <div class="job-post-container">
        <h2>Post a Job</h2>
        <?php if (isset($error)): ?>
            <p class="error"><?php echo $error; ?></p>
        <?php endif; ?>
        <form method="POST" onsubmit="return validateForm()">
            <div class="form-group">
                <label for="title">Job Title</label>
                <input type="text" id="title" name="title" required>
            </div>
            <div class="form-group">
                <label for="description">Description</label>
                <textarea id="description" name="description" required></textarea>
            </div>
            <div class="form-group">
                <label for="budget">Budget ($)</label>
                <input type="number" id="budget" name="budget" required>
            </div>
            <div class="form-group">
                <label for="deadline">Deadline</label>
                <input type="date" id="deadline" name="deadline" required>
            </div>
            <div class="form-group">
                <label for="category">Category</label>
                <select id="category" name="category" required>
                    <option value="Web Development">Web Development</option>
                    <option value="Graphic Design">Graphic Design</option>
                    <option value="Writing">Writing</option>
                    <option value="Marketing">Marketing</option>
                </select>
            </div>
            <div class="form-group">
                <label for="rate_type">Rate Type</label>
                <select id="rate_type" name="rate_type" required>
                    <option value="fixed">Fixed</option>
                    <option value="hourly">Hourly</option>
                </select>
            </div>
            <button type="submit">Post Job</button>
        </form>
        <a href="#" onclick="navigate('index.php')">Back to Home</a>
    </div>
    <script src="script.js"></script>
    <script>
        function validateForm() {
            console.log("Form submitted"); // Debug log
            return true;
        }
    </script>
</body>
</html>
