<?php
namespace ApiBundle\Dispatcher;

/**
 * Interface DispatcherInterface
 * @package ApiBundle\Dispatcher
 */
Interface DispatcherInterface
{
    /**
     * @param $eventKey
     * @param $eventValue
     * @return mixed
     */
    public function dispatch($eventKey, $eventValue);
}
