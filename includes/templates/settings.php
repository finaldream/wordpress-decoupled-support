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
                    <?php echo (defined('DECOUPLED_TOKEN') && !empty(DECOUPLED_TOKEN) ) ? 
                    DECOUPLED_TOKEN 
                    : 'Please define constant DECOUPLED_TOKEN'; ?>
                </td>
            </tr>
            <tr>
                <th>
                    <label for="decoupled_cache_invalidation_url">Cache Invalidation URL</label>
                </th>
                <td>
                    <?php echo (defined('DECOUPLED_CACHE_INVALIDATION_URL') && !empty(DECOUPLED_CACHE_INVALIDATION_URL) ) ? 
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
                    <?php echo (defined('DECOUPLED_CLIENT_URL') && !empty(DECOUPLED_CLIENT_URL) ) ? 
                        DECOUPLED_CLIENT_URL 
                        : 'Please define constant DECOUPLED_CLIENT_URL'; ?>
                </td>
            </tr>
            <?php 
                if ( (defined('DECOUPLED_CLIENT_URL') 
                    && !empty(DECOUPLED_CLIENT_URL) ) 
                    && !filter_var(DECOUPLED_CLIENT_URL, FILTER_VALIDATE_URL, FILTER_FLAG_SCHEME_REQUIRED)) { 
            ?>
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
                    <?php echo (defined('DECOUPLED_UPLOAD_URL') && !empty(DECOUPLED_UPLOAD_URL) ) ? 
                        DECOUPLED_UPLOAD_URL 
                        : 'Please define constant DECOUPLED_UPLOAD_URL'; ?>
                </td>
            </tr>
            <tr>
                <th>
                    <label for="decoupled_upload_url">Basic Authentication (Cache Clear)</label>
                </th>
                <td>
                    <?php echo (defined('DECOUPLED_BASIC_AUTH') && !empty(DECOUPLED_BASIC_AUTH) ) ? 
                        'Basic Authentication is set'
                        : 'Please define constant DECOUPLED_BASIC_AUTH to enable it'; ?>
                </td>
            </tr>
            <tr>
                <th>
                    <label for="decoupled_notfound_slug">Page content for 404 (Not found) errors</label>
                </th>
                <td>
                    <?php 
                        if (defined('DECOUPLED_NOTFOUND_SLUG') && !empty(DECOUPLED_NOTFOUND_SLUG)) {
                            $query = new WP_Query(
                                array(
                                    'name'   => DECOUPLED_NOTFOUND_SLUG,
                                    'post_type'   => 'page',
                                    'numberposts' => 1,
                                    'fields'      => 'ids',
                                ) 
                            );
                            $posts = $query->get_posts();
                            if (sizeof($posts) == 1) $link = get_edit_post_link($posts[0], 'link');
                            if (!isset($link)) {
                                $result = DECOUPLED_NOTFOUND_SLUG . ' - Please <a href="'. admin_url('post-new.php?post_type=page&post_title='.DECOUPLED_NOTFOUND_SLUG) .'">create a new page</a> with this slug';
                            } else {
                                $result =  DECOUPLED_NOTFOUND_SLUG . ' - <a href="'. $link .'">Click here to edit</a>'; 
                            }
                        } else {
                            $result = 'Please define constant DECOUPLED_NOTFOUND_SLUG to an existing page slug';
                        }
                        echo $result;
                    ?>
                </td>
            </tr>
            </tbody>
        </table>
        <p class="submit">
            <a id="decoupled_flush_cache" href="#" class="decoupled-clear-cache button button-large" data-action="decoupled_flush_cache">Clear all Caches</a>
            <span class="spinner" style="float: none"></span>
        </p>
        <h3>Latest Cache Clearing Status:</h3>
        <ul>
            <?php 
                echo (new CallbackNotifications())->printNotifications(['Cache'], false, 5);
            ?>             
        </ul>
</div>

