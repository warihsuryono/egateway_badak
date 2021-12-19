<?php

namespace App\Commands;

use App\Models\m_configuration;
use App\Models\m_measurement;
use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use App\Models\m_system_check;

class Sentdata extends BaseCommand
{
	protected $measurements;
	protected $configurations;
	protected $system_checks;

	public function __construct()
	{
		$this->measurements = new m_measurement();
		$this->configurations = new m_configuration();
		$this->system_checks = new m_system_check();
	}

	/**
	 * The Command's Group
	 *
	 * @var string
	 */
	protected $group = 'CodeIgniter';

	/**
	 * The Command's Name
	 *
	 * @var string
	 */
	protected $name = 'command:sentdata';

	/**
	 * The Command's Description
	 *
	 * @var string
	 */
	protected $description = '';

	/**
	 * The Command's Usage
	 *
	 * @var string
	 */
	protected $usage = 'command:name [arguments] [options]';

	/**
	 * The Command's Arguments
	 *
	 * @var array
	 */
	protected $arguments = [];

	/**
	 * The Command's Options
	 *
	 * @var array
	 */
	protected $options = [];

	/**
	 * Actually execute a command.
	 *
	 * @param array $params
	 */
	public function run(array $params)
	{
		$system_name = "sentdata";
		$system_checks_id = @$this->system_checks->where(["system" => $system_name])->findAll()[0]->id * 1;
		if ($system_checks_id <= 0) {
			$this->system_checks->save(["system" => $system_name, "status" => "1"]);
			$system_checks_id = $this->system_checks->insertID();
		} else
			$this->system_checks->update($system_checks_id, ["status" => "1"]);

		$is_looping = 1;

		while ($is_looping) {
			//CURL
			$measurementData = @$this->measurements->where(["is_sent_cloud" => 0])->orderBy("id DESC")->findAll(15);
			$cnfig = @$this->configurations->findAll()[0];

			if (!empty($measurementData)) {
				$token = "";
				$client =
					[
						'keys'         => 'UkFQUDE2MTk1MTQyMjE=',
						'email'     => 'rapp@trusur.com',
						'password'     => '4p&)z6)JNuLTeJ3'
					];
				$cEncode = http_build_query($client);

				/*$cLogin = curl_init();

			curl_setopt_array($cLogin, array(
				CURLOPT_URL => 'https://ispumaps.id/egateway_server/public/api/auth/clientlogin',
				CURLOPT_RETURNTRANSFER => true,
				CURLOPT_ENCODING => '',
				CURLOPT_MAXREDIRS => 10,
				CURLOPT_TIMEOUT => 0,
				CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
				CURLOPT_CUSTOMREQUEST => 'POST',
				CURLOPT_POSTFIELDS => $cEncode,
				CURLOPT_HTTPHEADER => array(
					'Content-Type: application/x-www-form-urlencoded'
				),
			));

			$responseLogin = curl_exec($cLogin);

			curl_close($cLogin);
			$resultLogin = json_decode($responseLogin, true);

			$token = @$resultLogin['token'];*/
				$token = "Tokenkbncvnmbvklnmom";

				if ($token != "") {
					foreach ($measurementData as $mData) {
						$data =
							[
								'egateway_code' => $cnfig->egateway_code,
								'measured_at' => $mData->measured_at,
								'client_parameter_id' => $mData->parameter_id,
								'value' => ($mData->value_correction == 0) ? $mData->value : $mData->value_correction,
								'unit_id' => $mData->unit_id,
								'is_sent' => $mData->is_sent_klhk,
								'sent_type' => $mData->sent_klhk_type,
								'sent_by' => $mData->sent_klhk_by,
								'sent_at' => $mData->sent_klhk_at,
								'sent_tries' => $mData->sent_klhk_tries,
							];

						$dEncode = http_build_query($data);

						$curl = curl_init();

						curl_setopt_array($curl, array(
							CURLOPT_URL => 'https://ispumaps.id/egateway_server/public/api/send/measurement',
							CURLOPT_RETURNTRANSFER => true,
							CURLOPT_ENCODING => '',
							CURLOPT_TIMEOUT => 3,
							CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
							CURLOPT_CUSTOMREQUEST => 'POST',
							CURLOPT_POSTFIELDS => $dEncode,
							CURLOPT_HTTPHEADER => array(
								'Content-Type: application/x-www-form-urlencoded',
							),
						));

						echo $response = curl_exec($curl);

						curl_close($curl);
						$result = json_decode($response, true);
						print_r($response);

						if (@$result['status'] == 200) {
							$this->measurements->update($mData->id, ['is_sent_cloud' => 1, 'sent_cloud_at' => date('Y-m-d H:i:s'), 'sent_cloud_by' => 'system']);
						}
					}
				}
			}
			sleep(10);
			$is_looping = @$this->system_checks->where(["system" => $system_name])->findAll()[0]->status;
		}
	}
}
