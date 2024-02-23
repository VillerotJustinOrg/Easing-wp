<?php

function Router_Service_Proximite($post, $post_id, $label, $token_access): void {
    error_log("");
    error_log("===================================================");
    error_log("              Router Service_Proximite");
    error_log("===================================================");
    error_log("");

    $Post_Status = $post->post_status;

    $Service_Proximite_ID = $post->post_title;
    $node_ID = get_Service_Proximite_id($Service_Proximite_ID, $token_access);

    error_log("===================================");
    error_log("              Info");
    error_log("Post Status: ".$Post_Status);
    error_log("Label: ".$label);
    error_log("Service_Proximite_ID: ".$Service_Proximite_ID);
    error_log("Node ID: ".$node_ID);
    error_log("===================================");
    error_log("");


    if ($node_ID < 1) {
        $fields = get_fields($post_id);
        if (gettype($fields) != "array"){
            error_log("fields empty");
            return;
        }
        create_Service_Proximite($post, $post_id, $label, $token_access, $fields);
    }
    else {
        if ($Post_Status == "publish"){
            update_Service_Proximite($node_ID, $post_id, $token_access);
        } elseif ($Post_Status == "trash") {
            delete_Service_Proximite($node_ID, $token_access);
        } else {
            // If you want to do something on draft
            error_log("Draft");
        }
    }
}

function get_Service_Proximite($Service_Proximite_ID, $token_access){
    error_log("");
    error_log("====================================");
    error_log("         Get Service_Proximite");
    error_log("====================================");
    error_log("ID: ".$Service_Proximite_ID);
    error_log("token: ".$token_access);

    $ID_url = "/graph/read_node_collection";

    error_log("URL: ".$GLOBALS['API_URL'].$ID_url);

    $response = wp_remote_get(
        $GLOBALS['API_URL'].$ID_url."?search_node_property=ID_Service_Proximite&node_property_value=".urlencode($Service_Proximite_ID), array(
            'headers' => array(
                'Content-Type' => 'application/x-www-form-urlencoded',
                'Accept' => 'application/json',
                'Authorization' => 'bearer '.$token_access
            ),
        )
    );

    if( is_wp_error( $response ) ) {
        error_log("Error");
    }

//    error_log(print_r($response, true));

    $Service_Proximites = json_decode($response['body'], true);


    if (count($Service_Proximites['nodes']) > 0) {
        error_log("========================================= Get Service_Proximite");
        error_log("");
        error_log("");

        return $Service_Proximites['nodes'][0];
    } else {
        error_log("========================================= Get Service_Proximite");
        error_log("");
        error_log("");

        return -1;
    }

}


function get_Service_Proximite_id($Service_Proximite_ID, $token_access){
    error_log("");
    error_log("=========================================");
    error_log("              Get Service_Proximite ID");
    error_log("=========================================");
    error_log("ID: ".$Service_Proximite_ID);
    error_log("token: ".$token_access);

    $Service_Proximite = get_Service_Proximite($Service_Proximite_ID, $token_access);

    if ($Service_Proximite != -1) {
        $node_ID = $Service_Proximite['node_id'];

        error_log("========================================= Get Service_Proximite ID");
        error_log("");
        error_log("");

        return $node_ID;
    } else {
        error_log("========================================= Get Service_Proximite ID");
        error_log("");
        error_log("");

        return -1;
    }
}

function create_Service_Proximite($post, $post_id, $label, $token_access, $fields):void {
    error_log("");
    error_log("=========================================");
    error_log("              Create Service_Proximite");
    error_log("=========================================");
    error_log("post_id: ".$post_id);
    error_log("label: ".$label);
    error_log("token: ".$token_access);
    error_log("Service_Proximite_ID: ".$post->post_title);
    $Service_Proximite_ID = $post->post_title;

    // =================================================================================================================
    //                                                  Create Request
    // =================================================================================================================

    $create_url = "/graph/create_node";

    $complete_url = $GLOBALS['API_URL'].$create_url."?label=".urlencode($label);

//    error_log("complete url: ".$complete_url);

    $create_body = array(
        'ID_Service_Proximite'=>$Service_Proximite_ID
    );

    $create_body= add_field_info_to_body($create_body, $fields);

    $update_header = array(
        'Content-Type' => 'application/json',
        'Accept' => 'application/json',
        'Authorization' => 'bearer '.$token_access
    );

    $args = array(
        'headers' => $update_header,
        'body' => json_encode($create_body),
        'method' => 'POST'
    );

    $create_response = wp_remote_request($complete_url, $args);

    if( is_wp_error( $create_response ) ) {
        error_log("Error");
    }

    error_log("result: ".print_r($create_response, true));
    error_log("========================================= Create");
    error_log("");
    error_log("");
}

function update_Service_Proximite($node_ID, $post_id, $token_access):void {
    error_log("");
    error_log("=====================================");
    error_log("            Edit Service_Proximite");
    error_log("=====================================");

    // =================================================================================================================
    //                                                  Update Request
    // =================================================================================================================

    $update_url = "/graph/update/".$node_ID;


    $update_body = array();

    $update_body= add_field_info_to_body($update_body, get_fields($post_id));

    $update_header = array(
        'Content-Type' => 'application/json',
        'Accept' => 'application/json',
        'Authorization' => 'bearer '.$token_access
    );

    $args = array(
        'headers' => $update_header,
        'body' => json_encode($update_body),
        'method' => 'PUT'
    );

    $update_response = wp_remote_request($GLOBALS['API_URL'].$update_url, $args);

    if( is_wp_error( $update_response ) ) {
        error_log("Error");
    }

//    error_log("result: ".print_r($update_response, true));
    error_log("========================================= Edit Service_Proximite");
    error_log("");
    error_log("");
}

function delete_Service_Proximite($node_id, $token_access):void {
    error_log("");
    error_log("=========================================");
    error_log("              Delete Service_Proximite");
    error_log("=========================================");

    // DELETE all RELATIONSHIP

    // Delete all relationship linked to the logement
    $DEL_All_R_URL = "/graph/delete_all_relationship/$node_id";

    $header = array(
        'Content-Type'=>'application/json',
        'Accept' => 'application/json',
        'Authorization' => 'bearer '.$token_access
    );

    $args = array(
        'headers' => $header,
        'method' => 'POST'
    );

    $relationship_response = wp_remote_request( $GLOBALS['API_URL'].$DEL_All_R_URL, $args);
    if( is_wp_error($relationship_response) ) {
        error_log("Error");
    }


    $complete_url = $GLOBALS['API_URL']."/graph/delete/".$node_id;

    $update_header = array(
        'Content-Type' => 'application/json',
        'Accept' => 'application/json',
        'Authorization' => 'bearer '.$token_access
    );

    $args = array(
        'headers' => $update_header,
        'method' => 'POST'
    );

    $delete_response = wp_remote_request($complete_url, $args);

    if( is_wp_error($delete_response) ) {
        error_log("Error");
    }

    error_log("Delete Result: ".print_r(json_decode($delete_response['body'], true), true));

    error_log("========================================= Delete");
    error_log("");
    error_log("");

}
