<?php
session_start();
include 'db.php';
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'freelancer') {
    echo "<script>navigate('login.php')</script>";
    exit;
}
$job_id = $_GET['job_id'] ?? 0;
$job = $conn->query("SELECT * FROM jobs WHERE id = " . intval($job_id))->fetch_assoc();
if (!$job) {
    $error = "Job not found.";
}
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $cover_letter = $_POST['cover_letter'] ?? '';
    $bid_amount = $_POST['bid_amount'] ?? '';
    $freelancer_id = $_SESSION['user_id'];
    if (empty($cover_letter) || empty($bid_amount)) {
        $error = "All fields are required.";
    } else {
        $stmt = $conn->prepare("INSERT INTO proposals (job_id, freelancer_id, cover_letter, bid_amount) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("iisd", $job_id, $freelancer_id, $cover_letter, $bid_amount);
        if ($stmt->execute()) {
            echo "<script>navigate('job_list.php')</script>";
            exit;
        } else {
            $error = "Proposal submission failed: " . $conn->error;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Submit Proposal</title>
    <style>
        body { background: #f5f7fa; font-family: Arial, sans-serif; }
        .proposal-container { max-width: 800px; margin: 20px auto; background: #fff; padding: 40px; border-radius: 10px; box-shadow: 0 4px 10px rgba(0,0,0,0.2); }
        h2 { color: #1a73e8; margin-bottom: 20px; }
        .form-group { margin-bottom: 15px; }
        label { display: block; margin-bottom: 5px; font-weight: bold; }
        input, textarea { width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 5px; }
        button { background: #1a73e8; color: white; padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer; }
        button:hover { background: #0d47a1; }
        .error { color: red; text-align: center; }
        a { color: #1a73e8; text-decoration: none; }
        a:hover { text-decoration: underline; }
        @media (max-width: 768px) { .proposal-container { padding: 20px; } }
    </style>
</head>
<body>
    <div class="proposal-container">
        <h2>Submit Proposal for <?php echo htmlspecialchars($job['title'] ?? 'Job'); ?></h2>
        <?php if (isset($error)): ?>
            <p class="error"><?php echo $error; ?></p>
        <?php endif; ?>
        <form method="POST" onsubmit="return validateForm()">
            <div class="form-group">
                <label for="cover_letter">Cover Letter</label>
                <textarea id="cover_letter" name="cover_letter" required></textarea>
            </div>
            <div class="form-group">
                <label for="bid_amount">Bid Amount ($)</label>
                <input type="number" id="bid_amount" name="bid_amount" required>
            </div>
            <button type="submit">Submit Proposal</button>
        </form>
        <a href="#" onclick="navigate('job_list.php')">Back to Jobs</a>
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
