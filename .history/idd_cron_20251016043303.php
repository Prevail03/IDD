<?php
require('../commons/config/settings.php');
require(__DIR__.'/../../macros/funcs.php');
require('../commons/phpmailer/PHPMailerAutoload.php');

$conn = sqlsrv_connect(Settings::$serverName, Settings::$connectionInfo);

if (!$conn) {
    die(print_r(sqlsrv_errors(), true));
}

$mail = new PHPMailer;      
$mail->SMTPDebug = 0; 
$mail->isSMTP();                                      // Set mailer to use SMTP
$mail->Host = Settings::$email_host1;  						// Specify main and backup SMTP servers
$mail->SMTPAuth = true;                               // Enable SMTP authentication
$mail->Username = Settings::$email_username_cred1;                 // SMTP username
$mail->Password = Settings::$email_password_cred1;                           // SMTP password
$mail->SMTPSecure = Settings::$email_security1;                           // Enable TLS encryption, `ssl` also accepted
$mail->Port = Settings::$email_port1; 
$mail->setFrom(Settings::$correspondance_email1, 'Octagon Africa');
$recipients = [
    "retail-department@octagonafrica.com",
    "michael.komen@octagonafrica.com",
    "stacy.madara@octagonafrica.com",
    "cecilia.munene@octagonafrica.com"
];
foreach ($recipients as $address) {
    $mail->addAddress($address);
}
$mail->addBCC("prevailer.muhani@octagonafrica.com");
$mail->addReplyTo(Settings::$correspondance_email1, 'Octagon Africa');
$mail->isHTML(true);                                  // Set email format to HTML
//Make Subject Unique
$dt = new DateTime();
$append_dt = $dt->format('Y-m-d H:i:s');

$mail->Subject = "Octagon Africa - IDD Monthly Payroll Membership Status as at $append_dt";
$message = "<p>Dear Admin Team,</p><p>Please find below the list of members with more than 10 years of membership and those who have exhausted more than 75% of their funds as at $append_dt.</p><p>Kindly take the necessary actions.</p><br><br>";

// Re-build first table (members > 10 years)
$sql1 = "SELECT * FROM mydb.dbo.members_tb WHERE DATEDIFF(year, m_doj, GETDATE()) > 10 AND m_scheme_code = 'KE454'";
$res1 = sqlsrv_query($conn, $sql1);
if ($res1 === false) {
    $message .= "<p><strong>Error fetching long-term members:</strong> " . htmlspecialchars(print_r(sqlsrv_errors(), true)) . "</p>";
} else {
    $message .= "<h3>Members with more than 10 years of membership</h3>";
    $message .= "<table border='1' cellpadding='5' cellspacing='0' style='border-collapse:collapse;'>";
    $message .= "<thead><tr><th>#</th><th>ID</th><th>Scheme Code</th><th>Member Name</th><th>Email</th><th>Join Date</th><th>D.o.B</th><th>National ID</th><th>Phone</th></tr></thead><tbody>";
    $i = 1;
    while ($row = sqlsrv_fetch_array($res1, SQLSRV_FETCH_ASSOC)) {
    $doj = ($row['m_doj'] instanceof DateTime) ? $row['m_doj']->format('Y-m-d') : '';
    $dob = ($row['m_dob'] instanceof DateTime) ? $row['m_dob']->format('Y-m-d') : '';
    $message .= "<tr>";
    $message .= "<td>" . $i++ . "</td>";
    $message .= "<td>" . htmlspecialchars($row['m_id']) . "</td>";
    $message .= "<td>" . htmlspecialchars($row['m_scheme_code']) . "</td>";
    $message .= "<td>" . htmlspecialchars($row['m_name']) . "</td>";
    $message .= "<td>" . htmlspecialchars($row['m_email']) . "</td>";
    $message .= "<td>" . htmlspecialchars($doj) . "</td>";
    $message .= "<td>" . htmlspecialchars($dob) . "</td>";
    $message .= "<td>" . htmlspecialchars($row['m_id_number']) . "</td>";
    $message .= "<td>" . htmlspecialchars($row['m_phone']) . "</td>";
    $message .= "</tr>";
    }
    $message .= "</tbody></table><br><br>";
}

// Re-build second table (members exhausted > 75%)
$sql2 = "SELECT 
    c.cont_scheme_code AS scheme_code,
    c.cont_member_number AS member_number,
    m.m_name AS member_name,
    SUM(CASE 
    WHEN c.cont_document = 'Transfers in' THEN c.cont_amount 
    ELSE 0 
    END) AS joined_amount,
    SUM(c.cont_amount) AS current_balance
FROM MYDB.DBO.CONTRIBUTIONS_TB c
INNER JOIN MYDB.DBO.MEMBERS_TB m
    ON m.m_scheme_code = c.cont_scheme_code 
    AND m.m_number = c.cont_member_number
WHERE c.cont_scheme_code = 'KE454'
GROUP BY 
    c.cont_scheme_code,
    c.cont_member_number,
    m.m_name
HAVING 
    SUM(c.cont_amount) > 0.75 * SUM(CASE 
    WHEN c.cont_document = 'Transfers in' THEN c.cont_amount 
    ELSE 0 
    END);";
$res2 = sqlsrv_query($conn, $sql2);
if ($res2 === false) {
    $message .= "<p><strong>Error fetching exhausted-funds members:</strong> " . htmlspecialchars(print_r(sqlsrv_errors(), true)) . "</p>";
} else {
    $message .= "<h3>Members who have exhausted their funds (more than 75%)</h3>";
    $message .= "<table border='1' cellpadding='5' cellspacing='0' style='border-collapse:collapse;'>";
    $message .= "<thead><tr><th>#</th><th>Scheme Code</th><th>Member Number</th><th>Member Name</th><th>Joined Amount</th><th>Current Balance</th></tr></thead><tbody>";
    $i = 1;
    while ($row = sqlsrv_fetch_array($res2, SQLSRV_FETCH_ASSOC)) {
    $message .= "<tr>";
    $message .= "<td>" . $i++ . "</td>";
    $message .= "<td>" . htmlspecialchars($row['scheme_code']) . "</td>";
    $message .= "<td>" . htmlspecialchars($row['member_number']) . "</td>";
    $message .= "<td>" . htmlspecialchars($row['member_name']) . "</td>";
    $message .= "<td>" . number_format((float)$row['joined_amount'], 2) . "</td>";
    $message .= "<td>" . number_format((float)$row['current_balance'], 2) . "</td>";
    $message .= "</tr>";
    }
    $message .= "</tbody></table><br><br>";
}

$mail->Body = $message;
$mail->AltBody = "<p>Dear Member,</p><p>You need to update your membership details for $scheme_name. Click the link below to proceed: </p><p>$details_update_link</p><p>Regards,</p><p>Octagon Africa team";

if($mail->send()) {
    // send mail
    $response =[
    "status"=>200,
    "operation"=>"Success",
    "message"=>"Mail sent successfully toAdmin Team",
    ];
    header('Content-Type: application/json');
    echo json_encode($response);  
} else {
    //Mail  not sent
    $response =[
    "status"=>400,
    "operation"=>"Failure",
    "message"=>"Failed to send email",
    "error"=>$mail->ErrorInfo
    
    ];
    header('Content-Type: application/json');
    echo json_encode($response);
}
sqlsrv_close($conn);
?>
