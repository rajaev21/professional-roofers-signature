<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

require(__DIR__ . "/../assets/vendors/PHPMailer/vendor/autoload.php");

class SendEmail
{
  private $host;
  private $userName;
  private $password;
  public $response;

  function __construct()
  {
    // $this->host = "gator4146.hostgator.com";
    $this->host       = 'smtp.gmail.com';
    $this->userName   = 'professionalroofersnearmeco@gmail.com';
    $this->password   = 'gnin oacw tslm hhog';
    // $this->host = "mail.primesealsignatures.com";
    // $this->userName = "mail@primesealsignatures.com";
    // $this->password = "D,do_Vl(-uJ1";
    $this->response = array("success" => false, "message" => "", "debug" => "");
  }

  public function send($email_to, $body, $name, $subject, $debug = 2)
  {
    $mail = new PHPMailer();

    try {

      $mail->Debugoutput = function ($str, $level) {
        $this->response["debug"] .= "Debug: $str";
      };

      $mail->SMTPDebug = $debug;
      $mail->isSMTP();
      $mail->Host = $this->host;
      $mail->SMTPAuth = true;
      $mail->Username = $this->userName;
      $mail->Password = $this->password;
      $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
      $mail->Port       = 465;

      $mail->setFrom($this->userName, 'Professional Roofers Signatures');
      $mail->addAddress($email_to, $name);
      $mail->addReplyTo('no-reply@app.primesealsignatures.com');

      $mail->isHTML(true);
      $mail->Subject = $subject;
      $mail->Body = $body;

      // if ($email_to == "montemarjohn66@gmail.com") {
      if ($mail->send()) {
        $this->response["success"] = true;
      } else {
        $this->response["success"] = false;
        $this->response["message"] = "Internal Server Error!";
      }
      // }
    } catch (Exception $e) {
      $this->response["success"] = false;
      $this->response["message"] = $e->getMessage();
    }
    $mail->smtpClose();
    $mail->clearAddresses();

    return (object)$this->response;
  }
}
