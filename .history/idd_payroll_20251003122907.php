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
	    
	    
	   <div class="col-sm-12 mt-3 px-3 py-3">
            <form action="idd_payroll.php" method="post" enctype="multipart/form-data">
                <input type="hidden" name="form_name" value="load_payroll"/>
                <input type="hidden" name="scheme_code" value="<?php echo($_SESSION['scheme_code']); ?>"/>

                <div class="form-group row">
                    <div class="col">
                        <label class="form-label">Select Period:</label>
                        <select class="form-control" id="periodSelect" name="period">
                            <option value="">-- Select Period --</option>
                            <?php
                            $sql = "SELECT * FROM scheme_periods_tb 
                                    WHERE period_scheme_code = '$scheme_code' 
                                    ORDER BY period_end_date DESC";
                            $stmt = sqlsrv_query($conn, $sql, [], ["Scrollable" => "buffered", "ReturnDatesAsStrings" => true]);

                            if ($stmt === false) {
                                throw new Exception('Failed to get period details. ERROR: '.print_r(sqlsrv_errors(), true));
                            }

                            while($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
                                echo '<option value="'.$row['period_id'].'">'.$row['period_name'].'</option>';
                            }
                            ?>
                        </select>
                    </div>

                    <div class="col">
                        <label class="form-label">Select Sub Period:</label>
                        <select class="form-control" id="subPeriodSelect" name="sub_period">
                            <option value="">-- Select Sub Period --</option>
                        </select>
                    </div>

                    <!-- Put the button in the same row -->
                    <div class="col-auto d-flex align-items-end">
                        <button type="submit" class="btn btn-primary">Submit</button>
                    </div>
                </div>
            </form>
        </div>
        <?php

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            if (isset($_POST['form_name']) && $_POST['form_name'] === "load_payroll") {
                $scheme_code   = $_POST['scheme_code'];
                $period        = $_POST['period'];
                $sub_period    = $_POST['sub_period'];

                // 1. Get details of selected sub period
                $sql_sub = "SELECT sub_period_start_date, sub_period_end_date 
                            FROM scheme_sub_periods_tb 
                            WHERE sub_period_id = ?";
                $params_sub = [$sub_period];
                $stmt_sub = sqlsrv_query($conn, $sql_sub, $params_sub, ["Scrollable" => "buffered"]);
                if ($stmt_sub === false) {
                    die("Error loading sub-periods: " . print_r(sqlsrv_errors(), true));
                }
                $sub_row  = sqlsrv_fetch_array($stmt_sub, SQLSRV_FETCH_ASSOC);
                $selected_start = $sub_row['sub_period_start_date'];
                $selected_end   = $sub_row['sub_period_end_date'];

                // 2. Main payroll query
                $sql = "
                SELECT 
                    p.insert_id,
                    p.member_number,
                    p.member_name,
                    p.account_number,
                    p.bank_code,
                    p.gross_after_deductions,
                    ISNULL(med.medical_amount,0) AS medical_amount,
                    ex.payment_frequency,
                    adv.insert_id AS advance_id,
                    ssp_start.sub_period_start_date AS adv_start_date,
                    ssp_end.sub_period_end_date     AS adv_end_date,
                    mt.drawdown_commence_date,
                    p.absolute_drawdown_percentage,
                    p.closing_balance
                FROM MYDB.DBO.idd_payroll p
                INNER JOIN MYDB.DBO.members_tb mt 
                    ON p.scheme_code = mt.m_scheme_code 
                AND p.member_number = mt.m_number
                LEFT JOIN MYDB.DBO.idd_medical med 
                    ON p.insert_id = med.payroll_id AND med.hidden_status='No'
                LEFT JOIN MYDB.DBO.idd_payroll_exeptions ex 
                    ON p.insert_id = ex.payroll_id AND ex.scheme_code = ? AND ex.hidden_status='No'
                LEFT JOIN MYDB.DBO.idd_advance_payroll adv 
                    ON p.insert_id = adv.payroll_id AND adv.scheme_code = ?
                LEFT JOIN MYDB.DBO.scheme_sub_periods_tb ssp_start 
                    ON adv.start_sub_period = ssp_start.sub_period_id
                LEFT JOIN MYDB.DBO.scheme_sub_periods_tb ssp_end 
                    ON adv.end_sub_period = ssp_end.sub_period_id
                WHERE p.scheme_code = ? AND p.hidden_status='No'
                ORDER BY p.member_name ASC
                ";
                $params_main = [$scheme_code, $scheme_code, $scheme_code];
                $stmt = sqlsrv_query($conn, $sql, $params_main, ["Scrollable" => "buffered"]);
                if ($stmt === false) {
                    die("Error loading members: " . print_r(sqlsrv_errors(), true));
                }

                // Buckets
                $valid_rows = [];
                $overpaid_rows = [];
                $excluded_rows = [];

                while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
                    $include   = true;
                    $overpaid  = false;

                    $gross   = (float)$row['gross_after_deductions'];
                    $medical = (float)$row['medical_amount']; // from SQL alias
                    $net     = $gross - $medical;

                    // === Withdrawals YTD ===
                    $withdrawals = 0;
                    $sql_w = "SELECT SUM(cont_amount) AS total_withdrawals
                            FROM MYDB.DBO.CONTRIBUTIONS_TB
                            WHERE cont_scheme_code=? 
                                AND cont_member_number=?
                                AND cont_document='withdrawals'
                                AND YEAR(cont_display_date) = YEAR(?)";
                    $params_w = [$scheme_code, $row['member_number'], $selected_end];
                    $stmt_w = sqlsrv_query($conn, $sql_w, $params_w);
                    if ($stmt_w && ($wrow = sqlsrv_fetch_array($stmt_w, SQLSRV_FETCH_ASSOC))) {
                        $withdrawals = (float)$wrow['total_withdrawals'];
                    }

                    $allowed_annual  = ((float)$row['absolute_drawdown_percentage'] * (float)$row['closing_balance']) / 100;

                    // ================================
                    // 1. Exceptions Handling & Scaling
                    // ================================
                    $scale_factor = 1; // default monthly
                    $month = (int)$selected_end->format('n');

                    if (!empty($row['payment_frequency'])) {
                        switch ($row['payment_frequency']) {
                            case 'Annually':
                                if ($month == 12) {
                                    $scale_factor = 12;
                                } else {
                                    $include = false;
                                }
                                break;

                            case 'Semi-Annually':
                                if (in_array($month, [6, 12])) {
                                    $scale_factor = 6;
                                } else {
                                    $include = false;
                                }
                                break;

                            case 'Quarterly':
                                if (in_array($month, [3, 6, 9, 12])) {
                                    $scale_factor = 3;
                                } else {
                                    $include = false;
                                }
                                break;

                            default: // Monthly or undefined
                                $scale_factor = 1;
                                break;
                        }
                    }

                    // Apply scaling
                    $gross   *= $scale_factor;
                    $medical *= $scale_factor;
                    $net     *= $scale_factor;

                    // ======================
                    // 2. Overpayment Check
                    // ======================
                    if (($withdrawals + $net) > $allowed_annual) {
                        $overpaid = true;
                        $include  = false;
                    }

                    // ======================
                    // 3. Categorize Members
                    // ======================
                    if ($include) {
                        $valid_rows[] = array_merge($row, [
                            'gross'       => round($gross, 2),
                            'medical'     => round($medical, 2),
                            'net'         => round($net, 2),
                            'withdrawals' => $withdrawals,
                            'allowed'     => $allowed_annual
                        ]);
                    } elseif ($overpaid) {
                        $overpaid_rows[] = array_merge($row, [
                            'gross'       => round($gross, 2),
                            'medical'     => round($medical, 2),
                            'net'         => round($net, 2),
                            'withdrawals' => $withdrawals,
                            'allowed'     => $allowed_annual
                        ]);
                    } else {
                        $excluded_rows[] = $row;
                    }
                }


                // === Output valid payroll ===
                echo "<h4>Payroll Disbursement List</h4>";
                echo "<table class='table table-bordered table-striped'>
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Member Number</th>
                        <th>Member Name</th>
                        <th>Account</th>
                        <th>Bank</th>
                        <th>Drawdown %</th>
                        <th>Closing Balance</th>
                        <th>Gross</th>
                        <th>Medical</th>
                        <th>Net Pay</th>
                    </tr>
                </thead>
                <tbody>";
                $i=1;
                foreach($valid_rows as $r){
                    echo "<tr>
                            <td>".($i++)."</td>
                            <td>{$r['member_number']}</td>
                            <td>{$r['member_name']}</td>
                            <td>{$r['account_number']}</td>
                            <td>{$r['bank_code']}</td>
                            <td>{$r['absolute_drawdown_percentage']} %</td>
                           <td>{$r['closing_balance']}</td>
                            <td>".number_format($r['gross'],2)."</td>
                            <td>".number_format($r['medical'],2)."</td>
                            <td>".number_format($r['net'],2)."</td>
                        </tr>";
                }
                echo "</tbody></table>";

                // === Expired list ===
                if (!empty($expired_rows)) {
                    echo "<h4>Expired Members (10+ Years Since Drawdown)</h4>";
                    echo "<table class='table table-warning'><tr><th>Member</th><th>Name</th><th>Drawdown Start</th></tr>";
                    foreach($expired_rows as $r){
                        echo "<tr><td>{$r['member_number']}</td><td>{$r['member_name']}</td><td>{$r['drawdown_commence_date']->format('Y-m-d')}</td></tr>";
                    }
                    echo "</table>";
                }

                // === Overpaid list ===
                if (!empty($overpaid_rows)) {
                    echo "<h4>Overpayment Cases</h4>";
                    echo "<table class='table table-danger'><tr>
                            <th>Member</th><th>Name</th><th>Net Pay</th>
                            <th>Withdrawals YTD</th><th>Allowed Annual</th>
                        </tr>";
                    foreach($overpaid_rows as $r){
                        echo "<tr>
                                <td>{$r['member_number']}</td>
                                <td>{$r['member_name']}</td>
                                <td>".number_format($r['net'],2)."</td>
                                <td>".number_format($r['withdrawals'],2)."</td>
                                <td>".number_format($r['allowed'],2)."</td>
                            </tr>";
                    }
                    echo "</table>";
                }
            }
        }
        ?>

	    
	</div>
	
</div>



<script>
    function loadSubPeriods(periodSelectId, subPeriodSelectId) {
      let periodId = document.getElementById(periodSelectId).value;
      let subPeriodSelect = document.getElementById(subPeriodSelectId);

      // Reset options
      subPeriodSelect.innerHTML = '<option value="">-- Select Sub Period --</option>';

      if (periodId) {
          fetch('get_sub_periods.php?period_id=' + periodId)
              .then(response => response.json())
              .then(data => {
                  data.forEach(sub => {
                      let opt = document.createElement('option');
                      opt.value = sub.sub_period_id;
                      opt.textContent = sub.sub_period_name;
                      subPeriodSelect.appendChild(opt);
                  });
              })
              .catch(err => {
                  console.error('Error fetching sub periods:', err);
              });
      }
  }

  // Attach events
  document.getElementById('periodSelect')
      .addEventListener('change', () => loadSubPeriods('periodSelect', 'subPeriodSelect'));

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