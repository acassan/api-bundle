<?php

namespace ApiBundle\Api;

use GuzzleHttp\Client;
use Symfony\Component\Routing\Exception\MethodNotAllowedException;

/**
 * Class Api
 * @package ApiBundle\Api
 */
Class Api
{
	/**
	 * @var string
	 */
	private $endpoint;

	/**
	 * @var Client
	 */
	private $client;

	/**
	 * @var array
	 */
	private $services;

	/**
	 * @var array
	 */
	private $servicesMethods;

	/**
	 * @param $endpoint
	 */
	public function __construct($endpoint)
	{
		$this->endpoint = $endpoint;
	}

	/**
	 * @return mixed|string
	 * @throws \Exception
	 */
	public function getServices()
	{
		$path 		= "/services.json";
		$url 		= $this->endpoint.$path;
		$services  	= $this->call($url);

		return $services;
	}

	/**
	 * @return Client
	 */
	private function getClient()
	{
		if(is_null($this->client)) {
			$this->client = new Client();
		}

		return $this->client;
	}

	/**
	 * @param        $url
	 * @param array  $parameters
	 * @param string $method
	 * @param bool   $raw
	 * @return mixed|string
	 * @throws \Exception
	 */
	public function call($url, array $parameters = [], $method = 'get', $raw = false)
	{
		$allowedMethods = ['get','post','put','head','patch'];

		if(!in_array($method, $allowedMethods)) {
			throw new MethodNotAllowedException("Method '$method' not allowed ('".implode(',', $allowedMethods)."')");
		}

		if(preg_match("/\{([a-z0-9]+)\}/i", $url, $matches)) {

			// Remove first element of matches
			array_shift($matches);

			// Replace parameters in url
			foreach($matches as $urlParameterName) {
				if(!isset($parameters[$urlParameterName])) {
					throw new \Exception("Parameter '$urlParameterName' not found for url $url");
				}
				$url = str_replace(sprintf("{%s}", $urlParameterName), $parameters[$urlParameterName], $url);
			}
		}

		switch($method) {
			case 'get':
			case 'put':
				$urlParameters = [];
				foreach($parameters as $paramName => $paramValue) { $urlParameters[] = sprintf("%s=%s", $paramName, urlencode($paramValue)); }
				$url .= '?'.implode('&', $urlParameters);
				$parameters = [];
				break;
		}

		$response 			= $this->getClient()->{$method}($url, ['form_params' => $parameters]);
		$responseContent 	= $response->getBody()->getContents();

		if(!$raw) {
			$responseContent = json_decode($responseContent);
		}

		return $responseContent;
	}

	/**
	 * @param       $routeName
	 * @param array $parameters
	 * @return string
	 * @throws \Exception
	 */
	public function callMethod($routeName, array $parameters = [])
	{
		if(is_null($this->services)) {
			$this->loadServices();
		}

		// Check method exists
		if(!isset($this->servicesMethods[$routeName])) {
			throw new \Exception("Route $routeName not found in all services configuration");
		}

		$method = $this->findMethodInRoute($routeName);

		return $this->call($this->servicesMethods[$routeName], $parameters, $method);
	}

	/**
	 * @param $routeName
	 * @return mixed
	 */
	private function findMethodInRoute($routeName)
	{
		$routeExploded = explode("_", $routeName);

		return $routeExploded[0];
	}

	/**
	 * Load services from dispatcher
	 */
	private function loadServices()
	{
		$this->services = [];

		foreach($this->getServices()->services as $serviceName => $serviceConfiguration) {
			if(!isset($serviceConfiguration->endpoint)) { continue; }

			$this->services[$serviceName] = $serviceConfiguration;

			foreach($serviceConfiguration->routes as $routeName => $routePath) {
				$this->servicesMethods[$routeName] = $serviceConfiguration->endpoint.$routePath;
			}
		}
	}
}