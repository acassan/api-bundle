<?php
namespace ApiBundle\Dispatcher;

/**
 * Class DispatcherManager
 * @package ApiBundle\Dispatcher
 */
Class DispatcherManager
{
    /**
     * @var DispatcherInterface[]
     */
    private $dispatcher;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->dispatcher = [];
    }

    /**
     * @param DispatcherInterface $dispatcher
     */
    public function addDispatcher(DispatcherInterface $dispatcher)
    {
        $this->dispatcher[] = $dispatcher;
    }

    /**
     * @param $eventKey
     * @param $eventValue
     */
    public function dispatch($eventKey, $eventValue)
    {
        foreach($this->dispatcher as $dispatcher) {
            $dispatcher->dispatch($eventKey, $eventValue);
        }
    }
}
