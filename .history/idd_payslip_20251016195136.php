<?php
require 'vendor/autoload.php'; // if using composer
use Dompdf\Dompdf;
use Dompdf\Options;

// database connection
require('../commons/config/settings.php');
require(__DIR__.'/../../macros/funcs.php');
require('../commons/phpmailer/PHPMailerAutoload.php');

$conn = sqlsrv_connect(Settings::$serverName, Settings::$connectionInfo);

if (!$conn) {
    die(print_r(sqlsrv_errors(), true));
}

// fetch payroll records
$sql = "SELECT 
            scheme_code, member_number, member_name,
            closing_balance, annual_withdrawal,
            gross_after_deductions, drawdown_percentage,
            absolute_drawdown_percentage, annual_payroll_of_prev_yr, payroll_year
        FROM mydb.dbo.idd_payroll";

$result = sqlsrv_query($conn, $sql);

if ($result === false) {
    die(print_r(sqlsrv_errors(), true));
}

// setup dompdf
$options = new Options();
$options->set('isRemoteEnabled', true);
$dompdf = new Dompdf($options);

// loop through each member
while ($row = sqlsrv_fetch_array($result, SQLSRV_FETCH_ASSOC)) {
    $html = '
    <html>
    <head>
        <style>
            body { font-family: DejaVu Sans, sans-serif; }
            .payslip-box {
                width: 700px;
                border: 1px solid #ccc;
                padding: 20px;
                margin: 0 auto;
            }
            h2 { text-align: center; color: #333; }
            table {
                width: 100%;
                border-collapse: collapse;
                margin-top: 15px;
            }
            th, td {
                border: 1px solid #ddd;
                padding: 8px;
                font-size: 13px;
            }
            th {
                background-color: #f2f2f2;
                text-align: left;
            }
        </style>
    </head>
    <body>
        <div class="payslip-box">
            <h2>Member Payslip - '.$row['payroll_year'].'</h2>
            <p><strong>Member Name:</strong> '.$row['member_name'].'<br>
               <strong>Member Number:</strong> '.$row['member_number'].'<br>
               <strong>Scheme Code:</strong> '.$row['scheme_code'].'</p>

            <table>
                <tr><th>Closing Balance</th><td>'.number_format($row['closing_balance'], 2).'</td></tr>
                <tr><th>Annual Withdrawal</th><td>'.number_format($row['annual_withdrawal'], 2).'</td></tr>
                <tr><th>Gross After Deductions</th><td>'.number_format($row['gross_after_deductions'], 2).'</td></tr>
                <tr><th>Drawdown %</th><td>'.number_format($row['drawdown_percentage'], 2).'%</td></tr>
                <tr><th>Absolute Drawdown %</th><td>'.number_format($row['absolute_drawdown_percentage'], 2).'%</td></tr>
                <tr><th>Annual Payroll (Prev Year)</th><td>'.number_format($row['annual_payroll_of_prev_yr'], 2).'</td></tr>
            </table>

            <p style="text-align:center; margin-top:20px;">
                <small>Generated on '.date('d-M-Y').'</small>
            </p>
        </div>
    </body>
    </html>';

    // load HTML and generate
    $dompdf->loadHtml($html);
    $dompdf->setPaper('A4', 'portrait');
    $dompdf->render();

    // save each file
    $filename = 'payslip_'.$row['member_number'].'_'.$row['payroll_year'].'.pdf';
    file_put_contents('payslips/'.$filename, $dompdf->output());
}

echo "✅ Payslips generated successfully in /payslips folder!";
?>
