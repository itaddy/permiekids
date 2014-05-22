<?php
/**
 * This file contains the call out for the IT Exchange Customer Data box
 * @package IT_Exchange
 * @since 0.4.0
*/
// Just adding internal CSS rule here since it won't be around long.
?>

<?php
if ( empty( $_REQUEST['user_id'] ) )
    $user_id = get_current_user_id();
else
    $user_id = $_REQUEST['user_id'];

$user_object = get_userdata( $user_id );
?>

<table class="form-table">
<tbody>
<tr id="it_exchange_customer_data">
    <th><?php _e( 'iThemes Exchange', 'it-l10n-ithemes-exchange' ); ?></th>
    <td>
    <?php echo "<a class='it-exchange-cust-info' href='" . esc_url( add_query_arg( array( 'wp_http_referer' => urlencode( stripslashes_deep( $_SERVER['REQUEST_URI'] ) ), 'it_exchange_customer_data' => 1 ), get_edit_user_link( $user_object->ID ) ) ) . "'>" . __( 'View Customer Data', 'it-l10n-ithemes-exchange' ) . "</a>"; ?>
    </td>
</tr>
</tbody>
</table>
