<?php
// header('Content-Type: application/json');
if (session_status() == PHP_SESSION_NONE) {
  session_start();
}

class Dashboard
{
  private $conn;
  private $helpers;

  public function __construct($conn, $helpers)
  {
    $this->conn = $conn;
    $this->helpers = $helpers;
  }

  public function get_count_data($current_user)
  {
    $action = [];
    $waiting = [];
    $expiring = [];
    $completed = [];

    $params = "id IS NOT NULL AND is_void = 0 AND expired = 0";

    if ($current_user->role === "admin") {
      $params .= " AND sender_id = ?";
    }

    $signingQuery = "SELECT signing_id FROM sent_templates WHERE $params GROUP BY signing_id";

    $stmt = $this->conn->prepare($signingQuery);
    if ($current_user->role === "admin") {
      $stmt->bind_param("i", $current_user->id);
    }
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
      $signingIds = array_column($result->fetch_all(MYSQLI_ASSOC), 'signing_id');

      foreach ($signingIds as $signingId) {
        $recipients = $this->helpers->select_all_with_params("sent_templates", "signing_id = '$signingId' ORDER BY email_order ASC");

        $isSignedAll = count(array_filter($recipients, function ($recipient) {
          return !empty($recipient->signed_finished_date);
        })) === count($recipients);

        if ($isSignedAll) {
          array_push($completed, $signingId);
        } else {
          $hasExpired = false;
          foreach ($recipients as $recipient) {
            $expiryDate = new DateTime($recipient->date_updated);
            $expiryDate->modify('+14 days');
            $currentDate = new DateTime();

            if ($currentDate >= $expiryDate) {
              array_push($expiring, $signingId);
              $hasExpired = true;
            }
          }

          if (!$hasExpired) {
            $countNoSigned = count(array_filter($recipients, function ($recipient) {
              return empty($recipient->signed_finished_date);
            }));

            if ($countNoSigned < count($recipients)) {
              array_push($waiting, $signingId);
            } else if ($countNoSigned == count($recipients)) {
              array_push($action, $signingId);
            }
          }
        }
      }
    }

    return [
      "action" => $action,
      "waiting" => $waiting,
      "expiring" => $expiring,
      "completed" => $completed
    ];
  }
}
