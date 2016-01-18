<?php

	error_reporting(0);
	session_start(); 
	header("Expires: 0");
	header("cache-control: no-store, no-cache, must-revalidate"); 
	header("Pragma: no-cache");
	ini_set('memory_limit','1024M');	
	ini_set('max_execution_time','256000');

	// Parse with sections
	$init_params = parse_ini_file("sync.ini", true);

	$today = date("Y-m-d");
	$month_ago =  date('Y-m-d', strtotime("now -30 days"));

	$_SESSION['record_count'] = 0;
	$_SESSION['records'] = array();
	$_SESSION['project']= "cin";
	$_SESSION['updateType']="full";	
	$_SESSION['init_params'] = $init_params;
	$_SESSION['first_run']=TRUE;

	require_once("incremental.php");

?>
<!DOCTYPE html>
<html class="no-js">
<head>
  	<meta http-equiv="content-type" content="text/html; charset=UTF-8">
   	<title>HSU REDCap Data Synchronization System</title>
   	<script src="js/jquery-1.10.2.min.js" type="text/javascript"></script>
	<script src="js/jquery-ui-1.10.3.custom.min.js" type="text/javascript"></script>
	<script src="js/modernizr.js" type="text/javascript"></script>
	<script type="text/javascript">

		var endDate = <?php echo json_encode($today); ?>;
		var maxDate = endDate;
		var minDate = "2013-09-16";
		var startDate = <?php echo json_encode($month_ago); ?>

		$.fn.center = function () {
    			this.css("position","absolute");
    			this.css("top", Math.max(0, (($(window).height() - $(this).outerHeight()) / 2) + 
                                                $(window).scrollTop()) + "px");
    			this.css("left", Math.max(0, (($(window).width() - $(this).outerWidth()) / 2) + 
                                                $(window).scrollLeft()) + "px");
    			return this;
		}
		$(document).ready(function(){
			$('#loading').hide();
			$('#page').center();
			$("#startDate").val(startDate);	
			$("#endDate").val(endDate);	
			$("#full").prop("checked", true);
			$("#cin_karatina").prop("checked", true);
			$("#startDate").datepicker({
				<?php $_SESSION['first_run']=FALSE; ?>
			     changeMonth:true,
			     changeYear:true,
			     minDate: "2013-09-16",
			     maxDate:endDate,
			     defaultDate:startDate,
			     yearRange:"-5:+0",
			     dateFormat:"yy-mm-dd",
			     showOn: "button",
			     buttonImage:"http://theonlytutorials.com/demo/x_office_calendar.png",
	             onSelect: function(dateText){
					var project = $("input[name='project']:checked").val();
					var updateType = $("input[name='updateType']:checked").val();
					startDate = dateText;
					$.ajax({
	            	        url: 'incremental.php',
	            	        type: 'POST',
	            	        data: {"startDate": dateText,"endDate":endDate,"updateType": updateType,"project":project},
	            	        dataType: "json",
	            	        success: function(data){
		            	        $("#record_count").text(data);									
	            	        },
							error: function(response){
								$('#startDate').datepicker('setDate', minDate);
								startDate = minDate;
								alert('The end date cannot be earlier than start date');
							}
	            	    });
	             }
			});
			$("#endDate").datepicker({
				<?php $_SESSION['first_run']=FALSE; ?>
				
			     changeMonth:true,
			     changeYear:true,
			     minDate: "2013-09-16",
			     maxDate:endDate,
			     defaultDate:endDate,
			     yearRange:"-5:+0",
			     dateFormat:"yy-mm-dd",
			     showOn: "button",
			     buttonImage:"http://theonlytutorials.com/demo/x_office_calendar.png",
	             onSelect: function(dateText){
					var project = $("input[name='project']:checked").val();
					var updateType = $("input[name='updateType']:checked").val();
					endDate = dateText;
					$.ajax({
	            	        url: 'incremental.php',
	            	        type: 'POST',
	            	        data: {"startDate": startDate,"endDate":dateText,"updateType": updateType,"project":project},
	            	        dataType: "json",
	            	        success: function(data){
		            	        $("#record_count").text(data);									
	            	        },
							error: function(response){
								$('#endDate').datepicker('setDate', maxDate);
								endDate = maxDate;
								alert('The end date cannot be earlier than start date');
							}
	            	});
	             }
			});
			$("input[name='updateType']").change(function(){
				<?php $_SESSION['first_run']=FALSE; ?>
				var updateType = $(this).val();
				var project = $("input[name='project']:checked").val();
				$.ajax({
					url: 'incremental.php',
                    type: 'POST',
					data: {"startDate": startDate,"endDate":endDate,"updateType": updateType,"project":project},
					dataType: "json",
					success: function(data) {
							$("#record_count").text(data);
					}
				});
			});	
			
			$("input[name='project']").change(function(){
				<?php $_SESSION['first_run']=FALSE; ?>
				var project = $(this).val();
				var updateType = $("input[name='updateType']:checked").val();
				$.ajax({
					url: 'incremental.php',
					type: 'POST',
					data: {"startDate": startDate,"endDate":endDate,"project":project,"updateType": updateType},
					dataType: "json",
					success: function(data) {
							$("#record_count").text(data);
					}
                		});
			});
			
			$('#loadCin').click(function(){
				$('#block').fadeIn();
				$('#loading').fadeIn();
				$.ajax({
					url: 'cin.php',
					type: 'GET',
					dataType: "json",
					success: function(data) {
						$('#block').fadeOut();
			 			$("#loading").fadeOut();
						$("#content").html(data);
					},
					error: function(){
						$('#block').fadeOut();
			 			$("#loading").fadeOut();
					}
                		});
			});
	});
	</script>
	<link href='http://fonts.googleapis.com/css?family=Electrolize:400' rel='stylesheet' type='text/css'>
   	<link href='http://fonts.googleapis.com/css?family=Signika Negative:400' rel='stylesheet' type='text/css'>
   	<link href='http://fonts.googleapis.com/css?family=Belleza:400' rel='stylesheet' type='text/css'>
	<link href="http://code.jquery.com/ui/1.10.3/themes/smoothness/jquery-ui.css" rel="stylesheet" type='text/css' />
	<style type="text/css">

		a:link {color:  #283B1D; font-weight:bold; text-decoration:none;}    /* unvisited link */
		a:visited {color:#185397;} /* visited link */
		a:hover {color:#E46431;text-decoration:underline;}   /* mouse over link */
		a:active {color:#0000FF;}  /* selected link */

		body{
			background-color:#FFFFF0;
			padding:4px;
			font-family:Belleza,Verdana, Geneva, Arial, Helvetica, sans-serif;
		}
		
		label{
			padding:3px 1px 0px 3px;
			margin:3px 1px 0px 3px;
		}

		#page{
			background-color:#EEEEEE;
			-webkit-border-radius: 6px;
			-moz-border-radius: 6px;
			border-radius: 6px;
			-webkit-box-shadow:3px 3px 1px rgba(50, 50, 50, 0.49);
			-moz-box-shadow: 3px 3px 1px rgba(50, 50, 50, 0.49);
			box-shadow:3px 3px 1px rgba(50, 50, 50, 0.49);
		}

		#content{
			padding:8px;
		}

		ul li
		{
			padding:6px;
			border-bottom: 1px #E0EAF1 dashed;
			list-style:none;
			width:550px;
		}
		h2
		{
			font-size: 20pt;
			color: #3E78FD;
			padding-top: 12px;
			padding-bottom: 3px;
		}
		h3
		{
			color:#BD313C;
			font-size:24px;
			padding:2px;
			border-bottom: 1px #9A9A9A solid;
		}
		h4{
			color:#EF8222;
			margin:auto;
			margin-top:0px;
			margin-bottom:0px;
		}
		span{
			font-size:18px;
			color:#265DF8;
			font-family:Electrolize,Verdana, Geneva, Arial, Helvetica, sans-serif;
		}
		label{
			font-size:16px;
			color:#9D0E02;
			font-family:Signika Negative,Verdana, Geneva, Arial, Helvetica, sans-serif;
		}
		input#gText:focus{
			outline:0 none !important;
		}
		input[type="text"]{
			font-size:18px;
			color:#BA0001;
			font-family:Electrolize,Verdana, Geneva, Arial, Helvetica, sans-serif;
		}
		#param{
			width:90%;
			margin:auto;
		}
		#block{
			background: #000;
			opacity:0.6;
			position: fixed;
			width: 100%;
			height: 100%;
			top:0;
			left:0;
			display:none;
		}
		.ui-datepicker{
    		background: #000;
    		opacity:0.8;
		}
		#loading
		{
		   position:absolute;
		   top: 50%;
		   left: 50%;
		   width: 300px;
		   height: 300px;
		   margin-top: -150px; /* Half the height */
		   margin-left: -150px; /* Half the width */
		}

	</style>
</head>
<body>
	<div id="page" style="width:75%;max-width:680px; height:450px; top:25%;">
		<section style="padding:5px;text-align:center;">
			<div style="height:46px;width:90%;background:url(images/logo.png);background-repeat:no-repeat;background-position:center;"></div>
			<h3>Health Services Unit</h3>
		</section>
		<div id="block"></div>
		<div id="content">
			<div id="param">
				<span id="updateType">Update Type:</span>
				<label for="incremental">Incremental update</label><input id="incremental" name="updateType" type="radio" value="incremental" /> 
				<label for="full">Full update</label><input id="full" name="updateType" type="radio" value="full" />
				<br clear="all"/>
				<p></p>				
				<div id="startDefault" style='float:left;width=50%;'>				
					<span>Start Date: </span><input type="text" id="startDate" readonly size="12" />
				</div>	
				<br clear="all" />
				<p></p>
				<div id="startCustom" style='float:left;width=50%'>
					<span>End Date: </span><input type="text" id="endDate" readonly size="12" />
				</div>
				<br clear="all" />
				<p></p>
				<span>Record Count: </span>
				<span style="color:#BA0001;" id="record_count"> <?php echo $_SESSION['record_count']; ?> </span>
				<br clear="all"/>				
				<p></p>
				<span id="updateType">Project Name:</span>
				<label for="project">Effie Wycsnyach</label><input id="cin" name="project" type="radio" value="cin" />
				<label for="project">Zara Wycsnyach</label><input id="clinician" name="project" type="radio" value="clinician" />
				
           </div>
			<ul>
				<li><a id="loadCin" href="#">Synchronize Project Data</a></li>
			</ul>
		<img src="images/loading.gif" id="loading"></img>
	</div>
</body>
</html>

