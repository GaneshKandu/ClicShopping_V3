<?php
/**
 *
 *  @copyright 2008 - https://www.clicshopping.org
 *  @Brand : ClicShopping(Tm) at Inpi all right Reserved
 *  @Licence GPL 2 & MIT
 *  @Info : https://www.clicshopping.org/forum/trademark/
 *
 */

  use ClicShopping\OM\CLICSHOPPING;
?>
<div class="<?php echo $text_position; ?> col-md-<?php echo $content_width; ?>">
  <div class="separator"></div>
    <div class="text-end productsReviewsListingImage"><?php echo $reviews_image; ?></div>
    <div class="text-end productsReviewsListingProductsName"><?php echo $products_name; ?></div>
    <div class="text-end productsReviewsListingProductsPrice"><?php echo CLICSHOPPING::getDef('text_price') . ' ' . $products_price; ?></div>
</div>