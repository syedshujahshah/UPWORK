<?php
session_start();
include 'db.php';
if ($conn->connect_error) {
    die("Database connection failed. Please try again later.");
}
$jobs = $conn->query("SELECT * FROM jobs LIMIT 6");
$freelancers = $conn->query("SELECT * FROM users WHERE role='freelancer' LIMIT 4");
if (!$jobs || !$freelancers) {
    $error = "Error fetching data: " . $conn->error;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Freelance Marketplace - Home</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: Arial, sans-serif; }
        body { background: #f5f7fa; color: #333; }
        header { background: #1a73e8; padding: 20px; color: white; text-align: center; }
        header h1 { font-size: 2.5em; }
        nav { display: flex; justify-content: center; gap: 20px; padding: 10px; background: #fff; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        nav a { text-decoration: none; color: #1a73e8; font-weight: bold; cursor: pointer; padding: 5px 10px; }
        nav a:hover { background: #e8f0fe; border-radius: 5px; }
        .container { max-width: 1200px; margin: 20px auto; padding: 0 20px; }
        .job-grid, .freelancer-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 20px; margin-bottom: 40px; }
        .job-card, .freelancer-card { background: #fff; padding: 20px; border-radius: 8px; box-shadow: 0 4px 8px rgba(0,0,0,0.1); transition: transform 0.3s; }
        .job-card:hover, .freelancer-card:hover { transform: translateY(-5px); }
        .job-card h3 { color: #1a73e8; }
        .freelancer-card img { width: 100px; height: 100px; border-radius: 50%; object-fit: cover; }
        button { background: #1a73e8; color: white; padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer; font-size: 1em; }
        button:hover { background: #0d47a1; }
        .error { color: red; text-align: center; }
        @media (max-width: 768px) { .job-grid, .freelancer-grid { grid-template-columns: 1fr; } nav { flex-direction: column; gap: 10px; } }
    </style>
</head>
<body>
    <header>
        <h1>Freelance Marketplace</h1>
        <nav>
            <a href="#" onclick="navigate('index.php')">Home</a>
            <a href="#" onclick="navigate('job_list.php')">Jobs</a>
            <?php if (isset($_SESSION['user_id'])): ?>
                <a href="#" onclick="navigate('profile.php')">Profile</a>
                <a href="#" onclick="navigate('messages.php')">Messages</a>
                <a href="#" onclick="navigate('logout.php')">Logout</a>
            <?php else: ?>
                <a href="#" onclick="navigate('login.php')">Login</a>
                <a href="#" onclick="navigate('signup.php')">Sign Up</a>
            <?php endif; ?>
        </nav>
    </header>
    <div class="container">
        <?php if (isset($error)): ?>
            <p class="error"><?php echo $error; ?></p>
        <?php endif; ?>
        <h2>Featured Jobs</h2>
        <div class="job-grid">
            <?php while ($job = $jobs->fetch_assoc()): ?>
                <div class="job-card">
                    <h3><?php echo htmlspecialchars($job['title']); ?></h3>
                    <p><?php echo htmlspecialchars($job['description']); ?></p>
                    <p>Budget: $<?php echo $job['budget']; ?></p>
                    <button onclick="navigate('proposal.php?job_id=<?php echo $job['id']; ?>')">Apply</button>
                </div>
            <?php endwhile; ?>
        </div>
        <h2>Top Freelancers</h2>
        <div class="freelancer-grid">
            <?php while ($freelancer = $freelancers->fetch_assoc()): ?>
                <div class="freelancer-card">
                    <img src="<?php echo $freelancer['profile_picture'] ?: 'default.jpg'; ?>" alt="Profile">
                    <h3><?php echo htmlspecialchars($freelancer['name']); ?></h3>
                    <p><?php echo htmlspecialchars($freelancer['skills']); ?></p>
                    <button onclick="navigate('profile.php?user_id=<?php echo $freelancer['id']; ?>')">View Profile</button>
                </div>
            <?php endwhile; ?>
        </div>
    </div>
    <script src="script.js"></script>
</body>
</html>
