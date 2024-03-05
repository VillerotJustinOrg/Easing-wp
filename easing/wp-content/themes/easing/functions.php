<?php

$API_URL = "http://localhost:8000";

require_once "Code_API/UtilsAPI.php";
require_once "Code_API/Logement.php";
require_once "Code_API/Clients.php";
require_once "Code_API/Equipements_accessibilites.php";
require_once "Code_API/default_node_type.php";


// =======================================================================
//                              Set Hooks
// =======================================================================

// Creating & Deleting
add_action('save_post', 'send_data_to_api_on_post_save', 10, 3);

// =======================================================================
//                              Fonctions
// =======================================================================

function send_data_to_api_on_post_save($post_id, $post, $update): void {
    error_log("");
    error_log("==================================================================================");
    error_log("==================================================================================");
    error_log("");

    error_log("==================");
    error_log("Miscellaneous Info");
    error_log("==================");
    error_log("post_id: ".$post_id);
    error_log("post status: ".$post->post_status);
    error_log("post type: ".$post->post_type);
//    error_log("post: ".print_r($post,true));
    error_log("update: ".$update);
    error_log("==================");
    error_log("");

    if ($post->post_status != "auto-draft") {
        $token = get_API_Token();
        $token_access = $token['access_token'];
        switch ($post->post_type){
            default:
                Router_Default($post, $post_id, $post->post_type, $token_access);
                break;
            case "equipement_access":
                Router_Equipement_access($post, $post_id, "equipement_access", $token_access);
                break;
            case "logement":
                Router_logement($post, $post_id, "logement", $token_access);
                break;
        }
    }
    error_log("");
    error_log("==================================================================================");
    error_log("==================================================================================");
}