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
        <table class="form-table">
            <tbody>
            <tr>
                <td colspan="2">
                    <h3>Settings Information</h3>
                </td>
            </tr>
            <tr>
                <th>
                    <label for="decoupled_token">Decoupled Auth Token</label>
                </th>
                <td>
                    <?php echo defined('DECOUPLED_TOKEN') ? 
                    DECOUPLED_TOKEN 
                    : 'Please define constant DECOUPLED_TOKEN'; ?>
                </td>
            </tr>
            <tr>
                <th>
                    <label for="decoupled_cache_invalidation_url">Cache Invalidation URL</label>
                </th>
                <td>
                    <?php echo defined('DECOUPLED_CACHE_INVALIDATION_URL') ? 
                    DECOUPLED_CACHE_INVALIDATION_URL 
                    : 'Please define constant DECOUPLED_CACHE_INVALIDATION_URL'; ?>
                </td>
            </tr>
            <tr>
                <td colspan="2">
                    <h3>URL Settings</h3>
                </td>
            </tr>
            <tr>
                <th>
                    <label for="decoupled_client_url">Decoupled Client Domain (including Scheme)</label>
                </th>
                <td>
                    <?php echo defined('DECOUPLED_CLIENT_URL') ? 
                        DECOUPLED_CLIENT_URL 
                        : 'Please define constant DECOUPLED_CLIENT_URL'; ?>
                </td>
            </tr>
            <?php if (defined('DECOUPLED_CLIENT_URL') && !filter_var(DECOUPLED_CLIENT_URL, FILTER_VALIDATE_URL, FILTER_FLAG_SCHEME_REQUIRED)) { ?>
            <tr>
                <th style="color: red;" colspan="2">
                    WARNING: The Decoupled Client URL must include the Scheme ( http:// or https:// )
                </th>
            </tr>
            <?php } ?>
            <tr>
                <th>
                    <label for="decoupled_upload_url">Uploads URL</label>
                </th>
                <td>
                    <?php echo defined('DECOUPLED_UPLOAD_URL') ? 
                        DECOUPLED_UPLOAD_URL 
                        : 'Please define constant DECOUPLED_UPLOAD_URL'; ?>
                </td>
            </tr>
            </tbody>
        </table>
        <p class="submit">
            <a id="decoupled_flush_cache" href="#" class="decoupled-clear-cache button button-large" data-action="decoupled_flush_cache">Clear all Caches</a>
            <span class="spinner" style="float: none"></span>
        </p>
</div>

