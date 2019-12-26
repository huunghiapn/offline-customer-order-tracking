<?php
/**
 * Offline Order Details
 *
 */

defined( 'ABSPATH' ) || exit;
get_header();
global $post;
?>
    <div id="primary" class="content-area">
        <main id="main" class="site-main" role="main">

            <?php
            while ( have_posts() ) :
                the_post();
            ?>
            <table>
                <tr>
                    <th><?php _e("Order No", "ocot") ?></th>
                    <td><?php echo get_field("order_id")  ?></td>
                </tr>
                <tr>
                    <th><?php _e("Customer's Name", "ocot") ?></th>
                    <td><?php echo get_field("customer_name")  ?></td>
                </tr>
                <tr>
                    <th><?php _e("Customer's phone", "ocot") ?></th>
                    <td><?php echo get_field("customer_phone")  ?></td>
                </tr>
                <tr>
                    <th><?php _e("Customer's Address", "ocot") ?></th>
                    <td><?php echo get_field("customer_address")  ?></td>
                </tr>
                <tr>
                    <th><?php _e("Purchases Date", "ocot") ?></th>
                    <td><?php echo get_field("purchases_date")  ?></td>
                </tr>
                <tr>
                    <th><?php _e("Products", "ocot") ?></th>
                    <td><?php foreach ( get_field("products") as $product) {echo $product->post_title . ' ';} ?></td>
                </tr>
                <tr>
                    <th><?php _e("Total", "ocot") ?></th>
                    <td><?php echo get_field("total_money")  ?></td>
                </tr>
                <tr>
                    <th><?php _e("Notes", "ocot") ?></th>
                    <td><?php echo get_field("notes")  ?></td>
                </tr>
            </table>
            <?php

            endwhile; // End of the loop.
            ?>

        </main><!-- #main -->
    </div><!-- #primary -->

<?php
get_footer();
