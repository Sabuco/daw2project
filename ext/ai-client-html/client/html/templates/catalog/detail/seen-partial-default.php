<?php

/**
 * @license LGPLv3, http://opensource.org/licenses/LGPL-3.0
 * @copyright Metaways Infosystems GmbH, 2013
 * @copyright Aimeos (aimeos.org), 2015-2016
 */

$enc = $this->encoder();

$detailTarget = $this->config( 'client/html/catalog/detail/url/target' );
$detailController = $this->config( 'client/html/catalog/detail/url/controller', 'catalog' );
$detailAction = $this->config( 'client/html/catalog/detail/url/action', 'detail' );
$detailConfig = $this->config( 'client/html/catalog/detail/url/config', [] );


?>
<?php if( isset( $this->seenProductItem ) ) : $productItem = $this->seenProductItem; ?>

	<?php $mediaItems = $productItem->getRefItems( 'media', 'default', 'default' ); ?>
	<?php $params = array( 'd_name' => $productItem->getName( 'url' ), 'd_prodid' => $productItem->getId() ); ?>

	<a href="<?php echo $enc->attr( $this->url( $detailTarget, $detailController, $detailAction, $params, [], $detailConfig ) ); ?>">

		<?php if( ( $mediaItem = reset( $mediaItems ) ) !== false ) : ?>
			<div class="media-item" style="background-image: url('<?php echo $this->content( $mediaItem->getPreview() ); ?>')"></div>
		<?php else : ?>
			<div class="media-item"></div>
		<?php endif; ?>

		<h3 class="name"><?php echo $enc->html( $productItem->getName(), $enc::TRUST ); ?></h3>

		<div class="price-list">
			<?php echo $this->partial(
				$this->config( 'client/html/common/partials/price', 'common/partials/price-default.php' ),
				array( 'prices' => $productItem->getRefItems( 'price', null, 'default' ) )
			); ?>
		</div>

	</a>
<?php endif; ?>
