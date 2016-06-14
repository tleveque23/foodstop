<?php


ini_set("log_errors", 1);
ini_set("error_log", "/tmp/php-error.log");

if (!isset($_SERVER["HTTP_HOST"])) {
  parse_str($argv[1], $_GET);
  parse_str($argv[1], $_POST);
}

include('db.php');

header("Content-Type: application/json");

$lOrderby = trim($_GET['orderby']);
$lDirection = trim($_GET['direction']);

	try
	{
		$db = new PDO($db_pdostr, $db_user, $db_pass);
		$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);


        $lDbResult = array();

        $lQuery = "SELECT * FROM foodstop_items where price > 0  ORDER BY ";

        // set default values if no params
        if ($lOrderby == "") {
            $lOrderby = "desc";
        }

        if ($lDirection == "") {
            $lDirection = "ASC";
        }

        $lQuery = $lQuery . $lOrderby . " " . $lDirection;

        error_log( "Query --> " . $lQuery );

        foreach ($db->query($lQuery) as $row)
        {
            $lDbResult[] = array('barcode' => $row['barcode'], 'id' => $row['id'], 'price' => $row['price'], 'desc' => $row['desc'], 'createdate' => $row['createdate'], 'updatedate' => $row['updatedate'], 'stock' => $row['stock']);
        }
	}
	catch (PDOException $ex)
	{
		$lDbResult = array('error' => 'database error: '.$ex->getFile().':'.$ex->getLine().' -> '.$ex->getMessage());
	}


echo json_encode($lDbResult);

exit();
?>
