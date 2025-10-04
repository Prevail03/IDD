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
$user_id = $_SESSION['user_id'];
// Create connection
$conn = sqlsrv_connect( Settings::$serverName, Settings::$connectionInfo);
if(isset($_GET['action'])){
	$action = $_GET['action'];
	$batch_id = $_GET['batch_id'];
}
if($action == 2){

    // 1. Approve batch
    // $sql = "UPDATE MYDB.DBO.idd_payroll_batches 
    //         SET status = 'Approved', approved_by = ? 
    //         WHERE batch_id = ? AND scheme_code = ?";
    // $params = array($_SESSION['user_name'], $batch_id, $_SESSION['scheme_code']);
    // $stmt = sqlsrv_query($conn, $sql, $params);   
    // if($stmt === false) {
    //     echo "Error in payroll batches update.\n";
    //     die(print_r(sqlsrv_errors(), true));
    // }

    // 2. Approve staging
    // $sql = "UPDATE MYDB.DBO.idd_payroll_staging 
    //         SET status = 'Approved' 
    //         WHERE batch_id = ? AND scheme_code = ?";
    // $params = array($batch_id, $_SESSION['scheme_code']);
    // $stmt = sqlsrv_query($conn, $sql, $params);  
    // if($stmt === false) {
    //     echo "Error in payroll staging update.\n";
    //     die(print_r(sqlsrv_errors(), true));
    // }

    //3. Add to contributions mydb.dbo.contributions_flat_entries_tb $ contmydb.dbo.contributions_flat_batches_tb
    //Assumptions the initiator is the person who posted tha aprover is the person loggen in rn 
    // TODO: Confirm with James Kimani if there;s any other tables and also if theres risks with the asumption above 
    // i)insert into batches_tb
    $flat_scheme_code = $_SESSION['scheme_code'];
    $batch_auto_info = md5($flat_scheme_code.time().uniqid());
    //Get batch id 
    $flat_document = "Withdrawals";
    $sql_batch = "INSERT INTO [MYDB].[dbo].[batches_tb] (batch_description,batch_scheme_code,batch_gen_date,batch_auto_info) OUTPUT INSERTED.batch_id VALUES (?,?,?,?)";
    $params_batch = array($flat_document, $flat_scheme_code, date('Y-m-d'), $batch_auto_info);
    $stmt_batch = sqlsrv_query( $conn, $sql_batch ,$params_batch );   
    if( $stmt_batch === false) {
        die( print_r( sqlsrv_errors(), true) );
    }
  
    $batch_row = sqlsrv_fetch_array($stmt_batch, SQLSRV_FETCH_ASSOC);
    $insert_batch_id  = $batch_row['batch_id'];
    // ii) insert into flat_batches_tb
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
            idd.added_by,
            COUNT(stg.staging_id) AS total_members,
            SUM(stg.net_salary) AS total_net_salary
        FROM mydb.dbo.idd_payroll_batches idd
        INNER JOIN mydb.dbo.scheme_sub_periods_tb s 
            ON s.sub_period_id = idd.sub_period_id
        INNER JOIN mydb.dbo.scheme_periods_tb sp
            ON sp.period_id = idd.period_id
        LEFT JOIN mydb.dbo.idd_payroll_staging stg
            ON stg.batch_id = idd.batch_id
        WHERE idd.scheme_code = ?  and idd.batch_id = ?
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
            idd.status,
            idd.added_by
        ORDER BY idd.created_at DESC;";
    $params = array($flat_scheme_code, $batch_id);
    $stmt = sqlsrv_query( $conn, $sql ,$params);
    if( $stmt === false) {
        die( print_r( sqlsrv_errors(), true) );
    }
   
    $flat_scheme_code = $_SESSION['scheme_code'];
    $flat_batch_id = $insert_batch_id;
    $flat_document = "Withdrawals";
    $flat_document_type = "Normal";
    $flat_posting_date = date('Y-m-d'); 
    $flat_initiated_by = '';
    $flat_initiated_on = '';
    $flat_approved_by = $_SESSION['username'];
    $flat_approved_on = date('Y-m-d H:i:s');
    $flat_approved = 1; //1 for approved 0 for not approved
    $flat_action_name = "Payroll Batch Approval";
    $flat_action_comments = "Payroll Batch Approved ";



    while( $row = sqlsrv_fetch_array( $stmt, SQLSRV_FETCH_ASSOC) ) {
        $flat_initiated_by = $row['added_by'];
        $flat_initiated_on = $row['created_at']->format('Y-m-d H:i:s');

    }
    $sql_flat = "INSERT INTO [MYDB].[dbo].[contributions_flat_batches_tb] ( flat_scheme_code ,flat_batch_id ,flat_document ,flat_document_type ,flat_posting_date ,flat_initiated_by ,flat_initiated_on ,flat_approved_by ,flat_approved_on ,flat_approved ,flat_action_name ,flat_action_comment ) 
    VALUES ( ?,?,?,?,?,?,?,?,?,?,?,?)";
    					
    $params_flat = array($flat_scheme_code ,$flat_batch_id ,$flat_document ,$flat_document_type ,$flat_posting_date ,$flat_initiated_by ,$flat_initiated_on ,$flat_approved_by ,$flat_approved_on,$flat_approved,$flat_action_name,$flat_action_comments);
   
    $stmt_flat = sqlsrv_query( $conn, $sql_flat ,$params_flat);   
    if( $stmt_flat === false) {
        die( print_r( sqlsrv_errors(), true) );
    }
    // iii) insert into flat_entries_tb
    $sql_entries = "INSERT INTO mydb.dbo.contributions_flat_entries_tb
        (
            cont_flat_batch_id,
            cont_flat_scheme_code,
            cont_flat_member_number,
            cont_flat_ee_te,
            cont_flat_ee_nte,
            cont_flat_avc_te,
            cont_flat_avc_nte,
            cont_flat_er_te,
            cont_flat_er_nte,            
            cont_flat_type,
            cont_flat_display_date,
            cont_flat_date_paid,
            cont_flat_er_te_nssf,
            cont_flat_er_nte_nssf,
            cont_flat_ee_te_nssf,
            cont_flat_ee_nte_nssf
        )
        SELECT  
            ? AS cont_flat_batch_id,
            stg.scheme_code AS cont_flat_scheme_code,
            stg.member_number AS cont_flat_member_number,
            -stg.net_salary AS cont_flat_ee_te,
            'Normal' AS cont_flat_type,
            CONVERT(date, GETDATE()) AS cont_flat_display_date,
            CONVERT(date, stg.created_at) AS cont_flat_date_paid
        FROM mydb.dbo.idd_payroll_batches idd
        JOIN mydb.dbo.idd_payroll_staging stg
            ON idd.batch_id = stg.batch_id
        WHERE idd.batch_id = ? 
        AND idd.scheme_code = ?;
    ";

    $params_entries = array($flat_batch_id, $batch_id, $_SESSION['scheme_code']);
    $stmt_entries = sqlsrv_query($conn, $sql_entries, $params_entries);  
    if($stmt_entries === false) {
        echo "Error inserting into contributions_flat_entries_tb.\n";
        die(print_r(sqlsrv_errors(), true));
    }


    exit();




    // 4. Insert transactions into testing contributions table
    $sql = "INSERT INTO mydb.dbo.contributions_tb_testing
        (
            cont_scheme_code,
            cont_combined,
            cont_member_number,
            cont_fund_owner,
            cont_document,
            cont_category,
            cont_taxation,
            cont_type,
            cont_amount,
            cont_batch_id,
            cont_date_paid,
            cont_date_posted,
            cont_done_by
        )
        SELECT  
            stg.scheme_code AS cont_scheme_code,
            CONCAT(stg.scheme_code, ':', stg.member_number) AS cont_combined,
            stg.member_number AS cont_member_number,
            'EE Fund' AS cont_fund_owner,
            'Withdrawals' AS cont_document,
            cat.cont_category,
            tax.cont_taxation,
            'Normal' AS cont_type,
            CASE  
                WHEN cat.cont_category = 'EE' AND tax.cont_taxation = 'Tax Exempt'  
                    THEN -stg.net_salary  
                ELSE 0  
            END AS cont_amount,
            ? AS cont_batch_id,
            CONVERT(date, GETDATE()) AS cont_date_paid,
            CONVERT(date, stg.created_at) AS cont_date_posted,
            ? AS cont_done_by
        FROM mydb.dbo.idd_payroll_batches idd
        JOIN mydb.dbo.idd_payroll_staging stg
            ON idd.batch_id = stg.batch_id
        CROSS JOIN (VALUES ('EE'), ('ER'), ('AVC')) AS cat(cont_category)
        CROSS JOIN (VALUES ('Tax Exempt'), ('Non Tax Exempt')) AS tax(cont_taxation)
        WHERE idd.batch_id = ? 
          AND idd.scheme_code = ?;
    ";

    $params = array($flat_batch_id,$_SESSION['user_name'], $batch_id, $_SESSION['scheme_code']);
    $stmt = sqlsrv_query($conn, $sql, $params);  
    if($stmt === false) {
        echo "Error inserting into contributions_tb_testing.\n";
        die(print_r(sqlsrv_errors(), true));
    }

    // 4. Final message
    $_SESSION['add_message'] = "Payroll batch approved and transactions inserted into testing table successfully.";
}

if($action == 3){
    $rejected_at = date('Y-m-d H:i:s');
    $sql = "UPDATE MYDB.DBO.idd_payroll_batches SET status = 'Rejected', rejected_at =?, rejected_by = ?, WHERE batch_id = ? AND scheme_code = ?";
    $params = array($rejected_at,$_SESSION['user_id'], $batch_id,$_SESSION['scheme_code']);
    $stmt = sqlsrv_query( $conn, $sql ,$params);   
    if( $stmt === false) {
        echo "Error in payroll batches statement execution.\n";
      die( print_r( sqlsrv_errors(), true) );
    }
    $sql = "UPDATE MYDB.DBO.idd_payroll_staging SET status = 'Reject' WHERE batch_id = ? AND scheme_code = ?";
    $params = array($batch_id,$_SESSION['scheme_code']);
    $stmt = sqlsrv_query( $conn, $sql ,$params);   
    if( $stmt === false) {
        echo "Error in payroll staging statement execution.\n";
      die( print_r( sqlsrv_errors(), true) );
    }


    $_SESSION['add_message'] = "Payroll batch Rejected successfully";


}


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
                    <td><?php echo($_SESSION['username']); ?></td>
					<td>
						<div class="dropdown">
						  <button type="button" class="btn btn-primary dropdown-toggle" data-toggle="dropdown">
						    Action
						  </button>
						  <div class="dropdown-menu">
						    <a class="dropdown-item" href="./view_payroll_batch_entries.php?batch_id=<?php echo($row['batch_id']); ?>">View Entries</a>
						    <a class="dropdown-item" href="./idd_payroll_batches.php?action=2&batch_id=<?php echo($row['batch_id']); ?>">Approve </a>
						    <a class="dropdown-item" href="./idd_payroll_batches.php?action=3&batch_id=<?php echo($row['batch_id']); ?>">Reject</a>
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