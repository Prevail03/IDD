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
                <th>Created At</th>
				<th>Done By</th>
				<th>Action</th>
			</tr>
		</thead>
		<tbody>
			<?php
				$sql = "SELECT 
                    idd.batch_id,
                    idd.scheme_code,
                    idd.period_id,
                    sp.period_name,
                    sp.period_start_date,
                    sp.period_end_date,
                    idd.sub_period_id,
                    s.sub_period_name,
                    s.sub_period_start_date,
                    s.sub_period_end_date,
                    idd.created_at,
                    idd.status,
                    COUNT(stg.staging_id) AS total_members,
                    SUM(stg.net_salary) AS total_net_salary
                FROM mydb.dbo.idd_payroll_batches idd
                INNER JOIN mydb.dbo.scheme_sub_periods_tb s 
                    ON s.sub_period_id = idd.sub_period_id
                INNER JOIN mydb.dbo.scheme_periods_tb sp
                    ON sp.period_id = idd.period_id
                LEFT JOIN mydb.dbo.idd_payroll_staging stg
                    ON stg.batch_id = idd.batch_id
                WHERE idd.scheme_code = ? 
                GROUP BY 
                    idd.batch_id,
                    idd.scheme_code,
                    idd.period_id,
                    sp.period_name,
                    sp.period_start_date,
                    sp.period_end_date,
                    idd.sub_period_id,
                    s.sub_period_name,
                    s.sub_period_start_date,
                    s.sub_period_end_date,
                    idd.created_at,
                    idd.status
                ORDER BY idd.created_at DESC;";
				$params = array($_SESSION['scheme_code'],1,1);
				$stmt = sqlsrv_query( $conn, $sql ,$params);
			    if( $stmt === false) {
				  die( print_r( sqlsrv_errors(), true) );
				}
                $i = 1;
				while( $row = sqlsrv_fetch_array( $stmt, SQLSRV_FETCH_ASSOC) ) {
				?>
				<tr>
					<td><?php echo $i++; ?></td>
					<td><?php echo($row['period_name']); ?></td>
					<td><?php echo($row['sub_period_name']); ?></td>
					<td><?php echo($row['status']); ?></td>
					<td><?php echo($row['total_members']); ?></td>
					<td><?php echo($row['total_net_salary']); ?></td>
                    <td><?php echo $row['created_at']->format('Y-m-d H:i:s'); ?></td>
                    <td><?php echo($_SESSION['user_name']); ?></td>
					<td>
						<div class="dropdown">
						  <button type="button" class="btn btn-primary dropdown-toggle" data-toggle="dropdown">
						    Action
						  </button>
						  <div class="dropdown-menu">
						    <a class="dropdown-item" href="./pen_payroll_payees.php?batch_id=<?php echo($row['batch_id']); ?>">Payees</a>
						    <a class="dropdown-item" href="./pen_payrolls_processed.php?action=2&batch_id=<?php echo($row['batch_id']); ?>">Restore</a>
						    <a class="dropdown-item" href="./pen_payrolls_processed.php?action=3&batch_id=<?php echo($row['batch_id']); ?>">Check</a>
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