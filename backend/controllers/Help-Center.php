<?php
header('Content-Type: application/json');
if (session_status() == PHP_SESSION_NONE) {
  session_start();
}

class Help_Center
{
  private $conn;
  private $helpers;

  public function __construct($conn, $helpers)
  {
    $this->conn = $conn;
    $this->helpers = $helpers;
  }

  public function get_help_center_by_search($post)
  {
    $search = $post['search'];
    $entries = $this->helpers->custom_query_multiple("select * from help_center hc join help_center_categories hcc on hc.category_id = hcc.id where (hc.title LIKE '%$search%' OR hc.description LIKE '%$search%') AND hc.is_archived = 0 AND hcc.is_archived = 0");

    return [
      "success" => true,
      "data" => $entries,
    ];
  }

  public function add_help_center_category($post)
  {
    $category = strtolower(str_replace(" ", "_", $post['category']));
    $postData = array(
      "formatted_category" => ucwords($post['category']),
      "category" => $category
    );

    $insert_id = $this->helpers->insert("help_center_categories", $postData);
    if ($insert_id) {
      return [
        "success" => true,
        "message" => "Category added successfully.",
      ];
    } else {
      return [
        "success" => false,
        "message" => "Failed to add category. Please try again.",
      ];
    }
  }

  public function archive_help_center_category($post)
  {
    $category_id = $post['category_id'];

    try {
      $this->helpers->update("help_center_categories", array("is_archived" => 1), "id", $category_id);
      return [
        "success" => true,
        "message" => "Category archived successfully.",
      ];
    } catch (Exception $e) {
      return [
        "success" => false,
        "message" => "Failed to archive category. Please try again.",
      ];
    }
  }
  public function archive_item($post)
  {
    $item_id = $post['item_id'];

    try {
      $this->helpers->update("help_center", array("is_archived" => 1), "id", $item_id);
      return [
        "success" => true,
        "message" => "Item archived successfully.",
      ];
    } catch (Exception $e) {
      return [
        "success" => false,
        "message" => "Failed to archive item. Please try again.",
      ];
    }
  }

  public function add_help_center_item($post, $files)
  {
    $id = $post["id"] ?? null;
    $pdf_name = null;
    $video_name = null;
    $category_id = $post["category_id"];
    $category = $this->helpers->select_all_individual("help_center_categories", "id = '$category_id'");
    $video_path = __DIR__ . "/../../uploads/$category->category/videos/";
    $pdf_path = __DIR__ . "/../../uploads/$category->category/pdfs/";

    $validate_video = $this->helpers->validate_upload_file($files["upload_video"], ['mp4', 'avi', 'mov', 'wmv', 'flv', 'mkv']);
    if (!$validate_video->success) {
      return array(
        "success" => false,
        "message" => $validate_video->message
      );
      exit();
    }

    $validate_pdf = $this->helpers->validate_upload_file($files["upload_pdf"], ['pdf']);
    if (!$validate_pdf->success) {
      return array(
        "success" => false,
        "message" => $validate_pdf->message
      );
      exit();
    }

    if ($files['upload_video']) {
      $video_upload = $this->helpers->upload_single_file($files["upload_video"], $video_path);
      if ($video_upload->success) {
        $video_name = $video_upload->file_name;
      }
    }

    if ($files['upload_pdf']) {
      $pdf_upload = $this->helpers->upload_single_file($files["upload_pdf"], $pdf_path);
      if ($pdf_upload->success) {
        $pdf_name = $pdf_upload->file_name;
      }
    }

    if ($id) {

      $postData = array(
        "title" => ucwords($post["title"]),
        "description" => empty($post["description"]) ? "set_null" : $post["description"],
        "content" => empty($post["content"]) ? "set_null" : $post["content"],
        "created_by" => $post["created_by"],
        "external_link" => empty($post["external_link"]) ? "set_null" : $post["external_link"],
        "category_id" => $category_id
      );

      $help_center = $this->helpers->select_all_individual("help_center", "id = '$id'");
      $postData['video_file'] = $video_name;
      if ($help_center->video_file && $video_name) {
        $this->helpers->delete_file("$video_path/$help_center->video_file");
      }

      $postData['pdf_file'] =  $pdf_name;
      if ($help_center->pdf_file && $pdf_name) {
        $this->helpers->delete_file("$pdf_path/$help_center->pdf_file");
      }
      $this->helpers->update("help_center", $postData, "id", $id);
      $this->helpers->response_json(array(
        "success" => true,
        "message" => "Item updated successfully.",
      ));
    } else {

      $postData = array(
        "title" => ucwords($post["title"]),
        "description" => $post["description"],
        "content" => $post["content"],
        "video_file" => $video_name,
        "pdf_file" => $pdf_name,
        "created_by" => $post["created_by"],
        "external_link" => $post["external_link"],
        "category_id" => $category_id
      );

      $insert_id = $this->helpers->insert("help_center", $postData);
      if ($insert_id) {
        return [
          "success" => true,
          "message" => "Help center item added successfully.",
        ];
      } else {
        return [
          "success" => false,
          "message" => "Failed to add help center item. Please try again.",
        ];
      }
    }
  }
}
