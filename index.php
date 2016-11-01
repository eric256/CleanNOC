<?php
require_once(dirname(__FILE__) . '/../../common.inc.php');

// initialization stuff
pre_init();
// start session
init_session();
// grab GET or POST variables
grab_request_vars();
// check prereqs
check_prereqs();
// check authentication
check_authentication(false);

$refreshvalue = 10; //value in seconds to refresh page
?>

<!DOCTYPE html>
<html lang="en">
 <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- The above 3 meta tags *must* come first in the head; any other head content must come *after* these tags -->
    <title>NOC</title>

	<!-- jQuery (necessary for Bootstrap's JavaScript plugins) -->
	<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.3/jquery.min.js"></script>
	<!-- Include all compiled plugins (below), or include individual files as needed -->

	<!-- Latest compiled and minified CSS -->
	<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.5/css/bootstrap.min.css">

	<!-- Optional theme -->
	<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.5/css/bootstrap-theme.min.css">


    <!-- HTML5 shim and Respond.js for IE8 support of HTML5 elements and media queries -->
    <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
    <!--[if lt IE 9]>
      <script src="https://oss.maxcdn.com/html5shiv/3.7.2/html5shiv.min.js"></script>
      <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
    <![endif]-->
	<style type="text/css">
		body {
			background: #404040;
			padding: .5em;
		}
		
		#taskbar {
		    background: lightgrey;
		}
		
		.noc_summary_data {
			font-size: 5em;
			line-height: 1em;
			text-align: center;
		}		
		

    .noc_unknown, .noc_warning, .noc_unknown a, .noc_warning a {
        color: black;
        text-shadow: none;
    }

    .noc_critical, .noc_ok, .noc_critical a, .noc_ok a {
        color: white;
        text-shadow: 1px 1px 0 #5f0000;
    }

    .noc_ok {
        background: green;
        color: white;
        text-shadow: 1px 1px 0 #015f00;
    }

    .noc_warning {
        background: yellow;
        color: black;
        text-shadow: -1px -1px 0 #feff5f;
    }
	
    .noc_unknown {
        background: orange;
        color: black;
        text-shadow: -1px -1px 0 #feff5f;
    }

	.noc_critical {
        background: red;
        color: white;
        text-shadow: 1px 1px 0 #5f0000;
    }
	
    .nagios_statusbar {
        background: gray;
        background: -moz-linear-gradient(top center, #6a6a6a, #464646);
        position: fixed;
        bottom: 0;
        width: 100%;
        margin: 0 0 0 -1em;
        height: 40px;
        text-align: right;
        border-top: 1px solid #818181;
        opacity: .9;
    }

    .nagios_statusbar_item {
        border-left: 2px groove #000;
        height: 40px;
        line-height: 40px;
        padding: 0 1em;
        color: white;
        text-shadow: 1px 1px 0 black;
        position: relative;
        float: right;
    }
	
    #loading {
        background: transparent url(../../../images/throbber.gif) no-repeat center center;
        width: 24px;
        height: 40px;
        position: absolute;
    }	
	
	.newAlert {
		font-weight: bold;
	}
	</style>
  </head>
<body>
<script type="text/javascript">
    var placeHolder,  refreshValue = 10;
	var count = refreshValue;

    $().ready(function () {
        placeHolder = $("#nagios_placeholder");
        updateNagiosData(placeHolder);
        window.setInterval(updateCountDown, 1000);
    });


    // timestamp stuff

    function createTimeStamp() {
        // create timestamp
        var ts = new Date();
        ts = ts.toTimeString();
        ts = ts.replace(/\s+\(.+/ig, "");
        $("#timestamp_wrap").empty().append("<div class=\"timestamp_drop\"></div><div class=\"timestamp_stamp\">" + ts + "</div>");
    }

	
    function updateNagiosData(block) {
        $("#loading").fadeIn(200);
        block.load("./details.php", function (response) {
            $(this).html(response);
            $("#loading").fadeOut(200);
            createTimeStamp();
        });
    }

    function updateCountDown() {
        var countdown = $("#refreshing_countdown");
		count--;

		if (count == 0) {
            updateNagiosData(placeHolder);
            count = refreshValue;
        }

		countdown.text(count);
		
    }

</script>

	<div id="nagios_placeholder"></div>

	<div class="nagios_statusbar">
		<div class="nagios_statusbar_item">
			<div id="timestamp_wrap"></div>
		</div>
		<div class="nagios_statusbar_item">
			<div id="loading"></div>
			<p id="refreshing">Refresh in <span id="refreshing_countdown"></span> seconds</p>
		</div>
	</div>

	<!-- Latest compiled and minified JavaScript -->
	<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.5/js/bootstrap.min.js"></script>
		
	  
</body>
</html>
