<?php
class Helpers
{
  private $conn;
  public $default_avatar = null;
  public $default_company_logo = null;
  private $allowed_types = array('jpg', 'jpeg', 'png', 'pdf');

  public function __construct($conn = null)
  {
    $this->conn = $conn;

    if ($_SERVER['HTTP_HOST'] == "localhost" || str_contains($_SERVER['HTTP_HOST'], "192.168.") || str_contains($_SERVER['HTTP_HOST'], "10.")) {
      date_default_timezone_set('Asia/Manila');
      define("SERVER_NAME", "https://{$_SERVER['HTTP_HOST']}/professional-roofers-signature");
    } else {
      date_default_timezone_set('US/Eastern');
      define("SERVER_NAME", "https://{$_SERVER['HTTP_HOST']}/sig");
    }

    $this->default_avatar = SERVER_NAME . "/custom-assets/images/user-placeholder.png";
    $this->default_company_logo = SERVER_NAME . "/custom-assets/images/company-placeholder.png";
  }

  public function format_recipients_name($recipients)
  {
    return "To: " . implode(", ", array_map(function ($recipient) {
      return $recipient->name;
    }, $recipients));
  }

  function format_attachment_name($fileName)
  {
    $exploded = explode("_", $fileName);

    if (count($exploded) > 1) {
      array_shift($exploded);
    }
    return implode("_", $exploded);
  }

  public function side_bar_data($role)
  {
    $links = array();

    if ($role == "super_admin") {
      $links = array(
        array(
          "title" => "Dashboard",
          "config" => array(
            "icon" => "bi bi-speedometer2",
            "url" => SERVER_NAME . "/views/dashboard",
          ),
        ),
        array(
          "title" => "Companies",
          "config" => array(
            "icon" => "bi bi-buildings",
            "url" => SERVER_NAME . "/views/companies",
          ),
        ),
        array(
          "title" => "Templates",
          "config" => array(
            "icon" => "bi bi-folder",
            "url" => SERVER_NAME . "/views/templates",
          ),
        ),
        array(
          "title" => "Reports",
          "config" => array(
            "icon" => "bi bi-file-earmark-text",
            "url" => SERVER_NAME . "/views/reports",
          ),
        ),
        array(
          "title" => "Users",
          "config" => array(
            "icon" => "bi bi-people",
            "url" => SERVER_NAME . "/views/users",
          ),
        ),
        array(
          "title" => "Legal",
          "config" => array(
            "icon" => "bi bi-file-earmark-lock",
            "url" => SERVER_NAME . "/views/legal",
          ),
        ),
      );
    } else if ($role == "admin") {
      $links = array(
        array(
          "title" => "Dashboard",
          "config" => array(
            "icon" => "bi bi-speedometer2",
            "url" => SERVER_NAME . "/views/dashboard",
          ),
        ),
        array(
          "title" => "Templates",
          "config" => array(
            "icon" => "bi bi-folder",
            "url" => SERVER_NAME . "/views/templates",
          ),
        ),
        array(
          "title" => "Reports",
          "config" => array(
            "icon" => "bi bi-file-earmark-text",
            "url" => SERVER_NAME . "/views/reports",
          ),
        ),
        array(
          "title" => "Users",
          "config" => array(
            "icon" => "bi bi-people",
            "url" => SERVER_NAME . "/views/users",
          ),
        ),
        array(
          "title" => "Legal",
          "config" => array(
            "icon" => "bi bi-file-earmark-lock",
            "url" => SERVER_NAME . "/views/legal",
          ),
        ),
      );
    }

    return $links;
  }

  public function add_email_logs($tableName, $tableId, $isEmailSent, $emailLog, $errMsg)
  {
    $data = array(
      "table_name" => $tableName,
      "table_id" => $tableId,
      "email_sent" => $isEmailSent == 1 ? 1 : "set_zero",
      "email_logs" => $emailLog,
      "error_message" => $errMsg
    );

    return $this->insert("email_logs", $data);
  }

  public function generate_modal_img($modal_id, $img_id)
  {
    return "
    <div id='$modal_id' class='div-modal pt-5'>
      <span class='modal-close' onclick='handleCloseModalImg(`$modal_id`, `$img_id`)'>&times;</span>
      <img class='modal-img' id='$img_id'>
    </div>
  ";
  }

  public function get_company_img_link($companyID = null)
  {
    if ($companyID) {
      $company = $this->select_custom_fields_individual("companies", ["logo"], "id='$companyID'");

      if ($company->logo) {
        return SERVER_NAME . "/uploads/companies/$company->logo";
      }
    }

    return $this->default_company_logo;
  }

  public function create_token()
  {
    return uniqid() . "-" . date("ymdHi");
  }

  /**  custom builder */
  // One token per session (reused across tabs and parallel AJAX calls); created lazily.
  function generate_csrf_token()
  {
    if (session_status() !== PHP_SESSION_ACTIVE && !headers_sent()) {
      session_start();
    }

    if (empty($_SESSION['csrf_token']) || !is_string($_SESSION['csrf_token'])) {
      $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }

    return $_SESSION['csrf_token'];
  }

  function validate_csrf_token($token = null)
  {
    if (session_status() !== PHP_SESSION_ACTIVE && !headers_sent()) {
      session_start();
    }

    if ($token === null) {
      $token = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? ($_POST['csrf_token'] ?? null);
    }

    if (!is_string($token) || $token === '') {
      return false;
    }

    $stored = $_SESSION['csrf_token'] ?? null;

    return is_string($stored) && $stored !== '' && hash_equals($stored, $token);
  }

  function send_csrf_header()
  {
    if (!headers_sent()) {
      header('X-CSRF-Token: ' . $this->generate_csrf_token());
    }
  }

  function array_unique_custom($array)
  {
    return array_intersect_key(
      $array,
      array_unique(array_map("strtolower", $array))
    );
  }

  public function encrypt($val)
  {
    $encrypt_method = "AES-256-CBC";
    $secret_key = 'AA74CDCC2BBRT935136HH7B63C27';
    $secret_iv = '5fgf5HJ5g27';

    $key = hash('sha256', $secret_key);
    $iv = substr(hash('sha256', $secret_iv), 0, 16);

    $output = openssl_encrypt($val, $encrypt_method, $key, 0, $iv);

    return base64_encode($output);
  }

  public function decrypt($val)
  {
    $encrypt_method = "AES-256-CBC";
    $secret_key = 'AA74CDCC2BBRT935136HH7B63C27';
    $secret_iv = '5fgf5HJ5g27';

    $key = hash('sha256', $secret_key);
    $iv = substr(hash('sha256', $secret_iv), 0, 16);

    return openssl_decrypt(base64_decode($val), $encrypt_method, $key, 0, $iv);
  }

  public function validate_upload_file($file, $allowed_types = null)
  {
    if ($allowed_types === null) {
      $allowed_types = $this->allowed_types;
    }

    $res = array(
      "success" => false,
      "message" => ""
    );

    try {
      // multiple files
      if (is_array($file["name"])) {
        if (intval($file["error"][0]) == 0) {
          for ($i = 0; $i < count($file["name"]); $i++) {
            $fileType = $file["name"][$i] == "blob" ? "png" : pathinfo($file["name"][$i], PATHINFO_EXTENSION);

            if (!$this->validate_file_type($fileType, $allowed_types)) {
              $res["message"] = "Invalid file type for file " . $file["name"][$i] . ". Allowed types: " . implode(", ", $allowed_types);
              return (object) $res;
            }
          }
        }
      } else {
        // single file
        if ($file["error"] == 0) {
          $fileType = $file["name"] == "blob" ? "png" : pathinfo($file["name"], PATHINFO_EXTENSION);

          if (!$this->validate_file_type($fileType, $allowed_types)) {
            $res["message"] = "Invalid file type. Allowed types: " . implode(", ", $allowed_types);
            return (object) $res;
          }
        }
      }

      $res["success"] = true;
      return (object) $res;
    } catch (Exception $e) {
      $err = $e->getMessage();
      $res["message"] = $err;
      return (object) $res;
    }
  }

  public function validate_file_type($fileType, $allowed_types = null)
  {
    if ($allowed_types === null) {
      $allowed_types = $this->allowed_types;
    }

    return in_array(strtolower($fileType), $allowed_types);
  }

  public function upload_file_with_index($file, $i, $path)
  {
    $res = array(
      "success" => false,
      "file_name" => ""
    );

    try {
      if (intval($file["error"][$i]) == 0) {
        $uploadFile = uniqid() . '_' . $file['name'][$i];

        if (!is_dir($path)) {
          mkdir($path, 0777, true);
        }

        if (move_uploaded_file($file['tmp_name'][$i], "$path/$uploadFile")) {
          $res["success"] = true;
          $res["file_name"] = $uploadFile;
        }
      }
    } catch (Exception $e) {
      $err = $e->getMessage();
    }
    return (object) $res;
  }

  public function upload_single_file($file, $path, $customName = "")
  {
    $res = array(
      "success" => false,
      "file_name" => ""
    );

    try {
      if (intval($file["error"]) == 0) {
        $uploadFile = uniqid() . '_' . $file['name'];

        if (!empty($customName)) {
          $uploadFile = $customName;
        }

        if (!is_dir($path)) {
          mkdir($path, 0777, true);
        }

        if (move_uploaded_file($file['tmp_name'], "$path/$uploadFile")) {
          $res["success"] = true;
          $res["file_name"] = $uploadFile;
        }
      }
    } catch (Exception $e) {
      $err = $e->getMessage();
      $res["message"] = $err;
    }
    return (object) $res;
  }

  public function delete_file($path)
  {
    if (file_exists($path)) {
      unlink($path);
    }
  }

  public function is_self_in_dropdown($self, array $configs)
  {
    $explodedSelf = explode("/", str_replace(".php", "", $self));
    $newSelf = $explodedSelf[count($explodedSelf) - 1];

    foreach ($configs as $config) {
      $explodedUrl = explode("/", $config["url"]);
      $newUrl =  $explodedUrl[count($explodedUrl) - 1];
      if ($newUrl == $newSelf) {
        return true;
      }
    }

    return false;
  }

  public function is_active_menu($url, $self)
  {
    $explodedUrl = explode("/", $url);
    $newUrl =  $explodedUrl[count($explodedUrl) - 1];

    $explodedSelf = explode("/", str_replace(".php", "", $self));
    $newSelf = $explodedSelf[count($explodedSelf) - 1];

    return $newUrl == $newSelf ? "active" : "";
  }

  public function insert($table, $data)
  {
    $columns = array();
    $values = array();

    try {
      if (count($data) > 0) {
        foreach ($data as $column => $value) {
          if ($value) {
            if ($value == "set_null") {
              array_push($columns, "`$column`");
              array_push($values, "NULL");
            } else if ($value == "set_zero") {
              array_push($columns, "`$column`");
              array_push($values, "0");
            } else {
              array_push($columns, "`$column`");
              array_push($values, "'" .   $this->conn->escape_string($value) . "'");
            }
          }
        }

        if (count($values) == count($columns)) {
          $queryStr = "INSERT INTO `$table` (" . implode(",", $columns) . ") VALUES (" . implode(",", $values) . ")";

          $query = $this->conn->query($queryStr);

          if ($query) {
            return $this->conn->insert_id;
          } else {
            $error =  $this->conn->error;
          }
        }

        return null;
      }
    } catch (Exception $e) {
      $error = $e->getMessage();
    }

    return null;
  }

  public function select_all_individual($table, $params)
  {
    $query = $this->conn->query("SELECT * FROM $table WHERE $params");

    return $query->num_rows > 0 ? $query->fetch_object() : null;
  }

  public function select_custom_fields_individual($table, array $fields, $params)
  {
    $customFields = implode(",", $fields);

    $query = $this->conn->query("SELECT $customFields FROM $table WHERE $params");

    return $query->num_rows > 0 ? $query->fetch_object() : null;
  }

  public function select_all($table)
  {
    $query = $this->conn->query("SELECT * FROM $table");

    $data = array();

    while ($row = $query->fetch_object()) {
      array_push($data, $row);
    }

    return $data;
  }

  public function select_all_with_params($table, $params)
  {
    $query = $this->conn->query("SELECT * FROM $table WHERE $params");

    $data = array();

    while ($row = $query->fetch_object()) {
      array_push($data, $row);
    }

    return $data;
  }

  public function select_custom_fields($table, array $fields)
  {
    $customFields = implode(",", $fields);

    $query = $this->conn->query("SELECT $customFields FROM $table");

    $data = array();

    while ($row = $query->fetch_object()) {
      array_push($data, $row);
    }

    return $data;
  }

  public function select_custom_fields_with_params($table, array $fields, $params)
  {
    $customFields = implode(",", $fields);

    $query = $this->conn->query("SELECT $customFields FROM $table WHERE $params");

    $data = array();

    while ($row = $query->fetch_object()) {
      array_push($data, $row);
    }

    return $data;
  }

  public function custom_query($query)
  {
    $query = $this->conn->query($query);

    return $query->num_rows > 0 ? $query->fetch_object() : null;
  }

  public function custom_query_multiple($query)
  {
    $query = $this->conn->query($query);

    $data = array();

    while ($row = $query->fetch_object()) {
      array_push($data, $row);
    }

    return $data;
  }

  function update($table, $data, $column_where, $column_val)
  {
    $set = array();

    try {
      if (count($data) > 0) {
        foreach ($data as $column => $value) {
          if ($value) {
            if ($value == "set_null") {
              array_push($set, "$column = NULL");
            } else if ($value == "set_zero") {
              array_push($set, "$column = '0'");
            } else {
              array_push($set, "$column = '" . $this->conn->escape_string($value) . "'");
            }
          }
        }

        if (count($set) > 0) {
          $queryStr = "UPDATE `$table` SET " . (implode(', ', $set)) . " WHERE $column_where='$column_val'";

          return $this->conn->query($queryStr);
        }

        return null;
      }
    } catch (Exception $e) {
      $error = $e->getMessage();
    }

    return null;
  }

  function delete($table, $column, $value)
  {
    try {
      $queryStr = "DELETE FROM `$table` WHERE `$column`='$value'";

      return $this->conn->query($queryStr);
    } catch (Exception $e) {
      $error = $e->getMessage();
    }

    return null;
  }

  public function is_selected($value, $to_check)
  {
    if ($value && $to_check) {
      if ($value == $to_check) {
        return "selected";
      } else {
        return "";
      }
    }
    return "";
  }

  public function get_user_by_email($email, $id = null)
  {
    $params = $id ? "AND id <> $id" : "";
    $query = $this->conn->query("SELECT * FROM users WHERE email='$email' $params");

    return $query->num_rows > 0 ? $query->fetch_object() : null;
  }

  public function get_user_by_id($user_id)
  {
    $query = $this->conn->query("SELECT * FROM users WHERE id='$user_id'");

    return $query->num_rows > 0 ? $query->fetch_object() : null;
  }

  public function get_full_name($user_id)
  {
    $user = $this->get_user_by_id($user_id);

    return ucwords("$user->fname $user->lname");
  }

  public function get_avatar_link($user_id)
  {
    $user = $this->get_user_by_id($user_id);

    if ($user->avatar) {
      return SERVER_NAME . "/uploads/users/$user->avatar";
    }

    return $this->default_avatar;
  }

  public function user_logout($path)
  {
    global $_SESSION;

    $_SESSION = array();

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

    session_destroy();
    echo "<script>
    sessionStorage.clear(); 
    localStorage.clear();
    window.history.pushState(null, null, window.location.href);
    window.location.href='$path'
    </script>";
  }

  public function generateNumericOTP($n)
  {
    $generator = "1357902468";
    $result = "";

    for ($i = 1; $i <= $n; $i++) {
      $result .= substr($generator, (rand() % (strlen($generator))), 1);
    }

    return $result;
  }

  public function response_json($params)
  {
    echo json_encode($params);
    exit;
  }

  public function select_user_by_company_id($id)
  {

    $query = $this->conn->query("SELECT * FROM users WHERE company_id='$id'");

    $user = array();

    while ($row = $query->fetch_object()) {
      array_push($user, $row);
    }
    return $user;
  }
}
