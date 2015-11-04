<?php

namespace ApiBundle\Controller;

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
	 *
	 * @param Request $request
	 * @return JsonResponse
	 */
    public function entryAction(Request $request)
    {
		if(!$request->request->has('eventKey')) {
			throw new InvalidArgumentException("Missing eventKey parameter");
		}

		if(!$request->request->has('eventValue')) {
			throw new InvalidArgumentException("Missing eventValue parameter");
		}

		$this->get('api.dispatcher')->dispatch($request->get('eventKey'), $request->get('eventValue'));

		return new JsonResponse();
    }
}
