<?php
class Crons
{
  private $conn;
  private $helpers;
  private $logFile;

  public function __construct($conn, $helpers)
  {
    $this->conn = $conn;
    $this->helpers = $helpers;

    $logDir = __DIR__ . '/../logs';

    if (!is_dir($logDir)) {
      mkdir($logDir, 0777, true);
    }

    $this->logFile = $logDir . '/cron.log';
    if (!file_exists($this->logFile)) {
      file_put_contents($this->logFile, '');
    }
  }

  public function expireDocInFourteenDays()
  {
    try {
      $sent_templates = $this->helpers->select_all_with_params("sent_templates", "expired <> 1 AND signed_finished_date IS NOT NULL");

      if (count($sent_templates) > 0) {
        foreach ($sent_templates as $sent_template) {
          $currentDate = new DateTime();

          $expiryDate = new DateTime($sent_template->date_updated);
          $expiryDate->modify('+14 days');

          if ($currentDate >= $expiryDate) {
            $updateResult = $this->helpers->update("sent_templates", ["expired" => 1], "id", $sent_template->id);
            if ($updateResult === false) {
              $this->logMessage("Failed to update sent_template ID: {$sent_template->id}");
            } else {
              $this->logMessage("Expired sent_template ID: {$sent_template->id}");
            }
          }
        }
      }
    } catch (Exception $e) {
      $this->logMessage("Error in expireDocInFourteenDays: " . $e->getMessage());
    }
  }

  public function testCron()
  {
    $this->logMessage("Cron job executed successfully!");
  }

  function logMessage($message)
  {
    file_put_contents($this->logFile, "[" . date('Y-m-d H:i:s') . "] " . $message . "\n", FILE_APPEND);
  }
}
