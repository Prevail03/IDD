<?php

    require('../commons/config/settings.php');
    require(__DIR__.'/../../macros/funcs.php');
    $conn = sqlsrv_connect( Settings::$serverName, Settings::$connectionInfo);

    //1. List members with more than 10 years of membership
    $sql = "SELECT * FROM mydb.dbo.members_tb WHERE DATEDIFF(year, m_doj, GETDATE()) > 10 and m_scheme_code = 'KE454'";
    $stmt = sqlsrv_query($conn, $sql);
    if ($stmt === false) {
        die(print_r(sqlsrv_errors(), true));
    }
    $members = [];
    while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
        $i=1;
    ?>
    
        <table>
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
                    <th>Email</th>
                </tr>
            </thead><?php
            foreach ($members as $member) {?>
                <tr>
                    <td><?php echo $i++; ?></td>
                    <td><?php echo $member['m_id']; ?></td>
                    <td><?php echo $member['m_scheme_code']; ?></td>
                    <td><?php echo $member['m_name']; ?></td>
                    <td><?php echo $member['m_email']; ?></td>
                    <td><?php echo $member['m_doj']->format('Y-m-d'); ?></td>
                    <td><?php echo $member['m_dob']->format('Y-m-d'); ?></td>
                    <td><?php echo $member['m_id_number']; ?></td>
                    <td><?php echo $member['m_phone']; ?></td>
                    <td><?php echo $member['m_email']; ?></td>
                </tr><?php
            }?>
        </table><?php
    }
    ?>
    
    