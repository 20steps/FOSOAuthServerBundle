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

use Monolog\Logger;

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
	
	    if ($request->request->get('grant_type')=='urn:ietf:params:oauth:grant-type:jwt-bearer') {
		    // spoof client id
		    $request->request->set('client_id',$this->container->getParameter('bricks_custom_twentysteps_alexa_account_linking_google_oauth2_client_id'));
		    $request->request->set('client_secret',$this->container->getParameter('bricks_custom_twentysteps_alexa_login_google_oauth2_client_secret'));
    	}
        try {
            $response = $this->server->grantAccessToken($request);
            $this->getLogger()->debug('token response: '.$response->getContent());
            return $response;
        } catch (OAuth2ServerException $e) {
	        $this->getLogger()->debug('token error: '.$e->getMessage());
            return $e->getHttpResponse();
        }
    }
	
	// helpers
	
	protected function autoDetectLocale(Request $request) {
		$request->setLocale($request->getPreferredLanguage(['de', 'en']));
		if ($this->container) {
			$this->container->get('translator')->setLocale($request->getLocale());
			$this->container->get('router')->getContext()->setParameter('_locale', $request->getLocale());
		}
	}
	
	/**
	 * @return Logger
	 */
	protected function getLogger() {
		return $this->container->get('monolog.logger.bricks.custom.twentysteps_alexa.oauth.jwt');
	}
}
