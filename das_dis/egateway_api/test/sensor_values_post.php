<?php
	$sispek_server = "https://warih.moc/egateway_api/";
	$sispek_server = "http://192.168.1.16/egateway_api/";
	
	function sensor_values_post($data){
		global $sispek_server;
		$url = "sensor_values_post.php";
		$data = json_encode($data);
		$curl = curl_init();
			curl_setopt_array($curl, array(
			  CURLOPT_URL => $sispek_server . $url,
			  CURLOPT_RETURNTRANSFER => true,
			  CURLOPT_ENCODING => "",
			  CURLOPT_SSL_VERIFYHOST => 0,
			  CURLOPT_SSL_VERIFYPEER => 0,
			  CURLOPT_MAXREDIRS => 10,
			  CURLOPT_TIMEOUT => 30,
			  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
			  CURLOPT_CUSTOMREQUEST => "POST",
			  CURLOPT_POSTFIELDS => $data,
			  CURLOPT_HTTPHEADER => array(
				"Authorization: Bearer 87897678687gvu8788",
				"Api-Key: VHJ1c3VyVW5nZ3VsVGVrbnVzYV9wVA==",
				"cache-control: no-cache",
				"content-type: application/json"
			  ),
			));

		return curl_exec($curl);
	}
	
	$data = ["stack_id" => 1,
		"waktu" => date("Y-m-d H:i:s"),
		"sensor1" => 200,
		"sensor2" => 150,
		"sensor3" => 350,
		"sensor4" => 250,
		"sensor5" => 400,
		];
	echo $response = sensor_values_post($data);
?>