<?php
namespace ApiBundle\Registry\Model;

/**
 * Class ServiceConfiguration
 * @package ApiBundle\Model
 */
Final Class ServiceConfiguration
{
    /**
     * Service name
     * @var string
     */
    private $name;

    /**
     * Endpoint of service
     * @var string
     */
    private $endpoint;

    /**
     * @var \SplObjectStorage()|ServiceRouteConfiguration[]
     */
    private $routes;

    /**
     * @param $name
     * @param $endpoint
     */
    public function __construct($name, $endpoint)
    {
        $this->name     = $name;
        $this->endpoint = $endpoint;
        $this->routes   = new \SplObjectStorage();
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * @return string
     */
    public function getEndpoint()
    {
        return $this->endpoint;
    }

    /**
     * @param string $endpoint
     */
    public function setEndpoint($endpoint)
    {
        $this->endpoint = $endpoint;
    }

    /**
     * @param $routeName
     * @return bool
     */
    public function hasRoute($routeName)
    {
        foreach($this->routes as $serviceRoute) {
            if($serviceRoute->getName() == $routeName) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param $routeName
     * @return ServiceRouteConfiguration|mixed|null
     */
    public function getRoute($routeName)
    {
        foreach($this->routes as $serviceRoute) {
            if($serviceRoute->getName() == $routeName) {
                return $serviceRoute;
            }
        }

        return null;
    }

    /**
     * @return \SplObjectStorage()
     */
    public function getRoutes()
    {
        return $this->routes;
    }

    /**
     * @param ServiceRouteConfiguration $routeConfiguration
     * @return $this
     */
    public function addRoute(ServiceRouteConfiguration $routeConfiguration)
    {
        $this->routes->attach($routeConfiguration);

        return $this;
    }

    /**
     * @param ServiceRouteConfiguration $routeConfiguration
     * @return $this
     */
    public function removeRoute(ServiceRouteConfiguration $routeConfiguration)
    {
        $this->routes->detach($routeConfiguration);

        return $this;
    }
}
