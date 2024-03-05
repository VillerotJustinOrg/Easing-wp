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

    $complete_url = $GLOBALS['API_URL'].$create_url."?label=logement";

//    error_log("complete url: ".$complete_url);

    $fields = get_fields($post_id);
    error_log(print_r($fields, true));
    if (gettype($fields) != "array") {
        return;
    }

    $create_body = array(
        'ID_Logement'=>$Logement_ID,
        'ID_Post'=>$post_id,
    );

    $create_body = add_field_to_logement_body($create_body, $fields);

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

    $fields = get_fields($post_id);

    $update_body = array(
        'ID_Post'=>$post_id
    );

    $update_body = add_field_to_logement_body($update_body, $fields);

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

    # Restriction
    update_relationship(
        $node_ID,
        get_field('restrictions', $post_id),
        'ID_Restriction',
        'restriction',
        "forbidden",
        $token_access
    );

    # Services de proximité
    update_relationship(
        $node_ID,
        get_field('services_de_proximite', $post_id),
        'ID_Service_Proximite',
        'service_de_proximite',
        'disponible',
        $token_access
    );

    # Équipements d'accessibilité
    update_relationship(
        $node_ID,
        get_field('equipements_daccessibilite', $post_id),
        'ID_Equipement_access',
        'equipement_access',
        'available',
        $token_access
    );

    # Services Domotique
    update_relationship(
        $node_ID,
        get_field('services_domotique', $post_id),
        'ID_Service_Domotique',
        'service_domotique',
        'service_domotique',
        $token_access
    );

    # Proprietaire
    update_relationship(
        $node_ID,
        get_field('propretaire', $post_id),
        'ID_Proprietaire',
        'proprietaire',
        'Owned_by',
        $token_access
    );

    # Proprietaire
    update_relationship(
        $node_ID,
        get_field('equipements_domotique', $post_id),
        'ID_Equipement_domotique',
        'equipement_domotique',
        'equipements_domotique',
        $token_access
    );

    # Type de propriété
    update_relationship(
        $node_ID,
        get_field('type_de_propriete', $post_id),
        'ID_Type_Propriete',
        'type_propriete',
        'type',
        $token_access
    );

//    contient_pieces
    # Pieces
    update_relationship(
        $node_ID,
        get_field('contient_pieces', $post_id),
        'ID_Piece',
        'piece',
        'contain',
        $token_access
    );


    // Logement 3D Visit
    $Visite_ZIP_Data = get_field('3D_Visit', $post_id);
    Visit_3D_Treatment($Visite_ZIP_Data, $token_access);


//    error_log('fields: '.print_r(get_fields($post_id), true));

//    error_log("result: ".print_r($update_response, true));
    error_log("========================================= Edit Logement");
    error_log("");
    error_log("");
}

function update_relationship($logement_node_ID, $info_object_to_connect, $object_id_label, $label, $relationship_type, $token_access): void{
    error_log("---------------------------");
    error_log("logement_node_ID: ".$logement_node_ID);
    error_log("Objects to connect to: ".print_r($info_object_to_connect, true));
    error_log("object_id_label: ".$object_id_label);
    error_log("Relationship Type: ".$relationship_type);
    error_log("---------------------------");

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
        error_log("Object ID: ".$object_to_connect_id);
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


        error_log(print_r($response, true));

        $nodes = json_decode($response['body'], true)['nodes'];

        if (count($nodes)){
            $object_node_ID = $nodes[0]['node_id'];

            // Create Relationship is_in between the equipment and the room

            $nodes_to_connect_to[] = array("ID"=>$object_node_ID, "Label"=>$label);
        }
    }

    $node_logement = array("ID"=>$logement_node_ID, "Label"=>"logement");

    update_relationship_between_node($node_logement, $nodes_to_connect_to, $relationship_type, $token_access);
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

function add_field_to_logement_body($create_body, $fields): array
{
    $field_to_skip = array(
        "equipements_domotique",
        "services_domotique",
        "equipements_daccessibilite",
        "services_de_proximite",
        "restrictions",
        "type_de_propriete",
        "propretaire",
        "contient_pieces",
        "test_file"
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

function Visit_3D_Treatment($Visite_ZIP_Data): void
{
    error_log("====================================================================");
    error_log("                         Visit 3D Treatment");
    error_log("====================================================================");
    if ($Visite_ZIP_Data == null) return;
    error_log("Visite_ZIP_Data: ".print_r($Visite_ZIP_Data, true));
    $file_url = wp_get_attachment_url($Visite_ZIP_Data['ID'] );
    $root = $_SERVER["DOCUMENT_ROOT"];
    $destination = $root.'/easing/wp-content/themes/easing/3D_Visits/'.$Visite_ZIP_Data['ID'];
    error_log("file_url: ".print_r($file_url, true));
    error_log("destination: ".print_r($destination, true));
    error_log("====================================================================");

    // TODO unzip

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

            error_log('ZIP file extracted successfully.');
            // Clean up: delete the temporary file
            unlink($tempFile);
            return true;
        } else {
            // Clean up: delete the temporary file
            unlink($tempFile);
            error_log('Failed to open the ZIP file.');
            return false;
        }
    } else {
        error_log('Failed to retrieve ZIP file from URL.');
        return false;
    }
}

function create_directory($directory): void
{
    // Check if the directory doesn't exist already
    if (!file_exists($directory)) {
        // Create the directory
        error_log($directory);
        if (mkdir($directory, 0755, true)) {
            error_log("Directory created successfully.");
        } else {
            error_log("Failed to create directory.");
        }
    } else {
        error_log("Directory already exists.");
    }
}