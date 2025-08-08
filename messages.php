<?php
session_start();
include 'db.php';
if (!isset($_SESSION['user_id'])) {
    echo "<script>navigate('login.php')</script>";
    exit;
}
$user_id = $_SESSION['user_id'];
$messages = $conn->query("SELECT m.*, u.name FROM messages m JOIN users u ON m.sender_id = u.id WHERE m.receiver_id = $user_id OR m.sender_id = $user_id ORDER BY m.created_at DESC");
$contracts = $conn->query("SELECT c.*, j.title FROM contracts c JOIN jobs j ON c.job_id = j.id WHERE c.client_id = $user_id OR c.freelancer_id = $user_id");

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['message'])) {
    $receiver_id = $_POST['receiver_id'] ?? 0;
    $message = $_POST['message'] ?? '';
    if (empty($receiver_id) || empty($message)) {
        $error = "All fields are required.";
    } else {
        $stmt = $conn->prepare("INSERT INTO messages (sender_id, receiver_id, message) VALUES (?, ?, ?)");
        $stmt->bind_param("iis", $user_id, $receiver_id, $message);
        if ($stmt->execute()) {
            echo "<script>navigate('messages.php')</script>";
            exit;
        } else {
            $error = "Message sending failed: " . $conn->error;
        }
    }
}
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['contract_status'])) {
    $contract_id = $_POST['contract_id'] ?? 0;
    $status = $_POST['contract_status'] ?? '';
    if (empty($contract_id) || empty($status)) {
        $error = "Invalid contract update.";
    } else {
        $stmt = $conn->prepare("UPDATE contracts SET status = ? WHERE id = ?");
        $stmt->bind_param("si", $status, $contract_id);
        if ($stmt->execute()) {
            echo "<script>navigate('messages.php')</script>";
            exit;
        } else {
            $error = "Contract update failed: " . $conn->error;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Messages & Contracts</title>
    <style>
        body { background: #f5f7fa; font-family: Arial, sans-serif; }
        .messages-container { max-width: 1200px; margin: 20px auto; padding: 20px; }
        .message-box, .contract-box { background: #fff; padding: 20px; border-radius: 8px; box-shadow: 0 4px 8px rgba(0,0,0,0.1); margin-bottom: 20px; }
        .form-group { margin-bottom: 15px; }
        label { display: block; margin-bottom: 5px; font-weight: bold; }
        input, textarea, select { width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 5px; }
        button { background: #1a73e8; color: white; padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer; }
        button:hover { background: #0d47a1; }
        .error { color: red; text-align: center; }
        a { color: #1a73e8; text-decoration: none; }
        a:hover { text-decoration: underline; }
        @media (max-width: 768px) { .messages-container { padding: 10px; } }
    </style>
</head>
<body>
    <div class="messages-container">
        <h2>Messages</h2>
        <?php if (isset($error)): ?>
            <p class="error"><?php echo $error; ?></p>
        <?php endif; ?>
        <form method="POST" onsubmit="return validateForm()">
            <div class="form-group">
                <label for="receiver_id">Send to (User ID)</label>
                <input type="number" id="receiver_id" name="receiver_id" required>
            </div>
            <div class="form-group">
                <label for="message">Message</label>
                <textarea id="message" name="message" required></textarea>
            </div>
            <button type="submit">Send</button>
        </form>
        <div class="message-box">
            <?php if ($messages->num_rows > 0): ?>
                <?php while ($msg = $messages->fetch_assoc()): ?>
                    <p><strong><?php echo htmlspecialchars($msg['name']); ?>:</strong> <?php echo htmlspecialchars($msg['message']); ?> <em>(<?php echo $msg['created_at']; ?>)</em></p>
                <?php endwhile; ?>
            <?php else: ?>
                <p>No messages found.</p>
            <?php endif; ?>
        </div>
        <h2>Contracts</h2>
        <div class="contract-box">
            <?php if ($contracts->num_rows > 0): ?>
                <?php while ($contract = $contracts->fetch_assoc()): ?>
                    <p><strong>Job:</strong> <?php echo htmlspecialchars($contract['title']); ?> | <strong>Status:</strong> <?php echo $contract['status']; ?></p>
                    <form method="POST" onsubmit="return validateForm()">
                        <input type="hidden" name="contract_id" value="<?php echo $contract['id']; ?>">
                        <select name="contract_status">
                            <option value="active" <?php echo $contract['status'] == 'active' ? 'selected' : ''; ?>>Active</option>
                            <option value="completed" <?php echo $contract['status'] == 'completed' ? 'selected' : ''; ?>>Completed</option>
                            <option value="cancelled" <?php echo $contract['status'] == 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                        </select>
                        <button type="submit">Update Status</button>
                    </form>
                <?php endwhile; ?>
            <?php else: ?>
                <p>No contracts found.</p>
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
