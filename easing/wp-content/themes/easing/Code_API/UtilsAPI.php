<?php

// =======================================================================
//                             API Connection
// =======================================================================

function get_API_Token() {
    error_log("=========================================");
    error_log("              Get API token");
    error_log("=========================================");

    //TODO get login and passwd from .env and api url
    $login = "admin";
    $passwd = "admin";
    $API_URL = "http://localhost:8000";
    $auth_url = "/auth/token";

    $complete_url = $API_URL.$auth_url;

    $body = "username=".$login."&password=".$passwd;

    $header = array(
        "Content-Type"=>"application/x-www-form-urlencoded",
        "Accept"=>"application/json"
    );

    $args = array(
        "body"=>$body,
        "header"=>$header
    );

    $response = wp_remote_post($complete_url, $args);

//    error_log("Response: ".print_r($response, true));

    if( is_wp_error( $response ) ) {
        error_log("Error");
    } else {
        $result_data = json_decode($response["body"], true);
        error_log('token: '.print_r($result_data, true));
        error_log("=========================================");
        error_log("");
        error_log("");
        return $result_data;
    }
}

// =======================================================================
//                                Other
// =======================================================================


function add_field_info_to_body($body, $fields)
{
    error_log("add_field_info_to_body: ".print_r($fields, true));
    $keys = array_keys($fields);
    foreach ($keys as $key){
        error_log($key.": ".$fields[$key]);
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


function update_relationship_between_node($source_node, $target_nodes, $relationship_type, $token_access): void
{
    error_log("=====================================");
    error_log('Update Relationship Between Two Node');

//    $node_ID1

    // Check  if there is already a relationship between this logement and this propriétaire

    $relationship_to_keep = array();

    foreach ($target_nodes as $target_node){

        error_log("target node: ".print_r($target_node, true));
        error_log("check_relationship_between_node");
        $relationship_between_node = check_relationship_between_node($source_node['ID'], $target_node['ID'], $token_access);

        if ($relationship_between_node == -1) {
            // Creation of relationship

            error_log("create_relationship_between_node");
            $relationship_id = create_relationship_between_node(
                $source_node['Label'],
                $source_node['ID'],
                $target_node['Label'],
                $target_node['ID'],
                $relationship_type,
                $token_access
            );

            $relationship_to_keep[] = $relationship_id;

        } else {
            $relationship_to_keep[] = $relationship_between_node;
        }
    }

    // Removing all old relationship.
    error_log("delete_old_relationships");
    delete_old_relationships($source_node['ID'], $relationship_type, $relationship_to_keep, $token_access);

}

function check_relationship_between_node($node1_ID, $node2_ID, $token_access):int
{
    error_log($node1_ID);
    error_log($node2_ID);
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

    error_log(print_r($result, true));

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
        error_log("Error");
    }

    error_log("result: ".print_r($create_response, true));

    $data = json_decode($create_response["body"], true);
//    error_log("data: ".print_r($data, true));
    return $data["relationship_id"];
}

function delete_old_relationships($node_id, $relationship_type, $relationship_to_keep, $token_access):void
{
    // Get All relationship linked to given node with given relationship type
    $cypher_string = "MATCH (n)-[r:$relationship_type]-(m) WHERE ID(n) = $node_id RETURN ID(r) AS ID";

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
        error_log("Error: Get All relationship linked to given node with given relationship type");
    }

    $ids = json_decode($response['body'], true)['response'];

    error_log("All relationship linked to given node with given relationship type: ".print_r($ids, true));

    // Transform info in two list of ID

    $relationship_ids = array();
    foreach ($ids as $id) {
        $relationship_ids[] = $id['ID'];
    }

    error_log('relationship_ids: '.print_r($relationship_ids, true));
    error_log('To keep: '.print_r($relationship_to_keep, true));
    $relationship_to_delete = array_diff($relationship_ids, $relationship_to_keep);
    error_log('To delete: '.print_r($relationship_to_delete, true));

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
        error_log("Error delete relationship");
    }
}


function request($body, $headers, $URL, $method){
    error_log("");
    error_log("");
    error_log("==========Request==========");
    error_log("URL: ".$URL);
    error_log("Method: ".$method);
    error_log("Body: ".print_r($body, true));
    error_log("Header: ".print_r($headers, true));
    error_log("");

    $args = array(
        'headers' => $headers,
        'body' => json_encode($body),
        'method' => $method
    );

    $response = wp_remote_request($URL, $args);

    $response_code = $response['response']['code'];

    $response_body = json_decode($response['body'], true);

    error_log("==========Result==========");
    error_log("Response Code: ".$response_code);
    error_log("Response: ".print_r($response_body, true));

    error_log("");
    error_log("");
    return array(
        'response_code'=>$response_code,
        'response'=>$response_body
    );
}


