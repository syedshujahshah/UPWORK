<?php
include 'db.php';
session_start();
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = trim($_POST['password'] ?? '');
    $role = trim($_POST['role'] ?? '');

    // Log POST data for debugging
    error_log("POST data: name=$name, email=$email, role=$role");

    if (empty($name) || empty($email) || empty($password) || empty($role)) {
        $error = "All fields are required.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Invalid email format.";
    } elseif (strlen($password) < 6) {
        $error = "Password must be at least 6 characters long.";
    } else {
        try {
            // Check if email already exists
            $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
            if (!$stmt) {
                $error = "Database query preparation failed: " . $conn->error;
                error_log("Prepare failed: " . $conn->error);
            } else {
                $stmt->bind_param("s", $email);
                $stmt->execute();
                $result = $stmt->get_result();
                if ($result->num_rows > 0) {
                    $error = "Email already registered. Please use a different email or log in.";
                } else {
                    // Proceed with registration
                    $password_hash = password_hash($password, PASSWORD_DEFAULT);
                    $stmt = $conn->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, ?)");
                    if (!$stmt) {
                        $error = "Database prepare failed: " . $conn->error;
                        error_log("Insert prepare failed: " . $conn->error);
                    } else {
                        $stmt->bind_param("ssss", $name, $email, $password_hash, $role);
                        if ($stmt->execute()) {
                            $success = "Registration successful! Redirecting to login...";
                            echo "<script>setTimeout(() => navigate('login.php'), 2000);</script>";
                            exit;
                        } else {
                            $error = "Registration failed: " . $stmt->error;
                            error_log("Insert failed: " . $stmt->error);
                        }
                        $stmt->close();
                    }
                }
                $stmt->close();
            }
        } catch (Exception $e) {
            $error = "An error occurred: " . $e->getMessage();
            error_log("Signup exception: " . $e->getMessage());
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign Up</title>
    <style>
        body { background: #f5f7fa; font-family: Arial, sans-serif; display: flex; justify-content: center; align-items: center; height: 100vh; }
        .signup-container { background: #fff; padding: 40px; border-radius: 10px; box-shadow: 0 4px 10px rgba(0,0,0,0.2); width: 100%; max-width: 400px; }
        h2 { text-align: center; color: #1a73e8; margin-bottom: 20px; }
        .form-group { margin-bottom: 15px; }
        label { display: block; margin-bottom: 5px; font-weight: bold; }
        input, select { width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 5px; }
        button { width: 100%; background: #1a73e8; color: white; padding: 10px; border: none; border-radius: 5px; cursor: pointer; }
        button:hover { background: #0d47a1; }
        .error { color: red; text-align: center; }
        .success { color: green; text-align: center; }
        a { color: #1a73e8; text-decoration: none; }
        a:hover { text-decoration: underline; }
        @media (max-width: 480px) { .signup-container { padding: 20px; } }
    </style>
</head>
<body>
    <div class="signup-container">
        <h2>Sign Up</h2>
        <?php if (!empty($error)): ?>
            <p class="error"><?php echo htmlspecialchars($error); ?></p>
        <?php endif; ?>
        <?php if (!empty($success)): ?>
            <p class="success"><?php echo htmlspecialchars($success); ?></p>
        <?php endif; ?>
        <form method="POST" onsubmit="return validateForm()">
            <div class="form-group">
                <label for="name">Name</label>
                <input type="text" id="name" name="name" value="<?php echo isset($_POST['name']) ? htmlspecialchars($_POST['name']) : ''; ?>" required>
            </div>
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>" required>
            </div>
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required>
            </div>
            <div class="form-group">
                <label for="role">Role</label>
                <select id="role" name="role" required>
                    <option value="freelancer" <?php echo (isset($_POST['role']) && $_POST['role'] == 'freelancer') ? 'selected' : ''; ?>>Freelancer</option>
                    <option value="client" <?php echo (isset($_POST['role']) && $_POST['role'] == 'client') ? 'selected' : ''; ?>>Client</option>
                </select>
            </div>
            <button type="submit">Sign Up</button>
        </form>
        <p>Already have an account? <a href="#" onclick="navigate('login.php')">Login</a></p>
    </div>
    <script src="/UPWORK/script.js"></script>
    <script>
        function validateForm() {
            console.log("Sign Up form submitted");
            const email = document.getElementById('email').value;
            const password = document.getElementById('password').value;
            if (!email.includes('@') || !email.includes('.')) {
                alert('Please enter a valid email address.');
                return false;
            }
            if (password.length < 6) {
                alert('Password must be at least 6 characters long.');
                return false;
            }
            return true;
        }
    </script>
</body>
</html>
