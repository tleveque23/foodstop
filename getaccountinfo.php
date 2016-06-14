<?php
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
		$stmt = $db->prepare("SELECT * FROM foodstop_accounts WHERE barcode = :barcode");
		$stmt->execute(array('barcode' => $barcode));
		$row = $stmt->fetch(PDO::FETCH_ASSOC);
		if ($row != null)
		{
			$pg1 = array('barcode' => $row['barcode'], 'id' => $row['id'], 'account_balance' => $row['balance'], 'postpaid' => $row['postpaid']);
		}
		else
		{
			$pg1 = array('error' => 'unknown account');
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
