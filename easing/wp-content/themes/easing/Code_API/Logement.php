<?php

require_once "Proprietaire.php";

function Router_logement($post, $post_id, $label, $token_access): void {
    error_log("");
    error_log("===================================================");
    error_log("              Router Logement");
    error_log("===================================================");
    error_log("");

    $Post_Status = $post->post_status;

    $Logement_ID = $post->post_title;
    $node_ID = get_logement_id($Logement_ID, $token_access);

    error_log("===================================");
    error_log("              Info");
    error_log("Post Status: ".$Post_Status);
    error_log("Label: ".$label);
    error_log("Logement_ID: ".$Logement_ID);
    error_log("Node ID: ".$node_ID);
    error_log("===================================");
    error_log("");


    if ($node_ID < 1) {
        create_logement($post, $post_id, $label, $token_access);
    }
    else {
        if ($Post_Status == "publish"){
            update_logement($node_ID, $post_id, $label, $token_access);
        } elseif ($Post_Status == "trash") {
            delete_logement($node_ID, $token_access, $label);
        } else {
            // If you want to do something on draft
            error_log("Draft");
        }
    }
}

function get_logement_id($Logement_ID, $token_access){
    error_log("");
    error_log("=========================================");
    error_log("              Get Logement ID");
    error_log("=========================================");
    error_log("ID: ".$Logement_ID);
    error_log("token: ".$token_access);

    $ID_url = "/graph/read_node_collection";

    error_log("URL: ".$GLOBALS['API_URL'].$ID_url);

    $response = wp_remote_get(
        $GLOBALS['API_URL'].$ID_url."?search_node_property=ID_Logement&node_property_value=".urlencode($Logement_ID), array(
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

    $logements = json_decode($response['body'], true);


    if (count($logements['nodes']) > 0) {
        $logement = $logements['nodes'][0];
        $node_ID = $logement['node_id'];

        error_log("========================================= Get Logement ID");
        error_log("");
        error_log("");

        return $node_ID;
    } else {
        error_log("========================================= Get Logement ID");
        error_log("");
        error_log("");

        return -1;
    }

}

function create_logement($post, $post_id, $label, $token_access):void {
    error_log("");
    error_log("=========================================");
    error_log("              Create logement");
    error_log("=========================================");
    error_log("post_id: ".$post_id);
    error_log("label: ".$label);
    error_log("token: ".$token_access);
    error_log("Logement_ID: ".$post->post_title);
    $Logement_ID = $post->post_title;

    // =================================================================================================================
    //                                                  Create Request
    // =================================================================================================================

    $create_url = "/graph/create_node";

    $complete_url = $GLOBALS['API_URL'].$create_url."?label=".urlencode($label);

//    error_log("complete url: ".$complete_url);

    $image_body = array();
    $images = get_field('photos', $post_id);
    if (gettype($images) == "array") {
        foreach ($images as $image) {
            $image_url = $image['url'];
//        error_log("url: ".$image_url);
            $image_body[] = $image_url;
        }
    }

    $create_body = array(
        'Statut'=>get_field('statut', $post_id),
        'Titre'=>get_field('titre', $post_id),
        'Description'=>get_field('description', $post_id),
        'Region'=>get_field('region', $post_id),
        'Latitude'=>get_field('latitude', $post_id),
        'Prix_nuit'=>get_field('prix_nuit', $post_id),
        'Nombre_personnes'=>get_field('nombre_personnes', $post_id),
        'Longitude'=>get_field('longitude', $post_id),
        'Ville'=>get_field('ville', $post_id),
        'Photos'=>$image_body,
        'ID_Logement'=>$Logement_ID
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

function update_logement($node_ID, $post_id, $label, $token_access):void {
    error_log("");
    error_log("=====================================");
    error_log("            Edit logement");
    error_log("=====================================");

    // =================================================================================================================
    //                                                  Update Request
    // =================================================================================================================

    $update_url = "/graph/update/".$node_ID;

    $image_body = array();
    $images = get_field('photos', $post_id);
    if (gettype($images) == "array") {
        foreach ($images as $image) {
            $image_url = $image['url'];
//        error_log("url: ".$image_url);
            $image_body[] = $image_url;
        }
    }

    $update_body = array(
        'Statut'=>get_field('statut', $post_id),
        'Titre'=>get_field('titre', $post_id),
        'Description'=>get_field('description', $post_id),
        'Region'=>get_field('region', $post_id),
        'Latitude'=>get_field('latitude', $post_id),
        'Prix_nuit'=>get_field('prix_nuit', $post_id),
        'Nombre_personnes'=>get_field('nombre_personnes', $post_id),
        'Longitude'=>get_field('longitude', $post_id),
        'Ville'=>get_field('ville', $post_id),
        'Photos'=>$image_body
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


    update_relationship_pop($node_ID, $label, $token_access);


//    error_log("result: ".print_r($update_response, true));
    error_log("========================================= Edit Logement");
    error_log("");
    error_log("");
}

function update_relationship_pop($node_ID, $label, $token_access): void
{
    error_log("=====================================");
    error_log('update_relationship');

    // Propriétaire

    // Recover propriétaire ID
    $Proprietaire_ID = get_field("propretaire")[0]->post_title;

//    error_log("Prop ID: ". $Proprietaire_ID);


    $Proprietaire = get_Proprietaire($Proprietaire_ID, $token_access);

    if ($Proprietaire == -1) {
        error_log("ERROR");
    }

    $Proprietaire_Node_ID = $Proprietaire['node_id'];

    // Check  if there is already a relationship between this logement and this propriétaire

    $Prop_URL = "/graph/read_relationship_btwn_node/?node_id1=$node_ID&node_id2=$Proprietaire_Node_ID";

    $Prop_header = array(
        'Accept' => 'application/json',
        'Authorization' => 'bearer '.$token_access
    );

    $args = array(
        'headers' => $Prop_header
    );

    $response = wp_remote_get($GLOBALS['API_URL'].$Prop_URL, $args);

    if( is_wp_error( $response ) ) {
        error_log("Error");
    }

//    error_log("result: ".$response["body"]);

    $relationship_to_keep = null;

    if ($response["body"] == -1) {
        // Creation of relationship

        $source_node = array(
            'label'=>"logement",
            'id'=>$node_ID
        );

        $target_node = array(
            'label'=>"proprietaire",
            'id'=>$Proprietaire_Node_ID
        );

        $body = array(
            'relationship_type'=>"Owned_by",
            'relationship_attributes'=>array(),
            'source_node'=>$source_node,
            'target_node'=>$target_node
        );

        $encoded_body = json_encode($body);

        $header = array(
            'Content-Type'=>'application/json',
            'Accept' => 'application/json',
            'Authorization' => 'bearer '.$token_access
        );

        $args = array(
            'headers' => $header,
            'body' => $encoded_body,
            'method' => 'POST'
        );

        $Create_URL = "/graph/create_relationship";

        $create_response = wp_remote_request($GLOBALS['API_URL'].$Create_URL, $args);

        if( is_wp_error( $create_response ) ) {
            error_log("Error");
        }

//        error_log("result: ".print_r($create_response, true));

        $data = json_decode($create_response["body"], true);
        $relationship_to_keep = $data["relationship_id"];
    }

    // Removing all old relationship.

    $cypher ="MATCH (startNode)-[r:Owned_by]->() WHERE id(startNode) = $node_ID AND id(r) <> $relationship_to_keep DELETE r";
    $header = array(
        'Content-Type'=>'application/json',
        'Accept' => 'application/json',
        'Authorization' => 'bearer '.$token_access
    );
    $args = array(
        'headers' => $header,
        'body' => json_encode(array("cypher_string"=>$cypher)),
        'method' => 'POST'
    );
    $result = wp_remote_request($GLOBALS['API_URL'].'/q', $args);


}

function update_relationship($logement_node_ID, $info_object_to_connect, $object_id_label, $relationship_type, $token_access): void{
    error_log("");
    error_log("logement_node_ID: ".$logement_node_ID);
    error_log("Objects to connect to: ".print_r($info_object_to_connect, true));
    error_log("");

    // GET Node ID of the room

    if (!$info_object_to_connect){
        error_log("No piece");
        return;
    }

    $nodes_to_connect_to = array();

    $ID_url = "/graph/read_node_collection";
    error_log("URL: ".$GLOBALS['API_URL'].$ID_url);
    foreach ($info_object_to_connect as $object_to_connect){
        $object_to_connect_id = $object_to_connect->post_title;

        $response = wp_remote_get(
            $GLOBALS['API_URL'].$ID_url."?search_node_property=".urlencode($object_id_label)."&node_property_value=".urlencode($object_to_connect_id),
            array(
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


//        error_log(print_r($response, true));

        $object_node_ID = json_decode($response['body'], true)['nodes'][0]['node_id'];

        // Create Relationship is_in between the equipment and the room

        $nodes_to_connect_to[] = array("ID"=>$object_node_ID, "Label"=>"piece");

    }

    $node_logement = array("ID"=>$logement_node_ID, "Label"=>"logement");

    update_relationship_between_node($node_logement, array($nodes_to_connect_to), $relationship_type, $token_access);
}


function delete_logement($node_id, $token_access):void {
    error_log("");
    error_log("=========================================");
    error_log("              Delete Logement");
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


    // Delete the logement

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

function filter_logement(){
    // TODO Call API Filter
}

function AI_Recommandation() {
    // TODO Call API for AI recommandation
}


