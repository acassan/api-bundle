<?php
namespace ApiBundle\Dispatcher;

use Symfony\Component\EventDispatcher\Event;

/**
 * Class DispatcherEntryEvent
 * @package ApiBundle\Dispatcher
 */
Class DispatcherEntryEvent extends Event
{
    /**
     * @var
     */
    private $eventkey;

    /**
     * @var string
     */
    private $eventvalue;

    /**
     * @param $eventkey
     * @param $eventvalue
     */
    public function __construct($eventkey, $eventvalue)
    {
        $this->eventkey     = $eventkey;
        $this->eventvalue   = $eventvalue;
    }

    /**
     * @return mixed
     */
    public function getEventkey()
    {
        return $this->eventkey;
    }

    /**
     * @return string
     */
    public function getEventvalue()
    {
        return $this->eventvalue;
    }
}
