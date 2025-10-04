<?php
require('../commons/config/settings.php');
require('./menu.php');
require(__DIR__.'/../../macros/funcs.php');
$conn = sqlsrv_connect( Settings::$serverName, Settings::$connectionInfo);

if (isset($_GET['period_id'])) {
    $period_id = intval($_GET['period_id']);

    $sql = "SELECT sub_period_id, sub_period_name 
            FROM scheme_sub_periods_tb 
            WHERE scheme_period_id = ? 
            ORDER BY sub_period_end_date DESC";
    $params = [$period_id];
    $stmt = sqlsrv_query($conn, $sql, $params, ["Scrollable" => "buffered", "ReturnDatesAsStrings" => true]);

    if ($stmt === false) {
        http_response_code(500);
        echo json_encode(["error" => sqlsrv_errors()]);
        exit;
    }

    $results = [];
    while($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
        $results[] = $row;
    }

    header('Content-Type: application/json');
    echo json_encode($results);
}
?>
