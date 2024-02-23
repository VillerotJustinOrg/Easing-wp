<?php

function Router_Proprietaire($post, $post_id, $label, $token_access): void {
    error_log("");
    error_log("===================================================");
    error_log("              Router Proprietaire");
    error_log("===================================================");
    error_log("");

    $Post_Status = $post->post_status;

    $Proprietaire_ID = $post->post_title;
    $node_ID = get_Proprietaire_id($Proprietaire_ID, $token_access);

    error_log("===================================");
    error_log("              Info");
    error_log("Post Status: ".$Post_Status);
    error_log("Label: ".$label);
    error_log("Proprietaire_ID: ".$Proprietaire_ID);
    error_log("Node ID: ".$node_ID);
    error_log("===================================");
    error_log("");


    if ($node_ID < 1) {
        create_Proprietaire($post, $post_id, $label, $token_access);
    }
    else {
        if ($Post_Status == "publish"){
            update_Proprietaire($node_ID, $post_id, $token_access);
        } elseif ($Post_Status == "trash") {
            delete_Proprietaire($node_ID, $token_access);
        } else {
            // If you want to do something on draft
            error_log("Draft");
        }
    }
}

function get_Proprietaire($Proprietaire_ID, $token_access){
    error_log("");
    error_log("====================================");
    error_log("         Get Proprietaire");
    error_log("====================================");
    error_log("ID: ".$Proprietaire_ID);
    error_log("token: ".$token_access);

    $ID_url = "/graph/read_node_collection";

    error_log("URL: ".$GLOBALS['API_URL'].$ID_url);

    $response = wp_remote_get(
        $GLOBALS['API_URL'].$ID_url."?search_node_property=ID_Proprietaire&node_property_value=".urlencode($Proprietaire_ID), array(
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

    $Proprietaires = json_decode($response['body'], true);


    if (count($Proprietaires['nodes']) > 0) {
        error_log("========================================= Get Proprietaire");
        error_log("");
        error_log("");

        return $Proprietaires['nodes'][0];
    } else {
        error_log("========================================= Get Proprietaire");
        error_log("");
        error_log("");

        return -1;
    }

}


function get_Proprietaire_id($Proprietaire_ID, $token_access){
    error_log("");
    error_log("=========================================");
    error_log("              Get Proprietaire ID");
    error_log("=========================================");
    error_log("ID: ".$Proprietaire_ID);
    error_log("token: ".$token_access);

    $Proprietaire = get_Proprietaire($Proprietaire_ID, $token_access);

    if ($Proprietaire != -1) {
        $node_ID = $Proprietaire['node_id'];

        error_log("========================================= Get Proprietaire ID");
        error_log("");
        error_log("");

        return $node_ID;
    } else {
        error_log("========================================= Get Proprietaire ID");
        error_log("");
        error_log("");

        return -1;
    }
}

function create_Proprietaire($post, $post_id, $label, $token_access):void {
    error_log("");
    error_log("=========================================");
    error_log("              Create Proprietaire");
    error_log("=========================================");
    error_log("post_id: ".$post_id);
    error_log("label: ".$label);
    error_log("token: ".$token_access);
    error_log("Proprietaire_ID: ".$post->post_title);
    $Proprietaire_ID = $post->post_title;

    // =================================================================================================================
    //                                                  Create Request
    // =================================================================================================================

    $create_url = "/graph/create_node";

    $complete_url = $GLOBALS['API_URL'].$create_url."?label=".urlencode($label);

//    error_log("complete url: ".$complete_url);

    $create_body = array(
        'nom'=>get_field('nom', $post_id),
        'prenom'=>get_field('prenom', $post_id),
        'numero_de_telephone'=>get_field('numero_de_telephone', $post_id),
        'adresse'=>get_field('adresse', $post_id),
        'ID_Proprietaire'=>$Proprietaire_ID
    );

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

function update_Proprietaire($node_ID, $post_id, $token_access):void {
    error_log("");
    error_log("=====================================");
    error_log("            Edit Proprietaire");
    error_log("=====================================");

    // =================================================================================================================
    //                                                  Update Request
    // =================================================================================================================

    $update_url = "/graph/update/".$node_ID;


    $update_body = array(
        'nom'=>get_field('nom', $post_id),
        'prenom'=>get_field('prenom', $post_id),
        'numero_de_telephone'=>get_field('numero_de_telephone', $post_id),
        'adresse'=>get_field('adresse', $post_id),
        'mail'=>get_field('mail', $post_id)
    );

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
    error_log("========================================= Edit Proprietaire");
    error_log("");
    error_log("");
}

function delete_Proprietaire($node_id, $token_access):void {
    error_log("");
    error_log("=========================================");
    error_log("              Delete Proprietaire");
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
