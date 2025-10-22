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
            absolute_drawdown_percentage, annual_payroll_of_prev_yr, payroll_year
        FROM mydb.dbo.idd_payroll";

$result = sqlsrv_query($conn, $sql);
if ($result === false) die(print_r(sqlsrv_errors(), true));

$payslipPath = __DIR__ . '/payslips/';
if (!is_dir($payslipPath)) mkdir($payslipPath, 0777, true);

$logo = 'https://www.octagonafrica.com/wp-content/uploads/2022/06/Octa-white.svg';

while ($row = sqlsrv_fetch_array($result, SQLSRV_FETCH_ASSOC)) {
    $memberName = htmlspecialchars($row['member_name']);
    $memberNumber = htmlspecialchars($row['member_number']);
    $schemeCode = htmlspecialchars($row['scheme_code']);
    $year = htmlspecialchars($row['payroll_year']);

    $html = '
    <html>
    <head>
        <style>
            body { font-family: DejaVu Sans, sans-serif; color: #333; font-size: 13px; }
            .payslip-box { width: 700px; border: 1px solid #ccc; padding: 20px; margin: 0 auto; }
            .header { text-align: center; margin-bottom: 10px; }
            .header img { width: 180px; }
            h2 { text-align: center; margin-top: 5px; color: #003366; }
            table { width: 100%; border-collapse: collapse; margin-top: 15px; }
            th, td { border: 1px solid #ddd; padding: 8px; }
            th { background-color: #f2f2f2; text-align: left; }
            .footer { text-align: center; font-size: 12px; color: #555; margin-top: 30px; }
        </style>
    </head>
    <body>
        <div class="payslip-box">
            <div class="header">
                <img src="'.$logo.'" alt="Octagon Logo">
                <h2>INCOME DRAWDOWN SCHEME PAYSLIP</h2>
                <p><strong>Payroll Year:</strong> '.$year.'</p>
            </div>

            <p><strong>Member Name:</strong> '.$memberName.'<br>
               <strong>Member Number:</strong> '.$memberNumber.'<br>
               <strong>Scheme Code:</strong> '.$schemeCode.'</p>

            <table>
                <tr><th>Closing Balance</th><td>'.number_format($row['closing_balance'], 2).'</td></tr>
                <tr><th>Annual Withdrawal</th><td>'.number_format($row['annual_withdrawal'], 2).'</td></tr>
                <tr><th>Gross After Deductions (Net Pay)</th><td>'.number_format($row['gross_after_deductions'], 2).'</td></tr>
                <tr><th>Drawdown %</th><td>'.number_format($row['drawdown_percentage'], 2).'%</td></tr>
                <tr><th>Absolute Drawdown %</th><td>'.number_format($row['absolute_drawdown_percentage'], 2).'%</td></tr>
                <tr><th>Annual Payroll (Prev Year)</th><td>'.number_format($row['annual_payroll_of_prev_yr'], 2).'</td></tr>
            </table>

            <div class="footer">
                <p><strong>Octagon Africa</strong> | info@octagonafrica.com | www.octagonafrica.com</p>
                <p>This payslip has been prepared and issued on behalf of the Board of Trustees of Octagon Income Drawdown Scheme by Octagon Pension Services Ltd.</p>
                <p><em>Generated on '.date('d M Y').'</em></p>
            </div>
        </div>
    </body>
    </html>';

    // Create PDF
    $options = new Options();
    $options->set('isRemoteEnabled', true);
    $dompdf = new Dompdf($options);
    $dompdf->loadHtml($html);
    $dompdf->setPaper('A4', 'portrait');
    $dompdf->render();

    // Save file
    $filename = $payslipPath . 'payslip_' . $memberName . '_' . $year . '.pdf';
    file_put_contents($filename, $dompdf->output());
    echo "✅ Generated: $filename<br>";
}

echo "<br>All payslips generated successfully!";
?>
