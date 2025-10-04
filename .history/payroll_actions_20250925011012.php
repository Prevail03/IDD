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
$conn = sqlsrv_connect(Settings::$serverName, Settings::$connectionInfo);
$scheme_code = $_SESSION['scheme_code'];
?>
<?php 
    date_default_timezone_set('Africa/Nairobi'); 
    $user_id = $_SESSION['user_id'];
    if($_GET['delete_payroll']){
        $insert_id = $_GET['delete_payroll'];
        $deleted_at = date('Y-m-d H:i:s');
         $sql = "UPDATE MYDB.DBO.idd_payroll SET hidden_status = 'Yes', deleted_at = ?, deleted_by = ? WHERE insert_id = ?"; 
            $params = [$account_number, $bank_code, $gross_after_deductions, $deleted_at, $user_id, $insert_id]; 
            $stmt = sqlsrv_query($conn, $sql, $params); 
            if ($stmt === false) { 
                die("Update failed: " . print_r(sqlsrv_errors(), true)); 
            } 
            echo "<div class='alert alert-success'>Payroll data updated successfully!</div>"; 

    }

    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        if (isset($_POST['form_name']) && $_POST['form_name'] === "update_payroll") {
            $insert_id = $_POST['insert_id'];
            $account_number = trim($_POST['account_number']); 
            $bank_code = !empty($_POST['bank_code']) ? intval($_POST['bank_code']) : null; 
            $gross_after_deductions = !empty($_POST['gross_after_deductions']) ? floatval($_POST['gross_after_deductions']) : 0.00; 
            $updated_at = date('Y-m-d H:i:s'); 
            $sql = "UPDATE MYDB.DBO.idd_payroll SET account_number = ?, bank_code = ?, gross_after_deductions = ? , updated_at = ?, updated_by = ? WHERE insert_id = ?"; 
            $params = [$account_number, $bank_code, $gross_after_deductions, $updated_at, $user_id, $insert_id]; 
            $stmt = sqlsrv_query($conn, $sql, $params); 
            if ($stmt === false) { 
                die("Update failed: " . print_r(sqlsrv_errors(), true)); 
            } 
            echo "<div class='alert alert-success'>Payroll data updated successfully!</div>"; 
        }else if(isset($_POST['form_name']) && $_POST['form_name'] === "update_medical"){
            $insert_id = $_POST['insert_id'];
            $medical_amount = trim($_POST['medical_amount']); 
            $bank_code = !empty($_POST['bank_code']) ? intval($_POST['bank_code']) : null; 
            $gross_after_deductions = !empty($_POST['gross_after_deductions']) ? floatval($_POST['gross_after_deductions']) : 0.00; 
            $updated_at = date('Y-m-d H:i:s'); 
            $sql = "UPDATE MYDB.DBO.idd_payroll SET account_number = ?, bank_code = ?, gross_after_deductions = ? , updated_at = ?, updated_by = ? WHERE insert_id = ?"; 
            $params = [$account_number, $bank_code, $gross_after_deductions, $updated_at, $user_id, $insert_id]; 
            $stmt = sqlsrv_query($conn, $sql, $params); 
            if ($stmt === false) { 
                die("Update failed: " . print_r(sqlsrv_errors(), true)); 
            } 
            echo "<div class='alert alert-success'>Payroll data updated successfully!</div>"; 

        }
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
                print_menu(array("menu-pos"=>"y","sub-menu-pos"=>"y1"));
            ?>
        </div>
    </div>
</div>

<div class="container-fluid  px-5 py-3">
    <h4>IDD Payroll Actions</h4>
    <div class="row">
        <div class="col-sm-12">
            <!-- Tabs Nav -->
            <ul class="nav nav-tabs" id="payrollTabs" role="tablist">
                <li class="nav-item"><a class="nav-link active" id="payroll-tab" data-toggle="tab" href="#payroll" role="tab">Payroll</a></li>
                <li class="nav-item"><a class="nav-link" id="medical-tab" data-toggle="tab" href="#medical" role="tab">Medical</a></li>
                <li class="nav-item"><a class="nav-link" id="advance-tab" data-toggle="tab" href="#advance" role="tab">Advances</a></li>
                <li class="nav-item"><a class="nav-link" id="exceptions-tab" data-toggle="tab" href="#exceptions" role="tab">Exceptions</a></li>
            </ul>

            <!-- Tabs Content -->
            <div class="tab-content" id="payrollTabsContent">

                <!-- PAYROLL TAB -->
                <div class="tab-pane fade show active p-3" id="payroll" role="tabpanel">
                    <h5>Payroll Records</h5>
                    <?php
                    $sql = "SELECT su.user_username, idd.* 
                            FROM MYDB.DBO.idd_payroll AS idd
                            INNER JOIN MYDB.DBO.sys_users_tb AS su ON su.user_id = idd.added_by
                            WHERE idd.scheme_code = '$scheme_code' AND idd.hidden_status = 'No'
                            ORDER BY idd.member_name ASC";
                    $stmt = sqlsrv_query($conn, $sql);

                    if ($stmt !== false) { ?>
                        <input type="text" id="payrollSearch" class="form-control mb-3" placeholder="Search payroll records...">
                        <table class="table table-bordered table-striped mt-3" id="payrollTable">
                            <thead>
                                <tr>
                                    <th>#</th><th>Member Number</th><th>Member Name</th>
                                    <th>Account Number</th><th>Bank Code</th>
                                    <th>Gross After Deductions</th><th>Created By</th><th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                            <?php $i=1; while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) { ?>
                                <tr>
                                    <form action="idd_payroll_actions.php" method="POST">
                                        <td><?= $i++; ?></td>
                                        <td><?= htmlspecialchars($row['member_number']); ?></td>
                                        <td><?= htmlspecialchars($row['member_name']); ?></td>
                                        <td><input type="text" name="account_number" class="form-control form-control-sm" value="<?= htmlspecialchars($row['account_number']); ?>"></td>
                                        <td><input type="number" name="bank_code" class="form-control form-control-sm" value="<?= htmlspecialchars($row['bank_code']); ?>"></td>
                                        <td><input type="number" step="0.01" name="gross_after_deductions" class="form-control form-control-sm" value="<?= htmlspecialchars($row['gross_after_deductions']); ?>"></td>
                                        <td><?= htmlspecialchars($row['user_username']); ?></td>
                                        <td>
                                            <input type="hidden" name="form_name" value="update_payroll">
                                            <input type="hidden" name="insert_id" value="<?= $row['insert_id']; ?>">
                                            <button type="submit" class="btn btn-sm btn-primary">Update</button>
                                            <a href="idd_payroll_actions.php?delete_payroll=<?= $row['insert_id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Delete this record?')">Delete</a>
                                        </td>
                                    </form>
                                </tr>
                            <?php } ?>
                            </tbody>
                        </table>
                    <?php } ?>
                </div>

                <!-- MEDICAL TAB -->
                <div class="tab-pane fade p-3" id="medical" role="tabpanel">
                    <h5>Medical Premiums</h5>
                    <?php
                    $sql = "SELECT su.user_username, idd.member_name, idd.member_number, iddm.* 
                            FROM MYDB.DBO.idd_payroll AS idd
                            INNER JOIN MYDB.DBO.idd_medical AS iddm ON idd.insert_id = iddm.payroll_id
                            INNER JOIN MYDB.DBO.sys_users_tb AS su ON su.user_id = idd.added_by
                            WHERE iddm.scheme_code = '$scheme_code' AND iddm.hidden_status = 'No'
                            ORDER BY idd.member_name ASC";
                    $stmt = sqlsrv_query($conn, $sql);

                    if ($stmt !== false) { ?>
                        <input type="text" id="medicalSearch" class="form-control mb-3" placeholder="Search medical records...">
                        <table class="table table-bordered table-striped mt-3" id="medicalTable">
                            <thead>
                                <tr>
                                    <th>#</th><th>Member Number</th><th>Member Name</th>
                                    <th>Medical Amount</th><th>Created By</th><th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                            <?php $i=1; while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) { ?>
                                <tr>
                                    <form action="idd_payroll_actions.php" method="POST">
                                        <td><?= $i++; ?></td>
                                        <td><?= htmlspecialchars($row['member_number']); ?></td>
                                        <td><?= htmlspecialchars($row['member_name']); ?></td>
                                        <td><input type="number" step="0.01" name="medical_amount" class="form-control form-control-sm" value="<?= htmlspecialchars($row['medical_amount']); ?>"></td>
                                        <td><?= htmlspecialchars($row['user_username']); ?></td>
                                        <td>
                                            <input type="hidden" name="form_name" value="update_medical">
                                            <input type="hidden" name="insert_id" value="<?= $row['insert_id']; ?>">
                                            <button type="submit" class="btn btn-sm btn-primary">Update</button>
                                            <a href="idd_payroll_actions.php?delete_medical=<?= $row['insert_id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Delete this record?')">Delete</a>
                                        </td>
                                    </form>
                                </tr>
                            <?php } ?>
                            </tbody>
                        </table>
                    <?php } ?>
                </div>

                <!-- ADVANCE TAB -->
                <div class="tab-pane fade p-3" id="advance" role="tabpanel">
                    <h5>Advance Requests</h5>
                    <?php
                    $sql = "SELECT su.user_username, idd.member_name, idd.member_number, adv.* 
                            FROM MYDB.DBO.idd_payroll AS idd
                            INNER JOIN MYDB.DBO.idd_advance_payroll AS adv ON idd.insert_id = adv.payroll_id
                            INNER JOIN MYDB.DBO.sys_users_tb AS su ON su.user_id = adv.added_by
                            WHERE adv.scheme_code = '$scheme_code'
                            ORDER BY idd.member_name ASC";
                    $stmt = sqlsrv_query($conn, $sql);

                    if ($stmt !== false) { ?>
                        <input type="text" id="advanceSearch" class="form-control mb-3" placeholder="Search advance records...">
                        <table class="table table-bordered table-striped mt-3" id="advanceTable">
                            <thead>
                                <tr>
                                    <th>#</th><th>Member Number</th><th>Member Name</th>
                                    <th>Start Period</th><th>End Period</th><th>Created By</th><th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                            <?php $i=1; while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) { ?>
                                <tr>
                                    <form action="idd_payroll_actions.php" method="POST">
                                        <td><?= $i++; ?></td>
                                        <td><?= htmlspecialchars($row['member_number']); ?></td>
                                        <td><?= htmlspecialchars($row['member_name']); ?></td>
                                        <td><?= htmlspecialchars($row['start_period']."/".$row['start_sub_period']); ?></td>
                                        <td><?= htmlspecialchars($row['end_period']."/".$row['end_sub_period']); ?></td>
                                        <td><?= htmlspecialchars($row['user_username']); ?></td>
                                        <td>
                                            <input type="hidden" name="form_name" value="update_advance">
                                            <input type="hidden" name="insert_id" value="<?= $row['insert_id']; ?>">
                                            <button type="submit" class="btn btn-sm btn-primary">Update</button>
                                            <a href="idd_payroll_actions.php?delete_advance=<?= $row['insert_id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Delete this record?')">Delete</a>
                                        </td>
                                    </form>
                                </tr>
                            <?php } ?>
                            </tbody>
                        </table>
                    <?php } ?>
                </div>

                <!-- EXCEPTIONS TAB -->
                <div class="tab-pane fade p-3" id="exceptions" role="tabpanel">
                    <h5>Exceptions</h5>
                    <?php
                    $sql = "SELECT su.user_username, idd.member_name, idd.member_number, exc.* 
                            FROM MYDB.DBO.idd_payroll AS idd
                            INNER JOIN MYDB.DBO.idd_payroll_exeptions AS exc ON idd.insert_id = exc.payroll_id
                            INNER JOIN MYDB.DBO.sys_users_tb AS su ON su.user_id = exc.added_by
                            WHERE exc.scheme_code = '$scheme_code'
                            ORDER BY idd.member_name ASC";
                    $stmt = sqlsrv_query($conn, $sql);

                    if ($stmt !== false) { ?>
                        <input type="text" id="exceptionsSearch" class="form-control mb-3" placeholder="Search exception records...">
                        <table class="table table-bordered table-striped mt-3" id="exceptionsTable">
                            <thead>
                                <tr>
                                    <th>#</th><th>Member Number</th><th>Member Name</th>
                                    <th>Payment Frequency</th><th>Created By</th><th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                            <?php $i=1; while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) { ?>
                                <tr>
                                    <form action="idd_payroll_actions.php" method="POST">
                                        <td><?= $i++; ?></td>
                                        <td><?= htmlspecialchars($row['member_number']); ?></td>
                                        <td><?= htmlspecialchars($row['member_name']); ?></td>
                                        <td><input type="text" name="payment_frequency" class="form-control form-control-sm" value="<?= htmlspecialchars($row['payment_frequency']); ?>"></td>
                                        <td><?= htmlspecialchars($row['user_username']); ?></td>
                                        <td>
                                            <input type="hidden" name="form_name" value="update_exception">
                                            <input type="hidden" name="insert_id" value="<?= $row['insert_id']; ?>">
                                            <button type="submit" class="btn btn-sm btn-primary">Update</button>
                                            <a href="idd_payroll_actions.php?delete_exception=<?= $row['insert_id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Delete this record?')">Delete</a>
                                        </td>
                                    </form>
                                </tr>
                            <?php } ?>
                            </tbody>
                        </table>
                    <?php } ?>
                </div>

            </div>
        </div>
    </div>
</div>

<script>
function setupSearch(inputId, tableId){
    document.getElementById(inputId).addEventListener("keyup", function() {
        let filter = this.value.toLowerCase();
        document.querySelectorAll("#"+tableId+" tbody tr").forEach(row=>{
            row.style.display = row.innerText.toLowerCase().includes(filter) ? "" : "none";
        });
    });
}
setupSearch("payrollSearch","payrollTable");
setupSearch("medicalSearch","medicalTable");
setupSearch("advanceSearch","advanceTable");
setupSearch("exceptionsSearch","exceptionsTable");
</script>
</body>
</html>
<?php sqlsrv_close($conn); ?>
