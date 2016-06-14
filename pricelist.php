<html>
<head>
<title>Food stop</title>
<style>
table {
	margin-left: auto;
	margin-right: auto;
	border: 1px solid #777777;
	border-collapse: collapse;
}
th {
	background-color: #777777;
}
tr.p1 {
	background-color: #dddddd;
}
#container {
	width: 100%;
}
</style>
</head>
<body>

<h1>Food stop</h1>

<?php
include('db.php');

$pg1 = '<div id="container">';

try {
	$pg1 = '<table>';
	$pg1 .= '<tr>';
	$pg1 .= '<th>Type</th><th>Description</th><th>Prix unitaire</th><th>En stock</th>';
	$pg1 .= '</tr>';
	$db = new PDO($db_pdostr, $db_user, $db_pass);
	$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
	$stmt = $db->prepare("SELECT foodstop_categories.name AS catname, foodstop_items.* FROM foodstop_items,foodstop_categories WHERE foodstop_items.category = foodstop_categories.id ORDER BY foodstop_items.category ASC, foodstop_items.desc ASC");
	$stmt->execute(array());
	$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
	$i = 0;
	foreach ($rows as $row)
	{
		$i++;
		if ($i % 2 == 0)
		{
			$pg1 .= '<tr>';
		}
		else
		{
			$pg1 .= '<tr class=\'p1\'>';
		}
		if ($row['price'] > 0)
		{
			$pg1 .= '<td>'.$row['catname'].'</td><td>'.$row['desc'].'</td><td>$'.$row['price'].'</td><td>'.$row['stock'].'</td>';
			/*
				$percentage = 100*($row['retail_price']-$row['price'])/$row['price'];
				if ($percentage != 0)
				{
					$pg1 .= '<td>$'.$row['retail_price'].' ('.round(100*($row['retail_price']-$row['price'])/$row['price'],0).'%)</td>';
				}
				else
				{
					$pg1 .= '<td></td>';
				}
			}*/
		}
		$pg1 .= '</tr>';
	}
	$pg1 = $pg1."\n".'</table>';
}
catch (PDOException $ex)
{
	$pg1 = 'Database error: '.$ex->getFile().':'.$ex->getLine().' -> '.$ex->getMessage();
}

$pg1 .= '</div>';
echo $pg1;

?>

</body>
