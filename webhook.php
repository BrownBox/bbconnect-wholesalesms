<?php
require_once(dirname(__FILE__)."/../../../wp-load.php");

$type = $_GET['type'];

if ($type == 'reply') {
    $id = $_GET['message_id'];
    $mobile = $_GET['mobile'];
    $response = $_GET['response'];
//     wp_mail('stephen@brownbox.net.au', 'SMS Debug', var_export($id, true)."\n\n".var_export($mobile, true)."\n\n".var_export($response, true)."\n\n");

    $search_criteria['field_filters'][] = array('key' => '9', 'value' => $id);
    $entries = GFAPI::get_entries(bbconnect_get_send_email_form(), $search_criteria);
    foreach ($entries as $entry) {
        $phone = rgar($entry, '10');
        $sender = $entry->user_agent;
        $phone = str_replace('+', '', $phone);
        if (strpos($phone, '0') === 0) {
            $phone = '61'.substr($phone, 1);
        }
        if ($phone == $mobile) {
            $email = rgar($entry, '3');
            break;
        }
    }

    if (!empty($response) && !empty($id)) {
        $action_form_id = bbconnect_get_action_form();
        $_POST = array();
        $entry = array(
                'input_1'   => 'comms',
                'input_3'   => 'SMS',
                'input_7'   => 'SMS Received from '.$mobile,
                'input_8'   => $response,
                'input_9.1' => 'Follow up required',
                'input_11'  => date("Y/m/d"),
                'input_14'  => 'Please review the reply and take action as required',
                'input_12'  => $sender,
                'input_18'  => $email,
        );
        GFAPI::submit_form($action_form_id, $entry);
    }

    // update sms subscribe options
    $entries = GFAPI::get_entries(bbconnect_get_action_form());

    foreach ($entries as $entry){
        $email = rgar($entry, '18');
        $user = get_user_by('email', $email);
        var_dump($user);exit();
        $message = rgar($entry, '8');
        if(stripos($message, 'unsubscribe') !== false ){
            update_user_meta( $user->ID, 'sms_subscribe', 'false');
        }
    }

}