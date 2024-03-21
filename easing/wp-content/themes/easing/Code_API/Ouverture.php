<?php

function Router_ouverture($post, $post_id, $label, $token_access): void {
    error_log("");
    error_log("===================================================");
    error_log("              Router ouverture Lieu");
    error_log("===================================================");
    error_log("");

    $Post_Status = $post->post_status;

    $ouverture_ID = $post->post_title;
    $node_ID = get_ouverture_id($ouverture_ID, $token_access);

    error_log("===================================");
    error_log("              Info");
    error_log("Post Status: ".$Post_Status);
    error_log("Label: ".$label);
    error_log("Ouverture_ID: ".$ouverture_ID);
    error_log("Node ID: ".$node_ID);
    error_log("===================================");
    error_log("");


    if ($node_ID < 1) {
        create_ouverture($post, $post_id, $label, $token_access);
    }
    else {
        if ($Post_Status == "publish"){
            update_ouverture($node_ID, $post_id, $label, $token_access);
        } elseif ($Post_Status == "trash") {
            delete_ouverture($node_ID, $token_access, $label);
        } else {
            // If you want to do something on draft
            error_log("Draft");
        }
    }
}

function get_ouverture_id($ouverture_ID, $token_access){
    error_log("");
    error_log("=========================================");
    error_log("              Get ouverture ID");
    error_log("=========================================");
    error_log("ID: ".$ouverture_ID);
    error_log("token: ".$token_access);

    $ID_url = "/graph/read_node_collection";

    error_log("URL: ".$GLOBALS['API_URL'].$ID_url);

    $response = wp_remote_get(
        $GLOBALS['API_URL'].$ID_url."?search_node_property=ID_Ouvertures&node_property_value=".urlencode($ouverture_ID), array(
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

    $ouvertures = json_decode($response['body'], true);


    if (count($ouvertures['nodes']) > 0) {
        $ouverture = $ouvertures['nodes'][0];
        $node_ID = $ouverture['node_id'];

        error_log("========================================= Get Ouverture ID");
        error_log("");
        error_log("");

        return $node_ID;
    } else {
        error_log("========================================= Get Ouverture ID");
        error_log("");
        error_log("");

        return -1;
    }

}

function create_ouverture($post, $post_id, $label, $token_access):void {
    error_log("");
    error_log("=========================================");
    error_log("              Create ouverture");
    error_log("=========================================");
    error_log("post_id: ".$post_id);
    error_log("label: ".$label);
    error_log("token: ".$token_access);
    error_log("ouverture_ID: ".$post->post_title);
    $Logement_ID = $post->post_title;

    // =================================================================================================================
    //                                                  Create Request
    // =================================================================================================================

    $create_url = "/graph/create_node";

    $complete_url = $GLOBALS['API_URL'].$create_url."?label=".$label;

//    error_log("complete url: ".$complete_url);

    $fields = get_fields($post_id);
    error_log(print_r($fields, true));
    if (gettype($fields) != "array") {
        return;
    }

    $create_body = array(
        'ID_'.ucfirst($label)=>$Logement_ID,
        'ID_Post'=>$post_id,
    );

    $create_body = add_field_to_ouverture_body($create_body, $fields);

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

    // TODO get node id from $create_response
    $response_body = json_decode($create_response['body'], true);
    $node_ID = $response_body["node_id"];

    # Adaptation
    update_relationship(
        $node_ID,
        $label,
        get_field('adaptation', $post_id),
        'ID_Adaptation',
        'adaptation',
        "peut_avoir_une",
        $token_access
    );

    error_log("result: ".print_r($create_response, true));
    error_log("========================================= Create");
    error_log("");
    error_log("");
}

function update_ouverture($node_ID, $post_id, $label, $token_access):void {
    error_log("");
    error_log("=====================================");
    error_log("            Edit ouverture");
    error_log("=====================================");

    // =================================================================================================================
    //                                                  Update Request
    // =================================================================================================================

    $update_url = "/graph/update/".$node_ID;

    $fields = get_fields($post_id);

    $update_body = array(
        'ID_Post'=>$post_id
    );

    $update_body = add_field_to_ouverture_body($update_body, $fields);

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

    # Adaptation
    update_relationship(
        $node_ID,
        $label,
        get_field('adaptation', $post_id),
        'ID_Adaptation',
        'adaptation',
        "peut_avoir_une",
        $token_access
    );

//    error_log('fields: '.print_r(get_fields($post_id), true));

//    error_log("result: ".print_r($update_response, true));
    error_log("========================================= Edit Ouverture");
    error_log("");
    error_log("");
}

function delete_ouverture($node_id, $token_access):void {
    error_log("");
    error_log("=========================================");
    error_log("              Delete Ouverture");
    error_log("=========================================");

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


    // Delete the opening

    $complete_url = $GLOBALS['API_URL']."/graph/delete/".$node_id;

    $delete_header = array(
        'Content-Type' => 'application/json',
        'Accept' => 'application/json',
        'Authorization' => 'bearer '.$token_access
    );

    $args = array(
        'headers' => $delete_header,
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

function add_field_to_ouverture_body($create_body, $fields): array
{
    $field_to_skip = array(
        "adaptation"
    );
    error_log(print_r($fields, true));
    $keys = array_keys($fields);
    foreach ($keys AS $key){
        error_log($key.": ".print_r($fields[$key], true));
        if (!in_array($key, $field_to_skip)){
            if (gettype($fields[$key]) == "array") {
                $array_body = array();
                $array = $fields[$key];
                foreach ($array as $object) {
                    if (gettype($object) == "string" OR
                        gettype($object) == "boolean" OR
                        gettype($object) == "integer" OR
                        gettype($object) == "double"){
                        $array_body[] = $object;
                    }
                    elseif (in_array("url", array_keys($array))){ // Easy array
                        $image_url = $object['url'];
                        $array_body[] = $image_url;
                    } else { // Need to find a string
                        $SUB_KEYS = array_keys($object);
                        $valid_key = "";
                        foreach ($SUB_KEYS as $SUB_KEY){
                            if (gettype($object[$SUB_KEY]) == "string" OR
                                gettype($object[$SUB_KEY]) == "boolean" OR
                                gettype($object[$SUB_KEY]) == "integer" OR
                                gettype($object[$SUB_KEY]) == "double"){
                                $valid_key = $SUB_KEY;
                            }
                        }
                        $array_body[] = $object[$valid_key];
                    }
                }
                $body[$key] = $array_body;
            } else {
                $body[$key] = $fields[$key];
            }
        }
    }

    return $create_body;
}
