<?php


// =======================================================================
//                             API Connection
// =======================================================================

function get_API_Token() {
    //error_log("=========================================");
    //error_log("              Get API token");
    //error_log("=========================================");

    //TODO get login and passwd from .env and api url
    $login = getenv('LOGIN');
    $passwd = getenv('PASSWD');
    $API_URL = getenv('API_URL');
    $auth_url = "/auth/token";

    //error_log("|".$login."|");
    //error_log("|".$passwd."|");
    //error_log("|".$API_URL."|");

    $complete_url = $API_URL.$auth_url;

    // Data to be sent in the request body
    $body = http_build_query(array(
        "username" => $login,
        "password" => $passwd
    ));

    // Headers for the request
    $headers = array(
        "Content-Type: application/x-www-form-urlencoded",
        "Accept: application/json"
    );

    // Initialize cURL session
    $ch = curl_init();

// Set cURL options
    curl_setopt($ch, CURLOPT_URL, $complete_url);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

// Execute cURL session
    $response = curl_exec($ch);

// Check for errors
    if ($response === false) {
        $error = curl_error($ch);
        error_log("cURL Error: " . $error);
        $result_data = "cURL Error: " . $error;
    } else {
        // Decode JSON response
        $result_data = json_decode($response, true);
        // Log token or do other processing
        error_log('token: ' . print_r($result_data, true));
        //error_log("=========================================");
    }

    // Close cURL session
    curl_close($ch);

    // Return result data if needed
    return $result_data;
}

// =======================================================================
//                                Other
// =======================================================================


function add_field_info_to_body($body, $fields)
{
    $special_fields = [];
    //error_log("add_field_info_to_body: ".print_r($fields, true));
    $keys = array_keys($fields);
    foreach ($keys as $key){
        //error_log($key.": ".print_r($fields[$key]), true);
        if (in_array($key, $special_fields)) {
            continue;
        }

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

    return $body;
}

function update_relationship($source_node_ID, $source_label, $info_targets, $target_id_label, $target_label, $relationship_type, $token_access): void{
    //error_log("---------------------------");
    //error_log("Source_node_ID: ".$source_node_ID);
    //error_log("Objects to connect to: ".print_r($info_targets, true));
    //error_log("object_id_label: ".$target_id_label);
    //error_log("Relationship Type: ".$relationship_type);
    //error_log("---------------------------");

    // GET Node ID of the room

    if (!$info_targets){
        //error_log("No object to connect to");
        return;
    }

    $target_nodes = array();

    $ID_url = "/graph/read_node_collection";
    //error_log("URL: ".$GLOBALS['API_URL'].$ID_url);
    foreach ($info_targets as $object_to_connect){
        $target_id = $object_to_connect->post_title;
        //error_log("Target ID: ".$target_id);
        $response = wp_remote_get(
            $GLOBALS['API_URL'].$ID_url."?search_node_property=".urlencode($target_id_label)."&node_property_value=".urlencode($target_id),
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

        $nodes = json_decode($response['body'], true)['nodes'];


        if (count($nodes)){
            $target_node_ID = $nodes[0]['node_id'];

            // Create Relationship is_in between the equipment and the room

            $target_nodes[] = array("ID"=>$target_node_ID, "Label"=>$target_label);
        }
    }

    $source_node = array("ID"=>$source_node_ID, "Label"=>$source_label);

    update_relationship_between_node($source_node, $target_nodes, $relationship_type, $token_access);
}

function update_relationship_between_node($source_node, $target_nodes, $relationship_type, $token_access): void
{
    //error_log("=====================================");
    //error_log('Update Relationship Between Two Node');

//    $node_ID1

    // Check  if there is already a relationship between this logement and this propriétaire

    $relationship_to_keep = array();

    foreach ($target_nodes as $target_node){

        //error_log("target node: ".print_r($target_node, true));
        //error_log("check_relationship_between_node");
        $relationship_between_node = check_relationship_between_node($source_node['ID'], $target_node['ID'], $token_access);

        if ($relationship_between_node == -1) {
            // Creation of relationship

            //error_log("create_relationship_between_node");
            $relationship_id = create_relationship_between_node(
                $source_node['Label'],
                $source_node['ID'],
                $target_node['Label'],
                $target_node['ID'],
                $relationship_type,
                $token_access
            );

            if ($relationship_id == -1) {
                continue;
            }

            $relationship_to_keep[] = $relationship_id;

        } else {
            $relationship_to_keep[] = $relationship_between_node;
        }
    }


    // Removing all old relationship.
    //error_log("delete_old_relationships");
    delete_old_relationships($source_node['ID'], $relationship_type, $relationship_to_keep, $token_access);

}

function check_relationship_between_node($node1_ID, $node2_ID, $token_access):int
{
    //error_log($node1_ID);
    //error_log($node2_ID);
    $Prop_URL = "/graph/read_relationship_btwn_node/?node_id1=$node1_ID&node_id2=$node2_ID";

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

    $result = json_decode($response['body'], true);

    //error_log(print_r($result, true));

    if ($result == -1) {
        return $result;
    } else {
        return $result['relationship_id'];
    }
}

function create_relationship_between_node(
    $source_node_label, $source_node_label_id,
    $target_node_label, $target_node_label_id,
    $relationship_type, $token_access):int
{

    $source_node = array(
        'label'=>$source_node_label,
        'id'=>$source_node_label_id
    );

    $target_node = array(
        'label'=>$target_node_label,
        'id'=>$target_node_label_id
    );

    $body = array(
        'relationship_type'=>$relationship_type,
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
        error_log("Error: ".$create_response);
        return -1;
    }

    //error_log("result: ".print_r($create_response, true));

    $data = json_decode($create_response["body"], true);
//    //error_log("data: ".print_r($data, true));
    return $data["relationship_id"];
}

function delete_old_relationships($node_id, $relationship_type, $relationship_to_keep, $token_access):void
{
    // Get All relationship linked to given node with given relationship type
    $cypher_string = "MATCH (n)-[r:$relationship_type]-(m) WHERE ID(n) = $node_id RETURN ID(r) AS ID, r";

    $body = array(
        "cypher_string"=>$cypher_string
    );

    $header = array(
        'Content-Type'=>'application/json',
        'Accept' => 'application/json',
        'Authorization' => 'bearer '.$token_access
    );

    $args = array(
        'headers' => $header,
        'body' => json_encode($body),
        'method' => 'POST'
    );

    $response = wp_remote_request($GLOBALS['API_URL'].'/q', $args);

    if( is_wp_error( $response ) ) {
        //error_log("Error: Get All relationship linked to given node with given relationship type");
    }

    $ids = json_decode($response['body'], true)['response'];

    //error_log("All relationship linked to given node with given relationship type: ".print_r($ids, true));

    // Transform info in two list of ID

    $relationship_ids = array();
    foreach ($ids as $id) {
        $relationship_ids[] = $id['ID'];
    }

    //error_log('relationship_ids: '.print_r($relationship_ids, true));
    //error_log('To keep: '.print_r($relationship_to_keep, true));
    $relationship_to_delete = array_diff($relationship_ids, $relationship_to_keep);
    //error_log('To delete: '.print_r($relationship_to_delete, true));

    foreach ($relationship_to_delete as $id) {
        delete_relationship($id, $token_access);
    }

}

function delete_relationship($relationship_id, $token_access)
{
    $DELETE_URL = "/graph/delete_relationship/$relationship_id";

    $header = array(
        'Content-Type'=>'application/json',
        'Accept' => 'application/json',
        'Authorization' => 'bearer '.$token_access
    );

    $args = array(
        'headers' => $header,
    );

    $response = wp_remote_post($GLOBALS['API_URL'].$DELETE_URL, $args);

    if( is_wp_error( $response ) ) {
        //error_log("Error delete relationship");
    }
}


function request(array $body, array $headers, string $URL, string $method): array
{
    //error_log("");
    //error_log("");
    //error_log("==========Request==========");
    //error_log("URL: ".$URL);
    //error_log("Method: ".$method);
    //error_log("Body: ".print_r($body, true));
    //error_log("Header: ".print_r($headers, true));
    //error_log("");

    // Initialize cURL session
    $ch = curl_init();

// Set cURL options
    curl_setopt($ch, CURLOPT_URL, $URL);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($body));

// Execute cURL session
    $response = curl_exec($ch);

// Check for errors
    if ($response === false) {
        $error = curl_error($ch);
        //error_log("cURL Error: " . $error);
        $response_code = 500; // Set response code to indicate error
        $response_body = array("error" => $error);
    } else {
        // Get response code
        $response_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        // Decode JSON response
        $response_body = json_decode($response, true);
    }

// Log result
    //error_log("==========Result==========");
    //error_log("Response Code: ".$response_code);
    //error_log("Response: ".print_r($response_body, true));
    //error_log("");

// Close cURL session
    curl_close($ch);

// Return response code and body
    return array(
        'response_code' => $response_code,
        'response' => $response_body
    );
}


function body_builder(array $body, array $fields, array $field_to_skip): array
{
    //error_log(print_r($fields, true));
    $keys = array_keys($fields);
    foreach ($keys AS $key){
        //error_log($key.": ".print_r($fields[$key], true));
        if ($fields[$key] == null or empty($fields[$key])) {
            continue;
        }
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
                    } elseif (gettype($object) == "array") { // Need to find a string
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

    return $body;
}