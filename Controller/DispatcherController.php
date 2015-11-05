<?php

namespace ApiBundle\Controller;

use ApiBundle\Dispatcher\DispatcherEntryEvent;
use ApiBundle\Dispatcher\DispatcherEventName;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Exception\InvalidArgumentException;

/**
 * Class DispatcherController
 * @package AppBundle\Controller
 * @Route("/api/dispatcher")
 */
class DispatcherController extends Controller
{
	/**
	 * @Route("/entry", name="api_dispatcher_entry")
	 * @Method("POST")
	 *
	 * @param Request $request
	 * @return JsonResponse
	 */
    public function entryAction(Request $request)
    {
		if(!$request->request->has('eventkey')) {
			throw new InvalidArgumentException("Missing eventKey parameter");
		}

		if(!$request->request->has('eventvalue')) {
			throw new InvalidArgumentException("Missing eventValue parameter");
		}

		$eventkey 	= $request->get('eventKey');
		$eventvalue = $request->get('eventValue');

		$this->get('event_dispatcher')->dispatch(DispatcherEventName::API_DISPATCHER_ENTRY, new DispatcherEntryEvent($eventkey, $eventvalue));

		return new JsonResponse();
    }
}
