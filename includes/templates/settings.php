<?php
/**
 * Decoupled Settings Page
 *
 * @author Louis Thai <louis.thai@finaldream.de>
 * @since 31.08.2017
 */

?>

<div class="wrap">
    <h1>Decoupled Settings</h1>
    <hr class="wp-header-end">
    <form name="decoupled" method="post" action="options.php">
		<?php settings_fields( 'decoupled-settings-group' ); ?>
		<?php do_settings_sections( 'decoupled-settings-group' ); ?>
        <table class="form-table">
            <tbody>
            <tr>
                <td colspan="2">
                    <h3>Basic Settings</h3>
                </td>
            </tr>
            <tr>
                <th>
                    <label for="decoupled_token">Decoupled Auth Token</label>
                </th>
                <td>
                    <input name="decoupled_token" id="decoupled_token"
                           value="<?php echo esc_attr( get_option( 'decoupled_token' ) ); ?>" class="regular-text" disabled>
                </td>
            </tr>
            <tr>
                <th>
                    <label for="decoupled_publish_trigger_url">Cache Invalidation URL</label>
                </th>
                <td>
                    <input name="decoupled_cache_invalidation_url" id="decoupled_cache_invalidation_url"
                           value="<?php echo esc_attr( get_option( 'decoupled_cache_invalidation_url' ) ); ?>"
                           class="regular-text" disabled>
                </td>
            </tr>
            <tr>
                <td colspan="2">
                    <h3>URL Settings</h3>
                </td>
            </tr>
            <tr>
                <th>
                    <label for="decoupled_upload_url">Decoupled Client URL</label>
                </th>
                <td>
                    <input name="decoupled_client_domain" id="decoupled_client_domain"
                           value="<?php echo esc_attr( get_option( 'decoupled_client_domain' ) ); ?>" class="regular-text" disabled>
                </td>
            </tr>
            <tr>
                <th>
                    <label for="decoupled_upload_url">Uploads URL</label>
                </th>
                <td>
                    <input name="decoupled_upload_url" id="decoupled_upload_url"
                           value="<?php echo esc_attr( get_option( 'decoupled_upload_url' ) ); ?>" class="regular-text" disabled>
                </td>
            </tr>
            </tbody>
        </table>
        <p class="submit">
            <input type="submit" name="submit" id="submit" class="button button-primary" value="Save Changes">
            <a id="decoupled_flush_cache" href="#" class="decoupled-clear-cache button button-large" data-action="decoupled_flush_cache">Clear all Caches</a>
            <span class="spinner" style="float: none"></span>
        </p>
    </form>
</div>

