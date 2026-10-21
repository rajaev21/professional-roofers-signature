<?php
header('Content-Type: application/json');
if (session_status() == PHP_SESSION_NONE) {
  session_start();
}

class Login
{
  private $conn;
  private $helpers;

  public function __construct($conn, $helpers)
  {
    $this->conn = $conn;
    $this->helpers = $helpers;
  }

  public function login($post)
  {
    $email = $post['email'];
    $password = $post['password'];

    $user = $this->helpers->get_user_by_email($email);

    if ($user) {
      if (password_verify($password, $user->password)) {
        if (isset($_SESSION["user_email"])) {
          unset($_SESSION["user_email"]);
        }
        $_SESSION['user_id'] = $user->id;

        return [
          "success" => true,
          "redirect" => SERVER_NAME . "/views/dashboard",
        ];
      } else {
        return [
          "success" => false,
          "message" => "Invalid password!"
        ];
      }
    } else {
      return [
        "success" => false,
        "message" => "User not found!"
      ];
    }
  }

  public function logout()
  {
    if (ini_get("session.use_cookies")) {
      $params = session_get_cookie_params();
      setcookie(
        session_name(),
        '',
        time() - 42000,
        $params["path"],
        $params["domain"],
        $params["secure"],
        $params["httponly"]
      );
    }

    session_unset();
    session_destroy();

    $this->helpers->response_json([
      "success" => true,
      "redirect" => SERVER_NAME . "/",
    ]);
  }
}
