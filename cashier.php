<html>
<head>
<title>Foodstop cashier</title>
<script src="jquery-2.2.3.js"></script>
<script src="flipclock.js"></script>
<link rel="stylesheet" href="flipclock.css">
<style>
body {
	font-size:32px;
}

#display, #help {
	margin: 30px;
	padding: 15px;
	border: 1px solid #777777;
	background-color: #eeeeee;
}

#info1, #info2 {
	font-weight: bold;
}

#hint {
	margin-top: 30px;
	font-size: 16px;
}

#input_container {
	text-align: center;
}

#timecnt {
	position: absolute;
	bottom: 0;
	width: 100%;
	text-align: center;
}

#date {
	font-size: 2.5em;
	font-weight: bold;
	font-family: sans-serif;
}

#clockwrapper {
	text-align: center;
}

#clock {
	display: inline-block;
	width: auto;
}
</style>
<script>

var state = 'READY';
var item_barcode;
var timer_id;
var clock;

function initHandlers()
{
	state = 'READY';
	showHelp();

	$('#barcode').focus();

	$('#barcode').keypress(function(event) {
		if (event.which == 13)
		{
			event.preventDefault();
			var barcode = $('#barcode').val();
			$('#barcode').val('');
			processNewBarcode(barcode);
		}
	});

	$('#barcode').blur(function(event) {
		$('#barcode').focus();
	});

	clock = $('#clock').FlipClock({
	        clockFace: 'TwentyFourHourClock'
	});

	initClock();
}

function initClock()
{
(function () {
    function checkTime(i) {
        return (i < 10) ? "0" + i : i;
    }

    function startTime() {
	var options = {weekday: "long", year: "numeric", month: "long", day: "numeric"};
        var today = new Date(),
            h = checkTime(today.getHours()),
            m = checkTime(today.getMinutes()),
            s = checkTime(today.getSeconds());
        document.getElementById('date').innerHTML = today.toLocaleDateString("fr-FR", options);
        t = setTimeout(function () {
            startTime()
        }, 500);
    }
    startTime();
})();
}

function processNewBarcode(barcode)
{
	// First we stop any timer
	if (timer_id) clearTimeout(timer_id);
	// Then it depends
	if ((state == 'READY') || (state == 'SHOWING_PURCHASE_INFO') || (state == 'SHOWING_ACCOUNT_INFO'))
	{
		// If it's an account label, show the balance
		// If it's a product label, start the purchasing process by showing the item info
		if (barcode.substring(0,6) == '999999')
		{
			// Account barcode
			showAccountInfo(barcode);
			state = 'SHOWING_ACCOUNT_INFO';		
		}
		else
		{
			// Product barcode
			item_barcode = barcode;
			showItemInfo(barcode);
			state = 'SHOWING_ITEM_INFO';
		}
	}
	else if (state == 'SHOWING_ITEM_INFO')
	{
		// If it's an account barcode, do the purchase
		// If it's another product info, show the info for that product
		if (barcode.substring(0,6) == '999999')
		{
			doPurchase(item_barcode, barcode);
			state = 'SHOWING_PURCHASE_INFO';
		}
		else
		{
			item_barcode = barcode;
			showItemInfo(barcode);
			state = 'SHOWING_ITEM_INFO';
		}
	}
	else
	{
		// Unknown state : go back to READY
		showHelp();
		state = 'READY';
	}

	if (state != 'READY')
	{
		timer_id = setTimeout(function() {
			showHelp();
			item_barcode = null;
			state = 'READY';
		}, 15000);
	}
}

function doPurchase(item_barcode, account_barcode)
{
	$.post( "purchase.php", { item_barcode: item_barcode, account_barcode: account_barcode }, function( data ) {
		if (data.error)
		{
			showData("Error", data.error, "There was an error processing your request.");
		}
		else
		{
			if (data.postpaid == 1)
			{
				showData("Purchase done !", "Please pay in the box.", "To purchase another item, scan it.");
			}
			else
			{
				if (data.account_balance < 0)
				{
					showData("Purchase done !", "Negative balance: $"+(0-data.account_balance), "Your account balance is negative. Please refill.");
				}
				else
				{
					showData("Purchase done !", "Balance left: $"+data.account_balance, "To purchase another item, scan it");
				}
			}
		}
	}, "json");
}

function showItemInfo(item_barcode)
{
	$.get( "getiteminfo.php", { barcode: item_barcode }, function( data ) {
		if (data.error)
		{
			showData("Error", data.error, "There was an error processing your request");
		}
		else
		{
			showData("Item: "+data.desc, "Price: $"+data.price,"To purchase, scan your account card.");
		}
	}, "json");
}

function showAccountInfo(account_barcode)
{
	$.get( "getaccountinfo.php", { barcode: account_barcode }, function( data ) {
		if (data.error)
		{
			showData("Error", data.error, "There was an error processing your request");
		}
		else
		{
			if (data.postpaid == 1)
			{
				showData("Postpaid account","","Please pay in the box when you purchase using this account.");
			}
			else
			{
				showData("Account balance: "+data.account_balance,"","");
			}
		}
	}, "json");
}

function showData(text1, text2, hint)
{
	$('#help').css('display','none');
	$('#display').css('display','block');
	$('#info1').text(text1);
	$('#info2').text(text2);
	$('#hint').text(hint);
}

function showHelp()
{
	$('#help').css('display','block');
	$('#display').css('display','none');
}

$(document).ready(initHandlers);
</script>
</head>
<body>
<div id="input_container"><input type="text" id="barcode"></input></div>
<div id="display">
	<div id="info1"></div>
	<div id="info2"></div>
	<div id="hint"></div>
</div>
<div id="help">
	<ul><li>Scan a product to know its price or purchase it</li><li>Scan an account to know its balance</li></ul>
</div>

<div id="timecnt">
<div id="clockwrapper" align="center"><div id="clock"></div></div>
<div id="date"></div>
</div>

</body>
</html>
