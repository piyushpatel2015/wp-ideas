<?php

/*
* Elementor Custom Query Filter
* - This function will retrieve related products in a single product page filter by tags of the current single product
*
*/
function custom_related_products_by_tag( $query ) {
    if ( is_product() ){
        global $product;
        $id = $product->get_id();
        $term_list = wp_get_post_terms( $id, 'product_tag', array( 'fields' => 'slug' ) );
        if(!empty($term_list)){
            $tax_query = array(
                            array(
                                'taxonomy' => 'product_tag',
                                'field'    => 'slug',
                                'terms'    => $term_list,
                            ),
                        );
            $query->set( 'post_type', 'product' );
            $query->set( 'tax_query', $tax_query );
        }
    }
}
add_action( 'elementor/query/{$query_id}', 'custom_related_products_by_tag' );

/*
* Custom WP Search Form - Shortcode
* - Place short-code anywhere on the site content or widget to get the default WP search form 
*
*/

function wpbsearchform( $form ) {
   
    $form = '<form role="search" method="get" id="searchform" action="' . home_url( '/' ) . '" >
    <input type="text" value="' . get_search_query() . '" name="s" id="s" placeholder="Search..." />
    <input type="submit" id="searchsubmit" value="'. esc_attr__('Search') .'" />
    </form>';
   
    return $form;
}
   
add_shortcode('wpbsearch', 'wpbsearchform');


/*
* WooComerce Data layer Functions
* Adds a New Data layer when Receiving new Orders for Marketing purposes. 
* - This will add a javascript data layer when customers purchase anything from the webshop. 
*   It very helpful to track order for marketing purpose.
*   This data layer includes customer details.
*/

function add_new_purchage_datalayer(){
        global $wp;
        if (is_single( 'thank-you-for-your-download-order' ) && 'marketing' == get_post_type() && is_wc_endpoint_url('order-received') ) {
            $order_id = absint($wp->query_vars['order-received']);
            $order    = wc_get_order( $order_id );
            $customer_id = $order->get_customer_id();
            
            $items = $order->get_items();
            $products = [];

            foreach ($items as $item) {
                $product = $item->get_product();
                $products[] = [
                    'id' => $product->get_id(),
                    'name' => $product->get_name(),
                    'category' => wc_get_product_category_list($product->get_id()),
                    'price' => $product->get_price(),
                ];
            }
            ob_start();
        ?>
        <!--  new Product purchage Details DataLayer -->
        <script type="text/javascript">
            window.dataLayer = window.dataLayer || [];
            window.dataLayer.push({
                'event': 'ProductPurchageEventNameHere',
                'newpurchageDetails': {
                    'firstName': '<?php echo $order->get_billing_first_name(); ?>',
                    'lastName': '<?php echo $order->get_billing_last_name(); ?>',
                    'companyName': '<?php echo $order->get_billing_company(); ?>',
                    'email': '<?php echo $order->get_billing_email(); ?>',
                    'phone': '<?php echo $order->get_billing_phone(); ?>',
                    'address': '<?php $order->get_billing_address_1(); ?>',
                    'city': '<?php echo $order->get_billing_city(); ?>',
                    'state': '<?php echo $order->get_billing_state(); ?>',
                    'zip': '<?php echo $order->get_billing_postcode(); ?>',
                    'products': '<?php echo json_encode($products); ?>',
                }
            });
        </script>
        <!--  END new Product purchage Details DataLayer -->
        <?php
            $dataLayer = ob_get_clean();
            echo $dataLayer;
    }
}
add_action( 'wp_head', 'add_new_purchage_datalayer' );
?>