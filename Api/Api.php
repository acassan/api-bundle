<?php

namespace ApiBundle\Api;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Psr\Http\Message\ResponseInterface;
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

		// TO REMOVE
		$this->loadServices();
	}

	/**
	 * @return mixed|string
	 * @throws \Exception
	 */
	public function getServices()
	{
		$path 		= "/services.json";
		$url 		= $this->endpoint.$path;
		$response  	= $this->call($url);
		$services 	= json_decode($response->getBody()->getContents(), true);

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
	 * @param $url
	 * @param array $parameters
	 * @param string $method
	 * @param bool|false $raw
	 * @return null|\Psr\Http\Message\ResponseInterface
	 * @throws \Exception
	 */
	public function call($url, array $parameters = [], $method = 'GET', $raw = false)
	{
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
			case 'GET':
			case 'PUT':
				$urlParameters = [];
				foreach($parameters as $paramName => $paramValue) {
					if(!is_null($paramValue)) {
						$urlParameters[] = sprintf("%s=%s", $paramName, urlencode($paramValue));
					}
				}
				$url .= '?'.implode('&', $urlParameters);
				$parameters = [];
				break;
		}

		// Try ... Catch block to handle response code 400 set in error by guzzle
		try {
			$response 			= $this->getClient()->{$method}($url, ['json' 	=> $parameters]);
		}
		catch( RequestException $e) {

			// Catch exactly error 400 use
			if ($e->getResponse()->getStatusCode() == '400') {
				$response = $e->getResponse();
			}
			else {
				throw $e;
			}
		}

		return $response;
	}

	/**
	 * @param $routeName
	 * @param array $parameters
	 * @return null|\Psr\Http\Message\ResponseInterface
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

		return $this->call($this->servicesMethods[$routeName]['url'], $parameters, $this->servicesMethods[$routeName]['method']);
	}

	/**
	 * @param ResponseInterface $response
	 * @return array
	 */
	public function decodeJsonResponseBody(ResponseInterface $response)
	{
		return json_decode($response->getBody()->getContents(), true);
	}

	/**
	 * Load services from dispatcher
	 */
	private function loadServices()
	{
		$this->services = [];

		foreach($this->getServices() as $serviceName => $serviceConfiguration) {
			if(!isset($serviceConfiguration['endpoint'])) { continue; }

			$this->services[$serviceName] = $serviceConfiguration;

			foreach($serviceConfiguration['routes'] as $routeName => $routeConfig) {
				$routeConfig['url'] = $serviceConfiguration['endpoint'].$routeConfig['path'];
				$this->servicesMethods[$routeName] = $routeConfig;
			}
		}
	}
}