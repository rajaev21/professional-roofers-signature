<?php
require(__DIR__ . "/../backend/DB.php");
require(__DIR__ . "/../backend/Helpers.php");
require(__DIR__ . "/../backend/controllers/Crons.php");

$db = new DB();
$conn = $db->getConnection();
$helpers = new Helpers($conn);

$crons = new Crons($conn, $helpers);
$crons->expireDocInFourteenDays();
