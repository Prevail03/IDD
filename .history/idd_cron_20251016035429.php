<?php
require('../commons/config/settings.php');
require(__DIR__.'/../../macros/funcs.php');

$conn = sqlsrv_connect(Settings::$serverName, Settings::$connectionInfo);

if (!$conn) {
    die(print_r(sqlsrv_errors(), true));
}

// 1. List members with more than 10 years of membership
$sql = "SELECT * FROM mydb.dbo.members_tb WHERE DATEDIFF(year, m_doj, GETDATE()) > 10 AND m_scheme_code = 'APP01'";
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
