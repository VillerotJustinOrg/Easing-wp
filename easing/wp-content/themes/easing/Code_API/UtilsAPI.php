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


function update_relationship_between_node($node1, $node2, $relationship_type, $token_access): void
{
    error_log("=====================================");
    error_log('Update Relationship Between Two Node');

//    $node_ID1

    // Check  if there is already a relationship between this logement and this propriétaire

    $Prop_URL = "/graph/read_relationship_btwn_node/?node_id1=".$node1['ID']."&node_id2=".$node2['ID'];

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
            'label'=>$node1['Label'],
            'id'=>$node1['ID']
        );

        $target_node = array(
            'label'=>$node2['Label'],
            'id'=>$node2['ID']
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

//        error_log("result: ".print_r($create_response, true));

        $data = json_decode($create_response["body"], true);
        $relationship_to_keep = $data["relationship_id"];
    }

    // Removing all old relationship.

    $cypher ="MATCH (startNode)-[r:$relationship_type]->() WHERE id(startNode) = ".$node1['ID']." AND id(r) <> $relationship_to_keep DELETE r";
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

function check_relationship between_node()
{
    
}