<?php

namespace ApiBundle\Api;

use ApiBundle\Model\ServiceConfiguration;
use ApiBundle\Model\ServiceRouteConfiguration;
use Doctrine\Common\Collections\ArrayCollection;
use GuzzleHttp\Client;

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
	 * @var ArrayCollection|ServiceConfiguration[]
	 */
	private $services;

	/**
	 * @param $endpoint
	 */
	public function __construct($endpoint)
	{
		$this->endpoint = $endpoint;
		$this->services = new ArrayCollection();

		// Load service from registry only once
		$this->loadServices();
	}

	/**
	 * @return \ApiBundle\Model\ServiceConfiguration[]|ArrayCollection
	 */
	public function getServices()
	{
		return $this->services;
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

		$response 			= $this->getClient()->{$method}($url, ['form_params' => $parameters]);
		$responseContent 	= $response->getBody()->getContents();

		if(!$raw) {
			$responseContent = json_decode($responseContent, true);
		}

		return $responseContent;
	}

	/**
	 * @param $serviceName
	 * @param $routeName
	 * @param array $parameters
	 * @param bool|false $raw
	 * @return mixed|string
	 * @throws \Exception
	 */
	public function callServiceMethod($serviceName, $routeName, array $parameters = [], $raw = false)
	{
		// Check service exists
		if(!$this->hasService($serviceName)) {
			throw new \Exception("Service $serviceName not found");
		}

		$serviceConfig = $this->getService($serviceName);

		// Check service has method
		if(!$serviceConfig->hasRoute($routeName)) {
			throw new \Exception("Service $serviceName doesnt have method $routeName");
		}

		$routeConfig = $serviceConfig->getRoute($routeName);
		$response 	 = $this->call($routeConfig->getUrl(), $parameters, $routeConfig->getMethod());

		if(!$raw) {
			$response = json_decode($response->getBody()->getContents(), true);
		}

		return $response;
	}

	/**
	 * @param $routeName
	 * @param array $parameters
	 * @param bool|false $raw
	 * @return mixed|string
	 * @throws \Exception
	 */
	public function callMethod($routeName, array $parameters = [], $raw = false)
	{
		// Check method exists
		if(!$this->hasRoute($routeName)) {
			throw new \Exception("Route $routeName not found in all services configuration");
		}

		$routeConfig = $this->getRoute($routeName);
		$response	 = $this->call($routeConfig->getUrl(), $parameters, $routeConfig->getMethod());

		if(!$raw) {
			$response = json_decode($response->getBody()->getContents(), true);
		}

		return $response;
	}

	/**
	 * @param $routeName
	 * @return bool
	 */
	public function hasRoute($routeName)
	{
		foreach($this->services as $service) {
			if($service->hasRoute($routeName)) {
				return true;
			}
		}

		return false;
	}

	/**
	 * @param $routeName
	 * @return ServiceRouteConfiguration|null
	 */
	public function getRoute($routeName)
	{
		foreach($this->services as $service) {
			if($service->hasRoute($routeName)) {
				return $service->getRoute($routeName);
			}
		}

		return null;
	}

	/**
	 * @param $serviceName
	 * @return bool
	 */
	public function hasService($serviceName)
	{
		foreach($this->services as $service) {
			if($service->getName() == $serviceName) {
				return true;
			}
		}

		return false;
	}

	/**
	 * @param $serviceName
	 * @return ServiceConfiguration|null
	 */
	public function getService($serviceName)
	{
		foreach($this->services as $service) {
			if($service->getName() == $serviceName) {
				return $service;
			}
		}

		return null;
	}

	/**
	 * Load services from registry
	 */
	private function loadServices()
	{
		$path 		= "/services.json";
		$url 		= $this->endpoint.$path;
		$services  	= $this->call($url);

		foreach($services as $serviceName => $serviceConfiguration) {
			if(!isset($serviceConfiguration['endpoint'])) { continue; }

			$serviceConfig = new ServiceConfiguration($serviceName, $serviceConfiguration['endpoint']);

			foreach($serviceConfiguration['routes'] as $routeName => $routeConfig) {
				$serviceRouteconfig = new ServiceRouteConfiguration($serviceConfig->getEndpoint(), $routeName, $routeConfig['path'], $routeConfig['method']);
				$serviceConfig->addRoute($serviceRouteconfig);
			}

			$this->services->add($serviceConfig);
		}
	}
}
