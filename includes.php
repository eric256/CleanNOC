<?php

function formatInterval($interval) {
	$return = array();
	
	if ($interval->m > 1 ) {
		array_push($return, $interval->format('%m Months'));
	} elseif ($interval->m == 1) {
		array_push($return, $interval->format('%m Month'));
	}

	if ($interval->d > 1 ) {
		array_push($return, $interval->format('%d Days'));
	} elseif ($interval->d == 1) {
		array_push($return, $interval->format('%d Day'));
	}

	if ($interval->h > 1 ) {
		array_push($return, $interval->format('%h Hours'));
	} elseif ($interval->h == 1) {
		array_push($return, $interval->format('%h Hour'));
	}


	if ($interval->i > 1 ) {
		array_push($return, $interval->format('%i Minutes'));
	} elseif ($interval->i == 1) {
		array_push($return, $interval->format('%i Minute'));
	}

	if ($interval->s > 1 ) {
		array_push($return, $interval->format('%s Seconds'));
	} elseif ($interval->s == 1) {
		array_push($return, $interval->format('%s Second'));
	}
	
	return join(", ", array_slice($return, 0,2));	
}

function statusToClass($status) {
	if ($status == 2) {
		$class = "noc_critical";
	} elseif ($status == 1 || $status == 3) {
		$class = "noc_warning";
	} else {
		$class = "noc_ok";
	}
	return $class;
}

function hostStatusToClass($status) {
	if ($status == 0) {
		$class = "noc_ok";
	} else {
		$class = "noc_critical";
	}

	return $class;
}

function queryServicesRow($query) {
	// limit what the user can see
	$args = array(
		"sql" => $query,
		"objectauthfields" => array(
			"nagios_servicestatus.service_object_id",
		),
		"objectauthperms" => P_READ,
	);
	$query = limit_sql_by_authorized_object_ids($args);

	$result = mysql_query($query);
	$row = mysql_fetch_array($result);
	return $row;
}

function queryServicesAll($query,$order_by) {
	// limit what the user can see
	$args = array(
		"sql" => $query,
		"objectauthfields" => array(
			"nagios_servicestatus.service_object_id",
		),
		"objectauthperms" => P_READ,
	);
	$query = limit_sql_by_authorized_object_ids($args);

	$result = mysql_query($query .  $order_by);
	$rows = array();
	
	while ($row = mysql_fetch_assoc($result)) {
		array_push($rows, $row);
	}
	return $rows;
}


function getServicesStatus() {
	$results = array();

	$select_agg = "SELECT count(*) as total, max(nagios_servicestatus.current_state) as status ";
	$select_rows = "SELECT obj1.name1 AS host_name, obj1.name2 AS service_name, nagios_servicestatus.current_state as status, nagios_servicestatus.output, nagios_servicestatus.last_hard_state_change,nagios_servicestatus.last_time_ok, nagios_servicestatus.last_check, nagios_servicestatus.problem_has_been_acknowledged,
			nagios_hosts.address as ha, nagios_hosts.alias as host_description, nagios_hoststatus.problem_has_been_acknowledged AS hack, nagios_services.service_id as sid ,
			nagios_servicestatus.servicestatus_id as ssid,
			nagios_hosts.host_object_id AS hid ";
	
	$base_joins = "
			FROM nagios_servicestatus 
			LEFT JOIN nagios_objects AS obj1 ON nagios_servicestatus.service_object_id=obj1.object_id 
			LEFT JOIN nagios_services ON nagios_servicestatus.service_object_id=nagios_services.service_object_id 
			LEFT JOIN nagios_hosts ON nagios_services.host_object_id=nagios_hosts.host_object_id
			LEFT JOIN nagios_hoststatus ON nagios_hosts.host_object_id=nagios_hoststatus.host_object_id
			";
			
			
	$base_where = " WHERE nagios_hoststatus.problem_has_been_acknowledged='0' AND nagios_hoststatus.last_hard_state='0' AND nagios_hoststatus.current_state='0' AND nagios_hoststatus.scheduled_downtime_depth='0' ";
	$all_where  = " WHERE 1=1 ";
	$up_query   = $base_where . " AND nagios_servicestatus.scheduled_downtime_depth='0' AND nagios_hoststatus.scheduled_downtime_depth='0' AND nagios_servicestatus.problem_has_been_acknowledged='0' AND nagios_servicestatus.current_state  = '0' ";
	$down_query = $base_where . " AND nagios_servicestatus.scheduled_downtime_depth='0' AND nagios_hoststatus.scheduled_downtime_depth='0' AND nagios_servicestatus.problem_has_been_acknowledged='0' AND nagios_servicestatus.current_state != '0' ";
	$ack_query  = $base_where . " AND nagios_servicestatus.scheduled_downtime_depth='0' AND nagios_hoststatus.scheduled_downtime_depth='0' AND nagios_servicestatus.problem_has_been_acknowledged='1' AND nagios_servicestatus.current_state != '0' ";
	$sch_query  = $base_where . " AND nagios_servicestatus.scheduled_downtime_depth!='0' AND nagios_hoststatus.scheduled_downtime_depth='0' AND nagios_servicestatus.problem_has_been_acknowledged='1' AND nagios_servicestatus.current_state != '0' ";
	
	$order_by = " ORDER BY nagios_servicestatus.last_time_ok DESC";
	 
	$row = queryServicesRow($select_agg . $base_joins . $down_query);
	$results['down']   = $row[0];	
	$results['status'] = $row[1];
	
    $row = queryServicesRow($select_agg . $base_joins . $ack_query);
	$results['ack'] = $row[0];			
	
	$row = queryServicesRow($select_agg . $base_joins . " WHERE 1=1 ");
	$results['total'] = $row[0];

	$results['down_pct'] = round($results['down'] / $results['total'] * 100, 2);
	$results['up']       = $results['total'] - $results['down'];
	$results['up_pct']   = round($results['up'] / $results['total'] * 100, 2);
	
	//$results['down_services'] = queryServicesAll($select_rows . $base_joins . $base_where . $down_query);
	$results['down_services'] = queryServicesAll($select_rows . $base_joins . $down_query, $order_by);

	return $results;
}


function queryHostRow($query) {
         
	$args = array(
		"sql" => $query,
		"objectauthfields" => array(
			"nagios_hoststatus.host_object_id",
		),
		"objectauthperms" => P_READ,
	);
	$query = limit_sql_by_authorized_object_ids($args);
	
	$result = mysql_query($query);
	$row = mysql_fetch_assoc($result);
	return $row;	
}

function queryHostsAll($query, $order_by) {
	// limit what the user can see
	$args = array(
		"sql" => $query,
		"objectauthfields" => array(
			"nagios_hoststatus.host_object_id",
		),
		"objectauthperms" => P_READ,
	);
	$query = limit_sql_by_authorized_object_ids($args);

	$result = mysql_query($query . $order_by);
	$rows = array();
	
	while ($row = mysql_fetch_assoc($result)) {
		array_push($rows, $row);
	}
	return $rows;
}


function getHostsStatus() {
	
	$results = array();
 	$select_agg  = "SELECT count(*) as total ";
	$select_rows = "SELECT obj1.name1 AS host_name, nagios_hosts.alias, nagios_hosts.address, current_state as status, problem_has_been_acknowledged , nagios_hoststatus.last_check,nagios_hoststatus.last_time_up ";
	$base_joins = " FROM nagios_hoststatus 
	                LEFT JOIN nagios_objects AS obj1 ON nagios_hoststatus.host_object_id=obj1.object_id 
					LEFT JOIN nagios_hosts ON nagios_hoststatus.host_object_id=nagios_hosts.host_object_id 
				   ";
	
	$query_down = " WHERE scheduled_downtime_depth='0' AND problem_has_been_acknowledged  = '0' AND current_state!='0'";
	$query_ack  = " WHERE scheduled_downtime_depth='0' AND problem_has_been_acknowledged != '0' AND current_state!='0'";
	
	$query_warn  = " WHERE scheduled_downtime_depth='0' AND problem_has_been_acknowledged != '0' AND (current_state = '1' or current_state = '3')";
	
	$query_exclude_down_parents = " AND (SELECT count(1) FROM nagios_host_parenthosts ph JOIN nagios_hoststatus ON nagios_hoststatus.host_object_id = ph.parent_host_object_id
														WHERE ph.host_id = nagios_hosts.host_id
														  AND scheduled_downtime_depth='0' 
														  AND problem_has_been_acknowledged='0' 
														  AND current_state!='0'
									) < 1";

	$query_all  = " WHERE 1=1";
	
	$order_by = " ORDER BY nagios_hoststatus.last_time_up DESC";

	$row = queryHostRow($select_agg . $base_joins . $query_all);
	$results['total'] = $row['total'];
	
	$row = queryHostRow($select_agg . $base_joins . $query_ack);
	$results['ack'] = $row['total'];
	
	$row = queryHostRow($select_agg . $base_joins . $query_down, $order_by);
	$results['down']   = $row['total']; 
	

	if ($results['down'] == 0) {
		$results['status'] == 0;
	} else {
		$results['status'] = 2;
	}
	
	$results['down_hosts'] = queryHostsAll($select_rows . $base_joins . $query_down . $query_exclude_down_parents);

	return $results;
}

