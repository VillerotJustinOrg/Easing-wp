<?php

$API_URL = "http://localhost:8000";

require_once "Code_API/UtilsAPI.php";
require_once "Code_API/Logement.php";
require_once "Code_API/Proprietaire.php";
require_once "Code_API/Clients.php";
require_once "Code_API/Equipements_accessibilites.php";
require_once "Code_API/Equipements_domotique.php";
require_once "Code_API/Restriction.php";
require_once "Code_API/Services_domotique.php";
require_once "Code_API/Services_proximite.php";
require_once "Code_API/Type_piece.php";
require_once "Code_API/Type_propriete.php";


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
            case "client":
                Router_CLient($post, $post_id, "client", $token_access);
                break;
            case "equipement_access":
                Router_Equipement_access($post, $post_id, "equipement_access", $token_access);
                break;
            case "equipement_domotique":
                Router_Equipement_domotique($post, $post_id, "equipement_domotique", $token_access);
                break;
            case "logement":
                Router_logement($post, $post_id, "logement", $token_access);
                break;
            case "proprietaire":
                Router_Proprietaire($post, $post_id, "proprietaire", $token_access);
                break;
            case "restriction":
                Router_Restriction($post, $post_id, "restriction", $token_access);
                break;
            case "service_domotique":
                Router_Service_Domotique($post, $post_id, "service_domotique", $token_access);
                break;
            case "service_de_proximite":
                Router_Service_Proximite($post, $post_id, "service_de_proximite", $token_access);
                break;
            case "piece":
                Router_Service_Type_Piece($post, $post_id, "piece", $token_access);
                break;
            case "type_propriete":
                Router_Service_Type_Propriete($post, $post_id, "type_propriete", $token_access);
                break;
        }
    }
    error_log("");
    error_log("==================================================================================");
    error_log("==================================================================================");
}