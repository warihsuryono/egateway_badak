<?php
class App extends mysqli
{
    private $dbhost = 'localhost';
    private $dbname = 'egateway_das';
    private $dbuser = 'root';
    private $dbpassword = 'root';
    // private $serverURL = 'http://egateway-das.test/simulation.php';
    private $serverURL = 'http://192.168.31.5/egateway_api/sensor_values_post.php';
    private $apiKey = 'VHJ1c3VyVW5nZ3VsVGVrbnVzYV9wVA==';

    public function __construct()
    {
        parent::__construct($this->dbhost, $this->dbuser, $this->dbpassword, $this->dbname);
        if (mysqli_connect_error()) {
            exit('Connect Error (' . mysqli_connect_errno() . ') ' . mysqli_connect_error());
        }
        parent::set_charset('utf-8');
    }

    public function getGroupNames()
    {
        $result = $this->query("SELECT group_name FROM sensors GROUP BY group_name order by id");
        while ($row = $result->fetch_assoc()) {
            $return[] = $row["group_name"];
        }
        return $return;
    }

    public function getSensorValues($group_name = "")
    {
        try {
            if ($group_name == "") {
                $result = $this->query("SELECT group_name FROM sensors ORDER BY id LIMIT 1");
                $group_name = $result->fetch_assoc()["group_name"];
            }
            $query = "SELECT sensor_values.*,sensors.sensor_code,sensors.unit FROM sensor_values JOIN sensors ON sensor_values.sensor_id = sensors.id WHERE sensors.group_name='" . $group_name . "'";
            // $query = "SELECT sensor_values.*,sensors.sensor_code,sensors.unit FROM sensor_values JOIN sensors ON sensor_values.sensor_id = sensors.id";
            $execute = $this->query($query);
            $i = 0;
            $data = [];
            while ($row = $execute->fetch_assoc()) {
                $data[$i] = $row;
                $i++;
            }
            return $data;
        } catch (Exception $e) {
            return null;
        }
    }

    public function sensor_value_averaging()
    {
        try {
            $sql = "SELECT xtimestamp FROM sensor_value_histories WHERE is_averaged='0' ORDER BY xtimestamp LIMIT 1";
            $result = $this->query($sql);
            $xtimestamp = $result->fetch_assoc();
            if (@$xtimestamp["xtimestamp"] != "") {
                $timegroup = substr($xtimestamp["xtimestamp"], 0, 16);
                $sql = "SELECT NOW() as now";
                $result = $this->query($sql);
                $now = substr($result->fetch_assoc()["now"], 0, 16);
                if ($timegroup < $now) {
                    foreach ($this->getSensors() as $sensor) {
                        // GROUP BY DATE_FORMAT(xtimestamp,'%Y-%m-%d %H:%i:');
                        $sql = "SELECT avg(data) as data FROM sensor_value_histories WHERE sensor_id='" . $sensor["id"] . "' AND is_averaged='0' AND xtimestamp LIKE '" . $timegroup . "%'";
                        $result = $this->query($sql);
                        $data = $result->fetch_assoc()["data"];
                        $sql = "INSERT INTO sensor_value_avg (sensor_id,data,xtimestamp) VALUES ('" . $sensor["id"] . "','" . $data . "','" . $timegroup . ":00');";
                        $this->query($sql);
                        $sql = "DELETE FROM sensor_value_histories WHERE sensor_id='" . $sensor["id"] . "' AND xtimestamp LIKE '" . $timegroup . "%'";
                        $this->query($sql);
                    }
                }
            }
        } catch (Exception $e) {
            return null;
        }
    }

    public function get_sensor_value_avg($group_name, $date1, $date2)
    {
        try {
            $data = [];
            $result = $this->query("SELECT xtimestamp FROM sensor_value_avg WHERE xtimestamp BETWEEN '" . $date1 . " 00:00:00' AND '" . $date2 . " 23:59:59' GROUP BY xtimestamp ORDER BY xtimestamp LIMIT 4000");
            while ($row = $result->fetch_assoc()) {
                $xtimestamp = $row["xtimestamp"];
                $sql = "SELECT sensor_id,data FROM sensor_value_avg WHERE xtimestamp = '" . $xtimestamp . "' AND sensor_id IN (SELECT id FROM sensors WHERE group_name = '" . $group_name . "')";
                $result2 = $this->query($sql);
                while ($sensor_value_avgs = $result2->fetch_assoc()) {
                    $data[$xtimestamp][$sensor_value_avgs["sensor_id"]] = $sensor_value_avgs["data"];
                }
            }
            return $data;
        } catch (Exception $e) {
            return null;
        }
    }

    public function getSensor($id)
    {
        try {
            $id = $this->real_escape_string($id);
            $query = "SELECT * FROM sensors WHERE id = '{$id}'";
            $execute = $this->query($query);
            return $execute->fetch_assoc();
        } catch (Exception $e) {
            return null;
        }
    }
    public function getSensors($group_name = "")
    {
        try {
            if ($group_name == "")
                $query = "SELECT * FROM sensors ORDER BY id DESC";
            else
                $query = "SELECT * FROM sensors WHERE group_name = '" . $group_name . "'";
            $execute = $this->query($query);
            $i = 0;
            $data = [];
            while ($row = $execute->fetch_assoc()) {
                $data[$i] = $row;
                $i++;
            }
            return $data;
        } catch (Exception $e) {
            return null;
        }
    }

    public function createSensor(array $data)
    {
        try {
            if (empty($data['formula'])) {
                return false;
            }
            $sensorCode = $this->real_escape_string($data['sensor_code']);
            $unit = $this->real_escape_string($data['unit']);
            $formula = $this->real_escape_string($data['formula']);
            $query = "INSERT INTO sensors (sensor_code,unit,formula) VALUES ('{$sensorCode}','{$unit}','{$formula}')";
            if ($this->query($query)) {
                return true;
            }
        } catch (Exception $e) {
            echo $e->getMessage();
        }
        return false;
    }

    public function updateSensor(array $data)
    {
        try {
            if (empty($data['id'])) {
                return false;
            }
            $id = $data['id'];
            $sensorCode = $this->real_escape_string($data['sensor_code']);
            $unit = $this->real_escape_string($data['unit']);
            $formula = $this->real_escape_string($data['formula']);
            $query = "UPDATE sensors SET sensor_code = '{$sensorCode}', unit = '{$unit}', formula = '{$formula}' WHERE id = '{$id}'";
            if ($this->query($query)) {
                return true;
            }
        } catch (Exception $e) {
            echo $e->getMessage();
        }
        return false;
    }

    public function getConfigs()
    {
        try {
            $query = "SELECT * FROM configurations WHERE id=1";
            $execute = $this->query($query);
            return $execute->fetch_assoc();
        } catch (Exception $e) {
            return null;
        }
    }

    public function updateConfig(array $data)
    {
        try {
            $serverIp = $this->real_escape_string($_POST['server_ip']);
            $analyzerIp = $this->real_escape_string($_POST['analyzer_ip']);
            $analyzerPort = $this->real_escape_string($_POST['analyzer_port']);
            $unitId = $this->real_escape_string($_POST['unit_id']);
            $startAddr = $this->real_escape_string($_POST['start_addr']);
            $addrNum = $this->real_escape_string($_POST['addr_num']);
            $query = "UPDATE configurations SET server_ip = '{$serverIp}', analyzer_ip = '{$analyzerIp}',analyzer_port = '{$analyzerPort}', unit_id = '{$unitId}', start_addr = '{$startAddr}', addr_num = '{$addrNum}' WHERE id = 1";
            if ($this->query($query)) {
                return true;
            }
        } catch (Exception $e) {
        }
        return false;
    }

    /**
     * function sender data analyzer to server
     *
     * @param [string] $json
     * @return json
     */
    public function sendData($json)
    {
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => $this->serverURL,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => $json,
            CURLOPT_HTTPHEADER => array(
                "Api-Key: {$this->apiKey}",
                "Content-Type: text/plain"
            ),
        ));
        $response = curl_exec($curl);
        curl_close($curl);
        $responseArray = json_decode($response, 1);
        return $responseArray;
    }

    /**
     * Function generate data random for demo
     *
     * @return void
     */
    public function generateData()
    {
        $query = "SELECT id FROM sensor_values";
        $execute = $this->query($query);
        $i = 0;
        $data = [];
        while ($row = $execute->fetch_assoc()) {
            $data[$i] = $row;
            $i++;
        }
        while (true) {
            foreach ($data as $value) {
                $randomNumber = rand(50, 500) . "." . rand(10, 50);
                $randomNumber = (float) $randomNumber;
                $updateQuery = "UPDATE sensor_values SET data = '{$randomNumber}' WHERE id = '{$value['id']}'";
                if ($this->query($updateQuery)) {
                    echo "{$value['id']} Updated! " . PHP_EOL;
                } else {
                    echo "{$value['id']} Something when wrong! " . $this->error . PHP_EOL;
                }
            }
            sleep(1);
        }
    }
}
