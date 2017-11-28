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
    <hr class="wp-header-end">
    <form name="dcoupled" method="post" action="options.php">
		<?php settings_fields( 'dcoupled-settings-group' ); ?>
		<?php do_settings_sections( 'dcoupled-settings-group' ); ?>
        <table class="form-table">
            <tbody>
            <tr>
                <td colspan="2">
                    <h3>Basic Settings</h3>
                </td>
            </tr>
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
                    <label for="dcoupled_publish_trigger_url">Cache Invalidation URL</label>
                </th>
                <td>
                    <input name="dcoupled_cache_invalidation_url" id="dcoupled_cache_invalidation_url"
                           value="<?php echo esc_attr( get_option( 'dcoupled_cache_invalidation_url' ) ); ?>"
                           class="regular-text">
                </td>
            </tr>
            <tr>
                <td colspan="2">
                    <h3>URL Settings</h3>
                </td>
            </tr>
            <tr>
                <th>
                    <label for="dcoupled_upload_url">Dcoupled Client Domain</label>
                </th>
                <td>
                    <input name="dcoupled_client_domain" id="dcoupled_client_domain"
                           value="<?php echo esc_attr( get_option( 'dcoupled_client_domain' ) ); ?>" class="regular-text">
                </td>
            </tr>
            <tr>
                <th>
                    <label for="dcoupled_upload_url">Uploads URL</label>
                </th>
                <td>
                    <input name="dcoupled_upload_url" id="dcoupled_upload_url"
                           value="<?php echo esc_attr( get_option( 'dcoupled_upload_url' ) ); ?>" class="regular-text">
                </td>
            </tr>
            </tbody>
        </table>
        <p class="submit">
            <input type="submit" name="submit" id="submit" class="button button-primary" value="Save Changes">
            <a id="dcoupled_flush_cache" href="#" class="dcoupled-clear-cache button button-large" data-action="dcoupled_flush_cache">Clear all Caches</a>
            <span class="spinner" style="float: none"></span>
        </p>
    </form>
</div>

