<?php
	require_once("includes.php");

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

	//use variables from config.inc.php
	$host = grab_array_var($cfg['db_info']['ndoutils'], 'dbserver', 'localhost');
	$user = grab_array_var($cfg['db_info']['ndoutils'], 'user', 'ndoutils');
	$db = grab_array_var($cfg['db_info']['ndoutils'], 'db', 'nagios');
	$pw = grab_array_var($cfg['db_info']['ndoutils'], 'pwd', 'n@gweb');

	//echo "HOST $host USER $user DB $db PW $pw<br />";

	$con = mysql_connect($host, $user, $pw) or die("<h3><font color=red>Could not connect to the database!</font></h3>");
	$db = mysql_select_db($db, $con);


	$services = getServicesStatus();
	$service_class = statusToClass($services['status']);
	
	$hosts = getHostsStatus();
	$host_class = hostStatusToClass($hosts['status']);
	
	$today = new DateTime();

?>
	<div class="col-sm-8">
		<div class="panel panel-default">
				<div class="panel-heading">
				  <h3 class="panel-title">Host Issues</h3>
				</div>
				<?php if (count($hosts['down_hosts']) > 0 ) { ?>
					<table class="table table-condensed table-bordered">
					    <tr>
							<th>Host</th>
							<th>Alias</th>
							<th>Address</th>
							<th>Time Down</th>
						</tr>
				        <?php  foreach ($hosts['down_hosts'] as $row) { 
							$class = hostStatusToClass($row['status']);
							$host_url = "../xicore/status.php?show=hostdetail&host=" . $row['host_name'];
							$lastUp = date_create($row['last_time_up']);
							$interval = date_diff($lastUp, $today);
							$timeDown =formatInterval($interval);
							
							$secondsDown = $today->getTimestamp() - $lastUp->getTimestamp();
							if ($secondsDown < 60) {
								$class .= " newAlert";
							}
							
						?>
						
						<tr class="<?php print $class ?>">
							<td><a href="<?php print $host_url ?>"><?php print $row['host_name']?></a></td>
							<td><?php print $row['alias']?></td>
							<td><?php print $row['address']?></td>
							<td><?php print $timeDown ?></td>
						</tr>
						<?php } ;?>					
					</table>
				<?php } else { ?>
					<div class="noc_ok">There are no host issues.</div>
				<?php } ?>
		</div>
    </div>
	<div class="col-sm-2">		
		<div class="panel panel-default">
				<div class="panel-heading">
				  <h3 class="panel-title">Hosts</h3>
				</div>
				 <table class="table table-condensed table-bordered">
					<tr class="">
						<td rowspan="2" class=" noc_summary_data <?php echo $host_class?>"> <?php print $hosts['down'] ?></td>
						<td class="text-right <?php echo $host_class?>"><?php print $hosts['total']?> Up </td>
					</tr>
					<tr>
						<td class="text-right <?php echo $host_class?>"><?php print $hosts['ack'] ?> Ack</td>
					</tr>
				</table>
		</div>
		
    </div>

	<div class="col-sm-2">
		<div class="panel panel-default">
				<div class="panel-heading">
				  <h3 class="panel-title">Services</h3>
				</div>
				 <table class="table table-condensed table-bordered">
					<tr class="">
						<td rowspan="2" class="noc_summary_data <?php echo $service_class?>"><?php print $services['down'] ?></td>				
						<td class="text-right <?php echo $service_class?>"><?php print $services['total']?> Up</td>
					</tr>
					<tr>
						<td class="text-right <?php echo $service_class?>"><?php print $services['ack']?> Ack</td>			
					</tr>
				</table>
		</div>
	</div>


	<div class="col-sm-12">
		<div class="panel panel-default">
				<div class="panel-heading">
				  <h3 class="panel-title">Service Issues</h3>
				</div>
				<?php if (count($services['down_services']) > 0 ) { ?>
					<table class="table table-condensed table-bordered">
					    <tr>
							<th>Host</th>
							<th>Description</th>
							<th>Service</th>
							<th>Output</th>
							<th>Time Down</th>
						</tr>
				        <?php  foreach ($services['down_services'] as $row) { 
							$class = statusToClass($row['status']);
							$host_url = "../xicore/status.php?show=hostdetail&host=" . $row['host_name'] . "&service=" . $row['service_name'] . "&dest=auto";
							$service_url = "../xicore/status.php?show=servicedetail&host=" . $row['host_name'] . "&service=" . $row['service_name'] . "&dest=auto";
							
							$lastUp = date_create($row['last_time_ok']);
							$interval = date_diff($lastUp, $today);
							$timeDown =formatInterval($interval);
							
							$secondsDown = $today->getTimestamp() - $lastUp->getTimestamp();
							if ($secondsDown < 60) {
								$class .= " newAlert";
							}							
						?>
						
						<tr class="<?php print $class ?>">
							<td><a href="<?php print $host_url?>"><?php print $row['host_name']?></a></td>
							<td><?php print $row['host_description'] ?></td>
							<td><a href="<?php print $service_url?>"><?php print $row['service_name']?></a></td>
							<td><?php print $row['output']?></td>
							<td><?php print $timeDown ?></td>
						</tr>
						<?php } ;?>					
					</table>
				<?php } else { ?>
					<div class="noc_ok">There are no service issues.</div>
				<?php } ?>
		</div>
	</div>

