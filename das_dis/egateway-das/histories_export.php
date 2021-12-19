<?php
header("Content-Type: application/xls");

$date1 = (isset($_GET["date1"])) ? $_GET["date1"] : date("Y-m-d");
$date2 = (isset($_GET["date2"])) ? $_GET["date2"] : date("Y-m-d");
header("Content-Disposition: attachment; filename=History Data " . $date1 . " " . $date2 . ".xls");
header("Pragma: no-cache");
header("Expires: 0");
include_once 'app.php';
$app = new App();
$groupNames = $app->getGroupNames();
if (!isset($_GET["group_name"])) echo "<script> window.location='?group_name=" . $groupNames[0] . "';</script>";
$group_name = @$_GET["group_name"];
$sensors = $app->getSensors($group_name);
$sensorValues = $app->getSensorValues((isset($group_name)) ? $group_name : "");
$sensor_value_avgs = $app->get_sensor_value_avg($group_name, $date1, $date2);
?>
<!DOCTYPE html>
<html lang="en">

<body>
    <h2>History Data</h2>
    <table border="1">
        <tr>
            <th scope="col">Datetime</th>
            <?php foreach ($sensors as $sensor) : ?>
                <th scope="col"><?= $sensor["sensor_code"]; ?> (<?= $sensor["unit"]; ?>)</th>
            <?php endforeach; ?>
        </tr>
        <?php foreach ($sensor_value_avgs as $xtimestamp => $sensor_value_avg) : ?>
            <tr>
                <td nowrap><?= $xtimestamp; ?></td>
                <?php foreach ($sensors as $sensor) : ?>
                    <td><?= round($sensor_value_avg[$sensor["id"]], 3); ?></td>
                <?php endforeach; ?>
            </tr>
        <?php endforeach; ?>
    </table>
</body>

</html>