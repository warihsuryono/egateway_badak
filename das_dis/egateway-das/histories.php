<?php
include_once 'app.php';
$app = new App();
$groupNames = $app->getGroupNames();
if (!isset($_GET["group_name"])) echo "<script> window.location='?group_name=" . $groupNames[0] . "';</script>";
$group_name = @$_GET["group_name"];
$sensors = $app->getSensors($group_name);
$sensorValues = $app->getSensorValues((isset($group_name)) ? $group_name : "");
$date1 = (isset($_GET["date1"])) ? $_GET["date1"] : date("Y-m-d");
$date2 = (isset($_GET["date2"])) ? $_GET["date2"] : date("Y-m-d");
$sensor_value_avgs = $app->get_sensor_value_avg($group_name, $date1, $date2);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- Favicons -->
    <link href="assets/logo-trudas.png" rel="icon">
    <title>TRUDAS</title>
    <link href="assets/css/bootstrap.min.css" rel="stylesheet">

</head>

<body>
    <?php include 'assets/navbar.php'; ?>
    <div class="container mt-5">
        <h1 class="h2 text-dark border-bottom border-3 pr-3 pb-3 border-info d-inline-block">History Data</h1>
        <div class="row">
            <div class="col-md-4">
                <ul class="nav nav-pills">
                    <?php foreach ($groupNames as $groupName) : ?>
                        <li class="nav-item">
                            <a class="nav-link <?= ($group_name == $groupName) ? "active" : ""; ?>" aria-current="page" href="?group_name=<?= $groupName; ?>"><?= $groupName; ?></a>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div>
        <br>
        <div class="row">
            <div class="col-md-6">
                <div class="input-group">
                    <input type="date" name="date1" id="filterdate1" value="<?= $date1; ?>" class="form-control">&nbsp;&nbsp;&nbsp;-&nbsp;&nbsp;&nbsp;
                    <input type="date" name="date2" id="filterdate2" value="<?= $date2; ?>" class="form-control">
                    <span class="input-group-btn">
                        <button type="button" class="btn btn-primary btn-flat" onclick="loading()">Load</button>
                        <button type="button" class="btn btn-success btn-flat" onclick="exporting()">Export</button>
                    </span>
                </div>
            </div>
        </div>
        <script>
            function loading() {
                window.location = "?group_name=<?= $_GET["group_name"]; ?>&date1=" + $("#filterdate1").val() + "&date2=" + $("#filterdate2").val();
            }

            function exporting() {
                window.open("histories_export.php?group_name=<?= $_GET["group_name"]; ?>&date1=" + $("#filterdate1").val() + "&date2=" + $("#filterdate2").val(), "_BLANK");
            }
        </script>
        <br>
        <div class="row">
            <table class="table">
                <thead>
                    <tr>
                        <th scope="col">Datetime</th>
                        <?php foreach ($sensors as $sensor) : ?>
                            <th scope="col"><?= $sensor["sensor_code"]; ?> (<?= $sensor["unit"]; ?>)</th>
                        <?php endforeach; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($sensor_value_avgs as $xtimestamp => $sensor_value_avg) : ?>
                        <tr>
                            <td nowrap><?= $xtimestamp; ?></td>
                            <?php foreach ($sensors as $sensor) : ?>
                                <td><?= round($sensor_value_avg[$sensor["id"]], 3); ?></td>
                            <?php endforeach; ?>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
    <script src="assets/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/jquery-3.6.0.min.js"></script>
</body>

</html>