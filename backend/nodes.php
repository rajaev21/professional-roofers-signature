<?php
if (session_status() == PHP_SESSION_NONE) {
  session_start();
}

require(__DIR__ . "/DB.php");
require(__DIR__ . "/Helpers.php");

$db = new DB();
$conn = $db->getConnection();
$helpers = new Helpers($conn);

if ($_SERVER["REQUEST_METHOD"] == "POST") {
  try {
    if (isset($_POST['action'])) {
      $action = $_POST['action'];

      $token_valid = false;
      $exempt_actions = array('logout', 'get_signed_data', 'get_template_config', 'agree_terms', 'save_signature');

      if (in_array($action, $exempt_actions)) {
        $token_valid = true;
      } else {
        $token_valid = $helpers->validate_csrf_token();
      }

      if ($token_valid) {
        $routes = array(
          "get_help_center_by_search" => [
            "file" => "/controllers/Help-center.php",
            "class" => "Help_Center",
            "method" => "get_help_center_by_search",
            "args" => ["post"]
          ],
          "archive_help_center_category" => [
            "file" => "/controllers/Help-center.php",
            "class" => "Help_Center",
            "method" => "archive_help_center_category",
            "args" => ["post"]
          ],
          "add_help_center_category" => [
            "file" => "/controllers/Help-center.php",
            "class" => "Help_Center",
            "method" => "add_help_center_category",
            "args" => ["post"]
          ],
          "add_help_center_item" => [
            "file" => "/controllers/Help-center.php",
            "class" => "Help_Center",
            "method" => "add_help_center_item",
            "args" => ["post", "files"]
          ],
          "archive_item" => [
            "file" => "/controllers/Help-center.php",
            "class" => "Help_Center",
            "method" => "archive_item",
            "args" => ["post"]
          ],
          "change_password" => [
            "file" => "/controllers/Users.php",
            "class" => "Users",
            "method" => "change_password",
            "args" => ["post"]
          ],
          "update_profile" => [
            "file" => "/controllers/Users.php",
            "class" => "Users",
            "method" => "update_profile",
            "args" => ["post", "files"]
          ],
          "add_user" => [
            "file" => "/controllers/Users.php",
            "class" => "Users",
            "method" => "add_user",
            "args" => ["post", "files"]
          ],
          "edit_user" => [
            "file" => "/controllers/Users.php",
            "class" => "Users",
            "method" => "edit_user",
            "args" => ["post", "files"]
          ],
          "resend_email" => [
            "file" => "/controllers/Reports.php",
            "class" => "Reports",
            "method" => "resend_email",
            "args" => ["post"]
          ],
          "void_sent_template" => [
            "file" => "/controllers/Signing.php",
            "class" => "Signing",
            "method" => "void_sent_template",
            "args" => ["post"]
          ],
          "submit_document" => [
            "file" => "/controllers/Signing.php",
            "class" => "Signing",
            "method" => "submit_document",
            "args" => ["post"]
          ],
          "save_signed_data" => [
            "file" => "/controllers/Signing.php",
            "class" => "Signing",
            "method" => "save_signed_data",
            "args" => ["post"]
          ],
          "get_signed_data" => [
            "file" => "/controllers/Signing.php",
            "class" => "Signing",
            "method" => "get_signed_data",
            "args" => ["post"]
          ],
          "save_signature" => [
            "file" => "/controllers/Signing.php",
            "class" => "Signing",
            "method" => "save_signature",
            "args" => ["post", "files"]
          ],
          "agree_terms" => [
            "file" => "/controllers/Signing.php",
            "class" => "Signing",
            "method" => "agree_terms",
            "args" => ["post"]
          ],
          "edit_template" => [
            "file" => "/controllers/Templates.php",
            "class" => "Templates",
            "method" => "edit_template",
            "args" => ["post", "files"]
          ],
          "remove_pdf_file" => [
            "file" => "/controllers/Templates.php",
            "class" => "Templates",
            "method" => "remove_pdf_file",
            "args" => ["post"]
          ],
          "get_pdf_files" => [
            "file" => "/controllers/Templates.php",
            "class" => "Templates",
            "method" => "get_pdf_files",
            "args" => ["post"]
          ],
          "send_use_template" => [
            "file" => "/controllers/Templates.php",
            "class" => "Templates",
            "method" => "send_use_template",
            "args" => ["post"]
          ],
          "toggle_archive" => [
            "file" => "/controllers/Templates.php",
            "class" => "Templates",
            "method" => "toggle_archive",
            "args" => ["post"]
          ],
          "publish_template" => [
            "file" => "/controllers/Templates.php",
            "class" => "Templates",
            "method" => "publish_template",
            "args" => ["post"]
          ],
          "save_text_config" => [
            "file" => "/controllers/Templates.php",
            "class" => "Templates",
            "method" => "save_text_config",
            "args" => ["post"]
          ],
          "save_sign_config" => [
            "file" => "/controllers/Templates.php",
            "class" => "Templates",
            "method" => "save_sign_config",
            "args" => ["post"]
          ],
          "get_template_config" => [
            "file" => "/controllers/Templates.php",
            "class" => "Templates",
            "method" => "get_template_config",
            "args" => ["post"]
          ],
          "create_template" => [
            "file" => "/controllers/Templates.php",
            "class" => "Templates",
            "method" => "create_template",
            "args" => ["post", "files"]
          ],
          "add_company" => [
            "file" => "/controllers/Companies.php",
            "class" => "Companies",
            "method" => "add_company",
            "args" => ["post", "files"]
          ],
          "edit_company" => [
            "file" => "/controllers/Companies.php",
            "class" => "Companies",
            "method" => "edit_company",
            "args" => ["post", "files"]
          ],
          "login" => [
            "file" => "/controllers/Login.php",
            "class" => "Login",
            "method" => "login",
            "args" => ["post"]
          ],
          "logout" => [
            "file" => "/controllers/Login.php",
            "class" => "Login",
            "method" => "logout",
            "args" => []
          ],
        );

        if (!isset($routes[$action])) {
          $helpers->response_json(array("success" => false, "message" => "Invalid Request"));
          exit;
        }

        $route = $routes[$action];
        $controller_file = __DIR__ . $route['file'];

        $controllers_dir = realpath(__DIR__ . '/controllers');
        $controller_file_real = realpath($controller_file);
        if (!$controllers_dir || !$controller_file_real || strpos($controller_file_real, $controllers_dir) !== 0) {
          $helpers->response_json(array("success" => false, "message" => "Controller not found"));
          exit;
        }

        require_once($controller_file_real);

        $class = $route['class'];
        $method = $route['method'];
        $args_def = $route['args'];

        if (!class_exists($class)) {
          $helpers->response_json(array("success" => false, "message" => "Controller class missing"));
          exit;
        }

        $controller = new $class($conn, $helpers);

        if (!method_exists($controller, $method)) {
          $helpers->response_json(array("success" => false, "message" => "Method not found"));
          exit;
        }

        $params = array();
        foreach ($args_def as $a) {
          if ($a === "post") $params[] = $_POST;
          if ($a === "files") $params[] = $_FILES;
        }

        try {
          $func = call_user_func_array(array($controller, $method), $params);
          $helpers->send_csrf_header();
          $helpers->response_json($func);
        } catch (Exception $e) {
          $helpers->response_json(array("success" => false, "message" => $e->getMessage()));
        }
      } else {
        $helpers->send_csrf_header();
        $helpers->response_json(array(
          "success" => false,
          "message" => "Invalid token. Please refresh the page and try again."
        ));
      }
    }
  } catch (Exception $e) {
    $helpers->response_json(array("success" => false, "message" => $e->getMessage()));
  }
}
