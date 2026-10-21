<?php
header('Content-Type: application/json');
if (session_status() == PHP_SESSION_NONE) {
  session_start();
}

class Reports
{
  private $conn;
  private $helpers;

  public function __construct($conn, $helpers)
  {
    $this->conn = $conn;
    $this->helpers = $helpers;
  }

  public function resend_email($post)
  {
    $id = $post['sent_template_id'];
    $resent_by = $this->helpers->decrypt($post['resent_by']);

    $sent_template = $this->helpers->select_custom_fields_individual(
      "sent_templates",
      [
        "sender_id",
        "signing_id",
        "template_id",
        "name",
        "email",
        "email_subject",
        "email_message",
        "is_email_sent",
        "signed_finished_date",
        "signing_link",
        "completion_link"
      ],
      "id = '$id'"
    );

    if ($sent_template) {
      if ($sent_template->is_email_sent == "0") {
        $this->helpers->response_json([
          "success" => true,
          "message" => "Email is not yet sent.<br>Please wait for other to finished signing before re-sending email."
        ]);
      } else {
        if (!class_exists('SendEmail')) {
          require(__DIR__ . "/../SendEmail.php");
        }
        $sender = new SendEmail();

        $html_body = "";
        $email_subject = $sent_template->email_subject;

        if ($sent_template->signed_finished_date) {
          // Completion
          $html_body = file_get_contents(__DIR__ . "/../email_templates/completion-email.html");
          $html_body = str_replace("{{link}}", $sent_template->completion_link, $html_body);

          $this->helpers->insert("resent_emails", [
            "sent_template_id" => $id,
            "type" => "completion",
            "resent_by" => $resent_by
          ]);
        } else {
          // Signing
          $senderName = $this->helpers->get_full_name($sent_template->sender_id);

          $email_message_content = $sent_template->email_message ? "
            <p style='margin-top: 20px;font-size: 14px;line-height: 16px;font-family: Arial, sans-serif;'>
              " . nl2br($sent_template->email_message) . "
            </p>" : "";

          $html_body = file_get_contents(__DIR__ . "/../email_templates/email-template.html");

          $html_body = str_replace("{{sender_name}}", $senderName, $html_body);
          $html_body = str_replace("{{link}}", $sent_template->signing_link, $html_body);
          $html_body = str_replace("{{email_message}}", $email_message_content, $html_body);

          $this->helpers->insert("resent_emails", [
            "sent_template_id" => $id,
            "type" => "signing",
            "resent_by" => $resent_by
          ]);
        }

        $sendEmail = $sender->send($sent_template->email, $html_body, $sent_template->name, $email_subject);

        if ($sendEmail->success) {
          $this->helpers->add_email_logs("sent_templates", $id, 1, $sendEmail->debug, $sendEmail->message);
        } else {
          $this->helpers->add_email_logs("sent_templates", $id, 0, $sendEmail->debug, $sendEmail->message);
        }

        return [
          "success" => true,
          "message" => "Email has been resent successfully."
        ];
      }
    } else {
      return [
        "success" => false,
        "message" => "Sent template not found."
      ];
    }
  }
}
