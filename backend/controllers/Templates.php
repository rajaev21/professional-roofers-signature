<?php
header('Content-Type: application/json');
if (session_status() == PHP_SESSION_NONE) {
  session_start();
}

class Templates
{
  private $conn;
  private $helpers;

  public function __construct($conn, $helpers)
  {
    $this->conn = $conn;
    $this->helpers = $helpers;
  }

  public function edit_template($post, $files)
  {
    $template_id = $post['template_id'];
    $template_name = ucwords($post["name"]);
    $email_subject = $post['email_subject'];
    $email_message = $post['email_message'];

    $upload_template = $files["upload_template"];

    $validate_file = $this->helpers->validate_upload_file($upload_template, ['pdf']);
    if (!$validate_file->success) {
      return array(
        "success" => false,
        "message" => $validate_file->message
      );
      exit();
    }

    $file_order = json_decode($post["file_order"], true);

    if (count($file_order) > 0) {
      foreach ($file_order as $index => $file) {
        if (isset($file['id']) && $file['id'] !== null) {
          $this->helpers->update("template_files", ["file_order" => intval($file['index']) + 1], "id", $file['id']);
        }
      }
    }

    for ($i = 0; $i < count($upload_template['name']); $i++) {
      $uploaded_file = $this->helpers->upload_file_with_index($upload_template, $i, (__DIR__ . "/../../uploads/templates"));
      $uploaded_file_name = $uploaded_file->file_name;

      $order = array_search($upload_template['name'][$i], array_column($file_order, 'name'));

      if ($uploaded_file->success) {
        $fileData = [
          "template_id" => $template_id,
          "file_name" => $uploaded_file_name,
          "file_order" => $order + 1,
        ];

        $this->helpers->insert("template_files", $fileData);
      }
    }

    $update_data = [
      "document_status" => "draft",
      "name" => $template_name,
      "email_subject" => $email_subject,
      "email_message" => $email_message,
      "signature_config" => "set_null",
      "text_config" => "set_null"
    ];

    $update = $this->helpers->update("templates", $update_data, "id", $template_id);

    if ($update) {
      return array(
        "success" => true,
        "message" => "Template updated successfully."
      );
    } else {
      return array(
        "success" => false,
        "message" => "Failed to update template."
      );
    }
  }

  public function remove_pdf_file($post)
  {
    $file_id = $post['file_id'];
    $template_id = $post['template_id'];

    $file_query = $this->helpers->select_all_individual("template_files", "id = '$file_id' AND template_id = '$template_id'");

    $delete = $this->helpers->delete("template_files", "id", $file_id);

    if ($delete) {
      $template_file_data = $this->helpers->select_all_with_params("template_files", "template_id = '$template_id' ORDER BY file_order ASC");

      if (count($template_file_data) > 0) {
        foreach ($template_file_data as $index => $file) {
          $this->helpers->update("template_files", ["file_order" => $index + 1], "id", $file->id);
        }
      }

      if ($file_query) {
        $this->helpers->delete_file(__DIR__ . "/../../uploads/templates/" . $file_query->file_name);
      }

      $this->helpers->update(
        "templates",
        [
          "document_status" => "draft",
          "signature_config" => "set_null",
          "text_config" => "set_null"
        ],
        "id",
        $template_id
      );

      return array(
        "success" => true
      );
    } else {
      return array(
        "success" => false,
        "message" => "Failed to remove file."
      );
    }
  }

  public function get_pdf_files($post)
  {
    $template_id = $post['template_id'];
    $pdf_files = $this->helpers->select_custom_fields_with_params("template_files", ["id", "file_name"], "template_id = '$template_id' ORDER BY file_order ASC");

    foreach ($pdf_files as $file) {
      $file->url = SERVER_NAME . "/uploads/templates/" . $file->file_name;
    }

    if ($pdf_files) {
      return array(
        "success" => true,
        "file_data" => $pdf_files
      );
    } else {
      return array(
        "success" => false,
        "message" => "No PDF files found."
      );
    }
  }

  public function send_use_template($post)
  {
    if (!class_exists('SendEmail')) {
      require(__DIR__ . "/../SendEmail.php");
    }
    $sender = new SendEmail();

    $id = $this->helpers->decrypt($post['id']);
    $senderId = $post['sender_id'];
    $senderName = $post['sender_name'];

    $email_subject = $post['email_subject'];
    $email_message = $post['email_message'];

    $firstSignerName = isset($post['first_signer_name']) ? $post['first_signer_name'] : null;
    $firstSignerEmail = isset($post['first_signer_email']) ? $post['first_signer_email'] : null;
    $secondSignerName = isset($post['second_signer_name']) ? $post['second_signer_name'] : null;
    $secondSignerEmail = isset($post['second_signer_email']) ? $post['second_signer_email'] : null;
    $thirdSignerName = isset($post['third_signer_name']) ? $post['third_signer_name'] : null;
    $thirdSignerEmail = isset($post['third_signer_email']) ? $post['third_signer_email'] : null;

    $emailData = array(
      array(
        "order" => 1,
        "name" => $firstSignerName,
        "email" => $firstSignerEmail,
      ),
      array(
        "order" => 2,
        "name" => $secondSignerName,
        "email" => $secondSignerEmail,
      ),
      array(
        "order" => 3,
        "name" => $thirdSignerName,
        "email" => $thirdSignerEmail,
      ),
    );

    $emailData = array_filter($emailData, function ($item) {
      return !empty($item['name']) && !empty($item['email']);
    });

    if (count($emailData) > 0) {
      $sent = false;
      $signing_id = $this->helpers->create_token();

      for ($i = 0; $i < count($emailData); $i++) {
        $email = $emailData[$i]['email'];
        $name = $emailData[$i]['name'];
        $email_order = $emailData[$i]['order'];

        $postData = [
          "signing_id" => $signing_id,
          "sender_id" => $senderId,
          "template_id" => $id,
          "email_order" => $email_order,
          "terms_agree" => "set_zero",
          "name" => $name,
          "email" => $email,
          "is_email_sent" => "set_zero",
          "email_subject" => $email_subject,
          "email_message" => $email_message,
        ];

        $sentTemplateId = $this->helpers->insert("sent_templates", $postData);

        if ($sentTemplateId) {
          if ($i == 0) {
            $email_message_content = $email_message ? "
            <p style='margin-top: 20px;font-size: 14px;line-height: 16px;font-family: Arial, sans-serif;'>
              " . nl2br($email_message) . "
            </p>" : "";

            $encrypted_signing_id = $this->helpers->encrypt($signing_id);
            $encrypted_template_id = $this->helpers->encrypt($id);
            $encrypted_sent_template_id = $this->helpers->encrypt($sentTemplateId);

            $link = (SERVER_NAME . "/public/?s=$encrypted_signing_id&t=$encrypted_template_id&c=$encrypted_sent_template_id");
            $this->helpers->update("sent_templates", ["signing_link" => $link], "id", $sentTemplateId);

            $html_body = file_get_contents(__DIR__ . "/../email_templates/email-template.html");

            $html_body = str_replace("{{sender_name}}", $senderName, $html_body);
            $html_body = str_replace("{{link}}", $link, $html_body);
            $html_body = str_replace("{{email_message}}", $email_message_content, $html_body);

            $sendEmail = $sender->send($email, $html_body, $name, $email_subject ? $email_subject : "$senderName has sent you a Document to Review and Sign!");

            $dateNow = date("Y-m-d H:i:s");
            if ($sendEmail->success) {
              $this->helpers->update(
                "sent_templates",
                [
                  "is_email_sent" => 1,
                  "email_error" => "set_null",
                  "email_sent_date" => $dateNow
                ],
                "id",
                $sentTemplateId
              );

              $this->helpers->add_email_logs("sent_templates", $sentTemplateId, 1, $sendEmail->debug, $sendEmail->message);
            } else {
              $this->helpers->update(
                "sent_templates",
                [
                  "is_email_sent" => "set_zero",
                  "email_sent_date" => $dateNow,
                  "email_error" => $sendEmail->message
                ],
                "id",
                $sentTemplateId
              );

              $this->helpers->add_email_logs("sent_templates", $sentTemplateId, 0, $sendEmail->debug, $sendEmail->message);
            }
          }
          $sent = true;
        }
      }

      if ($sent) {
        return [
          "success" => true,
        ];
      } else {
        return [
          "success" => false,
          "message" => "Error sending emails.",
        ];
      }
    } else {
      return [
        "success" => false,
        "message" => "No valid email addresses provided.",
      ];
    }
  }

  public function toggle_archive($post)
  {
    $id = $this->helpers->decrypt($post['encryptedId']);
    $action = $post['method'];

    if ($action === 'archive') {
      $data = ['document_status' => 'archived'];
    } else {
      $data = ['document_status' => 'draft'];
    }

    if ($this->helpers->update("templates", $data, "id", $id)) {
      return [
        "success" => true,
        "message" => "Template " . $action . " successfully.",
      ];
    } else {
      return [
        "success" => false,
        "message" => "Error: " . $this->conn->error,
      ];
    }
  }

  public function publish_template()
  {
    $id = $_POST['id'];
    $data = [
      'document_status' => 'published',
    ];

    if ($this->helpers->update("templates", $data, "id", $id)) {
      return [
        "success" => true,
        "message" => "Template published successfully.",
      ];
    } else {
      return [
        "success" => false,
        "message" => "Error: " . $this->conn->error,
      ];
    }
  }

  public function save_text_config($post)
  {
    $id = $post['id'];
    $data = [
      "text_config" => $post["config"],
      "document_status" => "draft",
    ];

    if ($this->helpers->update("templates", $data, "id", $id)) {
      return [
        "success" => true,
      ];
    } else {
      return [
        "success" => false,
        "message" => "Error: " . $this->conn->error,
      ];
    }
  }

  public function save_sign_config($post)
  {
    $id = $post['id'];
    $data = [
      "signature_config" => $post["config"],
      "document_status" => "draft",
    ];

    if ($this->helpers->update("templates", $data, "id", $id)) {
      return [
        "success" => true,
      ];
    } else {
      return [
        "success" => false,
        "message" => "Error: " . $this->conn->error,
      ];
    }
  }

  public function get_template_config($post)
  {
    $id = $post['template_id'];
    $query = "SELECT signature_config, text_config FROM templates WHERE id = ?";

    $stmt = $this->conn->prepare($query);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
      $row = $result->fetch_assoc();
      $pdf_files = $this->helpers->select_custom_fields_with_params("template_files", ["file_name"], "template_id = '$id' ORDER BY file_order ASC");

      return [
        "success" => true,
        "config" => $row['signature_config'] ? json_decode($row['signature_config'], true) : null,
        "text_config" => $row['text_config'] ? json_decode($row['text_config'], true) : null,
        "pdf_files" => count($pdf_files) > 0 ? array_map(function ($f) {
          return SERVER_NAME . "/uploads/templates/" . $f->file_name;
        }, $pdf_files) : [],
      ];
    } else {
      return [
        "success" => false,
        "message" => "Template not found.",
      ];
    }
  }

  public function create_template($post, $files)
  {
    $doc_id = $this->helpers->create_token();

    $name = ucwords($post["name"]);
    $file_order = json_decode($post["file_order"], true);

    $email_subject = $post["email_subject"];
    $email_message = $post["email_message"];

    $upload_template = $files["upload_template"];
    $created_by = $post['created_by'] ?? "";
    $document_status = "draft";

    $created_email = $post['created_email'] ?? null;
    if ($created_email && empty($created_by)) {
      $user = $this->helpers->select_all_individual("users", "email = '$created_email'");
      if ($user) {
        $created_by = $user->id;
      }
    }

    $validate_file = $this->helpers->validate_upload_file($upload_template, ['pdf']);
    if (!$validate_file->success) {
      return array(
        "success" => false,
        "message" => $validate_file->message
      );
      exit();
    }

    // $choices = $post["choices"];
    // $choices = "upload_document";
    // $creation_type = "";

    $postData = [
      "doc_id" => $doc_id,
      "name" => $name,
      "creation_type" => "upload",
      "email_subject" => $email_subject,
      "email_message" => $email_message,
      "created_by" => $created_by,
      "document_status" => $document_status,
    ];

    $insert = $this->helpers->insert("templates", $postData);

    if ($insert) {
      for ($i = 0; $i < count($upload_template['name']); $i++) {
        $uploaded_file = $this->helpers->upload_file_with_index($upload_template, $i, (__DIR__ . "/../../uploads/templates"));
        $uploaded_file_name = $uploaded_file->file_name;

        $order = array_search($upload_template['name'][$i], array_column($file_order, 'name'));

        if ($uploaded_file->success) {
          $fileData = [
            "template_id" => $insert,
            "file_name" => $uploaded_file_name,
            "file_order" => $order + 1,
          ];

          $this->helpers->insert("template_files", $fileData);
        }
      }

      $redirect = SERVER_NAME . "/views/templates?page=signature&id=" . $this->helpers->encrypt($insert);
      if ($created_email) {
        $redirect = SERVER_NAME . "/public/commercial/signature-builder?page=signature&id=" . $this->helpers->encrypt($insert);
      }

      return [
        "success" => true,
        "message" => "Template created successfully.",
        "redirect" => $redirect,
      ];
    } else {
      return [
        "success" => false,
        "message" => "Error: " . $this->conn->error,
      ];
    }
  }
}
