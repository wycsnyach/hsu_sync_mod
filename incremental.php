<?php

    session_start();

	header("Expires: 0");
	header("cache-control: no-store, no-cache, must-revalidate"); 
	header("Pragma: no-cache");

	$date_back = NULL;
	$init_params = parse_ini_file("sync.ini", true);

	$hostname 	= $init_params['database']['hostname'];
	$db 		= $init_params['database']['db'];
	$username 	= $init_params['database']['username'];
	$password 	= $init_params['database']['password'];
	$project 	= $_SESSION['project'];
	$updateType = $_SESSION['updateType'];

	if (!$conn = mysql_connect($hostname,$username,$password)) { 
		exit("The hostname ($hostname) / username ($username) / password (XXXXXX) combination could not connect to the MySQL server. Please check their values."); 
	}
	if (!$db_conn = mysql_select_db($db,$conn)) { 
		exit("The hostname ($hostname) / database ($db) / username ($username) / password (XXXXXX) combination could not connect to the MySQL server. Please check their values."); 
	}
	
	if(isset($_POST['project']) && !is_null($_POST['project'])){
		$project = $_POST['project'];
		$_SESSION['project'] = $project;	
	}
	
	if(isset($_POST['updateType']) && !is_null($_POST['updateType'])){
		$updateType = $_POST['updateType'];
		$_SESSION['updateType'] = $updateType;
	}

	if($project=="cin"){
		$project_id=$init_params['cin']['project_id'];
	}else if($project=="clinician"){
		$project_id=$init_params['clinician']['project_id'];
	}

	if(isset($_POST['startDate']) && !is_null($_POST['startDate'])){
		$startDate = $_POST['startDate'];
	}

	if(isset($_POST['endDate']) && !is_null($_POST['endDate'])){
		$endDate = $_POST['endDate'];
	}
	
	if(isset($_POST['today']) && !is_null($_POST['today'])){
		$today = $_POST['today'];
	}
	
	if($updateType=="full"){
		$incremental_records = "SELECT DISTINCT(record) FROM redcap_data WHERE project_id =".$project_id;
	}else{
		$incremental_records = "SELECT DISTINCT(record) FROM redcap_data WHERE project_id =".$project_id." AND field_name = 'date_today' AND value <= '".$endDate."' AND value >= '".$startDate."'";
	}

	$query = mysql_query($incremental_records);

	$records = array();
	while($row = mysql_fetch_assoc($query)) {
		array_push($records,$row['record']);
	}

	$record_count = mysql_num_rows($query);	

	mysql_free_result($query);

	unset($_SESSION['records']);
	unset($_SESSION['record_count']);

	$_SESSION['records'] = $records; 
	$_SESSION['record_count'] = $record_count;

	if(!$_SESSION['first_run']){
		if($startDate > $endDate){
			header('HTTP/1.1 500 Internal Server Booboo');
			header('Content-Type: application/json; charset=UTF-8');
			echo json_encode(array(
				'message' => 'ERROR',
				'code'=> 'Badly entered date range'
			));
		}else{
			echo json_encode($record_count);
		}		
	}
?>
