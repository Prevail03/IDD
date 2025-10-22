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

// if(isset($_GET['member_id'])){
	
// 	$_SESSION['member_id'] = $_GET['member_id'];
// // 	header("location: particulars_edit_member.php");
// // 	exit();
	
// }

$scheme_country = $scheme['scheme_country'];
$scheme_code = $scheme['scheme_code'];

switch (strtolower($scheme_country)) {
    case 'kenya':
        $tax_pin_narration = 'KRA PIN';
        break;
    case 'uganda':
        break;

    default:
        $tax_pin_narration = 'Tax ID Number';
        break;
}

require('../commons/config/settings.php');
require('./menu.php');
require('../commons/phpmailer/PHPMailerAutoload.php');
if($_SESSION['user_folder'] !== Settings::$folder){
	header("location: ../logout.php");
	exit("Folder inaccessible");
}
function formatKey($key) {
    $key = str_replace("m_", "Member ", $key); // Replace "m_" with "Member "
    return ucwords(str_replace("_", " ", $key)); // Replace "_" with space and capitalize words
}
// Create connection
$conn = sqlsrv_connect( Settings::$serverName, Settings::$connectionInfo);

if(isset($_POST['edit_record'])){
    
    
		 $allowed_ids = [104547,137274, 133165,125437,125256,95777,138181,98031,125758,102119,102422,71706,125444,131786,102146,98093,137274,138818,143136]; 
         if (!in_array($_SESSION['user_id'], $allowed_ids)) {
		  echo 'ACCESS DENIED: YOU ARE NOT ALLOWED TO PERFORM THIS ACTION!';
		  die();
         }
         
     
    $uploadDir = 'uploads/' . $_SESSION['scheme_name'] . '/';

// Ensure the directory exists
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0777, true);
}

function uploadFile($inputName, $uploadDir, $customName) {
    if (!isset($_FILES[$inputName]) || $_FILES[$inputName]['error'] != 0) {
        return null; // Return null if no file uploaded
    }

    $file = $_FILES[$inputName];

    // Allowed file types
    $allowedTypes = ['application/pdf'];

    // Validate MIME type
    if (!in_array($file['type'], $allowedTypes)) {
        return null;
    }

    // Generate filename: YYYY_MM_DD_HH_MM_SS_CustomName.pdf
    $timestamp = date('Y_m_d_H_i_s'); 
    $newFileName = "{$timestamp}_{$customName}.pdf";
    $targetFilePath = $uploadDir . $newFileName;

    // Move file to uploads directory
    if (move_uploaded_file($file['tmp_name'], $targetFilePath)) {
        return $targetFilePath; // Return file path on success
    }

    return null; // Return null if upload fails
}

// Upload files and store file paths
$uploadedFiles = [
    'national_id' => uploadFile('national_id_file', $uploadDir, "national_id"),
    'atm_card' => uploadFile('atm_card_file', $uploadDir, "atm_card"),
    'kra_pin' => uploadFile('kra_pin_file', $uploadDir, "kra_pin")
];

// Prepare email attachments array
$attachments = [];
foreach ($uploadedFiles as $key => $filePath) {
    if ($filePath) { // Only add successfully uploaded files
        $attachments[] = [
            'name' => strtoupper(str_replace('_', ' ', $key)), // Format name
            'file' => $filePath
        ];
    }
}

    
	//$m_id = $_SESSION['member_id'];
	$m_number = $_POST['m_number'];
		$scheme_code = $_POST['m_scheme'];
		$m_id = $_POST['m_id'];
	$m_name = $_POST['m_name'];	
	$m_dob = $_POST['m_dob'];
	$m_doe = $_POST['m_doe'];
	$m_doj = $_POST['m_doj'];
	$m_gender = $_POST['m_gender'];
	$m_marital = $_POST['m_marital'];
	$m_id_number = $_POST['m_id_number'];
	$m_email = $_POST['m_email'];
	$m_phone = $_POST['m_phone'];
	$m_employer = $_POST['m_employer'];
	$m_address = $_POST['m_address'];
	$m_pin = $_POST['m_pin'];
	$m_nationality = $_POST['m_nationality'];
	$m_additional_years = !empty(trim($_POST['m_additional_years']))?$_POST['m_additional_years']:0;
	$m_deferred_relief = !empty(trim($_POST['m_deferred_relief']))?$_POST['m_deferred_relief']:0;
	$m_department = $_POST['m_department'];
	$m_status = $_POST['m_status'];
	$m_transferred_relief = !empty(trim($_POST['m_transferred_relief']))?$_POST['m_transferred_relief']:0;
	$m_lock_withdrawal =0;
	if(isset($_POST['m_lock_withdrawal'])){
		$m_lock_withdrawal = $_POST['m_lock_withdrawal'];
	}
	$m_status_date = NULL;
	if(!empty(trim($_POST['m_status_date']))){
		$m_status_date = $_POST['m_status_date'];
	}
	$m_account_name = $_POST['m_account_name'];
	$m_account_no = $_POST['m_account_no'];
	$m_bank_name = $_POST['m_bank_name'];
	$m_branch_name = $_POST['m_branch_name'];
	$m_branch_code = $_POST['m_branch_code'];
	
	//Additional 2018-12-04
	$m_office_phone = $_POST['m_office_phone'];
    $m_kin_name = $_POST['m_kin_name'];
    $m_kin_id_number = $_POST['m_kin_id_number'];
    $m_kin_phone = $_POST['m_kin_phone'];
    $m_kin_address = $_POST['m_kin_address'];
    $m_cont_amount_salary = !empty(trim($_POST['m_cont_amount_salary']))?$_POST['m_cont_amount_salary']:0;
    $m_cont_amount_fixed = !empty(trim($_POST['m_cont_amount_fixed']))?$_POST['m_cont_amount_fixed']:0;
    $m_cont_amount_transferred = !empty(trim($_POST['m_cont_amount_transferred']))?$_POST['m_cont_amount_transferred']:0;
    $m_received_ee_amount = !empty(trim($_POST['m_received_ee_amount']))?$_POST['m_received_ee_amount']:0;
    $m_received_avc_amount = !empty(trim($_POST['m_received_avc_amount']))?$_POST['m_received_avc_amount']:0;
    $m_received_er_amount = !empty(trim($_POST['m_received_er_amount']))?$_POST['m_received_er_amount']:0;
    $m_propossed_ee_percentage = !empty(trim($_POST['m_propossed_ee_percentage']))?$_POST['m_propossed_ee_percentage']:0;
    $m_propossed_avc_percentage = !empty(trim($_POST['m_propossed_avc_percentage']))?$_POST['m_propossed_avc_percentage']:0;
    $m_propossed_er_percentage = !empty(trim($_POST['m_propossed_er_percentage']))?$_POST['m_propossed_er_percentage']:0;
    $m_payment_mode = $_POST['m_payment_mode'];
    $m_payment_frequency = $_POST['m_payment_frequency'];
    $m_preferred_retirement_age = !empty(trim($_POST['m_preferred_retirement_age']))?$_POST['m_preferred_retirement_age']:0;
    $m_transfer_from_scheme_name = $_POST['m_transfer_from_scheme_name'];
    
    $m_relief_to_use = $_POST['m_relief_to_use'];
    $m_group = $_POST['m_group'];
    $m_exempt_from_tax = $_POST['m_exempt_from_tax'];
    
     $formatted_m_dob = DateTime::createFromFormat("Y-m-d", $m_dob);
    $formatted_m_doe = DateTime::createFromFormat("Y-m-d", $m_doe);
    $formatted_m_doj = DateTime::createFromFormat("Y-m-d", $m_doj);


	                
$biodata = Array(
    m_id=> $m_id,
	m_number => $m_number, 
	m_name => $m_name, 
	m_dob => $formatted_m_dob->setTime(0, 0, 0), 
	 
	m_doe => $formatted_m_doe->setTime(0, 0, 0), 
	m_doj => $formatted_m_doj->setTime(0, 0, 0), 
	m_gender => $m_gender, 
	m_marital => $m_marital, 
	m_id_number => $m_id_number, 
	m_email => $m_email, 
	m_phone => $m_phone, 
	m_employer => $m_employer, 
	m_address => $m_address, 
	m_pin => $m_pin, 
	m_nationality => $m_nationality, 
	m_additional_years => $m_additional_years,
	m_deferred_relief => $m_deferred_relief, 
	m_department => $m_department, 
	m_status => $m_status, 
	m_transferred_relief => $m_transferred_relief, 
	m_lock_withdrawal => $m_lock_withdrawal,
	m_status_date=>$m_status_date,
	m_account_name => $m_account_name, 
	m_account_no => $m_account_no, 
	m_bank_name => $m_bank_name, 
	m_branch_name => $m_branch_name, 
	m_branch_code => $m_branch_code,
	m_office_phone => $m_office_phone,
	m_kin_name => $m_kin_name,
	m_kin_id_number => $m_kin_id_number,
	m_kin_phone => $m_kin_phone,
	m_kin_address => $m_kin_address,
	m_cont_amount_salary => $m_cont_amount_salary,
	m_cont_amount_fixed => $m_cont_amount_fixed,
	m_cont_amount_transferred => $m_cont_amount_transferred,
	m_received_ee_amount => $m_received_ee_amount,
	m_received_avc_amount => $m_received_avc_amount,
	m_received_er_amount => $m_received_er_amount,
	m_propossed_ee_percentage => $m_propossed_ee_percentage,
	m_propossed_avc_percentage => $m_propossed_avc_percentage,
	m_propossed_er_percentage =>  $m_propossed_er_percentage,
	m_payment_mode => $m_payment_mode,
	m_payment_frequency => $m_payment_frequency,
	m_preferred_retirement_age => $m_preferred_retirement_age,
	m_transfer_from_scheme_name => $m_transfer_from_scheme_name,
	m_relief_to_use =>  $m_relief_to_use,
	m_group => $m_group,
	m_bank_id => intval($_POST['m_bank_id']),
	m_exempt_from_tax => $m_exempt_from_tax);




 
	 $sql = "SELECT * FROM members_tb WHERE m_scheme_code LIKE ? AND m_id = ?";
	$m_id = $m_id;
	$params = array($scheme_code,$m_id);
	$stmt = sqlsrv_query( $conn, $sql ,$params);
	if( $stmt === false) {
	  die( print_r( sqlsrv_errors(), true) );
	}
	$array1 = sqlsrv_fetch_array( $stmt, SQLSRV_FETCH_ASSOC);
	if($array1){
	    
	             $differences = [];

        foreach ($biodata as $key => $value) {
            if (!array_key_exists($key, $array1) || $array1[$key] != $value) { // Check if key exists or value is different
                $differences[$key] = [
                    'original' => $array1[$key] ?? null, // Handle potentially missing values in the database
                    'changed' => $value,
                ];
            }
        }
		$edited_fields = json_encode($differences);
		//echo json_encode($differences, JSON_PRETTY_PRINT);
        
          
        // if (!empty($differences)) {
        //     echo "Differences found:<br>";
        //     echo "<pre>";
        //     print_r($differences);
        //     echo "</pre>";
        // } else {
        //     echo "No differences found.";
        // }

    } else {
        echo "No record found with m_id";
    }
    
    
     $biodata['m_dob'] = $m_dob;
    $biodata['m_doe'] = $m_doe;
    $biodata['m_doj'] = $m_doj;
	$bio_json = json_encode($biodata, true);
	

    
           $country = trim($_SESSION['scheme_country']); // Replace with your actual source for the country
    
    //echo "Session Country: " . $_SESSION['scheme_country'] . "<br>";
    



// Determine the email address based on the country
        $email = '';
        $line_managers_username='';
        
        if ($country === 'Kenya') {
            //$emails = ['monica.shilwatso@octagonafrica.com', 'cecilia.munene@octagonafrica.com'];
            //$line_managers_usernames = ['monica.shilwatso', 'cecilia.munene'];
            
             //$email = 'cecilia.munene@octagonafrica.com';
             // $line_managers_username = 'cecilia.munene';
            $email = 'monica.shilwatso@octagonafrica.com';
             //$email = 'brian.karanja@octagonafrica.com';
              $line_managers_username = 'monica.shilwatso';
          //$email = 'victor.malanga@octagonafrica.com';
             //$line_managers_username = 'victor.malanga';
            
        } elseif ($country === 'Zambia') {
            $email = 'manjolo.mwape@octagonafrica.com';
            $line_managers_username = 'manjolo.mwape';
            // $email = 'victor.malanga@octagonafrica.com';
            // $line_managers_username = 'victor.malanga';
            
        } elseif ($country === 'Uganda') {
              // $email = 'timothy.opolot@octagonafrica.com'; 
              // $line_managers_username = 'timothy.opolot';
                //   owen.ebichinji
               $email = 'owen.ebichinji@octagonafrica.com';
               $line_managers_username = 'owen.ebichinji';
                $email = 'sherinah.yiga@octagonafrica.com';
                $line_managers_username = 'sherinah.yiga';
          
                $email = 'diana.hamba@octagonafrica.com';
                $line_managers_username = 'diana.hamba';
               
                //$email = 'brian.karanja@octagonafrica.com';
        } 
	    $params1 = [
            $bio_json,         // 1st parameter for edit_details
            $_SESSION['scheme_code'],      // 2nd parameter for edit_scheme_code
            $_SESSION['username'],
            date('Y-m-d H:i:s'),
            'pending admin approval',
            $line_managers_username,
			$edited_fields,
			$m_number,
			$m_name,
        ];
        $sql1 = "INSERT INTO [MYDB].[dbo].[admin_member_edits] (edit_details, edit_scheme_code, edit_initiated_by, edit_initiated_on,edit_status,line_manager_approved_by,edited_fields,member_number,member_name) 
                 VALUES (?, ?, ?, ?,?,?,?,?,?)";
                 
      $stmt1 = sqlsrv_query($conn, $sql1, $params1);
        
        // Check if the insert query was executed successfully
        if ($stmt1 === false) {
            die(print_r(sqlsrv_errors(), true));
        }
        
        $sql2 = "SELECT IDENT_CURRENT('admin_member_edits') AS last_inserted_id";
    $stmt2 = sqlsrv_query($conn, $sql2);

    if ($stmt2) {
        sqlsrv_fetch($stmt2);
        $inserted_id = sqlsrv_get_field($stmt2, 0);
        //echo "Last inserted row ID: " . $lastInsertedId;
    } else {
        //echo "Error retrieving last ID.";
    }
 
    
        
        // Retrieve the last inserted edit_id using SCOPE_IDENTITY()
        // $sql2 = "SELECT COUNT(*) AS row_count FROM admin_member_edits";
        
        // $stmt2 = sqlsrv_query($conn, $sql2);
        
        // // Check if the query to fetch the last inserted ID was successful
        // if ($stmt2 === false) {
        //     die(print_r(sqlsrv_errors(), true));
        // }
        
        // // Fetch the inserted edit_id
        // $row = sqlsrv_fetch_array($stmt2, SQLSRV_FETCH_ASSOC);
        //   //print_r($row);
        // $inserted_id = $row['row_count'];
        
        // print_r($inserted_id);
        // die();
        // Generate the approval link using the base64-encoded inserted ID
        $approval_link = "https://cloud.octagonafrica.com/crm/portal/admin_bio_edits_approval.php?approve=" . base64_encode($inserted_id);
        
         $reject_link = "https://cloud.octagonafrica.com/crm/portal/admin_bio_edits_approval.php?reject=" . base64_encode($inserted_id);
       // print_r($_SESSION);
      
 
    //send email
$get_message = '
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bio Data Edit Request</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f7fc;
            padding-top: 20px;
        }
        .container {
            max-width: 800px;
        }
        .card {
            border-radius: 8px;
            border: 1px solid #ddd;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }
        .card-header {
            background-color: #007bff;
            color: #fff;
            font-weight: bold;
            text-align: center;
            padding: 15px;
            border-top-left-radius: 8px;
            border-top-right-radius: 8px;
        }
        .card-body {
            padding: 20px;
        }
        .card-title {
            font-size: 1.5rem;
            margin-bottom: 20px;
        }
        .card-text {
            font-size: 1rem;
            margin-bottom: 25px;
        }
        .table {
            width: 100%;
            border-collapse: collapse;
        }
        .table th, .table td {
            text-align: left;
            border: 1px solid #ddd; /* Add border to each table cell */
            padding: 10px;
        }
        .table th {
            background-color: #f1f1f1;
        }
        .btn-link {
            display: inline-block;
            margin-top: 15px;
            padding: 12px 20px;
            background-color: #28a745;
            color: #fff;
            border-radius: 5px;
            text-decoration: none;
            text-align: center;
            transition: background-color 0.3s ease;
        }
       .btn-link2 {
            display: inline-block;
            margin-top: 15px;
            padding: 12px 20px;
            background-color: red;
            color: #fff;
            border-radius: 5px;
            text-decoration: none;
            text-align: center;
            transition: background-color 0.3s ease;
        }
        .btn-link:hover {
            background-color: #218838;
        }
        @media (max-width: 768px) {
            .table th, .table td {
                font-size: 0.9rem;
            }
        }
    </style>
</head>
<body>

<div class="container">
    <div class="card">
        <div class="card-header">
            Bio Data Edit Request
        </div>
        <div class="card-body">
            <h5 class="card-title">Approve Bio Data Edit for ' . htmlspecialchars($m_name) . '</h5>
            <p class="card-text">
                ' . htmlspecialchars($_SESSION['username'] ?? "Admin") . ' has submitted a request to update bio data for ' . htmlspecialchars($m_name) . '.  
                Please review the changes and approve the edits
            </p>

            <!-- Table for displaying changes -->
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>Field</th>
                        <th>Current Details</th>
                        <th>Updated Details</th>
                    </tr>
                </thead>
                <tbody>';

                    foreach ($differences as $key => $value) {
                        // Check if the original or changed value is a DateTime object and format it
                        $original = $value['original'];
                        $changed = $value['changed'];

                        // Format DateTime objects
                        if ($original instanceof DateTime) {
                            $original = $original->format('Y-m-d');
                        }

                        if ($changed instanceof DateTime) {
                            $changed = $changed->format('Y-m-d');
                        }

                        $get_message .= '
                        <tr>
                            <td>' . formatKey($key) . '</td>
                            <td>' . htmlspecialchars($original) . '</td>
                            <td>' . htmlspecialchars($changed) . '</td>
                        </tr>';
                    }

            $get_message .= '
                </tbody>
            </table>

            <!-- Approval button -->
            <a href="' . htmlspecialchars($approval_link) . '" class="btn-link">Approve Edit Request</a>
            <hr>
              <a href="' . htmlspecialchars($reject_link) . '" class="btn-link2">Reject Edit Request</a>
        </div>
    </div>
</div>

</body>
</html>';

 
    // Add the determined email address
   
    
    
    
    // Send email using PHPMailer
     $mail = new PHPMailer;
    $mail->SMTPDebug = 0;  // Enable verbose debug output
    $mail->isSMTP();  // Set mailer to use SMTP
    $mail->Host = Settings::$email_host1;  // Specify main and backup SMTP servers
    $mail->SMTPAuth = true;  // Enable SMTP authentication
    $mail->Username = Settings::$email_username_cred1;  // SMTP username
    $mail->Password = Settings::$email_password_cred1;  // SMTP password
    $mail->SMTPSecure = Settings::$email_security1;  // Enable TLS encryption
    $mail->Port = Settings::$email_port1;  // TCP port to connect to
    
    $mail->setFrom(Settings::$correspondance_email1, 'Octagon Africa');
     $mail->addAddress($email);
     $mail->addbcc('james.azere@octagonafrica.com');
     $mail->addbcc('cecilia.munene@octagonafrica.com');
     //$mail->addAddress('jackson.wesonga@octagonafrica.com'); 
    $mail->addReplyTo(Settings::$correspondance_email1, 'Octagon Africa');
    
    // Set email format to HTML
    $mail->isHTML(true);
    
    // Make subject unique with current datetime
    $dt = new DateTime();
    $append_dt = $dt->format('Y-m-d H:i:s');
    //$mail->Subject = "Member details Approval reques - Update Scheme Membership details";
    
    $mail->Subject = "Member Details Approval Request - {$m_name}  - ({$_SESSION['scheme_name']}) - ${$SESSION['scheme_code']}";
    
    $mail->Body = $get_message;
     foreach ($attachments as $attachment) {
        if ($attachment['file']) {
            $mail->addAttachment($attachment['file'], $attachment['name'] . '.pdf'); // Add attachment with custom name
        }
    }
    $mail->AltBody = "Dear Member, your membership details are available in the attached table. Please contact us for any updates.";
    
    $mail->send();
    
    

	
	//Audit Start
	
// 	$audit_activity = "Member Details";
// 	$audit_description = "Edited member no ".$m_number;
	
// 	$sql = "INSERT INTO audit_trail_pension_tb (audit_date_time, audit_scheme_code, audit_username, audit_fullnames, audit_activity, audit_description) 
// 	VALUES(GETDATE(),?,?,?,?,?)";
// 	$params = array($_SESSION['scheme_code'],$_SESSION['username'],$_SESSION['user_full_names'],$audit_activity,$audit_description);
// 	$stmt = sqlsrv_query( $conn, $sql ,$params);
//     if( $stmt === false) {
// 	  die( print_r( sqlsrv_errors(), true) );
// 	}
	
	//Audit End
	$_SESSION['add_message'] = "Edit Request sent successfully";

// 	$stmt = sqlsrv_query( $conn, $sql ,$params);
// 	if( $stmt === false) {
// 	  die( print_r( sqlsrv_errors(), true) );
// 	}
	
// 	//Audit Start
	
// 	$audit_activity = "Member Details";
// 	$audit_description = "Edited member no ".$m_number;
	
// 	$sql = "INSERT INTO audit_trail_pension_tb (audit_date_time, audit_scheme_code, audit_username, audit_fullnames, audit_activity, audit_description) 
// 	VALUES(GETDATE(),?,?,?,?,?)";
// 	$params = array($_SESSION['scheme_code'],$_SESSION['username'],$_SESSION['user_full_names'],$audit_activity,$audit_description);
// 	$stmt = sqlsrv_query( $conn, $sql ,$params);
//     if( $stmt === false) {
// 	  die( print_r( sqlsrv_errors(), true) );
// 	}
	
// 	//Audit End
    $_SESSION['add_message'] = "Record request sent successfully to $email, james.azere@octagonafrica.com";
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
			<h3>DC Plan: Members Portal</h3>
		</div>
	</div>
	
	<div class="row">
		<div class="col-sm-12">
			<?php 
				print_menu(array("menu-pos"=>"p",
				                 "sub-menu-pos"=>"p1"));
			?>
		</div>
	</div>
	
</div>

<div class="container">
    <?php
		$sql = "SELECT * FROM members_tb WHERE m_scheme_code LIKE ? AND m_id = ?";
		$m_id = $_GET['member_id'];
		//print_r($_SESSION);
		$params = array($_GET['scheme_code'],$m_id);
		$stmt = sqlsrv_query( $conn, $sql ,$params);
		if( $stmt === false) {
		  die( print_r( sqlsrv_errors(), true) );
		}
		
		while( $row = sqlsrv_fetch_array( $stmt, SQLSRV_FETCH_ASSOC) ) {
			$m_id = $row['m_id'];
			$m_number = $row['m_number'];
			$m_scheme = $row['m_scheme_code'];
			$m_name = $row['m_name'];	
			$m_dob = $row['m_dob'];
			$m_doe = $row['m_doe'];
			$m_doj = $row['m_doj'];
			$m_gender = $row['m_gender'];
			$m_marital = $row['m_marital'];
			$m_id_number = $row['m_id_number'];
			$m_email = $row['m_email'];
			$m_phone = $row['m_phone'];
			$m_employer = $row['m_employer'];
			$m_address = $row['m_address'];
			$m_pin = $row['m_pin'];
			$m_nationality = $row['m_nationality'];
			$m_additional_years = $row['m_additional_years'];
			$m_deferred_relief = $row['m_deferred_relief'];
			$m_department = $row['m_department'];
			$m_status = $row['m_status'];	
			$m_transferred_relief = empty($row['m_transferred_relief'])?0.00:$row['m_transferred_relief'];
			$m_lock_withdrawal = $row['m_lock_withdrawal'];
			$m_status_date = "";
			if($row['m_status_date'] != NULL){
				$m_status_date = date_format($row['m_status_date'],"Y-m-d");
			}
			$m_account_name = $row['m_account_name'];
			$m_bank_id = $row['m_bank_id'];
			$m_account_no = $row['m_account_no'];
			//$m_bank_name = $row['m_bank_name'];
			$m_branch_name = $row['m_branch_name'];
			$m_branch_code = $row['m_branch_code'];
			
			$m_office_phone = $row['m_office_phone'];
            $m_kin_name = $row['m_kin_name'];
            $m_kin_id_number = $row['m_kin_id_number'];
            $m_kin_phone = $row['m_kin_phone'];
            $m_kin_address = $row['m_kin_address'];
            $m_cont_amount_salary = $row['m_cont_amount_salary'];
            $m_cont_amount_fixed = $row['m_cont_amount_fixed'];
            $m_cont_amount_transferred = $row['m_cont_amount_transferred'];
            $m_received_ee_amount = $row['m_received_ee_amount'];
            $m_received_avc_amount = $row['m_received_avc_amount'];
            $m_received_er_amount = $row['m_received_er_amount'];
            $m_propossed_ee_percentage = $row['m_propossed_ee_percentage'];
            $m_propossed_avc_percentage = $row['m_propossed_avc_percentage'];
            $m_propossed_er_percentage = $row['m_propossed_er_percentage'];
            $m_payment_mode = $row['m_payment_mode'];
            $m_payment_frequency = $row['m_payment_frequency'];
            $m_preferred_retirement_age = $row['m_preferred_retirement_age'];
            $m_transfer_from_scheme_name = $row['m_transfer_from_scheme_name'];
            $m_relief_to_use = $row['m_relief_to_use'];
		    $m_group = $row['m_group'];
		    $m_exempt_from_tax = $row['m_exempt_from_tax'];
		}
			$sql3 = "SELECT * FROM [MYDB_MACROS].[dbo].[banks] WHERE is_active = 1 AND bank_id = ? ORDER BY bank_name ASC";
    $params3 = array($m_bank_id); // Pass $m_bank_id as a variable, not a string
    $stmt3 = sqlsrv_query($conn, $sql3, $params3);   
    
    if ($stmt === false) {
        die(print_r(sqlsrv_errors(), true));
    }
    
    while ($row = sqlsrv_fetch_array($stmt3, SQLSRV_FETCH_ASSOC)) {
        $bank_name = $row['bank_name'];
    }
	?>
	<h2>Edit Member Details for Member No. <?php echo($m_number); ?></h2>
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
	<form method="post" class="form-horizontal" action="./particulars_edit_member.php" enctype="multipart/form-data">
		
		<input type="hidden" name="edit_record" value="1">
		
		<div class="row"><!--Start of Row-->
			<div class="col-sm-6" style="display:none;">
		 		 <div class="form-group" >
				    <label class="control-label col-sm-12">Member id:</label>
				    <div class="col-sm-12">
				      <input type="text" class="form-control" name="m_id" value="<?php echo($m_id); ?>" readonly>
				    </div>
				  </div>
		 	</div>
		 		<div class="col-sm-6" style="display:none;">
		 		 <div class="form-group">
				    <label class="control-label col-sm-12">Member Scheme:</label>
				    <div class="col-sm-12">
				      <input type="text" class="form-control" name="m_scheme" value="<?php echo($m_scheme); ?>" readonly>
				    </div>
				  </div>
		 	</div>
			 
		 	<div class="col-sm-6">
		 		 <div class="form-group">
				    <label class="control-label col-sm-12">Member Number:</label>
				    <div class="col-sm-12">
				      <input type="text" class="form-control" name="m_number" value="<?php echo($m_number); ?>" readonly>
				    </div>
				  </div>
		 	</div>
		 	
		 	<div class="col-sm-6">
		 		 <div class="form-group">
				    <label class="control-label col-sm-12">Member Name:</label>
				    <div class="col-sm-12">
				      <input type="text" class="form-control" name="m_name" value="<?php echo($m_name); ?>">
				    </div>
				  </div>
		 	</div>
		
		 </div><!--End of Row-->
		 
		 <div class="row"><!--Start of Row-->
			 
		 	<div class="col-sm-6">
		 		 <div class="form-group">
				    <label class="control-label col-sm-12">Date of Birth:</label>
				    <div class="col-sm-12">
				      <input type="text" class="form-control" name="m_dob" value="<?php echo(date_format($m_dob,'Y-m-d')); ?>">
				    </div>
				  </div>
		 	</div>
		 	
		 	<div class="col-sm-6">
		 		 <div class="form-group">
				    <label class="control-label col-sm-12">Date of Employment:</label>
				    <div class="col-sm-12">
				      <input readonly type="text" class="form-control" name="m_doe" value="<?php echo(date_format($m_doe,"Y-m-d")); ?>">
				    </div>
				  </div>
		 	</div>
		
		 </div><!--End of Row-->
		 
		 <div class="row"><!--Start of Row-->
			 
		 	<div class="col-sm-6">
		 		 <div class="form-group">
				    <label class="control-label col-sm-12">Date of Joining Scheme:</label>
				    <div class="col-sm-12">
				      <input readonly type="text" class="form-control" name="m_doj" value="<?php echo(date_format($m_doj,"Y-m-d")); ?>">
				    </div>
				  </div>
		 	</div>
		 	
		 	<div class="col-sm-6">
		 		 <div class="form-group">
				    <label class="control-label col-sm-12">Gender:</label>
				    <div class="col-sm-12">
				      <input type="text" class="form-control" name="m_gender" value="<?php echo($m_gender); ?>">
				    </div>
				  </div>
		 	</div>
		
		 </div><!--End of Row-->
		 
		 <div class="row"><!--Start of Row-->
			 
		 	<div class="col-sm-6">
		 		 <div class="form-group">
				    <label class="control-label col-sm-12">Marital Status:</label>
				    <div class="col-sm-12">
				      <input type="text" class="form-control" name="m_marital" value="<?php echo($m_marital); ?>">
				    </div>
				  </div>
		 	</div>
		 	
		 	<div class="col-sm-6">
		 		 <div class="form-group">
				    <label class="control-label col-sm-12">ID Number:</label>
				    <div class="col-sm-12">
				      <input type="text" class="form-control" name="m_id_number" value="<?php echo($m_id_number); ?>">
				    </div>
				  </div>
		 	</div>
		
		 </div><!--End of Row-->
		 
		 
		 <div class="row"><!--Start of Row-->
			 
		 	<div class="col-sm-6">
		 		 <div class="form-group">
				    <label class="control-label col-sm-12">Email:</label>
				    <div class="col-sm-12">
				      <input type="text" class="form-control" name="m_email" value="<?php echo($m_email); ?>">
				    </div>
				  </div>
		 	</div>
		 	
		 	<div class="col-sm-6">
		 		 <div class="form-group">
				    <label class="control-label col-sm-12">Phone:</label>
				    <div class="col-sm-12">
				      <input type="text" class="form-control" name="m_phone" value="<?php echo($m_phone); ?>">
				    </div>
				  </div>
		 	</div>
		
		 </div><!--End of Row-->
		 
		 
		 <div class="row"><!--Start of Row-->
			 
		 	<div class="col-sm-6">
		 		 <div class="form-group">
				    <label class="control-label col-sm-12">Employer:</label>
				    <div class="col-sm-12">
				      <input type="text" class="form-control" name="m_employer" value="<?php echo($m_employer); ?>">
				    </div>
				  </div>
		 	</div>
		 	
		 	<div class="col-sm-6">
		 		 <div class="form-group">
				    <label class="control-label col-sm-12">Postal Address:</label>
				    <div class="col-sm-12">
				      <input type="text" class="form-control" name="m_address" value="<?php echo($m_address); ?>">
				    </div>
				  </div>
		 	</div>
		
		 </div><!--End of Row-->
		 
		 
		 <div class="row"><!--Start of Row-->
			 
		 	<div class="col-sm-6">
		 		 <div class="form-group">
				    <label class="control-label col-sm-12">PIN:</label>
				    <div class="col-sm-12">
				      <input type="text" class="form-control" name="m_pin" value="<?php echo($m_pin); ?>">
				    </div>
				  </div>
		 	</div>
		 	
		 	<div class="col-sm-6">
		 		 <div class="form-group">
				    <label class="control-label col-sm-12">Nationality:</label>
				    <div class="col-sm-12">
				      <input type="text" class="form-control" name="m_nationality" value="<?php echo($m_nationality); ?>">
				    </div>
				  </div>
		 	</div>
		
		 </div><!--End of Row-->
		 
		 <div class="row"><!--Start of Row-->
			 
		 	<div class="col-sm-6">
		 		 <div class="form-group">
				    <label class="control-label col-sm-12">Department:</label>
				    <div class="col-sm-12">
				      <input type="text" class="form-control" name="m_department" value="<?php echo($m_department); ?>">
				    </div>
				  </div>
		 	</div>
		 	
		 	<div class="col-sm-6">
		 		 <div class="form-group">
				    <label class="control-label col-sm-12">Additional Years:</label>
				    <div class="col-sm-12">
				      <input type="text" class="form-control" name="m_additional_years" value="<?php echo($m_additional_years); ?>">
				    </div>
				  </div>
		 	</div>
		
		 </div><!--End of Row-->
		 
		 <div class="row"><!--Start of Row-->
			 
		 	<div class="col-sm-6">
		 		 <div class="form-group">
				    <label class="control-label col-sm-12">Tax Relief to use:</label>
				    <div class="col-sm-12">
				      <select class="form-control" name="m_relief_to_use">
				      	 	<option <?php echo($m_relief_to_use === "Compute" ? "Selected":""); ?> value="Compute">Compute</option>
				      	 	<option <?php echo($m_relief_to_use === "Deferred" ? "Selected":""); ?> value="Deferred">Deferred</option>
				      </select>
				    </div>
				  </div>
		 	</div>
		 	
		 	<div class="col-sm-6">
		 		 <div class="form-group">
				    <label class="control-label col-sm-12">Group Code:</label>
				    <div class="col-sm-12">
				      <input type="text" class="form-control" name="m_group" value="<?php echo($m_group); ?>">
				    </div>
				  </div>
		 	</div>
		
		 </div><!--End of Row-->
		 
		 <div class="row"><!--Start of Row-->
			 
		 	<div class="col-sm-6">
		 		 <div class="form-group">
				    <label class="control-label col-sm-12">Deferred Tax Relief:</label>
				    <div class="col-sm-12">
				      <input type="text" class="form-control" name="m_deferred_relief" value="<?php echo($m_deferred_relief); ?>">
				    </div>
				  </div>
		 	</div>
		 	
		 	<div class="col-sm-6">
		 		 <div class="form-group">
				    <label class="control-label col-sm-12">Status:</label>
				    <div class="col-sm-12">
				      <select class="form-control" name='m_status'>
				      	<option <?php echo(($m_status==="Active")?"selected":""); ?> value="Active">Active</option>
				      	<option <?php echo(($m_status==="Deferred")?"selected":""); ?> value="Deferred">Deferred</option>
				      	<option <?php echo(($m_status==="Unvested")?"selected":""); ?> value="Unvested">Unvested</option>
				      	<option <?php echo(($m_status==="Pending-Exit")?"selected":""); ?> value="Pending-Exit">Pending-Exit</option>
				      	<option <?php echo(($m_status==="Exited")?"selected":""); ?> value="Exited">Exited</option>
				      	<option <?php echo(($m_status==="New-Joiner")?"selected":""); ?> value="New-Joiner">New-Joiner</option>
				      </select>
				    </div>
				  </div>
		 	</div>
		
		 </div><!--End of Row-->
		 
		 <div class="row"><!--Start of Row-->
			 
		 	<div class="col-sm-6">
		 		 <div class="form-group">
				    <label class="control-label col-sm-12">Transferred Tax Relief:</label>
				    <div class="col-sm-12">
				      <input type="text" class="form-control" name="m_transferred_relief" value="<?php echo($m_transferred_relief); ?>">
				    </div>
				  </div>
		 	</div>
		 	
		 	<div class="col-sm-6">
		 		 <div class="form-group">
				    <label class="control-label col-sm-12">Preferred Retirement Age:</label>
				    <div class="col-sm-12">
				      <input type="text" class="form-control" name="m_preferred_retirement_age" value="<?php echo($m_preferred_retirement_age); ?>">
				    </div>
				  </div>
		 	</div>
		
		 </div><!--End of Row-->
		 
		 <div class="row"><!--Start of Row-->
			 
		 	<div class="col-sm-6">
		 		 <div class="form-group">
				    <label class="control-label col-sm-12">Last Payment Date:</label>
				    <div class="col-sm-12">
				      <input type="text" class="form-control" name="m_status_date" value="<?php echo($m_status_date); ?>">
				    </div>
				  </div>
		 	</div>
		 	
		 	<div class="col-sm-6">
		 		 <div class="form-group">
				    <label class="control-label col-sm-12">Office Tel:</label>
				    <div class="col-sm-12">
				      <input type="text" class="form-control" name="m_office_phone" value="<?php echo($m_office_phone); ?>">
				    </div>
				  </div>
		 	</div>
		
		 </div><!--End of Row-->
		 
		 <div class="row"><!--Start of Row-->
			 
		 	<div class="col-sm-6">
		 		 <div class="form-group">
				    <label class="control-label col-sm-12">EE Received Amount:</label>
				    <div class="col-sm-12">
				      <input type="text" class="form-control" name="m_received_ee_amount" value="<?php echo($m_received_ee_amount); ?>">
				    </div>
				  </div>
		 	</div>
		 	
		 	<div class="col-sm-6">
		 		 <div class="form-group">
				    <label class="control-label col-sm-12">EE Proposed Contribution(%):</label>
				    <div class="col-sm-12">
				      <input type="text" class="form-control" name="m_propossed_ee_percentage" value="<?php echo($m_propossed_ee_percentage); ?>">
				    </div>
				  </div>
		 	</div>
		
		 </div><!--End of Row-->
		 
		 <div class="row"><!--Start of Row-->
			 
		 	<div class="col-sm-6">
		 		 <div class="form-group">
				    <label class="control-label col-sm-12">AVC Received Amount:</label>
				    <div class="col-sm-12">
				      <input type="text" class="form-control" name="m_received_avc_amount" value="<?php echo($m_received_avc_amount); ?>">
				    </div>
				  </div>
		 	</div>
		 	
		 	<div class="col-sm-6">
		 		 <div class="form-group">
				    <label class="control-label col-sm-12">AVC Proposed Contribution(%):</label>
				    <div class="col-sm-12">
				      <input type="text" class="form-control" name="m_propossed_avc_percentage" value="<?php echo($m_propossed_avc_percentage); ?>">
				    </div>
				  </div>
		 	</div>
		
		 </div><!--End of Row-->
		 
		 <div class="row"><!--Start of Row-->
			 
		 	<div class="col-sm-6">
		 		 <div class="form-group">
				    <label class="control-label col-sm-12">ER Received Amount:</label>
				    <div class="col-sm-12">
				      <input type="text" class="form-control" name="m_received_er_amount" value="<?php echo($m_received_er_amount); ?>">
				    </div>
				  </div>
		 	</div>
		 	
		 	<div class="col-sm-6">
		 		 <div class="form-group">
				    <label class="control-label col-sm-12">ER Proposed Contribution(%):</label>
				    <div class="col-sm-12">
				      <input type="text" class="form-control" name="m_propossed_er_percentage" value="<?php echo($m_propossed_er_percentage); ?>">
				    </div>
				  </div>
		 	</div>
		
		 </div><!--End of Row-->
		 
		 
		 <div class="row"><!--Start of Row-->
			 
		 	<div class="col-sm-6">
		 		 <div class="form-group">
				    <label class="control-label col-sm-12">Contribution: (% of salary)</label>
				    <div class="col-sm-12">
				      <input type="text" class="form-control" name="m_cont_amount_salary" value="<?php echo($m_cont_amount_salary); ?>">
				    </div>
				  </div>
		 	</div>
		 	
		 	<div class="col-sm-6">
		 		 <div class="form-group">
				    <label class="control-label col-sm-12">Contribution: (Fixed Amount)</label>
				    <div class="col-sm-12">
				      <input type="text" class="form-control" name="m_cont_amount_fixed" value="<?php echo($m_cont_amount_fixed); ?>">
				    </div>
				  </div>
		 	</div>
		
		 </div><!--End of Row-->
		 
		 <div class="row"><!--Start of Row-->
			 
		 	<div class="col-sm-6">
		 		 <div class="form-group">
				    <label class="control-label col-sm-12">Contribution: (Transfer from Retirement Scheme)</label>
				    <div class="col-sm-12">
				      <input type="text" class="form-control" name="m_cont_amount_transferred" value="<?php echo($m_cont_amount_transferred); ?>">
				    </div>
				  </div>
		 	</div>
		 	
		 	<div class="col-sm-6">
		 		 <div class="form-group">
				    <label class="control-label col-sm-12">Retirement Scheme Name:</label>
				    <div class="col-sm-12">
				      <input type="text" class="form-control" name="m_transfer_from_scheme_name" value="<?php echo($m_transfer_from_scheme_name); ?>">
				    </div>
				  </div>
		 	</div>
		
		 </div><!--End of Row-->
		 
		 <div class="row"><!--Start of Row-->
			 
		 	<div class="col-sm-6">
		 		 <div class="form-group">
				    <label class="control-label col-sm-12">Contribution Payment Mode:</label>
				    <div class="col-sm-12">
				      <select class="form-control" name="m_payment_mode">
				      	<option <?php echo($m_payment_mode === "Salary Deduction"?"selected":""); ?> value="Salary Deduction">Salary Deduction</option>
				      	<option <?php echo($m_payment_mode === "Standing Order"?"selected":""); ?> value="Standing Order">Standing Order</option>
				      	<option <?php echo($m_payment_mode === "Cheque"?"selected":""); ?> value="Cheque">Cheque</option>
				      	<option <?php echo($m_payment_mode === "Direct Debit"?"selected":""); ?> value="Direct Debit">Direct Debit</option>
				      	<option <?php echo($m_payment_mode === "M-Pesa"?"selected":""); ?> value="M-Pesa">M-Pesa</option>
				      </select>
				    </div>
				  </div>
		 	</div>
		 	
		 	<div class="col-sm-6">
		 		 <div class="form-group">
				    <label class="control-label col-sm-12">Contribution Payment Frequency:</label>
				    <div class="col-sm-12">
				      <select class="form-control" name="m_payment_frequency">
				      	<option <?php echo($m_payment_frequency === "Once"?"selected":""); ?> value="Once">Once</option>
				      	<option <?php echo($m_payment_frequency === "Daily"?"selected":""); ?> value="Daily">Daily</option>
				      	<option <?php echo($m_payment_frequency === "Fortnightly"?"selected":""); ?> value="Fortnightly">Fortnightly</option>
				      	<option <?php echo($m_payment_frequency === "Monthly"?"selected":""); ?> value="Monthly">Monthly</option>
				      	<option <?php echo($m_payment_frequency === "Quarterly"?"selected":""); ?> value="Quarterly">Quarterly</option>
				      	<option <?php echo($m_payment_frequency === "Half Annually"?"selected":""); ?> value="Half Annually">Half Annually</option>
				      	<option <?php echo($m_payment_frequency === "Annually"?"selected":""); ?> value="Annually">Annually</option>
				      </select>
				    </div>
				  </div>
		 	</div>
		
		 </div><!--End of Row-->
		 
		 <div class="row"><!--Start of Row-->
			 
		 	<div class="col-sm-6">
		 		 <div class="form-group">
				    <label class="control-label col-sm-12">Account Name:</label>
				    <div class="col-sm-12">
				      <input type="text" class="form-control" name="m_account_name" value="<?php echo($m_account_name); ?>">
				    </div>
				  </div>
		 	</div>
		 	
		 	<div class="col-sm-6">
		 		 <div class="form-group">
				    <label class="control-label col-sm-12">Account No.:</label>
				    <div class="col-sm-12">
				      <input type="text" class="form-control" name="m_account_no" value="<?php echo($m_account_no); ?>">
				    </div>
				  </div>
		 	</div>
		
		 </div><!--End of Row-->
		 
		 <div class="row"><!--Start of Row-->
			 
		 	 <input type="text" class="form-control" name="m_bank_name" value="<?php echo($bank_name); ?>" hidden >
			 
		 	<div class="col-sm-6">
		 		 <div class="form-group">
				  
				    <div class="col-sm-12">
				      <label class="control-label">Bank Name: </label>
             			<select name="m_bank_id" class="form-control" >
             	
             			<option value="<?php echo($m_bank_id); ?>"><?php echo($bank_name); ?></option>
             		     
             				<?php
             				$sql = "SELECT * FROM [MYDB_MACROS].[dbo].[banks] WHERE is_active = 1 
             				 ORDER BY bank_name ASC";
             				$params = array($the_scheme['scheme_country']);
             				$stmt = sqlsrv_query( $conn, $sql ,$params );   
    					    if( $stmt === false) {
    					        die( print_r( sqlsrv_errors(), true) );
    					    }
    					    
    					    while( $row = sqlsrv_fetch_array( $stmt, SQLSRV_FETCH_ASSOC) ) {
    					    	?>
    					    	<option value="<?php echo($row['bank_id']); ?>"><?php echo($row['bank_name']); ?></option>
    					    	<?php
    					    }
             				?>
             			</select>
							                         			
							                         			
				    </div>
				  </div>
		 	</div>
		 	
		 	<div class="col-sm-6">
		 		 <div class="form-group">
				    <label class="control-label col-sm-12">Branch Name</label>
				    <div class="col-sm-12">
				      <input type="text" class="form-control" name="m_branch_name" value="<?php echo($m_branch_name); ?>">
				    </div>
				  </div>
		 	</div>
		
		 </div><!--End of Row-->
		 
		 <div class="row"><!--Start of Row-->
			 
		 	<div class="col-sm-6">
		 		 <div class="form-group">
				    <label class="control-label col-sm-12">Branch Code:</label>
				    <div class="col-sm-12">
				      <input type="text" class="form-control" name="m_branch_code" value="<?php echo($m_branch_code); ?>">
				    </div>
				  </div>
		 	</div>
		 	
		 	<div class="col-sm-6">
		 		 <div class="form-group">
				    <label class="control-label col-sm-12">Lock Withdrawal:</label>
				    <div class="col-sm-12">
				      <input type="checkbox" class="form-check" name="m_lock_withdrawal" value="1" <?php echo(($m_lock_withdrawal == 1)?"checked":""); ?>>
				    </div>
				  </div>
		 	</div>
		
		 </div><!--End of Row-->
		 
		 
		 <div class="row"><!--Start of Row-->
			 
		 	<div class="col-sm-6">
		 		 <div class="form-group">
				    <label class="control-label col-sm-12">Next of Kin Name:</label>
				    <div class="col-sm-12">
				      <input type="text" class="form-control" name="m_kin_name" value="<?php echo($m_kin_name); ?>">
				    </div>
				  </div>
		 	</div>
		 	
		 	<div class="col-sm-6">
		 		 <div class="form-group">
				    <label class="control-label col-sm-12">Next of Kin ID Number:</label>
				    <div class="col-sm-12">
				      <input type="text" class="form-control" name="m_kin_id_number" value="<?php echo($m_kin_id_number); ?>">
				    </div>
				  </div>
		 	</div>
		
		 </div><!--End of Row-->
		 
		 
		 <div class="row"><!--Start of Row-->
			 
		 	<div class="col-sm-6">
		 		 <div class="form-group">
				    <label class="control-label col-sm-12">Next of Kin Tel:</label>
				    <div class="col-sm-12">
				      <input type="text" class="form-control" name="m_kin_phone" value="<?php echo($m_kin_phone); ?>">
				    </div>
				  </div>
		 	</div>
		 	
		 	<div class="col-sm-6">
		 		 <div class="form-group">
				    <label class="control-label col-sm-12">Next of Kin Address:</label>
				    <div class="col-sm-12">
				      <input type="text" class="form-control" name="m_kin_address" value="<?php echo($m_kin_address); ?>">
				    </div>
				  </div>
		 	</div>
		 	
		
		 </div><!--End of Row-->
		 
		 <div class="row"><!--Start of Row-->
			 
		 	<div class="col-sm-6">
		 		 <div class="form-group">
				    <label class="control-label col-sm-12">Exempt Member Benefits From Taxation:</label>
				    <div class="col-sm-12">
				      <select name="m_exempt_from_tax" class="form-control">
				      	<option value="1" <?php echo($m_exempt_from_tax == 1 ? 'Selected':''); ?> >Yes</option>
				      	<option value="0" <?php echo($m_exempt_from_tax == 0 ? 'Selected':''); ?> >No</option>
				      </select>
				    </div>
				  </div>
		 	</div>
		 	
		   <div class="col-sm-6"> 
                                                                        <label class="control-label"  class="required">Upload National ID/Passport (pdf)<span style="color: red;"> *</span></label>
                                                                        <input type="file" class="form-control" required name="national_id_file" id="national_id_file" >
                                                                    </div>
		 	
		 	
                                                                  
                                                                  
                                                                           <div class="col-sm-6"> 
                                                                    <label class="control-label">Proof Bank Details (pdf)</label>
                                                                    <input type="file" class="form-control" name="atm_card_file" id="atm_card_file">
                                                                </div>
                                                                    <div class="col-sm-6"> 
                                                                    <label class="control-label"><?php echo $tax_pin_narration; ?> (pdf)</label>
                                                                    <input type="file" class="form-control" name="kra_pin_file" id="kra_pin_file">
                                                                </div>

                                                        
                                                   
		
		 </div><!--End of Row-->
		 <?php
		 $allowed_ids = [104547,137274, 133165,125437,125256,95777,138181,98031,125758,102119,102422,125444,131786, 102146,98093,138818,143136]; 
         if (in_array($_SESSION['user_id'], $allowed_ids)) {
		 ?>
		 <div class="col-sm-12" style="margin-top:20px">
		  	 <div class="form-group">
			    <div class="col-sm-offset-2 col-sm-10">
			      <button type="submit" class="btn btn-primary">Submit</button>
			    </div>
			  </div>
		  </div>
		  <?php } ?>
		
	</form>
</div>

</body>
</html>
<?php
	//Close connection 
	sqlsrv_close( $conn );
?>