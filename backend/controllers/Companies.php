<?php
header('Content-Type: application/json');
if (session_status() == PHP_SESSION_NONE) {
  session_start();
}

class Companies
{
  private $conn;
  private $helpers;

  public function __construct($conn, $helpers)
  {
    $this->conn = $conn;
    $this->helpers = $helpers;
  }

  function add_company($post, $files)
  {
    $name = $post["name"];
    $license = $post["license"];
    $address = $post["address"];
    $contact = $post["contact"];
    $email = $post["email"];
    $number_of_employee = $post["number_of_employee"];
    $website = $post["website"];
    $imgName = null;

    $validate_file = $this->helpers->validate_upload_file($files["img_upload"], ['jpg', 'jpeg', 'png']);
    if (!$validate_file->success) {
      return array(
        "success" => false,
        "message" => $validate_file->message
      );
      exit();
    }

    $uploadImg = $this->helpers->upload_single_file($files["img_upload"], (__DIR__ . "/../../uploads/companies"));
    if ($uploadImg->success) {
      $imgName = $uploadImg->file_name;
    }

    $postData = [
      "name" => $name,
      "license" => $license,
      "address" => $address,
      "contact" => $contact,
      "email" => $email,
      "number_of_employee" => $number_of_employee,
      "website" => $website,
      "logo" => $imgName,
    ];

    $insert = $this->helpers->insert("companies", $postData);
    if ($insert) {
      return [
        "status" => "success",
        "message" => "Company added successfully!",
      ];
    } else {
      return [
        "status" => "error",
        "message" => "Error: " . $this->conn->error,
      ];
    }
  }

  function edit_company($post, $files)
  {
    $id = $post["id"];
    $isImageReset = $post["isImageReset"] ?? null;
    $name = $post["name"];
    $license = $post["license"] ? $post["license"] : "set_null";
    $address = $post["address"];
    $contact = $post["contact"];
    $email = $post["email"];
    $number_of_employee = $post["number_of_employee"] ? $post["number_of_employee"] : "set_null";
    $website = $post["website"] ? $post["website"] : "set_null";
    $imgName = null;

    $validate_file = $this->helpers->validate_upload_file($files["img_upload"], ['jpg', 'jpeg', 'png']);
    if (!$validate_file->success) {
      return array(
        "success" => false,
        "message" => $validate_file->message
      );
      exit();
    }

    $company = $this->helpers->select_custom_fields_individual("companies", ["logo"], "id = '$id'");
    if ($company == null) {
      return [
        "status" => "error",
        "message" => "Company not found!",
      ];
      exit;
    }

    if ($isImageReset == "true" && $files["img_upload"]["error"] == 4) {
      $imgName = "set_null";
    } else {
      $uploadImg = $this->helpers->upload_single_file($files["img_upload"], (__DIR__ . "/../../uploads/companies"));
      if ($uploadImg->success) {
        $imgName = $uploadImg->file_name;
      }
    }

    $postData = [
      "name" => $name,
      "license" => $license,
      "address" => $address,
      "contact" => $contact,
      "email" => $email,
      "number_of_employee" => $number_of_employee,
      "website" => $website,
      "logo" => $imgName,
    ];

    $update = $this->helpers->update("companies", $postData, "id", $id);

    if ($update) {
      if ($imgName == "set_null" && $company->logo != null || ($imgName != null && $company->logo != null)) {
        $this->helpers->delete_file(__DIR__ . "/../../uploads/companies/" . $company->logo);
      }

      return [
        "status" => "success",
        "message" => "Company updated successfully!",
      ];
    } else {
      return [
        "status" => "error",
        "message" => "Error: " . $this->conn->error,
      ];
    }
  }
}
