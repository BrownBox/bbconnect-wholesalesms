<?php
/**
 * Wholesalesms quicklink
 */

class profile_wholesalesms_quicklink extends bb_form_quicklink {
    public $connector = null;

    public function __construct() {
        parent::__construct();
        $this->title = 'Send SMS';
        $this->connector = bbconnect_wholesalesms_connector::get_instance();
    }

    protected function form_contents(array $user_ids = array(), array $args = array()) {
        $mobile_count = 0;
        foreach ($user_ids as $user_id) {
            $mobile = $this->connector->get_mobile_for_user($user_id);
            if ($mobile && !$this->connector->has_opted_out($user_id)) {
                $mobile_count++;
                echo '<input type="hidden" name="telephone['.$user_id.']" value="'.$mobile.'">';
            }
        }
        echo '<div class="modal-row">';
        if ($mobile_count < count($user_ids)) {
            echo '    <p>You are attempting to send an SMS to '.count($user_ids).' contacts, however only '.$mobile_count.' of them have valid mobile numbers and will accept SMS messages. SMS messages can only be sent to mobiles.</p>';
        } else {
            echo '    <p>You are sending an SMS to '.count($user_ids).' contacts.</p>';
        }
        echo '</div>';
        echo '<div class="modal-row">';
        echo '    <label for="message">Message</label>';
        echo '    <textarea id="message" name="message" rows="20"></textarea>';
        echo '</div>';
        foreach ($user_ids as $user_id) {
            echo '<input type="hidden" name="recipients['.$user_id.']" value="'.$user_id.'">';
        }
    }

    public static function post_submission() {
        extract($_POST);
        if (empty($message)) {
            echo 'Message field is required.';
            die();
        }
        if (empty($telephone)) {
            echo 'No valid mobile number found.';
            die();
        }

        // Send SMS
        $connector = bbconnect_wholesalesms_connector::get_instance();
        $connector->send_sms($telephone, $message);

        if (!$this->connector->is_success()) {
            echo 'An error occured while attempted to send your SMS: '.$connector->get_last_error().'.';
            die();
        }

        return true;
    }
}

?>