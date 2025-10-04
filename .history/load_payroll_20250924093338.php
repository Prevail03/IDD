<?php
ini_set('display_errors', 1); ini_set('display_startup_errors', 1); error_reporting(E_ALL);
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
require(__DIR__.'/../../macros/funcs.php');

if($_SESSION['user_folder'] !== Settings::$folder){
	header("location: ../logout.php");
	exit("Folder inaccessible");
}

// Create connection
$conn = sqlsrv_connect( Settings::$serverName, Settings::$connectionInfo);
$scheme_code = $_SESSION['scheme_code'];
?>
<?php


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
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.1/css/all.min.css" integrity="sha512-2SwdPD6INVrV/lHTZbO2nodKhrnDdJK9/kg2XD1r9uGqPo1cUbujc+IYdlYdEErWNu69gVcYgdxlmVmzTWnetw==" crossorigin="anonymous" referrerpolicy="no-referrer" />
  <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
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
			<h3>Drawdown Payrolls</h3>
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

<div class="container-fluid  px-5 py-3">
	
	<div class="row"  x-data="initiatePayrollForm()" >
	    <div class="col-sm-9">
	        <h4>Drawdown Payrolls</h4>
	    </div>
	    
	    
	    <div class="col-sm-3">
	    <button type="button" class="w-100 btn btn-primary" @click="showForm()">Process Payroll</button>
	    </div>
	    
	    
	    <div class="col-sm-12 mt-3 px-3 py-3" style="background-color: #f5f5f5; border-radius: 5px" x-show="open">
	        <div class="form-group col-sm-3">
              <label for="sel1">Select Period:</label>
              <select class="form-control" >
                <?php
                $sql = "SELECT * FROM scheme_periods_tb WHERE period_scheme_code = '$scheme_code' ORDER BY period_end_date DESC";
                $stmt = sqlsrv_query( $conn, $sql ,$params, array('Scrollable' => 'buffered', 'ReturnDatesAsStrings' => true));
                if( $stmt === false) {
            	 
            	  throw new Exception('Failed to get period details from the database. ERROR: '.print_r( sqlsrv_errors(), true));
    	        }
    	        
                while($row = sqlsrv_fetch_array( $stmt, SQLSRV_FETCH_ASSOC)) {
                    echo '<option value="'.$row['period_id'].'">'.$row['period_name'].'</option>';
                }
                
                
                ?>
              </select>
            </div>
	    </div>
        <div class="row">
            <div class="col-sm-12">
                <form action="load_payroll.php" method="post" enctype="multipart/form-data">
                    <input type="hidden" name = "formname" value="load_payroll"/>

                    <div class="form-group">
                        <label for="file">Attach File</label>
                        <input type="file" class="form-control form-control-file" name="payroll"  accept=".xlsx" required=""/>
                    </div>
                    <button type="submit" class="btn btn-primary">Submit</button>
                </form>
            </div>
        </div>
                
	</div>
	
</div>



<script>
function initiatePayrollForm() {
    return {
        open: false, 
        showForm: function () {
            this.open = ! this.open
        }
        
    }
}
</script>
</body>
</html>
<?php
	//Close connection 
	sqlsrv_close( $conn );
?>