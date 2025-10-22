<?php
require 'vendor/autoload.php';
use Dompdf\Dompdf;
use Dompdf\Options;

require('../commons/config/settings.php');
$conn = sqlsrv_connect(Settings::$serverName, Settings::$connectionInfo);
if (!$conn) die(print_r(sqlsrv_errors(), true));

$sql = "SELECT 
            scheme_code, member_number, member_name,
            closing_balance, annual_withdrawal,
            gross_after_deductions, drawdown_percentage,
            absolute_drawdown_percentage, annual_payroll_of_prev_yr, payroll_year,
            bank_code, account_number
        FROM mydb.dbo.idd_payroll";

$result = sqlsrv_query($conn, $sql);
if ($result === false) die(print_r(sqlsrv_errors(), true));

$payslipPath = __DIR__ . '/payslips/';
if (!is_dir($payslipPath)) mkdir($payslipPath, 0777, true);

$logo = 'https://www.octagonafrica.com/wp-content/uploads/2022/06/Octa-white.svg';

while ($row = sqlsrv_fetch_array($result, SQLSRV_FETCH_ASSOC)) {
    $memberName = htmlspecialchars($row['member_name']);
    $memberNumber = htmlspecialchars($row['member_number']);
    $schemeName = 'NAWIRI INCOME DRAWDOWN SCHEME'; // static or fetched
    $employer = 'NAWIRI INCOME DRAWDOWN SCHEME';
    $month = 'August ' . htmlspecialchars($row['payroll_year']);
    $earnings = number_format($row['gross_after_deductions'], 2);
    $tax = number_format(0, 2);
    $medical = number_format(0, 2);
    $netPay = number_format($row['gross_after_deductions'], 2);
    $bankDetails = 'Bank: ' . $row['bank_code'] . ' Branch: 000 Account Name: Account No: ' . $row['account_number'];

    $html = '
    <html>
    <head>
        <style>
            body { font-family: DejaVu Sans, sans-serif; font-size: 13px; color: #000; }
            .header { text-align: left; border-bottom: 1px solid #000; padding-bottom: 5px; margin-bottom: 15px; }
            .header img { width: 120px; vertical-align: middle; }
            .header-text { display: inline-block; vertical-align: middle; margin-left: 15px; }
            .header-text h3 { margin: 0; font-size: 16px; color: #003366; }
            .header-text p { margin: 2px 0; font-size: 12px; }

            table { width: 100%; border-collapse: collapse; }
            th, td { border: 1px solid #000; padding: 6px 8px; text-align: left; }
            th { width: 35%; background-color: #f9f9f9; }

            .note { margin-top: 20px; font-size: 12px; }
            .note strong { font-weight: bold; }
            .stamp { text-align: center; margin-top: 25px; }
            .stamp img { width: 150px; opacity: 0.85; }
        </style>
    </head>
    <body>
        <div class="header">
            <img src="'.$logo.'" alt="Octagon Logo">
            <div class="header-text">
                <h3>Octagon Africa</h3>
                <p>info@octagonafrica.com</p>
                <p>www.octagonafrica.com</p>
            </div>
        </div>

        <table>
            <tr><th>Member No.</th><td>'.$memberNumber.'</td></tr>
            <tr><th>Name</th><td>'.$memberName.'</td></tr>
            <tr><th>Scheme</th><td>'.$schemeName.'</td></tr>
            <tr><th>Employer</th><td>'.$employer.'</td></tr>
            <tr><th>Payroll Month</th><td>'.$month.'</td></tr>
            <tr><th>Earnings:</th><td>'.$earnings.'</td></tr>
            <tr><th>Tax Deductions:</th><td>'.$tax.'</td></tr>
            <tr><th>Medical Deductions:</th><td>'.$medical.'</td></tr>
            <tr><th>Net Pay:</th><td>'.$netPay.'</td></tr>
            <tr><th>Payment Details:</th><td>'.$bankDetails.'</td></tr>
        </table>

        <div class="note">
            <p><strong>Note:</strong> This payslip has been prepared and issued on behalf of the Board of Trustees of OCTAGON INCOME DRAWDOWN SCHEME by Octagon Pension Services Ltd.</p>
            <p>Note - The member is exempted from Tax payment as per the Tax Laws (Amendment)(No.2) Bill, 2020, exempting members above age 65.</p>
        </div>

        <div class="stamp">
            <img src="https://i.ibb.co/Fn9qtw0/octagon-stamp.png" alt="Octagon Stamp">
            <p>P.O. Box 10034-00100, Nairobi</p>
        </div>
    </body>
    </html>';

    // Generate the PDF
    $options = new Options();
    $options->set('isRemoteEnabled', true);
    $dompdf = new Dompdf($options);
    $dompdf->loadHtml($html);
    $dompdf->setPaper('A4', 'portrait');
    $dompdf->render();

    // Save the file
    $filename = $payslipPath . 'payslip_' . preg_replace('/[^A-Za-z0-9_\-]/', '_', $memberName) . '_' . $month . '.pdf';
    file_put_contents($filename, $dompdf->output());

    echo "✅ Generated: $filename<br>";
}

echo "<br>All payslips generated successfully!";
?>
