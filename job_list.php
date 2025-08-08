<?php
session_start();
include 'db.php';
$category = $_GET['category'] ?? '';
$budget = $_GET['budget'] ?? '';
$rate_type = $_GET['rate_type'] ?? '';
$query = "SELECT * FROM jobs WHERE 1=1";
$params = [];
$types = '';
if ($category) {
    $query .= " AND category = ?";
    $params[] = $category;
    $types .= 's';
}
if ($budget) {
    $query .= " AND budget <= ?";
    $params[] = $budget;
    $types .= 'd';
}
if ($rate_type) {
    $query .= " AND rate_type = ?";
    $params[] = $rate_type;
    $types .= 's';
}
$stmt = $conn->prepare($query);
if ($params) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$jobs = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Job Listings</title>
    <style>
        body { background: #f5f7fa; font-family: Arial, sans-serif; }
        .job-list-container { max-width: 1200px; margin: 20px auto; padding: 20px; }
        .filter-form { background: #fff; padding: 20px; border-radius: 8px; box-shadow: 0 4px 8px rgba(0,0,0,0.1); margin-bottom: 20px; }
        .filter-form select, .filter-form input { padding: 10px; margin-right: 10px; border: 1px solid #ddd; border-radius: 5px; }
        .job-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 20px; }
        .job-card { background: #fff; padding: 20px; border-radius: 8px; box-shadow: 0 4px 8px rgba(0,0,0,0.1); transition: transform 0.3s; }
        .job-card:hover { transform: translateY(-5px); }
        .job-card h3 { color: #1a73e8; }
        button { background: #1a73e8; color: white; padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer; }
        button:hover { background: #0d47a1; }
        a { color: #1a73e8; text-decoration: none; }
        a:hover { text-decoration: underline; }
        @media (max-width: 768px) { .job-grid { grid-template-columns: 1fr; } .filter-form select, .filter-form input { margin-bottom: 10px; } }
    </style>
</head>
<body>
    <div class="job-list-container">
        <h2>Find Jobs</h2>
        <form class="filter-form" method="GET" onsubmit="return validateForm()">
            <select name="category">
                <option value="">All Categories</option>
                <option value="Web Development" <?php echo $category == 'Web Development' ? 'selected' : ''; ?>>Web Development</option>
                <option value="Graphic Design" <?php echo $category == 'Graphic Design' ? 'selected' : ''; ?>>Graphic Design</option>
                <option value="Writing" <?php echo $category == 'Writing' ? 'selected' : ''; ?>>Writing</option>
                <option value="Marketing" <?php echo $category == 'Marketing' ? 'selected' : ''; ?>>Marketing</option>
            </select>
            <input type="number" name="budget" value="<?php echo $budget; ?>" placeholder="Max Budget">
            <select name="rate_type">
                <option value="">All Rates</option>
                <option value="hourly" <?php echo $rate_type == 'hourly' ? 'selected' : ''; ?>>Hourly</option>
                <option value="fixed" <?php echo $rate_type == 'fixed' ? 'selected' : ''; ?>>Fixed</option>
            </select>
            <button type="submit">Filter</button>
        </form>
        <div class="job-grid">
            <?php if ($jobs->num_rows > 0): ?>
                <?php while ($job = $jobs->fetch_assoc()): ?>
                    <div class="job-card">
                        <h3><?php echo htmlspecialchars($job['title']); ?></h3>
                        <p><?php echo htmlspecialchars($job['description']); ?></p>
                        <p>Budget: $<?php echo $job['budget']; ?> (<?php echo $job['rate_type']; ?>)</p>
                        <p>Deadline: <?php echo $job['deadline']; ?></p>
                        <button onclick="navigate('proposal.php?job_id=<?php echo $job['id']; ?>')">Apply</button>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <p>No jobs found.</p>
            <?php endif; ?>
        </div>
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
