<?php
/**
 * Dcoupled Settings Page
 *
 * @author Louis Thai <louis.thai@finaldream.de>
 * @since 31.08.2017
 */

?>

<div class="wrap">
    <h1>Dcoupled Settings</h1>

    <form name="dcoupled" method="post" action="options.php">
		<?php settings_fields( 'dcoupled-settings-group' ); ?>
		<?php do_settings_sections( 'dcoupled-settings-group' ); ?>
        <table class="form-table">
            <tbody>
            <tr>
                <th>
                    <label for="dcoupled_token">Dcoupled Auth Token</label>
                </th>
                <td>
                    <input name="dcoupled_token" id="dcoupled_token"
                           value="<?php echo esc_attr( get_option( 'dcoupled_token' ) ); ?>" class="regular-text">
                </td>
            </tr>
            <tr>
                <th>
                    <label for="dcoupled_publish_trigger_url">Publish Trigger URL</label>
                </th>
                <td>
                    <input name="dcoupled_publish_trigger_url" id="dcoupled_publish_trigger_url"
                           value="<?php echo esc_attr( get_option( 'dcoupled_publish_trigger_url' ) ); ?>"
                           class="regular-text">
                </td>
            </tr>
            </tbody>
        </table>
        <p class="submit">
            <input type="submit" name="submit" id="submit" class="button button-primary" value="Save Changes">
            <a id="generate_all" href="#" class="button button-large">Generate All</a>
        </p>
    </form>
</div>
<script>
    jQuery(document).ready(function() {

        var callback = function(res) {
            alert(res.data);

            setTimeout(function() {
                jQuery('#generate_all').removeAttr('disabled');
            }, 10000);
        };

        jQuery('#generate_all').on('click', function(e) {
            e.preventDefault();

            jQuery(this).attr('disabled', true);

            jQuery.ajax({
                type: 'POST',
                url: ajaxurl,
                data: {
                    action: 'dcoupled_generate_all'
                },
                success: callback,
                error: callback
            });
        });
    })
</script>
