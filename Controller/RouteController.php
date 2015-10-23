<?php

namespace ApiBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * Class RouteController
 * @package AppBundle\Controller
 * @Route("/api/config/route")
 */
class RouteController extends Controller
{
    /**
     * @Route("/", name="api_config_routes")
	 * @Method("GET")
     */
    public function indexAction()
    {
		$router = $this->get('router');
		$routes	= [];

		foreach($router->getRouteCollection()->all() as $routeName => $params) {
			if(strpos($routeName, 'api') !== false) {
				$routes[$routeName] = [
					'name'		=> $routeName,
					'path' 		=> $params->getPath(),
					'method'	=> current($params->getMethods()),
				];
			}
		}

		return new JsonResponse($routes);
    }
}
