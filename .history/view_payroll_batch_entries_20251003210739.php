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
    //'delete'
    if (isset($_GET['delete_payroll'])) {
        $staging_id = $_GET['delete_payroll'];
        $deleted_at = date('Y-m-d H:i:s');
        $sql = "UPDATE MYDB.DBO.idd_payroll_staging 
                SET hidden_status = 'Yes', deleted_at = ?, deleted_by = ? 
                WHERE staging_id = ?"; 
        $params = [ $deleted_at, $user_id, $staging_id ]; 
        $stmt = sqlsrv_query($conn, $sql, $params); 
        if ($stmt === false) { 
            die("Update failed: " . print_r(sqlsrv_errors(), true)); 
        } 
        $batch_id = $_GET['batch_id'] ?? '';
        header("Location: view_payroll_batch_entries.php?batch_id=" . urlencode($batch_id) . "&msg=deleted");
        exit();
    }

    //updates

    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        if (isset($_POST['form_name']) && $_POST['form_name'] === "update_payroll") {
            $staging_id = $_POST['staging_id'];
            
            $net_salary = !empty($_POST['net_salary']) ? floatval($_POST['net_salary']) : 0.00; 
            $updated_at = date('Y-m-d H:i:s'); 
            $sql = "UPDATE MYDB.DBO.idd_payroll SET  net_salary = ? , updated_at = ?, updated_by = ? WHERE staging_id = ?"; 
            $params = [$net_salary, $updated_at, $user_id, $staging_id]; 
            $stmt = sqlsrv_query($conn, $sql, $params); 
            if ($stmt === false) { 
                die("Update failed: " . print_r(sqlsrv_errors(), true)); 
            } 
            $batch_id = $_POST['batch_id'] ?? '';
            header("Location: view_payroll_batch_entries.php?batch_id=" . urlencode($batch_id) . "&msg=updated");
            exit();
        
        }else if(isset($_POST['form_name']) && $_POST['form_name'] === "add_payroll"){
            $scheme_code   = $_POST['scheme_code'];
            $user_id       = $_SESSION['user_id'];
            $member_number = trim($_POST['member_number']);
            $member_name   = trim($_POST['member_name']);
            $account_number = trim($_POST['account_number']);
            $bank_code     = !empty($_POST['bank_code']) ? intval($_POST['bank_code']) : null;
            $gross_after   = !empty($_POST['gross_after_deductions']) ? floatval($_POST['gross_after_deductions']) : 0.00;

            // check duplicates
            $checkSql = "SELECT COUNT(*) AS cnt 
                        FROM MYDB.DBO.idd_payroll 
                        WHERE scheme_code = ? AND member_number = ?";
            $checkParams = [$scheme_code, $member_number];
            $checkStmt = sqlsrv_query($conn, $checkSql, $checkParams);
            $exists = sqlsrv_fetch_array($checkStmt, SQLSRV_FETCH_ASSOC);

            if ($exists['cnt'] == 0) {
                $sql = "INSERT INTO MYDB.DBO.idd_payroll 
                        (scheme_code, member_number, member_name, account_number, bank_code, gross_after_deductions, added_by) 
                        VALUES (?, ?, ?, ?, ?, ?, ?)";
                $params = [$scheme_code, $member_number, $member_name, $account_number, $bank_code, $gross_after, $user_id];
                $stmt = sqlsrv_query($conn, $sql, $params);

                if ($stmt === false) {
                    die("Insert failed: " . print_r(sqlsrv_errors(), true));
                } else {
                    echo "<div class='alert alert-success'>Payroll data inserted successfully!</div>";
                }
            } else {
                echo "<div class='alert alert-danger'>Failed to insert to payroll data. User Exists !!!!!!!!!!!!!</div>";
            }
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
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/choices.js/public/assets/styles/choices.min.css" />
    <script src="https://cdn.jsdelivr.net/npm/choices.js/public/assets/scripts/choices.min.js"></script>
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
<?php
if (isset($_GET['msg']) && $_GET['msg'] === 'deleted') {
    echo "<div class='alert alert-success'>Payroll data deleted successfully!</div>";
}

?>

<div class="container-fluid  px-5 py-3">
    <h4>IDD Payroll Actions</h4>
    <div class="row">
        <div class="col-sm-12">
            <!-- Tabs Nav -->
            <ul class="nav nav-tabs" id="payrollTabs" role="tablist">
                <li class="nav-item"><a class="nav-link active" id="payroll-tab" data-toggle="tab" href="#payroll" role="tab">Payroll Details</a></li>
                
            </ul>

            <!-- Tabs Content -->
            <div class="tab-content" id="payrollTabsContent">

                <!-- PAYROLL TAB -->
                <div class="tab-pane fade show active p-3" id="payroll" role="tabpanel">
                    <h5>Payroll Records</h5>
                    <button type="button" class="btn btn-success" data-toggle="modal" data-target="#addPayrollModal">Add Payroll Record</button>
                    </br></br>
                    <?php
                    $batch_id = isset($_GET['batch_id']) ? $_GET['batch_id'] : null;
                    if (!$batch_id) {
                        echo "<div class='alert alert-danger'>No batch ID provided.</div>";
                        exit();
                    }
                    $scheme_code = $_SESSION['scheme_code'];
                   $sql = "SELECT 
                            stg.staging_id, 
                            stg.member_number, 
                            stg.member_name, 
                            idd.account_number, 
                            idd.bank_code, 
                            stg.gross_salary, 
                            stg.medical_deduction, 
                            stg.net_salary, 
                            stg.drawdown_percentage, 
                            stg.closing_balance
                        FROM MYDB.DBO.idd_payroll_staging stg
                        LEFT JOIN MYDB.DBO.idd_payroll idd  
                            ON stg.member_number = idd.member_number 
                            AND stg.scheme_code = idd.scheme_code
                        WHERE stg.scheme_code = ? 
                        AND stg.hidden_status = 'No'
                        AND stg.batch_id = ?
                        ORDER BY stg.member_name ASC;
                    ";

                    $params = array($scheme_code, $batch_id);
                    $stmt = sqlsrv_query($conn, $sql, $params);

                    if ($stmt === false) {
                        die(print_r(sqlsrv_errors(), true));
                    }

                    
                    if ($stmt !== false) { ?>
                        <input type="text" id="payrollSearch" class="form-control mb-3" placeholder="Search payroll records...">
                        <table class="table table-bordered table-striped mt-3" id="payrollTable" data-page-length="100">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Member Number</th>
                                    <th>Member Name</th>
                                    <th>Account Number</th>
                                    <th>Bank Code</th>
                                    <th>Gross After Deductions</th>
                                    <th>Medical</th>
                                    <th>Net Salary</th>
                                    <th>Drawdown Percentage</th>
                                    <th>Closing  Balance</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                
                            <?php $i=1; while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) { ?>
                                <tr>
                                    <form action="view_payroll_batch_entries.php" method="POST">
                                        <td><?= $i++; ?></td>
                                        <td><?= htmlspecialchars($row['member_number']); ?></td>
                                        <td><?= htmlspecialchars($row['member_name']); ?></td>
                                        <td><?= htmlspecialchars($row['account_number']); ?></td>
                                        <td><?= htmlspecialchars($row['bank_code']); ?></td>
                                        <td><?= htmlspecialchars($row['gross_salary']); ?></td>
                                        <td><?= htmlspecialchars($row['medical_deduction']); ?></td>
                                        <td><input type="number" step="0.01" name="net_salary" class="form-control form-control-sm" value="<?= htmlspecialchars($row['net_salary']); ?>"></td>
                                        <td><?= htmlspecialchars($row['drawdown_percentage']. "%"); ?></td>
                                        <td><?= htmlspecialchars($row['closing_balance']); ?></td>
                                        <td>
                                            <input type="hidden" name="form_name" value="update_payroll">
                                            <input type="hidden" name="staging_id" value="<?= $row['staging_id']; ?>">
                                            <input type="hidden" name="batch_id" value="<?= $batch_id; ?>">
                                            <button type="submit" class="btn btn-sm btn-primary">Update</button>
                                            <a href="view_payroll_batch_entries.php?batch_id=<?php echo urlencode($batch_id); ?>&delete_payroll=<?= $row['staging_id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Delete this record?')">Delete</a>
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


<!-- Modals -->
 <!-- add payrollModal -->
<div class="modal fade" id="addPayrollModal" tabindex="-1" role="dialog" aria-labelledby="addPayrollLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
      <form action="idd_payroll_actions.php" method="post">
        <div class="modal-header">
          <h5 class="modal-title" id="addPayrollLabel">Add Payroll Record</h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>

        <div class="modal-body">
          <input type="hidden" name="form_name" value="add_payroll"/>
          <input type="hidden" name="scheme_code" value="<?php echo($_SESSION['scheme_code']); ?>"/>

          <div class="form-group row">
            <div class="col">
              <label class="form-label">Member Number</label>
              <input type="text" class="form-control" name="member_number" required>
            </div>
            <div class="col">
              <label class="form-label">Member Name</label>
              <input type="text" class="form-control" name="member_name" required>
            </div>
          </div>

          <div class="form-group row">
            <div class="col">
              <label class="form-label">Account Number</label>
              <input type="text" class="form-control" name="account_number">
            </div>
            <div class="col">
              <label class="form-label">Bank Code</label>
              <input type="number" class="form-control" name="bank_code">
            </div>
          </div>

          <div class="form-group">
            <label class="form-label">Gross After Deductions</label>
            <input type="number" step="0.01" class="form-control" name="gross_after_deductions" required>
          </div>
        </div>

        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
          <button type="submit" class="btn btn-primary">Save Record</button>
        </div>
      </form>
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
    

  </script>
    

<!-- DataTables CSS -->
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap4.min.css"/>
<link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.2/css/buttons.bootstrap4.min.css"/>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap4.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/dataTables.buttons.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.bootstrap4.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/pdfmake.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/vfs_fonts.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.html5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.print.min.js"></script>

<script>
$(document).ready(function () {
    $('#payrollTable').DataTable({
        dom: 'Bfrtip',
        buttons: [
            { extend: 'csvHtml5', className: 'btn btn-primary', title: 'Payroll Report' },
            { extend: 'excelHtml5', className: 'btn btn-success', title: 'Payroll Report' },
            { extend: 'pdfHtml5', className: 'btn btn-danger', title: 'Payroll Report' },
            { extend: 'print', className: 'btn btn-info', title: 'Payroll Report' }
        ]
    });
});
</script>

</body>

</html>
<?php sqlsrv_close($conn); ?>
