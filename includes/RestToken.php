<?php
/**
 * Simple token protect
 */


/**
 * Class RestToken
 */
class RestToken
{

    protected $error = null;


    public function protect($result)
    {

        $token = get_option('decoupled_token', '');

        if (!empty($result) || empty($token)) {
            return $result;
        }

        // Headers might arrive in different case than expected
        $headers = array_change_key_case(getallheaders(), CASE_LOWER);

        $headerToken = null;
        if (isset($headers['decoupled-token'])) {
            $headerToken = $headers['decoupled-token'];
        } else if (isset($headers['decoupled-token'])) {
            $headerToken = $headers['decoupled-token'];
        }

        if ($headerToken == null) {
            $this->error = new \WP_Error('rest_authentication_error', 'Access denied.');
        } elseif ($headerToken !== $token) {
            $this->error = new \WP_Error('rest_authentication_error', 'Invalid token.');
        }

        if (is_wp_error($this->error)) {
            http_response_code(403);
            wp_send_json_error(['error' => $this->error->get_error_message()]);
        }

        return $result;
    }
}