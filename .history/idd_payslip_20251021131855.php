<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require 'vendor/autoload.php';
use Dompdf\Dompdf;
use Dompdf\Options;

require('../commons/config/settings.php');

// 🔹 SQLSRV connection with extended timeout
Settings::$connectionInfo['LoginTimeout'] = 60;
$conn = sqlsrv_connect(Settings::$serverName, Settings::$connectionInfo);
if (!$conn) die(print_r(sqlsrv_errors(), true));

// 🔹 QueryTimeout for long reads
$queryOptions = ["Scrollable" => SQLSRV_CURSOR_FORWARD, "QueryTimeout" => 300];

// 🔹 Optimized query
$sql = "
SELECT  
    ist.staging_id,
    ist.scheme_code,
    ist.member_number,
    ist.member_name,
    ist.net_salary,
    ist.gross_salary,
    ist.medical_deduction,
    ip.account_number,
    ip.bank_code,
    ISNULL(ip.closing_balance, 0) AS closing_balance,
    sp.period_name,
    s.sub_period_name
FROM mydb.dbo.idd_payroll_staging AS ist
LEFT JOIN mydb.dbo.idd_payroll AS ip
    ON ist.member_number = ip.member_number
   AND ist.scheme_code = ip.scheme_code
LEFT JOIN mydb.dbo.idd_payroll_batches AS ib
    ON ist.batch_id = ib.batch_id
LEFT JOIN mydb.dbo.scheme_periods_tb AS sp
    ON ib.period_id = sp.period_id
LEFT JOIN mydb.dbo.scheme_sub_periods_tb AS s
    ON ib.sub_period_id = s.sub_period_id
WHERE LTRIM(RTRIM(ist.status)) = 'Approved'
  AND ist.batch_id = 8
ORDER BY ist.member_name ASC;
";

$result = sqlsrv_query($conn, $sql, [], $queryOptions);
if ($result === false) die(print_r(sqlsrv_errors(), true));

// 🔹 Create payslips folder if missing
$payslipPath = __DIR__ . '/payslips/';
if (!is_dir($payslipPath)) mkdir($payslipPath, 0777, true);

// 🔹 Branding
$logo = 'https://cloud.octagonafrica.com/opas/commons/OctagonMail/images/Artboard_1_copy_2.png';
$previousYearEndDate = (date('Y') - 1) . '-12-31';
$schemeName = 'NAWIRI INCOME DRAWDOWN SCHEME';

$count = 0;

while ($row = sqlsrv_fetch_array($result, SQLSRV_FETCH_ASSOC)) {
    $memberName = htmlspecialchars($row['member_name'] ?? '');
    $memberNumber = htmlspecialchars($row['member_number'] ?? '');
    $month = trim(($row['period_name'] ?? '') . ' - ' . ($row['sub_period_name'] ?? ''));
    $balanceAtEndYear = number_format((float)$row['closing_balance'], 2);
    $earnings = number_format((float)$row['gross_salary'], 2);
    $medical = number_format((float)$row['medical_deduction'], 2);
    $netPay = number_format((float)$row['net_salary'], 2);
    $bankCode = htmlspecialchars($row['bank_code'] ?? '');
    $accNo = htmlspecialchars($row['account_number'] ?? '');
    $bankDetails = "Bank: $bankCode | Account: $accNo";

    // 🔹 Clean filename
    $safeName = preg_replace('/[^A-Za-z0-9_\-]/', '_', $memberName);
    $safeMonth = preg_replace('/[^A-Za-z0-9_\-]/', '_', $month);
    $filename = "{$payslipPath}payslip_{$safeName}_{$safeMonth}.pdf";

    // 🔹 HTML Template (light DOM)
    $html = "
    <html>
    <head>
        <style>
            body { font-family: DejaVu Sans, sans-serif; font-size: 13px; color: #000; }
            .header { border-bottom: 1px solid #000; margin-bottom: 15px; }
            .header img { width: 140px; vertical-align: middle; }
            h2 { color: #003366; text-align: center; }
            table { width: 100%; border-collapse: collapse; margin-top: 10px; }
            th, td { border: 1px solid #000; padding: 6px; }
            th { background: #f2f2f2; width: 40%; text-align: left; }
            .note { margin-top: 20px; font-size: 12px; }
            .stamp { text-align: center; margin-top: 25px; }
            .stamp img { width: 140px; opacity: 0.85; }
        </style>
    </head>
    <body>
        <div class='header'>
            <img src='$logo' alt='Octagon Logo'>
            <h2>INCOME DRAWDOWN PAYSLIP</h2>
        </div>

        <table>
            <tr><th>Member No.</th><td>$memberNumber</td></tr>
            <tr><th>Name</th><td>$memberName</td></tr>
            <tr><th>Scheme</th><td>$schemeName</td></tr>
            <tr><th>Balance as at $previousYearEndDate</th><td>$balanceAtEndYear</td></tr>
            <tr><th>Payroll Month</th><td>$month</td></tr>
            <tr><th>Earnings</th><td>$earnings</td></tr>
            <tr><th>Medical Deductions</th><td>$medical</td></tr>
            <tr><th>Net Pay</th><td>$netPay</td></tr>
            <tr><th>Payment Details</th><td>$bankDetails</td></tr>
        </table>

        <div class='note'>
            <p><strong>Note:</strong> This payslip has been issued on behalf of the Trustees of the Octagon Income Drawdown Scheme by Octagon Pension Services Ltd.</p>
            <p>Members above age 65 are exempt from Tax as per the Tax Laws (Amendment)(No.2) Bill, 2020.</p>
        </div>

        <div class='stamp'>
            <img src='https://i.ibb.co/Fn9qtw0/octagon-stamp.png' alt='Stamp'>
            <p>P.O. Box 10034-00100, Nairobi</p>
        </div>
    </body>
    </html>";

    // 🔹 Generate PDF fast
    $options = new Options();
    $options->set('isRemoteEnabled', true);
    $options->set('defaultFont', 'DejaVu Sans');
    $dompdf = new Dompdf($options);
    $dompdf->loadHtml($html);
    $dompdf->setPaper('A4', 'portrait');
    $dompdf->render();

    // Save file
    file_put_contents($filename, $dompdf->output());
    $count++;

    echo "✅ Payslip generated for {$memberName} → {$filename}<br>";

    // Free memory after every 20 PDFs
    if ($count % 20 === 0) {
        gc_collect_cycles();
        sleep(1); // short pause for IO
    }
}

echo "<br><strong>✅ All $count payslips generated successfully!</strong>";
sqlsrv_close($conn);
?>
