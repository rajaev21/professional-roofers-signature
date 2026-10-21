<?php
header('Content-Type: application/json');
if (session_status() == PHP_SESSION_NONE) {
  session_start();
}

class Signing
{
  private $conn;
  private $helpers;

  public function __construct($conn, $helpers)
  {
    $this->conn = $conn;
    $this->helpers = $helpers;
  }

  public function void_sent_template($post)
  {
    $signing_id = $post['signing_id'];

    $update = $this->helpers->update(
      'sent_templates',
      ['is_void' => 1],
      "signing_id",
      $signing_id
    );

    if ($update) {
      return [
        "success" => true,
        "message" => 'Signing process has been voided.'
      ];
    } else {
      return [
        "success" => false,
        "message" => 'Failed to void signing process. ' . $this->conn->error
      ];
    }
  }

  public function submit_document($post)
  {
    if (!class_exists('SendEmail')) {
      require(__DIR__ . "/../SendEmail.php");
    }
    $sender = new SendEmail();

    $sent_template_id = $post['sent_template_id'];
    $template_id = $post['template_id'];

    $dateNow = date("Y-m-d H:i:s");

    $sentTemplateData = $this->helpers->select_all_individual("sent_templates", "id = '$sent_template_id' and template_id = '$template_id'");

    $postData = [
      'signed_finished_date' => $dateNow,
    ];

    $update = $this->helpers->update(
      'sent_templates',
      $postData,
      "id",
      $sent_template_id
    );

    if ($update) {
      $email_message_content = $sentTemplateData->email_message ? "
            <p style='margin-top: 20px;font-size: 14px;line-height: 16px;font-family: Arial, sans-serif;'>
              " . nl2br($sentTemplateData->email_message) . "
            </p>" : "";

      $remainingRecipientData = $this->helpers->select_all_with_params("sent_templates", "signing_id = '$sentTemplateData->signing_id' AND template_id = '$sentTemplateData->template_id' AND (is_email_sent='0' OR is_email_sent IS NULL) ORDER BY email_order ASC");

      $encryptedTemplateId = $this->helpers->encrypt($template_id);

      if (count($remainingRecipientData) > 0) {
        $nextRecipient = $remainingRecipientData[0];

        $encryptedSigningId = $this->helpers->encrypt($nextRecipient->signing_id);
        $encryptedNextRecipientId = $this->helpers->encrypt($nextRecipient->id);

        $senderData = $this->helpers->select_all_individual("users", "id = '$nextRecipient->sender_id'");
        $senderName = "$senderData->fname $senderData->lname";

        $link = (SERVER_NAME . "/public/?s=$encryptedSigningId&t=$encryptedTemplateId&c=$encryptedNextRecipientId");

        $html_body = file_get_contents(__DIR__ . "/../email_templates/email-template.html");
        $html_body = str_replace("{{sender_name}}", $senderName, $html_body);
        $html_body = str_replace("{{link}}", $link, $html_body);
        $html_body = str_replace("{{email_message}}", $email_message_content, $html_body);

        $sendEmail = $sender->send(
          $nextRecipient->email,
          $html_body,
          $nextRecipient->name,
          $sentTemplateData->email_subject ?? "$senderName has sent you a Document to Review and Sign!"
        );

        if ($sendEmail->success) {
          $this->helpers->update(
            "sent_templates",
            [
              "is_email_sent" => 1,
              "email_error" => "set_null",
              "email_sent_date" => $dateNow,
              "signing_link" => $link
            ],
            "id",
            $nextRecipient->id
          );

          $this->helpers->add_email_logs("sent_templates", $nextRecipient->id, 1, $sendEmail->debug, $sendEmail->message);
        }
      } else {
        $recipientsData = $this->helpers->select_all_with_params("sent_templates", "signing_id = '$sentTemplateData->signing_id' AND template_id = '$sentTemplateData->template_id' AND is_email_sent = 1 ORDER BY email_order ASC");

        if (count($recipientsData) > 0) {
          foreach ($recipientsData as $recipient) {
            $encryptedTemplateId = $this->helpers->encrypt($recipient->template_id);
            $encryptedSigningId = $this->helpers->encrypt($recipient->signing_id);

            $link = (SERVER_NAME . "/public/viewing?s=$encryptedSigningId&t=$encryptedTemplateId");
            $this->helpers->update("sent_templates", ["completion_link" => $link], "id", $recipient->id);

            $html_body = file_get_contents(__DIR__ . "/../email_templates/completion-email.html");
            $html_body = str_replace("{{link}}", $link, $html_body);

            $sendEmail = $sender->send($recipient->email, $html_body, $recipient->name, "Document Signing Complete by All Parties");

            if ($sendEmail->success) {
              $this->helpers->add_email_logs("sent_templates", $recipient->id, 1, $sendEmail->debug, $sendEmail->message);
            }
          }
        }
      }
      return [
        "success" => true,
        "message" => "Document submitted successfully."
      ];

      // if (function_exists('fastcgi_finish_request')) {
      //   fastcgi_finish_request();
      // }
    } else {
      return [
        "success" => false,
        "message" => "Failed to submit document." . $this->conn->error
      ];
    }
  }

  public function save_signed_data($post)
  {
    $signing_id = $post['signing_id'];
    $template_id = $post['template_id'];
    $sent_template_id = $post['sent_template_id'];
    $signed_data = $post['signed_data'];
    $text_box_data = $post['text_box_data'];

    $postData = [
      'signing_id' => $signing_id,
      'template_id' => $template_id,
      'signatures_data' => $signed_data,
      'text_config' => $text_box_data,
    ];

    $signedData = $this->helpers->select_all_individual("signed_data", "signing_id = '$signing_id' AND template_id = '$template_id'");

    $signedDataId =  $signedData->id ?? null;

    if ($signedDataId) {
      $this->helpers->update(
        'signed_data',
        $postData,
        "id",
        $signedDataId
      );
    } else {
      $signedDataId = $this->helpers->insert("signed_data", $postData);
    }

    if ($signedDataId) {
      $this->helpers->update(
        'sent_templates',
        ['signed_data_id' => $signedDataId],
        "id",
        $sent_template_id
      );

      return [
        "success" => true,
        "message" => "Signature data saved successfully."
      ];
    } else {
      return [
        "success" => false,
        "message" => "Failed to save signature data." . $this->conn->error
      ];
    }
  }

  public function get_signed_data($post)
  {
    $template_id = $post['template_id'];
    $signing_id = $post['signing_id'];

    $signedData = $this->helpers->select_all_individual("signed_data", "signing_id = '$signing_id' AND template_id = '$template_id'");
    $signaturesData = $this->helpers->select_custom_fields_with_params(
      "sent_templates",
      [
        "id",
        "email_order",
        "signature_token",
        "primary_signature",
        "initial_signature",
      ],
      "signing_id = '$signing_id' ORDER BY email_order ASC"
    );

    $data = [];
    $textboxConfig = [];
    if ($signedData) {
      $data = json_decode($signedData->signatures_data, true);
      $textboxConfig = json_decode($signedData->text_config, true);
    }

    $signatures = [];
    if (count($signaturesData) > 0) {
      foreach ($signaturesData as $signature) {
        $signer = "firstSigner";

        if ($signature->email_order == 2) {
          $signer = "secondSigner";
        } else if ($signature->email_order == 3) {
          $signer = "thirdSigner";
        }

        $signatures[] = [
          "signer" => $signer,
          "signature_token" => $signature->signature_token,
          "primary_signature" => $signature->primary_signature,
          "initial_signature" => $signature->initial_signature
        ];
      }
    }

    return [
      "data" => $data,
      "text_config" => $textboxConfig,
      "signatures" => $signatures,
    ];
  }

  public function save_signature($post, $files)
  {
    $template_id = $this->helpers->decrypt($post['template_id']);
    $customer_template_id = $this->helpers->decrypt($post['customer_template_id']);

    $validate_signature = $this->helpers->validate_upload_file($files["primary_signature"], ['jpg', 'jpeg', 'png', 'blob']);
    $validate_initial = $this->helpers->validate_upload_file($files["initial_signature"], ['jpg', 'jpeg', 'png', 'blob']);

    if (!$validate_signature->success || !$validate_initial->success) {
      return array(
        "success" => false,
        "message" => $validate_signature->message ?? $validate_initial->message
      );
      exit();
    }

    $customerTemplateData = $this->helpers->select_all_individual("sent_templates", "id = '$customer_template_id' AND template_id = '$template_id'");

    $signature_token = $this->helpers->create_token();

    $primary_signature_name = "primary_sign_" . uniqid() . ".png";
    $initial_signature_name = "initial_sign_" . uniqid() . ".png";

    $uploadPrimary = $this->helpers->upload_single_file($files["primary_signature"], (__DIR__ . "/../../uploads/signatures"), $primary_signature_name);
    $uploadInitial = $this->helpers->upload_single_file($files["initial_signature"], (__DIR__ . "/../../uploads/signatures"), $initial_signature_name);

    if ($uploadPrimary->success) {
      $primary_signature_name = $uploadPrimary->file_name;
    }

    if ($uploadInitial->success) {
      $initial_signature_name = $uploadInitial->file_name;
    }

    if (!$uploadPrimary->success || !$uploadInitial->success) {
      if ($uploadPrimary->success) {
        $this->helpers->delete_file(__DIR__ . "/../../uploads/signatures/" . $primary_signature_name);
      }
      if ($uploadInitial->success) {
        $this->helpers->delete_file(__DIR__ . "/../../uploads/signatures/" . $initial_signature_name);
      }

      return [
        "success" => false,
        "message" => "Signature file upload failed."
      ];
      return;
    } else {
      $postData = [
        'primary_signature' => $primary_signature_name,
        'initial_signature' => $initial_signature_name,
        'signature_token' => !$customerTemplateData->signature_token ? $signature_token : null
      ];

      $update = $this->helpers->update(
        'sent_templates',
        $postData,
        "id",
        $customer_template_id
      );

      if ($update) {
        if ($uploadPrimary->success || $uploadInitial->success) {
          if ($customerTemplateData->primary_signature && $customerTemplateData->primary_signature != $primary_signature_name) {
            $this->helpers->delete_file(__DIR__ . "/../../uploads/signatures/" . $customerTemplateData->primary_signature);
          }
          if ($customerTemplateData->initial_signature && $customerTemplateData->initial_signature != $initial_signature_name) {
            $this->helpers->delete_file(__DIR__ . "/../../uploads/signatures/" . $customerTemplateData->initial_signature);
          }
        }

        return [
          "success" => true,
          "message" => "Signature successfully saved!",
        ];
      } else {
        return [
          "success" => false,
          "message" => "Failed to save signature." . $this->conn->error
        ];
      }
    }
  }

  public function agree_terms($post)
  {
    $customer_template_id = $this->helpers->decrypt($post['customer_template_id']);

    $update = $this->helpers->update(
      'sent_templates',
      [
        'terms_agree' => 1
      ],
      "id",
      $customer_template_id
    );

    if ($update) {
      return [
        "success" => true,
      ];
    } else {
      return [
        "success" => false,
        "message" => "Failed to agree to terms." . $this->conn->error
      ];
    }
  }
}
