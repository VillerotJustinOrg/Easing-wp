<?php

function Router_logement($post, $post_id, $label, $token_access): void {
    //error_log("");
    //error_log("===================================================");
    //error_log("              Router Logement");
    //error_log("===================================================");
    //error_log("");

    $Post_Status = $post->post_status;

    $Logement_ID = $post->post_title;
    $node_ID = get_logement_id($Logement_ID, $token_access);

    //error_log("===================================");
    //error_log("              Info");
    //error_log("Post Status: ".$Post_Status);
    //error_log("Label: ".$label);
    //error_log("Logement_ID: ".$Logement_ID);
    //error_log("Node ID: ".$node_ID);
    //error_log("===================================");
    //error_log("");


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
            //error_log("Draft");
        }
    }
}

function get_logement_id($Logement_ID, $token_access){
    //error_log("");
    //error_log("=========================================");
    //error_log("              Get Logement ID");
    //error_log("=========================================");
    //error_log("ID: ".$Logement_ID);
    //error_log("token: ".$token_access);

    $ID_url = "/graph/read_node_collection";

    //error_log("URL: ".$GLOBALS['API_URL'].$ID_url);

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
        //error_log("Error");
    }

//    //error_log(print_r($response, true));

    $logements = json_decode($response['body'], true);


    if (count($logements['nodes']) > 0) {
        $logement = $logements['nodes'][0];
        $node_ID = $logement['node_id'];

        //error_log("========================================= Get Logement ID");
        //error_log("");
        //error_log("");

        return $node_ID;
    } else {
        //error_log("========================================= Get Logement ID");
        //error_log("");
        //error_log("");

        return -1;
    }

}

function create_logement($post, $post_id, $label, $token_access):void {
    //error_log("");
    //error_log("=========================================");
    //error_log("              Create logement");
    //error_log("=========================================");
    //error_log("post_id: ".$post_id);
    //error_log("label: ".$label);
    //error_log("token: ".$token_access);
    //error_log("Logement_ID: ".$post->post_title);
    $Logement_ID = $post->post_title;

    // =================================================================================================================
    //                                                  Create Request
    // =================================================================================================================

    $create_url = "/graph/create_node";

    $complete_url = $GLOBALS['API_URL'].$create_url."?label=logement";

//    //error_log("complete url: ".$complete_url);

    $fields = get_fields($post_id);
    //error_log(print_r($fields, true));
    if (gettype($fields) != "array") {
        return;
    }

    $create_body = array(
        'ID_Logement'=>$Logement_ID,
        'ID_Post'=>$post_id,
    );

    $field_to_skip = array(
        "proprietaire",
        "adaptations",
        "equipements",
        "piece_lieu",
        "caracteristiques"
    );

    $create_body = body_builder($create_body, $fields, $field_to_skip);

    if (!isset($create_body["accepte_enfant"])) {
        $create_body["accepte_enfant"] = false;
    }

    if (!isset($create_body["accepte_bebe"])) {
        $create_body["accepte_bebe"] = false;
    }

    if (!isset($create_body["effets_personnels"])) {
        $create_body["effets_personnels"] = false;
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

    $response_body = json_decode($create_response['body'], true);
    $node_ID = $response_body["node_id"];

    # Pièces
    update_relationship(
        $node_ID,
        $label,
        get_field('pieces', $post_id),
        'ID_Piece_lieu',
        'piece_lieu',
        "has_piece_lieu",
        $token_access
    );

    # Équipements
    update_relationship(
        $node_ID,
        $label,
        get_field('equipements', $post_id),
        'ID_Equipements',
        'equipements',
        "has_equipements",
        $token_access
    );

    # Adaptations
    update_relationship(
        $node_ID,
        $label,
        get_field('adaptations', $post_id),
        'ID_Adaptation',
        'adaptation',
        "has_adaptations",
        $token_access
    );

    # Propriétaire
    update_relationship(
        $node_ID,
        $label,
        get_field('proprietaire', $post_id),
        'ID_Proprietaire',
        'proprietaire',
        "owned_by",
        $token_access
    );

    # Characteristics
    update_relationship(
        $node_ID,
        $label,
        get_field('caracteristiques', $post_id),
        'ID_Caracteristique',
        'caracteristique',
        "owned_by",
        $token_access
    );

    //error_log("result: ".print_r($create_response, true));
    //error_log("========================================= Create");
    //error_log("");
    //error_log("");
}

function update_logement($node_ID, $post_id, $label, $token_access):void {
    //error_log("");
    //error_log("=====================================");
    //error_log("            Edit logement");
    //error_log("=====================================");

    // =================================================================================================================
    //                                                  Update Request
    // =================================================================================================================

    $update_url = "/graph/update/".$node_ID;

    $fields = get_fields($post_id);

    $update_body = array(
        'ID_Post'=>$post_id
    );

    $field_to_skip = array(
        "proprietaire",
        "adaptations",
        "equipements",
        "piece_lieu",
        "caracteristiques"
    );

    $update_body = body_builder($update_body, $fields, $field_to_skip);

    if (!isset($update_body["accepte_enfant"])) {
        $update_body["accepte_enfant"] = false;
    }

    if (!isset($update_body["accepte_bebe"])) {
        $update_body["accepte_bebe"] = false;
    }

    if (!isset($update_body["effets_personnels"])) {
        $update_body["effets_personnels"] = false;
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

    # Pièces
    update_relationship(
        $node_ID,
        $label,
        get_field('pieces', $post_id),
        'ID_Piece_lieu',
        'piece_lieu',
        "has_piece_lieu",
        $token_access
    );

    # Équipements
    update_relationship(
        $node_ID,
        $label,
        get_field('equipements', $post_id),
        'ID_Equipements',
        'equipements',
        "has_equipements",
        $token_access
    );

    # Adaptations
    update_relationship(
        $node_ID,
        $label,
        get_field('adaptations', $post_id),
        'ID_Adaptation',
        'adaptation',
        "has_adaptations",
        $token_access
    );

    # Propriétaire
    update_relationship(
        $node_ID,
        $label,
        get_field('proprietaire', $post_id),
        'ID_Proprietaire',
        'proprietaire',
        "Owned_by",
        $token_access
    );

    # Characteristics
    update_relationship(
        $node_ID,
        $label,
        get_field('caracteristiques', $post_id),
        'ID_Caracteristique',
        'caracteristique',
        "has_caracteristiques",
        $token_access
    );


    // Logement 3D Visit
    $Visite_ZIP_Data = get_field('3D_Visit', $post_id);
    Visit_3D_Treatment($Visite_ZIP_Data, $token_access);


//    //error_log('fields: '.print_r(get_fields($post_id), true));

//    //error_log("result: ".print_r($update_response, true));
    //error_log("========================================= Edit Logement");
    //error_log("");
    //error_log("");
}

function delete_logement($node_id, $token_access):void {
    //error_log("");
    //error_log("=========================================");
    //error_log("              Delete Logement");
    //error_log("=========================================");

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
        //error_log("Error");
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
        //error_log("Error");
    }

    //error_log("Delete Result: ".print_r(json_decode($delete_response['body'], true), true));

    //error_log("========================================= Delete");
    //error_log("");
    //error_log("");

}

function Visit_3D_Treatment($Visite_ZIP_Data): void
{
    //error_log("====================================================================");
    //error_log("                         Visit 3D Treatment");
    //error_log("====================================================================");
    if ($Visite_ZIP_Data == null) return;
    //error_log("Visite_ZIP_Data: ".print_r($Visite_ZIP_Data, true));
    $file_url = wp_get_attachment_url($Visite_ZIP_Data['ID'] );
    $root = $_SERVER["DOCUMENT_ROOT"];
    $destination = $root.'/easing/wp-content/themes/easing/3D_Visits/'.$Visite_ZIP_Data['ID'];
    //error_log("file_url: ".print_r($file_url, true));
    //error_log("destination: ".print_r($destination, true));
    //error_log("====================================================================");

    extract_zip($file_url, $destination);

}

function extract_zip($zipUrl, $extractPath): bool
{
    // Create Directory if didn't exist
    create_directory($extractPath);

    // Get the ZIP file contents
    $zipContents = file_get_contents($zipUrl);

    if ($zipContents !== false) {
        // Create a temporary file to store the ZIP contents
        $tempFile = tempnam(sys_get_temp_dir(), 'zip');

        // Write the ZIP contents to the temporary file
        file_put_contents($tempFile, $zipContents);

        // Create a new ZipArchive instance
        $zip = new ZipArchive();

        // Open the temporary file
        if ($zip->open($tempFile) === true) {
            // Extract the contents to the specified directory
            $zip->extractTo($extractPath);

            // Close the ZipArchive
            $zip->close();

            //error_log('ZIP file extracted successfully.');
            // Clean up: delete the temporary file
            unlink($tempFile);
            return true;
        } else {
            // Clean up: delete the temporary file
            unlink($tempFile);
            //error_log('Failed to open the ZIP file.');
            return false;
        }
    } else {
        //error_log('Failed to retrieve ZIP file from URL.');
        return false;
    }
}

function create_directory($directory): void
{
    // Check if the directory doesn't exist already
    if (!file_exists($directory)) {
        // Create the directory
        //error_log($directory);
        if (mkdir($directory, 0755, true)) {
            //error_log("Directory created successfully.");
        } else {
            //error_log("Failed to create directory.");
        }
    } else {
        //error_log("Directory already exists.");
    }
}