<?php
namespace ApiBundle\Model;

/**
 * Class ServiceRouteConfiguration
 * @package ApiBundle\Model
 */
Final Class ServiceRouteConfiguration
{
    /**
     * @var string
     */
    private $endpoint;

    /**
     * Name of the route
     * @var string
     */
    private $name;

    /**
     * Path of route
     * @var string
     */
    private $path;

    /**
     * Method allowed for the route
     * @var string
     */
    private $method;

    /**
     * @param $endpoint
     * @param $name
     * @param $path
     * @param $method
     */
    public function __construct($endpoint, $name, $path, $method)
    {
        $this->endpoint = $endpoint;
        $this->name     = $name;
        $this->path     = $path;
        $this->method   = $method;
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
    public function getPath()
    {
        return $this->path;
    }

    /**
     * @param string $path
     */
    public function setPath($path)
    {
        $this->path = $path;
    }

    /**
     * @return string
     */
    public function getMethod()
    {
        return $this->method;
    }

    /**
     * @param string $method
     */
    public function setMethod($method)
    {
        $this->method = $method;
    }

    /**
     * @return string
     */
    public function getUrl()
    {
        return sprintf("%s%s", $this->endpoint, $this->path);
    }
}
