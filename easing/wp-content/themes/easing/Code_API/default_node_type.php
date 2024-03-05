<?php

function Router_Default($post, $post_id, $label, $token_access): void {
    error_log("");
    error_log("===================================================");
    error_log("              Router Default");
    error_log("===================================================");
    error_log("");

    $Post_Status = $post->post_status;

    $WP_ID = $post->post_title;
    $node_ID = get_Restriction_id($WP_ID, $token_access);

    error_log("===================================");
    error_log("              Info");
    error_log("Post Status: ".$Post_Status);
    error_log("Label: ".$label);
    error_log("Restriction_ID: ".$Restriction_ID);
    error_log("Node ID: ".$node_ID);
    error_log("===================================");
    error_log("");


    if ($node_ID < 1) {
        $fields = get_fields($post_id);
        if (gettype($fields) != "array"){
            error_log("fields empty");
            return;
        }
        create_Restriction($post, $post_id, $label, $token_access, $fields);
    }
    else {
        if ($Post_Status == "publish"){
            update_Restriction($node_ID, $post_id, $token_access);
        } elseif ($Post_Status == "trash") {
            delete_Restriction($node_ID, $token_access);
        } else {
            // If you want to do something on draft
            error_log("Draft");
        }
    }
}

function get_Node($Restriction_ID, $label, $token_access){
    error_log("");
    error_log("====================================");
    error_log("         Get Restriction");
    error_log("====================================");
    error_log("ID: ".$Restriction_ID);
    error_log("token: ".$token_access);

    $ID_url = "/graph/read_node_collection";

    error_log("URL: ".$GLOBALS['API_URL'].$ID_url);

    $response = wp_remote_get(
        $GLOBALS['API_URL'].$ID_url."?search_node_property=ID_Restriction&node_property_value=".urlencode($Restriction_ID), array(
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

    $Restrictions = json_decode($response['body'], true);


    if (count($Restrictions['nodes']) > 0) {
        error_log("========================================= Get Restriction");
        error_log("");
        error_log("");

        return $Restrictions['nodes'][0];
    } else {
        error_log("========================================= Get Restriction");
        error_log("");
        error_log("");

        return -1;
    }

}

function get_Restriction_id($Restriction_ID, $token_access){
    error_log("");
    error_log("=========================================");
    error_log("              Get Restriction ID");
    error_log("=========================================");
    error_log("ID: ".$Restriction_ID);
    error_log("token: ".$token_access);

    $Restriction = get_Restriction($Restriction_ID, $token_access);

    if ($Restriction != -1) {
        $node_ID = $Restriction['node_id'];

        error_log("========================================= Get Restriction ID");
        error_log("");
        error_log("");

        return $node_ID;
    } else {
        error_log("========================================= Get Restriction ID");
        error_log("");
        error_log("");

        return -1;
    }
}

function create_Restriction($post, $post_id, $label, $token_access, $fields):void {
    error_log("");
    error_log("=========================================");
    error_log("              Create Restriction");
    error_log("=========================================");
    error_log("post_id: ".$post_id);
    error_log("label: ".$label);
    error_log("token: ".$token_access);
    error_log("Restriction_ID: ".$post->post_title);
    $Restriction_ID = $post->post_title;

    // =================================================================================================================
    //                                                  Create Request
    // =================================================================================================================

    $create_url = "/graph/create_node";

    $complete_url = $GLOBALS['API_URL'].$create_url."?label=".urlencode($label);

//    error_log("complete url: ".$complete_url);

    $create_body = array(
        'ID_Restriction'=>$Restriction_ID,
        'ID_Post'=>$post_id
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

function update_Restriction($node_ID, $post_id, $token_access):void {
    error_log("");
    error_log("=====================================");
    error_log("            Edit Restriction");
    error_log("=====================================");

    // =================================================================================================================
    //                                                  Update Request
    // =================================================================================================================

    $update_url = "/graph/update/".$node_ID;


    $update_body = array('ID_Post'=>$post_id);

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
    error_log("========================================= Edit Restriction");
    error_log("");
    error_log("");
}

function delete_Restriction($node_id, $token_access):void {
    error_log("");
    error_log("=========================================");
    error_log("              Delete Restriction");
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
