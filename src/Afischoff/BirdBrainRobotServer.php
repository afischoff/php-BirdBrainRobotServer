<?php
namespace Afischoff;

/**
 * Class BirdBrainRobotServer
 * @package Afischoff
 */
class BirdBrainRobotServer
{
	const SERVER_DEFAULT_URL = "http://localhost";
	const SERVER_DEFAULT_PORT = 22179;
	const DEVICE_FINCH = "finch";

	private $serverUrl;
	private $serverPort;
	private $connectedDevice;
	private $isConnected = false;
	private $debugMode = false;

	/**
	 * @param String $serverUrl
	 * @param int $serverPort
	 */
	public function __construct($serverUrl = null, $serverPort = null)
	{
		$this->serverUrl = $serverUrl;
		$this->serverPort = $serverPort;

		if (!$this->serverUrl) {
			$this->serverUrl = self::SERVER_DEFAULT_URL;
		}

		if (!$this->serverPort) {
			$this->serverPort = self::SERVER_DEFAULT_PORT;
		}

		$this->connectToServer();
	}

	/****************************
	 *  API
	 ****************************/

	public function isConnected()
	{
		return $this->isConnected;
	}

	public function setMotor($left = 0, $right = 0)
	{
		return $this->doRequest("out/motor/".$left."/".$right);
	}

	public function setBuzzer($frequency = 1000, $duration = 1000)
	{
		return $this->doRequest("out/buzzer/".$frequency."/".$duration);
	}

	public function setLed($red = 0, $green = 0, $blue = 0)
	{
		return $this->doRequest("out/led/".$red."/".$green."/".$blue);
	}

	public function getLights()
	{
		return explode(" ", $this->doRequest("in/lights"));
	}

	public function getObstacles()
	{
		return explode(" ", $this->doRequest("in/obstacles"));
	}

	public function getAccelerations()
	{
		return explode(" ", $this->doRequest("in/accelerations"));
	}

	public function getTemperature()
	{
		return $this->doRequest("in/temperature");
	}

	public function speak($msg)
	{
		$msgEncoded = urlencode($msg);
		return $this->doRequest('speak/'.$msgEncoded);
	}

	public function setDebugMode($onOff)
	{
		$this->debugMode = (bool)$onOff;
	}

	/****************************
	 *  Internals
	 ****************************/

	private function getServerUrl()
	{
		$output = $this->serverUrl . ":" . $this->serverPort . "/";

		if ($this->connectedDevice == self::DEVICE_FINCH) {
			$output .= self::DEVICE_FINCH . "/";
		}

		return $output;
	}

	private function doRequest($params = null)
	{
		$url = $this->getServerUrl();

		if (isset($params)) {
			$url .= $params;
		}

		if (strpos($params, "speak/") !== false) {
			$url = str_replace("/finch/", "/", $url);
		}

		if ($this->debugMode) {
			echo "Requesting: {$url}\n";
		}

		return file_get_contents($url);
	}

	private function connectToServer()
	{
		$resp = $this->doRequest();
		if (!$resp) {
			$this->isConnected = false;
			$this->connectedDevice = null;

		} else {
			$this->isConnected = true;
			$this->connectedDevice = self::DEVICE_FINCH;
		}
	}
}
