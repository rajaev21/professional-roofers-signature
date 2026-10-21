<?php
header('Content-Type: application/json');
if (session_status() == PHP_SESSION_NONE) {
  session_start();
}

class Users
{
  private $conn;
  private $helpers;
  private $dir = __DIR__ . "/../../uploads/users/";

  public function __construct($conn, $helpers)
  {
    $this->conn = $conn;
    $this->helpers = $helpers;
  }

  function add_user($post, $files)
  {
    $fname = $post["fname"];
    $lname = $post["lname"];
    $contact = $post["contact"];
    $email = $post["email"];
    $address = $post["address"];
    $role = $post["role"];
    $status = $post["status"];
    $company = $post["company"];
    $position = $post["position"];
    $password = password_hash($post["password"], PASSWORD_DEFAULT);

    $imgName = null;

    $validate_file = $this->helpers->validate_upload_file($files["img_upload"], ['jpg', 'jpeg', 'png']);
    if (!$validate_file->success) {
      return array(
        "success" => false,
        "message" => $validate_file->message
      );
      exit();
    }

    $user = $this->helpers->get_user_by_email($email);

    if ($user) {
      $this->helpers->response_json([
        "success" => false,
        "message" => "Email already exists!"
      ]);
    } else {
      $uploadImg = $this->helpers->upload_single_file($files["img_upload"], (__DIR__ . "/../../uploads/users"));
      if ($uploadImg->success) {
        $imgName = $uploadImg->file_name;
      }

      $userPost = [
        "fname" => $fname,
        "lname" => $lname,
        "contact" => $contact,
        "email" => $email,
        "password" => $password,
        "address" => $address,
        "role" => $role,
        "status" => $status,
        "company_id" => $company,
        "position" => $position,
        "avatar" => $imgName
      ];

      $userId = $this->helpers->insert("users", $userPost);
      if ($userId) {
        return [
          "success" => true,
          "message" => "User added successfully!"
        ];
      } else {
        return [
          "success" => false,
          "message" => "Error: " . $this->conn->error
        ];
      }
    }
  }

  function edit_user($post, $files)
  {
    $id = $post["id"];
    $isImageReset = $post["isImageReset"] ?? null;

    $fname = $post["fname"];
    $lname = $post["lname"];
    $contact = $post["contact"];
    $email = $post["email"];
    $address = $post["address"];
    $role = $post["role"];
    $status = $post["status"] == "1" ? 1 : "set_zero";
    $company = empty($post["company"]) ? "set_null" : $post["company"];
    $position = empty($post["position"]) ? "set_null" : $post["position"];

    $imgName = null;

    $validate_file = $this->helpers->validate_upload_file($files["img_upload"], ['jpg', 'jpeg', 'png']);
    if (!$validate_file->success) {
      return array(
        "success" => false,
        "message" => $validate_file->message
      );
      exit();
    }

    $checkUserEmail = $this->helpers->get_user_by_email($email, $id);

    if ($checkUserEmail) {
      return [
        "success" => false,
        "message" => "Email already exists!"
      ];
    } else {
      $user = $this->helpers->get_user_by_id($id);

      if ($user == null) {
        return [
          "status" => "error",
          "message" => "User not found!",
        ];
      }

      if ($isImageReset == "true" && $files["img_upload"]["error"] == 4) {
        $imgName = "set_null";
      } else {
        $uploadImg = $this->helpers->upload_single_file($files["img_upload"], (__DIR__ . "/../../uploads/users"));
        if ($uploadImg->success) {
          $imgName = $uploadImg->file_name;
        }
      }

      $userPost = [
        "fname" => $fname,
        "lname" => $lname,
        "contact" => $contact,
        "email" => $email,
        "address" => $address,
        "role" => $role,
        "status" => $status,
        "company_id" => $company,
        "position" => $position,
        "avatar" => $imgName
      ];

      $userUpdate = $this->helpers->update("users", $userPost, "id", $id);

      if ($userUpdate) {
        if ($imgName == "set_null" && $user->avatar != null || ($imgName != null && $user->avatar != null)) {
          $this->helpers->delete_file(__DIR__ . "/../../uploads/users/" . $user->avatar);
        }

        $this->helpers->response_json([
          "success" => true,
          "message" => "User added successfully!"
        ]);
      } else {
        $this->helpers->response_json([
          "success" => false,
          "message" => "Error: " . $this->conn->error
        ]);
      }
    }
  }

  public function change_password($post)
  {
    $user_id = $post["user_id"];
    $current_password = $post["current_password"];
    $new_password = $post["new_password"];

    $userData = $this->helpers->select_all_individual("users", "id = '$user_id'");
    if (!$userData) {
      return [
        "success" => false,
        "message" => "User not found"
      ];
    }

    if (!password_verify($current_password, $userData->password)) {
      return [
        "success" => false,
        "message" => "Current password is incorrect"
      ];
    }

    $hashedPassword = password_hash($new_password, PASSWORD_BCRYPT);

    $update = $this->helpers->update("users", array("password" => $hashedPassword), "id", $user_id);

    if ($update) {
      return [
        "success" => true,
        "message" => "Password changed successfully"
      ];
    } else {
      return [
        "success" => false,
        "message" => "Error changing password: " . $this->conn->error
      ];
    }
  }

  public function update_profile($post, $files)
  {
    $user_id = $post["user_id"];
    $avatar = $files["avatar"];
    $remove_avatar = isset($post["remove_avatar"]) ? true : false;

    $first_name = $post["first_name"];
    $last_name = $post["last_name"];
    $email = $post["email"];
    $contact = $post["contact"];
    $address = $post["address"];

    $file_name = null;

    $validate_file = $this->helpers->validate_upload_file($avatar, ['jpg', 'jpeg', 'png']);
    if (!$validate_file->success) {
      return array(
        "success" => false,
        "message" => $validate_file->message
      );
      exit();
    }

    $uploadResult = $this->helpers->upload_single_file($avatar, $this->dir);

    if (isset($uploadResult->success) && $uploadResult->success) {
      $file_name = $uploadResult->file_name;
      $userData = $this->helpers->select_all_individual("users", "id = '$user_id'");
      if ($userData && !empty($userData->avatar)) {
        $oldFile = $this->dir . $userData->avatar;
        if (file_exists($oldFile)) unlink($oldFile);
      }
      $avatarChanged = true;
    } elseif ($remove_avatar) {
      $userData = $this->helpers->select_all_individual("users", "id = '$user_id'");
      if ($userData && !empty($userData->avatar)) {
        $oldFile = $this->dir . $userData->avatar;
        if (file_exists($oldFile)) unlink($oldFile);
      }
      $file_name = null;
      $avatarChanged = true;
    } else {
      $avatarChanged = false;
    }

    $postData = array(
      "fname" => $first_name,
      "lname" => $last_name,
      "email" => $email,
      "contact" => $contact,
      "address" => $address,
    );

    if ($avatarChanged) {
      $postData["avatar"] = $file_name ? $file_name : "set_null";
    }

    $update = $this->helpers->update("users", $postData, "id", $user_id);
    if ($update) {
      return [
        "success" => true,
        "message" => "Profile updated successfully"
      ];
    } else {
      return [
        "success" => false,
        "message" => "Error updating profile: " . $this->conn->error
      ];
    }
  }
}
