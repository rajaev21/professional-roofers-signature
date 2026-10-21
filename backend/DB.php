<?php
class DB
{
  private $host;
  private $db_name;
  private $username;
  private $password;
  public $conn;

  public function __construct()
  {
    require __DIR__ . '/../assets/vendors/dotenv/vendor/autoload.php';

    $dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . "/../");
    $dotenv->load();

    if ($_SERVER['HTTP_HOST'] == "localhost" || str_contains($_SERVER['HTTP_HOST'], "192.168.") || str_contains($_SERVER['HTTP_HOST'], "10.")) {
      $this->host = $_ENV["LOCAL_DB_HOST"];
      $this->db_name = $_ENV["LOCAL_DB_NAME"];
      $this->username = $_ENV["LOCAL_DB_USER"];
      $this->password = $_ENV["LOCAL_DB_PASSWORD"];
    } else {
      $this->host = $_ENV["DB_HOST"];
      $this->db_name = $_ENV["DB_NAME"];
      $this->username = $_ENV["DB_USER"];
      $this->password = $_ENV["DB_PASSWORD"];
    }
  }

  public function getConnection()
  {
    $this->conn = null;
    try {
      $this->conn = new mysqli($this->host, $this->username, $this->password, $this->db_name);
      $this->conn->set_charset("utf8");
      if ($this->conn->connect_error) {
        throw new Exception("Connection failed: " . $this->conn->connect_error);
      }
    } catch (Exception $exception) {
      echo "Connection error: " . $exception->getMessage();
    }
    return $this->conn;
  }
}
