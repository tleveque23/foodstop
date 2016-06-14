<?php
if (!isset($_SERVER["HTTP_HOST"])) {
  parse_str($argv[1], $_POST);
}

include('db.php');

header("Content-Type: application/json");

$item_barcode = trim($_POST['item_barcode']);
$account_barcode = trim($_POST['account_barcode']);

$item_price = null;
$item_id = null;
$item_stock = null;
$account_id = null;
$refill_posrpaid = 0;

if (!is_numeric($item_barcode) || !is_numeric($account_barcode) || (isset($_SERVER["HTTP_HOST"]) && ($_SERVER['REQUEST_METHOD'] != 'POST')))
{
	$pg1 = array('error' => 'invalid request');
}
else
{
	try {
		// Find item price
		$db = new PDO($db_pdostr, $db_user, $db_pass);
		$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

		$db->beginTransaction();

		$stmt = $db->prepare("SELECT * FROM foodstop_items WHERE barcode = :barcode");
		$stmt->execute(array('barcode' => $item_barcode));
		$row = $stmt->fetch(PDO::FETCH_ASSOC);
		if ($row != null)
		{
			if ($row['barcode'] == $item_barcode)
			{
				$item_price = $row['price'];
				$item_id = $row['id'];
				$item_stock = $row['stock'];
			}
		}

		if (($item_id != null) && ($item_price != null))
		{
			// Create account if doesn't exist and reduce balance
			$stmt = $db->prepare("SELECT * FROM foodstop_accounts WHERE barcode = :barcode");
			$stmt->execute(array('barcode' => $account_barcode));
			$row = $stmt->fetch(PDO::FETCH_ASSOC);
			if ($row == null)
			{
				$stmt = $db->prepare("INSERT INTO foodstop_accounts (barcode, balance) VALUES  (:barcode, :balance)");
				$stmt->execute(array('barcode' => $account_barcode, 'balance' => -$item_price));
				$account_id = $db->lastInsertId();
				$account_balance = -$item_price;
				$account_postpaid = 0;
			}
			else
			{
				$account_postpaid = $row['postpaid'];
				if (($account_postpaid == 1) && ($row['price'] < 0))
				{
					$pg1 = array('error' => 'cannot refill postpaid account');
					$refill_postpaid = 1;
				}
				else
				{
					$account_id = $row['id'];
					$stmt = $db->prepare("UPDATE foodstop_accounts SET balance = :balance WHERE id = :id");
					$stmt->execute(array('id' => $account_id, 'balance' => $row['balance']-$item_price));
					$account_balance = $row['balance'] - $item_price;
				}
			}

			if ($refill_postpaid == 0)
			{
				// Create transaction
				$stmt = $db->prepare("INSERT INTO foodstop_transactions (account_id, item_id, quantity, unit_price) VALUES (:account_id, :item_id, :quantity, :unit_price)");
				$stmt->execute(array('account_id' => $account_id, 'item_id' => $item_id, 'unit_price' => $item_price, 'quantity' => 1));

				// Reduce stock
				$stmt = $db->prepare("UPDATE foodstop_items SET stock = :stock WHERE id = :id");
				$stmt->execute(array('stock' => $item_stock-1, 'id' => $item_id));

				$db->commit(); // Everything was a success -> commit

				$pg1 = array('account_balance' => $account_balance, 'item_stock' => $item_stock-1, 'postpaid' => $account_postpaid);
			}
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
