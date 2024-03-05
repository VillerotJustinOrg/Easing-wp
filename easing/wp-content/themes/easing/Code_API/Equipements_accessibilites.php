<?php

function Router_Equipement_access($post, $post_id, $label, $token_access): void {
    error_log("");
    error_log("===================================================");
    error_log("              Router Equipement_access");
    error_log("===================================================");
    error_log("");

    $Post_Status = $post->post_status;

    $Equipement_access_ID = $post->post_title;
    $node_ID = get_Equipement_access_id($Equipement_access_ID, $token_access);

    error_log("===================================");
    error_log("              Info");
    error_log("Post Status: ".$Post_Status);
    error_log("Label: ".$label);
    error_log("Equipement_access_ID: ".$Equipement_access_ID);
    error_log("Node ID: ".$node_ID);
    error_log("===================================");
    error_log("");


    if ($node_ID < 1) {
        create_Equipement_access($post, $post_id, $label, $token_access);
    }
    else {
        if ($Post_Status == "publish"){
            update_Equipement_access($node_ID, $post_id, $token_access);
        } elseif ($Post_Status == "trash") {
            delete_Equipement_access($node_ID, $label, $token_access);
        } else {
            // If you want to do something on draft
            error_log("Invalid post status: ");
        }
    }
}

function get_Equipement_access($Equipement_access_ID, $token_access){
    error_log("");
    error_log("====================================");
    error_log("         Get Equipement_access");
    error_log("====================================");
    error_log("ID: ".$Equipement_access_ID);
    error_log("token: ".$token_access);

    $ID_url = "/graph/read_node_collection";

    error_log("URL: ".$GLOBALS['API_URL'].$ID_url);

    $response = wp_remote_get(
        $GLOBALS['API_URL'].$ID_url."?search_node_property=ID_Equipement_access&node_property_value=".urlencode($Equipement_access_ID), array(
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

    $Equipement_accesss = json_decode($response['body'], true);


    if (count($Equipement_accesss['nodes']) > 0) {
        error_log("========================================= Get Equipement_access");
        error_log("");
        error_log("");

        return $Equipement_accesss['nodes'][0];
    } else {
        error_log("========================================= Get Equipement_access");
        error_log("");
        error_log("");

        return -1;
    }

}

function get_Equipement_access_id($Equipement_access_ID, $token_access){
    error_log("");
    error_log("=========================================");
    error_log("              Get Equipement_access ID");
    error_log("=========================================");
    error_log("ID: ".$Equipement_access_ID);
    error_log("token: ".$token_access);

    $Equipement_access = get_Equipement_access($Equipement_access_ID, $token_access);

    if ($Equipement_access != -1) {
        $node_ID = $Equipement_access['node_id'];

        error_log("========================================= Get Equipement_access ID");
        error_log("");
        error_log("");

        return $node_ID;
    } else {
        error_log("========================================= Get Equipement_access ID");
        error_log("");
        error_log("");

        return -1;
    }
}

function create_Equipement_access($post, $post_id, $label, $token_access):void {
    error_log("");
    error_log("=========================================");
    error_log("              Create Equipement_access");
    error_log("=========================================");
    error_log("post_id: ".$post_id);
    error_log("label: ".$label);
    error_log("token: ".$token_access);
    error_log("Equipement_access_ID: ".$post->post_title);
    $Equipement_access_ID = $post->post_title;

    // =================================================================================================================
    //                                                  Create Request
    // =================================================================================================================

    $create_url = "/graph/create_node";

    $complete_url = $GLOBALS['API_URL'].$create_url."?label=".urlencode($label);

//    error_log("complete url: ".$complete_url);

    $create_body = array(
        'ID_Equipement_access'=>$Equipement_access_ID,
        'ID_Post'=>$post_id
    );

    $fields =get_fields($post_id);
    if (gettype($fields) == "array"){
        $keys = array_keys($fields);
        foreach ($keys as $key){
            error_log($key.": ".print_r($fields[$key], true));
            if ($key != "type_de_piece")
                // Array
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
                    $create_body[$key] = $array_body;
                } else {
                    $create_body[$key] = $fields[$key];
                }
        }
    }

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

    update_link_piece(
        json_decode($create_response['body'], true)['node_id'],
        get_field('type_de_piece', $post_id),
        $token_access);

    error_log("result: ".print_r($create_response, true));
    error_log("========================================= Create");
    error_log("");
    error_log("");
}

function update_Equipement_access($node_ID, $post_id, $token_access):void {
    error_log("");
    error_log("=====================================");
    error_log("            Edit Equipement_access");
    error_log("=====================================");

    // =================================================================================================================
    //                                                  Update Request
    // =================================================================================================================

    $update_url = "/graph/update/".$node_ID;


    $update_body = array('ID_Post'=>$post_id);

    $fields =get_fields($post_id);
    if (gettype($fields) =="array"){
        $keys = array_keys($fields);
        foreach ($keys as $key){
            error_log($key.": ".print_r($fields[$key], true));
            if ($key != "type_de_piece") {
                // Array
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
                    $update_body[$key] = $array_body;
                } else {
                    $update_body[$key] = $fields[$key];
                }
            }
        }

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
//        error_log("result: ".print_r($update_response, true));

        update_link_piece($node_ID, get_field('type_de_piece', $post_id), $token_access);

    }

    error_log("========================================= Edit Equipement_access");
    error_log("");
    error_log("");
}

function update_link_piece($equipment_node_ID, $info_piece_type, $token_access):void
{
    error_log("");
    error_log("equipment_node_ID: ".$equipment_node_ID);
    error_log("info_piece_type: ".print_r($info_piece_type, true));
    error_log("");

    // GET Node ID of the room

    if (!$info_piece_type){
        error_log("No piece");
        return;
    }

    $room = $info_piece_type[0];
    $room_id = $room->post_title;

    $ID_url = "/graph/read_node_collection";

    error_log("URL: ".$GLOBALS['API_URL'].$ID_url);

    $response = wp_remote_get(
        $GLOBALS['API_URL'].$ID_url."?search_node_property=ID_Piece&node_property_value=".urlencode($room_id), array(
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

    $room_node_ID = json_decode($response['body'], true)['nodes'][0]['node_id'];

    // Create Relationship is_in between the equipment and the room

    $node_equipement = array("ID"=>$equipment_node_ID, "Label"=>"equipement_access");
    $room_node = array("ID"=>$room_node_ID, "Label"=>"piece");


    update_relationship_between_node($node_equipement, array($room_node), "is_in", $token_access);


}

function delete_Equipement_access($node_id, $label, $token_access):void {
    error_log("");
    error_log("=========================================");
    error_log("              Delete Equipement_access");
    error_log("=========================================");

    // DELETE all RELATIONSHIP

    // Delete all relationship linked to the logement
    $DEL_All_R_URL = "/graph/delete_all_relationship/$node_id";

    $header = array(
        'Content-Type'=>'application/json',
        'Accept' => 'application/json',
        'Authorization' => 'bearer '.$token_access
    );

    $body = array(
        "label"=>$label
    );

    $args = array(
        'headers' => $header,
        'body' => json_encode($body),
        'method' => 'POST'
    );

    $relationship_response = wp_remote_request( $GLOBALS['API_URL'].$DEL_All_R_URL, $args);
    if( is_wp_error($relationship_response) ) {
        error_log("Error");
    }

    error_log(print_r($relationship_response, true));

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
