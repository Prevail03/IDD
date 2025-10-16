<?php
require('../commons/config/settings.php');
require(__DIR__.'/../../macros/funcs.php');

$conn = sqlsrv_connect(Settings::$serverName, Settings::$connectionInfo);

if (!$conn) {
    die(print_r(sqlsrv_errors(), true));
}

// 1. List members with more than 10 years of membership
$sql = "SELECT * FROM mydb.dbo.members_tb WHERE DATEDIFF(year, m_doj, GETDATE()) > 10 AND m_scheme_code = 'KE454'";
$stmt = sqlsrv_query($conn, $sql);

if ($stmt === false) {
    die(print_r(sqlsrv_errors(), true));
}
?>

<table border="1" cellpadding="5" cellspacing="0">
    <thead>
        <tr>
            <th>#</th>
            <th>ID</th>
            <th>Scheme Code</th>
            <th>Member Name</th>
            <th>Email</th>
            <th>Join Date</th>
            <th>D.o.B</th>
            <th>National ID</th>
            <th>Phone</th>
        </tr>
    </thead>
    <tbody>
        <?php
        $i = 1;
        while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
            ?>
            <tr>
                <td><?php echo $i++; ?></td>
                <td><?php echo htmlspecialchars($row['m_id']); ?></td>
                <td><?php echo htmlspecialchars($row['m_scheme_code']); ?></td>
                <td><?php echo htmlspecialchars($row['m_name']); ?></td>
                <td><?php echo htmlspecialchars($row['m_email']); ?></td>
                <td><?php echo $row['m_doj'] instanceof DateTime ? $row['m_doj']->format('Y-m-d') : ''; ?></td>
                <td><?php echo $row['m_dob'] instanceof DateTime ? $row['m_dob']->format('Y-m-d') : ''; ?></td>
                <td><?php echo htmlspecialchars($row['m_id_number']); ?></td>
                <td><?php echo htmlspecialchars($row['m_phone']); ?></td>
            </tr>
            <?php
        }
        ?>
    </tbody>
</table>

<?php
//2. return List of members who have exhausted their funds  70 %
$sql = "SELECT 
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
$stmt = sqlsrv_query($conn, $sql);

if ($stmt === false) {
    die(print_r(sqlsrv_errors(), true));
}

?>
<br><br>
<h2>Members who have exhausted their funds (more than 75%)</h2>
<table border="1" cellpadding="5" cellspacing="0">
    <thead>
        <tr>
            <th>#</th>
            <th>Scheme Code</th>
            <th>Member Number</th>
            <th>Member Name</th>
            <th>Joined Amount</th>
            <th>Current Balance</th>
        </tr>
        </thead>
    <tbody>
        <?php
        $i = 1;
        while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
            ?>
            <tr>
                <td><?php echo $i++; ?></td>
                <td><?php echo htmlspecialchars($row['scheme_code']); ?></td>
                <td><?php echo htmlspecialchars($row['member_number']); ?></td>
                <td><?php echo htmlspecialchars($row['member_name']); ?></td>
                <td><?php echo number_format($row['joined_amount'], 2); ?></td>
                <td><?php echo number_format($row['current_balance'], 2); ?></td>
            </tr>
            <?php
        }
        ?>
    </tbody>
</table>
<?php
sqlsrv_close($conn);
?>
