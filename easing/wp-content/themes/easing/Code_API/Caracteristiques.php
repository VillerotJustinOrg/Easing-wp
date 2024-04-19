<?php

function Router_caracteristique($post, $post_id, $label, $token_access): void {
    error_log("");
    error_log("===================================================");
    error_log("              Router caracteristique Lieu");
    error_log("===================================================");
    error_log("");

    $Post_Status = $post->post_status;

    $caracteristique_ID = $post->post_title;
    $node_ID = get_caracteristique_id($caracteristique_ID, $token_access);

    error_log("===================================");
    error_log("              Info");
    error_log("Post Status: ".$Post_Status);
    error_log("Label: ".$label);
    error_log("Caracteristique_ID: ".$caracteristique_ID);
    error_log("Node ID: ".$node_ID);
    error_log("===================================");
    error_log("");


    if ($node_ID < 1) {
        create_caracteristique($post, $post_id, $label, $token_access);
    }
    else {
        if ($Post_Status == "publish"){
            update_caracteristique($node_ID, $post_id, $label, $token_access);
        } elseif ($Post_Status == "trash") {
            delete_caracteristique($node_ID, $token_access, $label);
        } else {
            // If you want to do something on draft
            error_log("Draft");
        }
    }
}

function get_caracteristique_id($caracteristique_ID, $token_access){
    error_log("");
    error_log("=========================================");
    error_log("              Get caracteristique ID");
    error_log("=========================================");
    error_log("ID: ".$caracteristique_ID);
    error_log("token: ".$token_access);

    $ID_url = "/graph/read_node_collection";

    error_log("URL: ".$GLOBALS['API_URL'].$ID_url);

    $response = wp_remote_get(
        $GLOBALS['API_URL'].$ID_url."?search_node_property=ID_Caracteristique&node_property_value=".urlencode($caracteristique_ID), array(
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

    $caracteristiques = json_decode($response['body'], true);


    if (count($caracteristiques['nodes']) > 0) {
        $caracteristique = $caracteristiques['nodes'][0];
        $node_ID = $caracteristique['node_id'];

        error_log("========================================= Get caracteristique ID");
        error_log("");
        error_log("");

        return $node_ID;
    } else {
        error_log("========================================= Get caracteristique ID");
        error_log("");
        error_log("");

        return -1;
    }

}

function create_caracteristique($post, $post_id, $label, $token_access):void {
    error_log("");
    error_log("=========================================");
    error_log("              Create caracteristique");
    error_log("=========================================");
    error_log("post_id: ".$post_id);
    error_log("label: ".$label);
    error_log("token: ".$token_access);
    error_log("Caracteristique_ID: ".$post->post_title);
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

    $field_to_skip = array(
        "adaptation",
        "equipements"
    );

    $create_body = body_builder($create_body, $fields, $field_to_skip);

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
        "access_adaptation",
        $token_access
    );

    # Equipements
    update_relationship(
        $node_ID,
        $label,
        get_field('equipements', $post_id),
        'ID_Equipements',
        'equipements',
        "include",
        $token_access
    );

    error_log("result: ".print_r($create_response, true));
    error_log("========================================= Create");
    error_log("");
    error_log("");
}

function update_caracteristique($node_ID, $post_id, $label, $token_access):void {
    error_log("");
    error_log("=====================================");
    error_log("            Edit caracteristique");
    error_log("=====================================");

    // =================================================================================================================
    //                                                  Update Request
    // =================================================================================================================

    $update_url = "/graph/update/".$node_ID;

    $fields = get_fields($post_id);

    $update_body = array(
        'ID_Post'=>$post_id
    );

    $field_to_skip = array(
        "adaptation",
        "equipements"
    );

    $update_body = body_builder($update_body, $fields, $field_to_skip);

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
        "access_adaptation",
        $token_access
    );

    # Equipment
    update_relationship(
        $node_ID,
        $label,
        get_field('equipements', $post_id),
        'ID_Equipements',
        'equipements',
        "include",
        $token_access
    );

//    error_log('fields: '.print_r(get_fields($post_id), true));

//    error_log("result: ".print_r($update_response, true));
    error_log("========================================= Edit caracteristique");
    error_log("");
    error_log("");
}

function delete_caracteristique($node_id, $token_access):void {
    error_log("");
    error_log("=========================================");
    error_log("              Delete caracteristique");
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
