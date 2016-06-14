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
$lUserCode = trim($_GET['usercode']);

	try
	{
		$db = new PDO($db_pdostr, $db_user, $db_pass);
		$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);


        $lDbResult = array();

        $lQuery = /** @lang SQL */
            "select foodstop_transactions.timestamp, foodstop_items.desc , foodstop_transactions.quantity, foodstop_transactions.unit_price
from foodstop_transactions, foodstop_items
where account_id = (select id from foodstop_accounts where barcode = '" . $lUserCode . "')
AND foodstop_transactions.item_id = foodstop_items.id
order by ";

        // set default values if no params
        if ($lOrderby == "") {
            $lOrderby = "timestamp";
        }

        if ($lDirection == "") {
            $lDirection = "DESC";
        }

        $lQuery = $lQuery . $lOrderby . " " . $lDirection;

        error_log( "Query --> " . $lQuery );

        foreach ($db->query($lQuery) as $row)
        {
            $lDbResult[] = array('timestamp' => $row['timestamp'], 'desc' => $row['desc'], 'quantity' => $row['quantity'], 'unit_price' => $row['unit_price']);
        }
	}
	catch (PDOException $ex)
	{
		$lDbResult = array('error' => 'database error: '.$ex->getFile().':'.$ex->getLine().' -> '.$ex->getMessage());
	}


echo json_encode($lDbResult);

exit();
?>
