<?php

class bbconnect_wholesalesms_connector {
    private $apikey = '';
    private $secretkey = '';
    private $url = 'https://app.wholesalesms.com.au/api/v2/send-sms.json';
    private $last_error = '';
    private $last_code = '';

    private static $_instance = null;

    private function __construct() {
        $this->apikey = get_option('bbconnect_wholesalesms_api_key');
        $this->secretkey = get_option('bbconnect_wholesalesms_api_secret_key');
    }

    public static function get_instance() {
        if (self::$_instance == null) {
            self::$_instance = new self;
        }

        return self::$_instance;
    }

    /**
     * Was the last request successful?
     */
    public function is_success() {
        return $this->last_code == 200;
    }

    /**
     * Get error message from last request
     */
    public function get_last_error() {
        return $this->last_error;
    }

    /**
     * Get status code from last request
     */
    public function get_last_code() {
        return $this->last_code;
    }

    public function get_mobile_for_user($user_id){
        $phone_data = maybe_unserialize(get_user_meta($user_id, 'telephone', true));

        foreach ($phone_data as $existing_phone) {
            if ($this->is_valid_number($existing_phone['value'])) {
                return $existing_phone['value'];
            }
        }
        return false;
    }

    public function is_valid_number($number){
        $phone_is_valid = false;
        if (strpos($number, '04') === 0 || strpos($number, '+614') === 0) {
            $phone_is_valid = true;
        }
        return $phone_is_valid;
    }

    public function has_opted_out($user_id){
        $subscribe = get_user_meta($user_id, 'sms_subscribe', true);
        return $subscribe == 'false';
    }

    public function send_sms(array $recipients, $message) {
        foreach ($recipients as $user_id => $phone_number) {
            if ($this->has_opted_out($user_id)) {
                unset($recipients[$user_id]);
            }
        }
        if (!empty($recipients)) {
            $args = array(
                    'headers' => array(
                            'Authorization' => 'Basic ' . base64_encode( $this->apikey . ':' . $this->secretkey )
                    ),
                    'body' => array(
                            'message' => $message,
                            'to' => implode(',',$recipients),
                            'dlr_callback' => BBCONNECT_WHOLESALESMS_WEBHOOK_URL.'?type=delivery',
                            'reply_callback' => BBCONNECT_WHOLESALESMS_WEBHOOK_URL.'?type=reply',
                    ),
            );

            $response = wp_remote_post( $this->url, $args );
            $result = json_decode($response['body']);

            if (true) { // @todo If send was successful
                $form_id = bbconnect_get_send_email_form();
                $numbers = array();
                foreach ($recipients as $recipient => $number){
                    $numbers[] = $number;
                    foreach ($numbers as $number){
                        $firstname = $lastname = $email = '';
                        $user = get_user_by('id', $recipient);
                        if ($user instanceof WP_User) {
                            $firstname = $user->user_firstname;
                            $lastname = $user->user_lastname;
                            $email = $user->user_email;
                        }
                        // Insert GF entry
                        $_POST = array(); // Hack to allow multiple form submissions via API in single process
                        $entry = array(
                                'input_2_3' => $firstname,
                                'input_2_6' => $lastname,
                                'input_3' => $email,
                                'input_4' => $subject,
                                'input_5' => $message,
                                'input_6' => 'wholesalesms',
                                'input_7' => 'wholesalesms',
                                'input_9' => $result->message_id,
                                'input_10' => $number,
                                'agent_id' => get_current_user_id(),
                                'created_by' => $recipient,
                        );
                        GFAPI::submit_form($form_id, $entry);
                    }
                }
            }
        }
    }
}