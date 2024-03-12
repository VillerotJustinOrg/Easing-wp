<?php
require_once 'Code_API/UtilsAPI.php';

// regarder Politique CORS (acces serveur) mode stricte. Voir code console
function treatment($POST)
{
    $token = get_API_Token();
    $token_access = $token['access_token'];

    $URL = "http://localhost:8000/graph/create_relationship"; // Create relationship URL

    $body = array(
        'relationship_type'=>'located_by',
        'relationship_attributes'=>array(
            'debut'=>$POST['debut'],
            'fin'=>$POST['fin'],
            'nbr_person'=>$POST['nbr_person']
        ),
        "source_node"=>array(
            "id"=>$POST['Node_ID'],
            "label"=>"logement"
        ),
        "target_node"=>array(
            "id"=>$POST['client'],
            "label"=>"client"
        )
    );

    $header = array(
        'Content-Type'=>'application/json',
        'Accept' => 'application/json',
        'Authorization' => 'Bearer '.$token_access
    );

    $args = array(
        'headers' => $header,
        'body' => json_encode($body),
        'method' => 'POST'
    );

    $response = wp_remote_post($URL, $args);

    error_log("Response: ".print_r($response, true));
}