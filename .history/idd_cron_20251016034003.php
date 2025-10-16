<?php

    require('../commons/config/settings.php');
    require(__DIR__.'/../../macros/funcs.php');
    $conn = sqlsrv_connect( Settings::$serverName, Settings::$connectionInfo);

    //1. List members with more than 10 years of membership
    $sql = "SELECT * FROM members WHERE DATEDIFF(year, m_doj, GETDATE()) > 10 and m_scheme_code = 'KE454'";
    $stmt = sqlsrv_query($conn, $sql);
    if ($stmt === false) {
        die(print_r(sqlsrv_errors(), true));
    }
    $members = [];
    while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {?>
        <table>
            <thead>
                
                <tr>
                    <th>ID</th>
                    <th>First Name</th>
                    <th>Last Name</th>
                    <th>Email</th>
                    <th>Join Date</th>
                </tr>
            </thead><?php
            foreach ($members as $member) {?>
                <tr>
                    <td><?php echo $member['id']; ?></td>
                    <td><?php echo $member['first_name']; ?></td>
                    <td><?php echo $member['last_name']; ?></td>
                    <td><?php echo $member['email']; ?></td>
                    <td><?php echo $member['join_date']->format('Y-m-d'); ?></td>
                </tr><?php
            }?>
        </table><?php
    }
    ?>
    
    