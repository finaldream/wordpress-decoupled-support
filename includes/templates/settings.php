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
                    <?php echo (defined('DECOUPLED_TOKEN') && DECOUPLED_TOKEN != null ) ? 
                    DECOUPLED_TOKEN 
                    : 'Please define constant DECOUPLED_TOKEN'; ?>
                </td>
            </tr>
            <tr>
                <th>
                    <label for="decoupled_cache_invalidation_url">Cache Invalidation URL</label>
                </th>
                <td>
                    <?php echo (defined('DECOUPLED_CACHE_INVALIDATION_URL') && DECOUPLED_CACHE_INVALIDATION_URL != null ) ? 
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
                    <label for="decoupled_client_url">Decoupled Client URL (including Protocol)</label>
                </th>
                <td>
                    <?php echo (defined('DECOUPLED_CLIENT_URL') && DECOUPLED_CLIENT_URL != null ) ? 
                        DECOUPLED_CLIENT_URL 
                        : 'Please define constant DECOUPLED_CLIENT_URL'; ?>
                </td>
            </tr>
            <?php if ( (defined('DECOUPLED_CLIENT_URL') && DECOUPLED_CLIENT_URL != null ) && !filter_var(DECOUPLED_CLIENT_URL, FILTER_VALIDATE_URL, FILTER_FLAG_SCHEME_REQUIRED)) { ?>
            <tr>
                <th style="color: red;" colspan="2">
                    WARNING: The Decoupled Client URL must include the protocol ( http:// or https:// )
                </th>
            </tr>
            <?php } ?>
            <tr>
                <th>
                    <label for="decoupled_upload_url">Uploads URL</label>
                </th>
                <td>
                    <?php echo (defined('DECOUPLED_UPLOAD_URL') && DECOUPLED_UPLOAD_URL != null ) ? 
                        DECOUPLED_UPLOAD_URL 
                        : 'Please define constant DECOUPLED_UPLOAD_URL'; ?>
                </td>
            </tr>
            <tr>
                <th>
                    <label for="decoupled_upload_url">Basic Authentication (Cache Clear)</label>
                </th>
                <td>
                    <?php echo (defined('DECOUPLED_BASIC_AUTH') && DECOUPLED_BASIC_AUTH != null ) ? 
                        'Basic Authentication is set'
                        : 'Please define constant DECOUPLED_BASIC_AUTH to enable it'; ?>
                </td>
            </tr>
            </tbody>
        </table>
        <p class="submit">
            <a id="decoupled_flush_cache" href="#" class="decoupled-clear-cache button button-large" data-action="decoupled_flush_cache">Clear all Caches</a>
            <span class="spinner" style="float: none"></span>
        </p>
        <p>
            <strong>Latest Cache Clearing Status:</strong>
        </p>
        <p>
            <?php 
                // TODO: Move the display logic to a method in CallbackNotifications Class
                $log = get_transient( 'decoupled_notifications_base' );
                if(sizeof($log) > 0) {
                    $cacheEvents = array_filter($log, function ($event) {
                        return in_array('Cache', $event['tags']);
                    });
                    if(sizeof($cacheEvents) > 0) {
                        $i = 0;
                        $limit = 5;
                        foreach (array_reverse($cacheEvents) as $event) if ($i < $limit)  {     
                            $datetime = $event['date'];
                            $timezone = 'Europe/Berlin';
                            $date = new \DateTime( $datetime, new \DateTimeZone( 'UTC' ) );
                            $date->setTimezone( new \DateTimeZone( $timezone ) );
                            echo '<p>'.$date->format('Y/m/d H:i:s').' - '.$event['message'].'</p>';
                            $i++;
                        }
                    } else {
                        echo '<p>Cache clearing has not been recently activated</p>';
                    }
                } else  {
                    echo '<p>Cache clearing has not been recently activated</p>';
                }
            ?>             
        </p>
</div>

