<?php

/**
 * @license LGPLv3, http://opensource.org/licenses/LGPL-3.0
 * @copyright Aimeos (aimeos.org), 2017
 * @package Client
 * @subpackage JsonApi
 */


namespace Aimeos\Client\JsonApi\Service;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;


/**
 * JSON API standard client
 *
 * @package Client
 * @subpackage JsonApi
 */
class Standard
	extends \Aimeos\Client\JsonApi\Base
	implements \Aimeos\Client\JsonApi\Iface
{
	/**
	 * Returns the resource or the resource list
	 *
	 * @param \Psr\Http\Message\ServerRequestInterface $request Request object
	 * @param \Psr\Http\Message\ResponseInterface $response Response object
	 * @return \Psr\Http\Message\ResponseInterface Modified response object
	 */
	public function get( ServerRequestInterface $request, ResponseInterface $response )
	{
		$view = $this->getView();

		try
		{
			$ref = $view->param( 'include', ['media', 'price', 'text'] );

			if( is_string( $ref ) ) {
				$ref = explode( ',', $ref );
			}

			$cntl = \Aimeos\Controller\Frontend\Factory::createController( $this->getContext(), 'service' );
			$basketCntl = \Aimeos\Controller\Frontend\Factory::createController( $this->getContext(), 'basket' );
			$basket = $basketCntl->get();

			if( ( $id = $view->param( 'id' ) ) != '' )
			{
				$provider = $cntl->getProvider( $id, $ref );

				if( $provider->isAvailable( $basket ) === true )
				{
					$view->attributes = [$id => $provider->getConfigFE( $basket )];
					$view->prices = [$id => $provider->calcPrice( $basket )];
					$view->items = $provider->getServiceItem();
					$view->total = 1;
				}
			}
			else
			{
				$attributes = $prices = $items = [];
				$type = $view->param( 'filter/cs_type' );

				foreach( $cntl->getProviders( $type, $ref ) as $id => $provider )
				{
					if( $provider->isAvailable( $basket ) === true )
					{
						$attributes[$id] = $provider->getConfigFE( $basket );
						$prices[$id] = $provider->calcPrice( $basket );
						$items[$id] = $provider->getServiceItem();
					}
				}

				$view->attributes = $attributes;
				$view->prices = $prices;
				$view->items = $items;
				$view->total = count( $items );
			}

			$status = 200;
		}
		catch( \Aimeos\MShop\Exception $e )
		{
			$status = 404;
			$view->errors = array( array(
				'title' => $this->getContext()->getI18n()->dt( 'mshop', $e->getMessage() ),
				'detail' => $e->getTraceAsString(),
			) );
		}
		catch( \Exception $e )
		{
			$status = 500;
			$view->errors = array( array(
				'title' => $e->getMessage(),
				'detail' => $e->getTraceAsString(),
			) );
		}

		/** client/jsonapi/service/standard/template
		 * Relative path to the service JSON API template
		 *
		 * The template file contains the code and processing instructions
		 * to generate the result shown in the JSON API body. The
		 * configuration string is the path to the template file relative
		 * to the templates directory (usually in client/jsonapi/templates).
		 *
		 * You can overwrite the template file configuration in extensions and
		 * provide alternative templates. These alternative templates should be
		 * named like the default one but with the string "standard" replaced by
		 * an unique name. You may use the name of your project for this. If
		 * you've implemented an alternative client class as well, "standard"
		 * should be replaced by the name of the new class.
		 *
		 * @param string Relative path to the template creating the body for the GET method of the JSON API
		 * @since 2017.03
		 * @category Developer
		 */
		$tplconf = 'client/jsonapi/service/standard/template';
		$default = 'service/standard.php';

		$body = $view->render( $view->config( $tplconf, $default ) );

		return $response->withHeader( 'Allow', 'GET' )
			->withHeader( 'Content-Type', 'application/vnd.api+json' )
			->withBody( $view->response()->createStreamFromString( $body ) )
			->withStatus( $status );
	}
}
