<?php
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}

if (isset($_SESSION['user_id'])) {
  $user_id = $_SESSION['user_id'];

  if (!class_exists('DB')) {
    include(__DIR__ . "/../../backend/DB.php");
  }

  if (!isset($conn)) {
    $db = new DB();
    $conn = $db->getConnection();
  }

  $query = "SELECT 
              id, 
              fname, 
              lname,
              CONCAT(fname, ' ', lname) AS full_name, 
              contact,
              `address`,
              email, 
              `role`, 
              avatar,
              company_id,
              position,
              `status`
            FROM users 
            WHERE id = ?";

  $stmt = $conn->prepare($query);
  $stmt->bind_param("i", $user_id);
  $stmt->execute();
  $result = $stmt->get_result();

  if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $current_user = (object) $row;
  } else {
    $current_user = null;
  }
} else {
  header("Location: " . SERVER_NAME . "/");
  exit();
}
