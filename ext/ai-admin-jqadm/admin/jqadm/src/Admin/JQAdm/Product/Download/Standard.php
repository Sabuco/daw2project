<?php

/**
 * @license LGPLv3, http://opensource.org/licenses/LGPL-3.0
 * @copyright Aimeos (aimeos.org), 2016
 * @package Admin
 * @subpackage JQAdm
 */


namespace Aimeos\Admin\JQAdm\Product\Download;


/**
 * Default implementation of product download JQAdm client.
 *
 * @package Admin
 * @subpackage JQAdm
 */
class Standard
	extends \Aimeos\Admin\JQAdm\Common\Admin\Factory\Base
	implements \Aimeos\Admin\JQAdm\Common\Admin\Factory\Iface
{
	/** admin/jqadm/product/download/standard/subparts
	 * List of JQAdm sub-clients rendered within the product download section
	 *
	 * The output of the frontend is composed of the code generated by the JQAdm
	 * clients. Each JQAdm client can consist of serveral (or none) sub-clients
	 * that are responsible for rendering certain sub-parts of the output. The
	 * sub-clients can contain JQAdm clients themselves and therefore a
	 * hierarchical tree of JQAdm clients is composed. Each JQAdm client creates
	 * the output that is placed inside the container of its parent.
	 *
	 * At first, always the JQAdm code generated by the parent is printed, then
	 * the JQAdm code of its sub-clients. The order of the JQAdm sub-clients
	 * determines the order of the output of these sub-clients inside the parent
	 * container. If the configured list of clients is
	 *
	 *  array( "subclient1", "subclient2" )
	 *
	 * you can easily change the order of the output by reordering the subparts:
	 *
	 *  admin/jqadm/<clients>/subparts = array( "subclient1", "subclient2" )
	 *
	 * You can also remove one or more parts if they shouldn't be rendered:
	 *
	 *  admin/jqadm/<clients>/subparts = array( "subclient1" )
	 *
	 * As the clients only generates structural JQAdm, the layout defined via CSS
	 * should support adding, removing or reordering content by a fluid like
	 * design.
	 *
	 * @param array List of sub-client names
	 * @since 2016.03
	 * @category Developer
	 */
	private $subPartPath = 'admin/jqadm/product/download/standard/subparts';
	private $subPartNames = [];


	/**
	 * Copies a resource
	 *
	 * @return string admin output to display or null for redirecting to the list
	 */
	public function copy()
	{
		$view = $this->getView();

		$this->setData( $view );
		$view->downloadBody = '';

		foreach( $this->getSubClients() as $client ) {
			$view->downloadBody .= $client->copy();
		}

		/** admin/jqadm/product/download/template-item
		 * Relative path to the HTML body template of the download subpart for products.
		 *
		 * The template file contains the HTML code and processing instructions
		 * to generate the result shown in the body of the frontend. The
		 * configuration string is the path to the template file relative
		 * to the templates directory (usually in admin/jqadm/templates).
		 *
		 * You can overwrite the template file configuration in extensions and
		 * provide alternative templates. These alternative templates should be
		 * named like the default one but with the string "default" replaced by
		 * an unique name. You may use the name of your project for this. If
		 * you've implemented an alternative client class as well, "default"
		 * should be replaced by the name of the new class.
		 *
		 * @param string Relative path to the template creating the HTML code
		 * @since 2016.04
		 * @category Developer
		 */
		$tplconf = 'admin/jqadm/product/download/template-item';
		$default = 'product/item-download-default.php';

		return $view->render( $view->config( $tplconf, $default ) );
	}


	/**
	 * Creates a new resource
	 *
	 * @return string admin output to display or null for redirecting to the list
	 */
	public function create()
	{
		$view = $this->getView();

		$this->setData( $view );
		$view->downloadBody = '';

		foreach( $this->getSubClients() as $client ) {
			$view->downloadBody .= $client->create();
		}

		$tplconf = 'admin/jqadm/product/download/template-item';
		$default = 'product/item-download-default.php';

		return $view->render( $view->config( $tplconf, $default ) );
	}


	/**
	 * Deletes a resource
	 *
	 * @return string|null admin output to display or null for redirecting to the list
	 */
	public function delete()
	{
		$view = $this->getView();
		$listManager = \Aimeos\MShop\Factory::createManager( $context = $this->getContext(), 'product/lists' );
		$search = $listManager->createSearch();

		foreach( (array) $view->param( 'id' ) as $id )
		{
			$listItems = $this->getListItems( $id );
			$items = $this->getAttributeItems( $listItems );

			foreach( $listItems as $listid => $listItem )
			{
				$refId = $listItem->getRefId();

				$expr = array(
					$search->compare( '==', 'product.lists.refid', $refId ),
					$search->compare( '==', 'product.lists.domain', 'attribute' ),
					$search->compare( '==', 'product.lists.type.code', 'hidden' ),
					$search->compare( '==', 'product.lists.type.domain', 'attribute' ),
				);
				$search->setConditions( $search->combine( '&&', $expr ) );
				$result = $listManager->aggregate( $search, 'product.lists.refid' );

				if( isset( $items[$refId] ) && $result[$refId] == 1 ) {
					$listItem->setRefItem( $items[$refId] );
				} else {
					unset( $listItems[$id] );
				}
			}

			$this->cleanupItems( $listItems, [] );
		}
	}


	/**
	 * Returns a single resource
	 *
	 * @return string admin output to display or null for redirecting to the list
	 */
	public function get()
	{
		$view = $this->getView();

		$this->setData( $view );
		$view->downloadBody = '';

		foreach( $this->getSubClients() as $client ) {
			$view->downloadBody .= $client->get();
		}

		$tplconf = 'admin/jqadm/product/download/template-item';
		$default = 'product/item-download-default.php';

		return $view->render( $view->config( $tplconf, $default ) );
	}


	/**
	 * Saves the data
	 *
	 * @return string|null admin output to display or null for redirecting to the list
	 */
	public function save()
	{
		$view = $this->getView();
		$context = $this->getContext();

		$manager = \Aimeos\MShop\Factory::createManager( $context, 'product/lists' );
		$attrManager = \Aimeos\MShop\Factory::createManager( $context, 'attribute' );

		$manager->begin();
		$attrManager->begin();

		try
		{
			$this->updateItems( $view );
			$view->downloadBody = '';

			foreach( $this->getSubClients() as $client ) {
				$view->downloadBody .= $client->save();
			}

			$attrManager->commit();
			$manager->commit();
			return;
		}
		catch( \Aimeos\MShop\Exception $e )
		{
			$error = array( 'product-item-download' => $context->getI18n()->dt( 'mshop', $e->getMessage() ) );
			$view->errors = $view->get( 'errors', [] ) + $error;

			$attrManager->rollback();
			$manager->rollback();
		}
		catch( \Exception $e )
		{
			$context->getLogger()->log( $e->getMessage() . ' - ' . $e->getTraceAsString() );
			$error = array( 'product-item-download' => $e->getMessage() );
			$view->errors = $view->get( 'errors', [] ) + $error;

			$attrManager->rollback();
			$manager->rollback();
		}

		throw new \Aimeos\Admin\JQAdm\Exception();
	}


	/**
	 * Returns the sub-client given by its name.
	 *
	 * @param string $type Name of the client type
	 * @param string|null $name Name of the sub-client (Default if null)
	 * @return \Aimeos\Admin\JQAdm\Iface Sub-client object
	 */
	public function getSubClient( $type, $name = null )
	{
		/** admin/jqadm/product/download/decorators/excludes
		 * Excludes decorators added by the "common" option from the product JQAdm client
		 *
		 * Decorators extend the functionality of a class by adding new aspects
		 * (e.g. log what is currently done), executing the methods of the underlying
		 * class only in certain conditions (e.g. only for logged in users) or
		 * modify what is returned to the caller.
		 *
		 * This option allows you to remove a decorator added via
		 * "admin/jqadm/common/decorators/default" before they are wrapped
		 * around the JQAdm client.
		 *
		 *  admin/jqadm/product/download/decorators/excludes = array( 'decorator1' )
		 *
		 * This would remove the decorator named "decorator1" from the list of
		 * common decorators ("\Aimeos\Admin\JQAdm\Common\Decorator\*") added via
		 * "admin/jqadm/common/decorators/default" to the JQAdm client.
		 *
		 * @param array List of decorator names
		 * @since 2016.03
		 * @category Developer
		 * @see admin/jqadm/common/decorators/default
		 * @see admin/jqadm/product/download/decorators/global
		 * @see admin/jqadm/product/download/decorators/local
		 */

		/** admin/jqadm/product/download/decorators/global
		 * Adds a list of globally available decorators only to the product JQAdm client
		 *
		 * Decorators extend the functionality of a class by adding new aspects
		 * (e.g. log what is currently done), executing the methods of the underlying
		 * class only in certain conditions (e.g. only for logged in users) or
		 * modify what is returned to the caller.
		 *
		 * This option allows you to wrap global decorators
		 * ("\Aimeos\Admin\JQAdm\Common\Decorator\*") around the JQAdm client.
		 *
		 *  admin/jqadm/product/download/decorators/global = array( 'decorator1' )
		 *
		 * This would add the decorator named "decorator1" defined by
		 * "\Aimeos\Admin\JQAdm\Common\Decorator\Decorator1" only to the JQAdm client.
		 *
		 * @param array List of decorator names
		 * @since 2016.03
		 * @category Developer
		 * @see admin/jqadm/common/decorators/default
		 * @see admin/jqadm/product/download/decorators/excludes
		 * @see admin/jqadm/product/download/decorators/local
		 */

		/** admin/jqadm/product/download/decorators/local
		 * Adds a list of local decorators only to the product JQAdm client
		 *
		 * Decorators extend the functionality of a class by adding new aspects
		 * (e.g. log what is currently done), executing the methods of the underlying
		 * class only in certain conditions (e.g. only for logged in users) or
		 * modify what is returned to the caller.
		 *
		 * This option allows you to wrap local decorators
		 * ("\Aimeos\Admin\JQAdm\Product\Decorator\*") around the JQAdm client.
		 *
		 *  admin/jqadm/product/download/decorators/local = array( 'decorator2' )
		 *
		 * This would add the decorator named "decorator2" defined by
		 * "\Aimeos\Admin\JQAdm\Product\Decorator\Decorator2" only to the JQAdm client.
		 *
		 * @param array List of decorator names
		 * @since 2016.03
		 * @category Developer
		 * @see admin/jqadm/common/decorators/default
		 * @see admin/jqadm/product/download/decorators/excludes
		 * @see admin/jqadm/product/download/decorators/global
		 */
		return $this->createSubClient( 'product/download/' . $type, $name );
	}


	/**
	 * Deletes the removed list items and their referenced items
	 *
	 * @param array $listItems List of items implementing \Aimeos\MShop\Common\Item\Lists\Iface
	 * @param array $listIds List of IDs of the still used list items
	 */
	protected function cleanupItems( array $listItems, array $listIds )
	{
		$context = $this->getContext();
		$fs = $context->getFilesystemManager()->get( 'fs-secure' );
		$manager = \Aimeos\MShop\Factory::createManager( $context, 'attribute' );
		$listManager = \Aimeos\MShop\Factory::createManager( $context, 'product/lists' );

		$rmItems = [];
		$rmListIds = array_diff( array_keys( $listItems ), $listIds );

		foreach( $rmListIds as $rmListId )
		{
			if( ( $item = $listItems[$rmListId]->getRefItem() ) !== null )
			{
				if( $item->getCode() != '' && $fs->has( $item->getCode() ) ) {
					$fs->rm( $item->getCode() );
				}
				$rmItems[] = $item->getId();
			}
		}

		$listManager->deleteItems( $rmListIds  );
		$manager->deleteItems( $rmItems  );
	}


	/**
	 * Creates a new pre-filled attribute item
	 *
	 * @return \Aimeos\MShop\Attribute\Item\Iface New attribute item object
	 */
	protected function createItem()
	{
		$context = $this->getContext();
		$manager = \Aimeos\MShop\Factory::createManager( $context, 'attribute' );
		$typeManager = \Aimeos\MShop\Factory::createManager( $context, 'attribute/type' );

		$item = $manager->createItem();
		$item->setTypeId( $typeManager->findItem( 'download', [], 'product' )->getId() );
		$item->setDomain( 'product' );
		$item->setStatus( 1 );

		return $item;
	}


	/**
	 * Creates a new pre-filled list item
	 *
	 * @param string $id Parent ID for the new list item
	 * @return \Aimeos\MShop\Common\Item\Lists\Iface New list item object
	 */
	protected function createListItem( $id )
	{
		$context = $this->getContext();
		$manager = \Aimeos\MShop\Factory::createManager( $context, 'product/lists' );
		$typeManager = \Aimeos\MShop\Factory::createManager( $context, 'product/lists/type' );

		$item = $manager->createItem();
		$item->setTypeId( $typeManager->findItem( 'hidden', [], 'attribute' )->getId() );
		$item->setDomain( 'attribute' );
		$item->setParentId( $id );
		$item->setStatus( 1 );

		return $item;
	}


	/**
	 * Returns the attribute items for the given list items
	 *
	 * @param array $listItems List of list items with IDs as key and items implementing \Aimeos\MShop\Common\Item\List\Iface as values
	 * @return array List of attribute items with ID as key and items implementing \Aimeos\MShop\Attribute\Item\Iface as values
	 */
	protected function getAttributeItems( array $listItems )
	{
		$refIds = [];

		foreach( $listItems as $item ) {
			$refIds[] = $item->getRefId();
		}

		$manager = \Aimeos\MShop\Factory::createManager( $this->getContext(), 'attribute' );

		$search = $manager->createSearch();
		$expr = array(
			$search->compare( '==', 'attribute.id', $refIds ),
			$search->compare( '==', 'attribute.domain', 'product' ),
			$search->compare( '==', 'attribute.type.domain', 'product' ),
			$search->compare( '==', 'attribute.type.code', 'download' ),
		);
		$search->setConditions( $search->combine( '&&', $expr ) );
		$search->setSlice( 0, 0x7fffffff );

		return $manager->searchItems( $search );
	}


	/**
	 * Returns the referenced products for the given product ID
	 *
	 * @param string $prodid Unique product ID
	 * @return array Associative list of bundle product IDs as keys and list items as values
	 */
	protected function getListItems( $prodid )
	{
		$manager = \Aimeos\MShop\Factory::createManager( $this->getContext(), 'product/lists' );

		$search = $manager->createSearch();
		$expr = array(
			$search->compare( '==', 'product.lists.parentid', $prodid ),
			$search->compare( '==', 'product.lists.domain', 'attribute' ),
			$search->compare( '==', 'product.lists.type.domain', 'attribute' ),
			$search->compare( '==', 'product.lists.type.code', 'hidden' ),
		);
		$search->setConditions( $search->combine( '&&', $expr ) );

		return $manager->searchItems( $search );
	}


	/**
	 * Returns the list of sub-client names configured for the client.
	 *
	 * @return array List of JQAdm client names
	 */
	protected function getSubClientNames()
	{
		return $this->getContext()->getConfig()->get( $this->subPartPath, $this->subPartNames );
	}


	/**
	 * Returns the mapped input parameter or the existing items as expected by the template
	 *
	 * @param \Aimeos\MW\View\Iface $view View object with helpers and assigned parameters
	 */
	protected function setData( \Aimeos\MW\View\Iface $view )
	{
		$view->downloadData = (array) $view->param( 'download', [] );

		if( !empty( $view->downloadData ) ) {
			return;
		}

		$data = [];
		$listItems = $this->getListItems( $view->item->getId() );
		$attrItems = $this->getAttributeItems( $listItems );

		foreach( $listItems as $listItem )
		{
			if( !isset( $attrItems[$listItem->getRefId()] ) ) {
				continue;
			}

			foreach( $listItem->toArray( true ) as $key => $value ) {
				$data[$key] = $value;
			}

			foreach( $attrItems[$listItem->getRefId()]->toArray( true ) as $key => $value ) {
				$data[$key] = $value;
			}

			$data['path'] = $attrItems[$listItem->getRefId()]->getCode();

			try
			{
				$fs = $this->getContext()->getFilesystemManager()->get( 'fs-secure' );

				$data['time'] = $fs->time( $data['path'] );
				$data['size'] = $fs->size( $data['path'] );
			}
			catch( \Exception $e ) { ; } // Show product even if file isn't available any more
		}

		$view->downloadData = $data;
	}


	/**
	 * Stores the uploaded file in the "fs-secure" file system
	 *
	 * @param \Psr\Http\Message\UploadedFileInterface $file
	 * @param string $path Path the file should be stored at
	 * @return string Path to the uploaded file
	 */
	protected function storeFile( \Psr\Http\Message\UploadedFileInterface $file, $path )
	{
		$fs = $this->getContext()->getFilesystemManager()->get( 'fs-secure' );

		if( $path == null )
		{
			$ext = pathinfo( $file->getClientFilename(), PATHINFO_EXTENSION );
			$hash = md5( $file->getClientFilename() . microtime( true ) );
			$path = sprintf( '%s/%s/%s.%s', $hash[0], $hash[1], $hash, $ext );

			if( !$fs->isdir( $hash[0] . '/' . $hash[1] ) ) {
				$fs->mkdir( $hash[0] . '/' . $hash[1] );
			}
		}

		$fs->writes( $path, $file->getStream()->detach() );

		return $path;
	}


	/**
	 * Updates existing product download references or creates new ones
	 *
	 * @param \Aimeos\MW\View\Iface $view View object with helpers and assigned parameters
	 */
	protected function updateItems( \Aimeos\MW\View\Iface $view )
	{
		$id = $view->item->getId();
		$context = $this->getContext();

		$attrManager = \Aimeos\MShop\Factory::createManager( $context, 'attribute' );
		$listManager = \Aimeos\MShop\Factory::createManager( $context, 'product/lists' );

		$listItems = $this->getListItems( $id );
		$listId = $view->param( 'download/product.lists.id' );

		if( isset( $listItems[$listId] ) ) {
			$litem = $listItems[$listId];
		} else {
			$litem = $this->createListItem( $id );
		}

		if( ( $attrId = $view->param( 'download/attribute.id' ) ) != '' ) {
			$item = $attrManager->getItem( $attrId );
		} else {
			$item = $this->createItem();
		}

		if( ( $file = $view->value( (array) $view->request()->getUploadedFiles(), 'download/file' ) ) !== null
			&& $file->getError() === UPLOAD_ERR_OK
		) {
			$path = ( $view->param( 'download/overwrite' ) == 1 ? $item->getCode() : null );
			$item->setCode( $this->storeFile( $file, $path ) );
		}

		if( ( $label = $view->param( 'download/attribute.label' ) ) != '' )
		{
			$item->setLabel( $label );
			$attrManager->saveItem( $item );

			$litem->setPosition( 0 );
			$litem->setRefId( $item->getId() );
			$litem->setStatus( $view->param( 'download/product.lists.status' ) );
			$listManager->saveItem( $litem );
		}

		$this->cleanupItems( $listItems, array( $listId ) );
	}
}