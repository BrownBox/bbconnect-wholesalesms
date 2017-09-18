<?php
add_filter('bbconnect_options_tabs', 'bbconnect_wholesalesms_options');
function bbconnect_wholesalesms_options($navigation) {
    $navigation['bbconnect_wholesalesms_settings'] = array(
            'title' => __('WholesaleSMS', 'bbconnect'),
            'subs' => false,
    );
    return $navigation;
}

function bbconnect_wholesalesms_settings() {
    return array(
            array(
                    'meta' => array(
                            'source' => 'bbconnect',
                            'meta_key' => 'bbconnect_wholesalesms_api_key',
                            'name' => __('API Key', 'bbconnect'),
                            'help' => '',
                            'options' => array(
                                    'field_type' => 'text',
                                    'req' => true,
                                    'public' => false,
                            ),
                    ),
            ),
            array(
                    'meta' => array(
                            'source' => 'bbconnect',
                            'meta_key' => 'bbconnect_wholesalesms_api_secret_key',
                            'name' => __('API Secret Key', 'bbconnect'),
                            'help' => '',
                            'options' => array(
                                    'field_type' => 'text',
                                    'req' => true,
                                    'public' => false,
                            ),
                    ),
            ),
    );
}
