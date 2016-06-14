<?php

ini_set("log_errors", 1);
ini_set("error_log", "/tmp/php-error.log");

if (!isset($_SERVER["HTTP_HOST"])) {
  parse_str($argv[1], $_GET);
  parse_str($argv[1], $_POST);
}

include('db.php');

header("Content-Type: application/json");

$barcode = trim($_GET['barcode']);

if (!is_numeric($barcode))
{
	$pg1 = array('error' => 'invalid request');
}
else
{
	try {
		$db = new PDO($db_pdostr, $db_user, $db_pass);
		$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		$lQuery = "SELECT * FROM foodstop_items WHERE barcode like '%" . $barcode . "' ";
		error_log( "Query --> " . $lQuery );
		$stmt = $db->prepare($lQuery);
		$stmt->execute();
		$row = $stmt->fetch(PDO::FETCH_ASSOC);
		if ($row != null)
		{
			$pg1 = array('barcode' => $row['barcode'], 'id' => $row['id'], 'price' => $row['price'], 'desc' => $row['desc'], 'createdate' => $row['createdate'], 'updatedate' => $row['updatedate'], 'stock' => $row['stock']);
		}
		else
		{
			$pg1 = array('error' => 'unknown item');
		}
	}
	catch (PDOException $ex)
	{
		$pg1 = array('error' => 'database error: '.$ex->getFile().':'.$ex->getLine().' -> '.$ex->getMessage());
	}
}

echo json_encode($pg1);

exit();
?>
