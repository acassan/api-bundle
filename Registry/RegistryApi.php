<?php

namespace ApiBundle\Registry;

use ApiBundle\Registry\Model\ServiceConfiguration;
use ApiBundle\Registry\Model\ServiceRouteConfiguration;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Psr\Http\Message\ResponseInterface;

/**
 * Class Api
 * @package ApiBundle\Api
 */
Class RegistryApi
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
	 * @var ServiceConfiguration[]|\SplObjectStorage
	 */
	private $services;

	/**
	 * @param $endpoint
	 */
	public function __construct($endpoint)
	{
		$this->endpoint = $endpoint;
		$this->services = new \SplObjectStorage();
		$this->client 	= new Client();

		// Load services if endpoint
		if(!is_null($this->endpoint)) {
			$this->loadServices();
		}
	}

	/**
	 * Load services from registry
	 */
	private function loadServices()
	{
		$response 	= $this->call(sprintf("%s/%s", $this->endpoint, "services.json"));
		$services  	= $this->getResponseContent($response);

		foreach($services as $serviceName => $serviceConfiguration) {
			if(!isset($serviceConfiguration['endpoint'])) { continue; }

			$serviceConfig = new ServiceConfiguration($serviceName, $serviceConfiguration['endpoint']);

			foreach($serviceConfiguration['routes'] as $routeName => $routeConfig) {
				$serviceRouteconfig = new ServiceRouteConfiguration($serviceConfig->getEndpoint(), $routeName, $routeConfig['path'], $routeConfig['method']);
				$serviceConfig->addRoute($serviceRouteconfig);
			}

			$this->services->attach($serviceConfig);
		}
	}

	/**
	 * @param ResponseInterface $response
	 * @return mixed
	 */
	public function getResponseContent(ResponseInterface $response)
	{
		return json_decode($response->getBody()->getContents(), true);
	}

	/**
	 * @param $name
	 * @param $path
	 * @param array $parameters
	 * @param string $method
	 * @return ResponseInterface
	 * @throws \Exception
	 */
	public function callService($name, $path, $parameters = [], $method = "GET")
	{
		// Check service exists
		if(!$this->hasService($name)) {
			throw new \Exception("Service $name not found");
		}

		$ServiceConfig = $this->getService($name);
		$url = sprintf("%s%s", $ServiceConfig->getEndpoint(), $path);

		return $this->call($url, $parameters, $method);
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
	 * @return Model\ServiceConfiguration[]|\SplObjectStorage
	 */
	public function getServices()
	{
		return $this->services;
	}

	/**
	 * @param $url
	 * @param array $parameters
	 * @param string $method
	 * @return ResponseInterface
	 * @throws \Exception
	 */
	public function call($url, array $parameters = [], $method = 'GET')
	{
		// Replace parameters in URL
		if(preg_match_all("/\{([a-z0-9]+)\}/i", $url, $matches)) {
			// Remove first element of matches
			array_shift($matches);
			$matches = current($matches);

			// Replace by value
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
						if(is_array($paramValue)) {
							foreach($paramValue as $paramKey => $paramVal) {
								$urlParameters[] = sprintf("%s=%s", $paramKey, urlencode($paramVal));
							}
						}
						elseif(is_string($paramValue)) {
							$urlParameters[] = sprintf("%s=%s", $paramName, urlencode($paramValue));
						}
						else {
							throw new \Exception(sprintf("Invalid param type '%s'", gettype($paramValue)));
						}

					}
				}

				$url .= '?'.implode('&', $urlParameters);
				$parameters = [];
				break;
		}

		try {
			/** @var ResponseInterface $response */
			$response = $this->client->{$method}($url, ['json' => $parameters]);
		} catch (RequestException $e) {
			$response = $e->getResponse();
		}

		return $response;
	}
}
