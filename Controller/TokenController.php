<?php

/*
 * This file is part of the FOSOAuthServerBundle package.
 *
 * (c) FriendsOfSymfony <http://friendsofsymfony.github.com/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FOS\OAuthServerBundle\Controller;

use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use OAuth2\OAuth2;
use OAuth2\OAuth2ServerException;

class TokenController implements ContainerAwareInterface
{
    /**
     * @var OAuth2
     */
    protected $server;
	
	/**
	 * @var ContainerInterface
	 */
	protected $container;
	
	/**
	 * Sets the container.
	 *
	 * @param ContainerInterface|null $container A ContainerInterface instance or null
	 */
	public function setContainer(ContainerInterface $container = null)
	{
		$this->container = $container;
	}

	
	/**
     * @param OAuth2 $server
     */
    public function __construct(OAuth2 $server)
    {
        $this->server = $server;
    }

    /**
     * @param Request $request
     *
     * @return Response
     */
    public function tokenAction(Request $request)
    {
    	$this->autoDetectLocale($request);
        try {
            return $this->server->grantAccessToken($request);
        } catch (OAuth2ServerException $e) {
            return $e->getHttpResponse();
        }
    }
	
	// helpers
	
	protected function autoDetectLocale(Request $request) {
		$request->setLocale($request->getPreferredLanguage(['de', 'en']));
		$this->container->get('translator')->setLocale($request->getLocale());
		$this->container->get('router')->getContext()->setParameter('_locale', $request->getLocale());
	}
}
