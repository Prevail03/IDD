<?php
session_start();
if(!isset($_SESSION['auth_code'])){
	header("location: ../logout.php");
	exit("User not authenticated");
}
//Can access this module
if($_SESSION['pension'] != 1){
	$_SESSION['module_error'] = "You are not allowed to access this module";
	header("location: ../modules.php");
	exit();
}
require('../commons/config/settings.php');
require('./menu.php');

if($_SESSION['user_folder'] !== Settings::$folder){
	header("location: ../logout.php");
	exit("Folder inaccessible");
}

// Create connection
$conn = sqlsrv_connect( Settings::$serverName, Settings::$connectionInfo);



?>
<!DOCTYPE html>
<html lang="en">
<head>
  <title>Pension Module</title>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="stylesheet" href="../commons/bootstrap/css/bootstrap.min.css">
  <link rel="stylesheet" href="../commons/bootstrap/css/custom.css">
  <script src="../commons/js/jquery.js"></script>
  <script src="../commons/js/popper.min.js"></script>
  <script src="../commons/bootstrap/js/bootstrap.min.js"></script>
  <link rel="icon" type="image/ico" href="../commons/images/favicon.ico">
</head>
<body>

<div class="container-fluid sticky-top" style="background-color: #eee;">

	<div class="row">
		<div class="col-sm-4">
			<img src="../commons/images/Octagon_logo.png" width="300" height="100"/>
		</div>
		<div class="col-sm-8">
			<h2><?php echo($_SESSION['scheme_code'].": ".$_SESSION['scheme_name']); ?></h2>
			<h3>DC Plan: Payroll Batches</h3>
		</div>
	</div>
	
	<div class="row">
		<div class="col-sm-12">
			<?php 
				print_menu(array("menu-pos"=>"y",
				                 "sub-menu-pos"=>"y1"));
			?>
		</div>
	</div>
	
</div>

<div class="container-fluid">
	
	<h2>Payroll Batches</h2>
	
	<?php
			
		if(isset($_SESSION['add_message'])){
			if(!empty($_SESSION['add_message'])){
				?>
				<div class="alert alert-info alert-dismissable">
			    <a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>
			    <strong>Info!</strong> <?php echo($_SESSION['add_message']); ?>
			    </div>
				<?php
				unset($_SESSION['add_message']);
			}
		}
	?>
	
	<table class="table table-striped">
		<thead class="thead-dark">
			<tr>
				<th>#</th>
				<th>Period Name</th>
				<th>Sub Period Name</th>
				<th>Status</th>
				<th>Total Members</th>
                <th>Total Net Salary</th>
				<th>Done By</th>
				<th>Action</th>
			</tr>
		</thead>
		<tbody>
			<?php
				$sql = "SELECT * FROM payroll_tb, scheme_periods_tb, scheme_sub_periods_tb WHERE payroll_scheme_code LIKE ? AND payroll_period_id = period_id AND payroll_sub_period_id = sub_period_id AND period_id = scheme_period_id AND payroll_status = ? AND payroll_posted = ? ORDER BY payroll_date DESC";
				$params = array($_SESSION['scheme_code'],1,1);
				$stmt = sqlsrv_query( $conn, $sql ,$params);
			    if( $stmt === false) {
				  die( print_r( sqlsrv_errors(), true) );
				}
				while( $row = sqlsrv_fetch_array( $stmt, SQLSRV_FETCH_ASSOC) ) {
				?>
				<tr>
					<td><?php echo($row['payroll_id']); ?></td>
					<td><?php echo($row['period_name']); ?></td>
					<td><?php echo($row['sub_period_name']); ?></td>
					<td><?php echo(date_format($row['payroll_date'],"Y-m-d")); ?></td>
					<td><?php echo($row['payroll_description']); ?></td>
					<td><?php echo($row['payroll_done_by']); ?></td>
					<td>
						<div class="dropdown">
						  <button type="button" class="btn btn-primary dropdown-toggle" data-toggle="dropdown">
						    Action
						  </button>
						  <div class="dropdown-menu">
						    <a class="dropdown-item" href="./pen_payroll_payees.php?payroll_id=<?php echo($row['payroll_id']); ?>">Payees</a>
						    <a class="dropdown-item" href="./pen_payrolls_processed.php?action=2&payroll_id=<?php echo($row['payroll_id']); ?>">Restore</a>
						    <a class="dropdown-item" href="./pen_payrolls_processed.php?action=3&payroll_id=<?php echo($row['payroll_id']); ?>">Check</a>
						  </div>
						</div>
					</td>
				</tr>
				<?php } ?>
		</tbody>
	</table>
	
</div>

</body>
</html>
<?php
	//Close connection 
	sqlsrv_close( $conn );
?>