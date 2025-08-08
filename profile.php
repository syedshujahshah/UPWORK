<?php
session_start();
include 'db.php';
if (!isset($_SESSION['user_id'])) {
    echo "<script>navigate('login.php')</script>";
    exit;
}
$user_id = $_SESSION['user_id'];
$stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $_POST['name'] ?? '';
    $skills = $_POST['skills'] ?? '';
    $experience = $_POST['experience'] ?? '';
    $profile_picture = $_FILES['profile_picture']['name'] ?? '';
    if ($profile_picture) {
        $target_dir = "uploads/";
        if (!is_dir($target_dir)) {
            mkdir($target_dir, 0755, true);
        }
        $target_file = $target_dir . basename($profile_picture);
        if (move_uploaded_file($_FILES['profile_picture']['tmp_name'], $target_file)) {
            $stmt = $conn->prepare("UPDATE users SET name = ?, skills = ?, experience = ?, profile_picture = ? WHERE id = ?");
            $stmt->bind_param("ssssi", $name, $skills, $experience, $profile_picture, $user_id);
        } else {
            $error = "Failed to upload profile picture.";
        }
    } else {
        $stmt = $conn->prepare("UPDATE users SET name = ?, skills = ?, experience = ? WHERE id = ?");
        $stmt->bind_param("sssi", $name, $skills, $experience, $user_id);
    }
    if ($stmt->execute()) {
        echo "<script>navigate('profile.php')</script>";
        exit;
    } else {
        $error = "Update failed: " . $conn->error;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile</title>
    <style>
        body { background: #f5f7fa; font-family: Arial, sans-serif; }
        .profile-container { max-width: 800px; margin: 20px auto; background: #fff; padding: 40px; border-radius: 10px; box-shadow: 0 4px 10px rgba(0,0,0,0.2); }
        h2 { color: #1a73e8; margin-bottom: 20px; }
        .form-group { margin-bottom: 15px; }
        label { display: block; margin-bottom: 5px; font-weight: bold; }
        input, textarea { width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 5px; }
        button { background: #1a73e8; color: white; padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer; }
        button:hover { background: #0d47a1; }
        img { width: 150px; height: 150px; border-radius: 50%; object-fit: cover; margin-bottom: 20px; }
        .error { color: red; text-align: center; }
        a { color: #1a73e8; text-decoration: none; }
        a:hover { text-decoration: underline; }
        @media (max-width: 768px) { .profile-container { padding: 20px; } }
    </style>
</head>
<body>
    <div class="profile-container">
        <h2><?php echo $_SESSION['role'] == 'freelancer' ? 'Freelancer Profile' : 'Client Profile'; ?></h2>
        <?php if (isset($error)): ?>
            <p class="error"><?php echo $error; ?></p>
        <?php endif; ?>
        <img src="<?php echo $user['profile_picture'] ?: 'default.jpg'; ?>" alt="Profile Picture">
        <form method="POST" enctype="multipart/form-data" onsubmit="return validateForm()">
            <div class="form-group">
                <label for="name">Name</label>
                <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($user['name']); ?>" required>
            </div>
            <?php if ($_SESSION['role'] == 'freelancer'): ?>
                <div class="form-group">
                    <label for="skills">Skills</label>
                    <textarea id="skills" name="skills"><?php echo htmlspecialchars($user['skills']); ?></textarea>
                </div>
                <div class="form-group">
                    <label for="experience">Experience</label>
                    <textarea id="experience" name="experience"><?php echo htmlspecialchars($user['experience']); ?></textarea>
                </div>
            <?php endif; ?>
            <div class="form-group">
                <label for="profile_picture">Profile Picture</label>
                <input type="file" id="profile_picture" name="profile_picture" accept="image/*">
            </div>
            <button type="submit">Update Profile</button>
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
