<?php
//echo "<pre>".print_r($_POST, true)."</pre>";


// Default parameters
$destination=$_POST["destination"];
$debut=$_POST["debut"];
$fin=$_POST["fin"];
$nombre_personne=$_POST["champNombre"];
// Detail
$adult = $_POST["adult_h"];
$kid = $_POST["kid_h"];
$baby = $_POST["baby_h"];
$pet = $_POST["pet_h"];


// Advanced parameter
if (isset($_POST["Min_Price"])) $Min_Price = $_POST["Min_Price"];
if (isset($_POST["Max_Price"])) $Max_Price = $_POST["Max_Price"];
if (isset($_POST["type"])) $Type = $_POST["type"];
if (isset($_POST["categorie"])) $Categorie = $_POST["categorie"];

$Reservation_auto = $_POST["reservation_auto"] ?? false;

$Arrive_auto = $_POST["arrive_auto"] ?? false;

if (isset($_POST["nbr_chambre"])) $nbr_chambre = $_POST["nbr_chambre"];
if (isset($_POST["nbr_salle_bain"])) $nbr_salle_bain = $_POST["nbr_salle_bain"];
if (isset($_POST["nbr_lits"])) $nbr_lits = $_POST["nbr_lits"];

// IA
if (isset($_POST["user_request"])) $user_request = $_POST["user_request"];

$taxonomy = 'accessibilite';

// Récupérer tous les termes de la taxonomie (excluant les termes sans post associé)
$terms = get_terms(array(
    'taxonomy'   => $taxonomy,
    'hide_empty' => false,
    'pad_counts' => true, // Exclure les termes sans post associé
));
$filtered_terms = wp_list_filter($terms, array('count' => 0), 'NOT');

$is_AI_recomandation = ((!empty($user_request)) or (!empty($debut)) or (!empty($fin)));
if (!$is_AI_recomandation){
//    error_log("=====================================================================================================================");
//    error_log("                                                 Filter Without IA");
//    error_log("=====================================================================================================================");
//    error_log("Filter Without IA");

    $args = array(
        'post_type'      => 'logement',
        'posts_per_page' => -1,
    );

    $nbr_arg = 0;

    if (!empty($nombre_personne)) {
        error_log("nombre_personne");

        $args['meta_query'][] = array(
            'key'     => 'nombre_personnes',
            'value'   => $nombre_personne,
            'compare' => '>=',
            'type'    => 'NUMERIC',
        );
        $nbr_arg ++;

        if ($kid > 0){
            $args['meta_query'][] = array(
                'key'     => 'accepte_enfant',
                'value'   => true,
                'compare' => '=',
                'type'    => 'BOOLEAN',
            );
            $nbr_arg ++;
        }

        if ($baby > 0){
            $args['meta_query'][] = array(
                'key'     => 'accepte_bebe',
                'value'   => true,
                'compare' => '=',
                'type'    => 'BOOLEAN',
            );
            $nbr_arg ++;
        }
    }

    if (!empty($destination)) {
        error_log("destination");
        $args['meta_query'][] = array(
            'relation' => 'OR',
            array(
                'key'     => 'ville',
                'value'   => $destination,
                'compare' => 'LIKE',
            ),
            array(
                'key'     => 'region',
                'value'   => $destination,
                'compare' => '=',
            ),
        );
        $nbr_arg ++;
    }

    if (!empty($Min_Price)){
        error_log("min price");
        $args['meta_query'][] = array(
            'key'     => 'prix_nuitee',
            'value'   => $Min_Price,
            'compare' => '>=',
            'type'    => 'NUMERIC',
        );
        $nbr_arg ++;
    }

    if (!empty($Max_Price)){
        error_log("max price");
        $args['meta_query'][] = array(
            'key'     => 'prix_nuitee',
            'value'   => $Max_Price,
            'compare' => '<=',
            'type'    => 'NUMERIC',
        );
        $nbr_arg ++;
    }

    if (!empty($Categorie)){
        error_log("categorie");
        $args['meta_query'][] = array(
            'key'     => 'type_habitation',
            'value'   => $Categorie,
            'compare' => '='
        );
        $nbr_arg ++;
    }

    if (!empty($Type)){
        error_log("type");
        $args['meta_query'][] = array(
            'key'     => 'type_reservation',
            'value'   => $Type,
            'compare' => '='
        );
        $nbr_arg ++;
    }


    if ($Reservation_auto) {
        $args['meta_query'][] = array(
            'key'     => 'acceptation',
            'value'   => "Instantanée",
            'compare' => '='
        );
        $nbr_arg ++;
        error_log("reserv");
    }

    if ($Arrive_auto) {
        $args['meta_query'][] = array(
            'key'     => 'accueil',
            'value'   => "Arrivée autonome",
            'compare' => '='
        );
        $nbr_arg ++;
        error_log("arriv");
    }

    if (!empty($nbr_chambre)){
        error_log("nbr_chambre");
        $args['meta_query'][] = array(
            'key'     => 'nbr_chambre',
            'value'   => $nbr_chambre,
            'compare' => '<=',
            'type'    => 'NUMERIC',
        );
        $nbr_arg ++;
    }

    if (!empty($nbr_salle_bain) AND $nbr_salle_bain > 1){
        error_log("nbr_salle_bain");
        $args['meta_query'][] = array(
            'key'     => 'nbr_salle_bain',
            'value'   => $nbr_salle_bain,
            'compare' => '<=',
            'type'    => 'NUMERIC',
        );
        $nbr_arg ++;
    }

    if (!empty($nbr_lits) AND $nbr_lits > 1){
        error_log("nbr_lits");
        $args['meta_query'][] = array(
            'key'     => 'nbr_lits',
            'value'   => $nbr_lits,
            'compare' => '<=',
            'type'    => 'NUMERIC',
        );
        $nbr_arg ++;
    }

    if ($nbr_arg > 1) {
        $args['meta_query'] = array(
            'relation' => 'AND',
            $args['meta_query'],
        );
    }

//    error_log("Get post filters");
    $logements = get_posts($args);
    $list_logements = array();

    foreach ($logements as $logement) {
        $fields_logement = get_fields($logement->ID);
        $fields_logement['link'] = get_permalink($logement->ID); // Ajoute le lien du logement au tableau
        $list_logements[] = $fields_logement;
    }

} else {
//    error_log("=====================================================================================================================");
//    error_log("                                                 Filter With IA");
//    error_log("=====================================================================================================================");

    $token = get_API_Token();
    $token_access = $token["access_token"];

    $URL = getenv('API_URL')."/IA/search";

    $advanced_filters = array();

    foreach (array_keys($_POST) as $post_key) {
        if (!empty($_POST[$post_key])) {
            $advanced_filters[$post_key] = $_POST[$post_key];
        }
    }

    $body = array(
        "destination"=>$destination,
        "start"=>$debut,
        "end"=>$fin,
        "number_person"=>intval($nombre_personne),
        "adult"=>intval($adult),
        "kid"=>intval($kid),
        "baby"=>intval($baby),
        "pet"=>intval($pet),
        "advanced_filters"=>$advanced_filters,
        "n_result"=>30,
        "method"=>"TF-IDF+Word2Vec",
        "similarity_method"=>"cosine",
        "user_request"=>$user_request,
    );

    $jsonData = json_encode($body);

    // Set the HTTP headers
    $options = array(
        'http' => array(
            'header'  => "Content-Type: application/json\r\n" . "Accept: application/json\r\n" . "Authorization: bearer $token_access\r\n",
            'method'  => 'POST',
            'content' => $jsonData
        )
    );

    // Create a stream context
    $context  = stream_context_create($options);

    $response = file_get_contents($URL, false, $context);

    // Check for errors
    if ($response === false) {
        // Handle cURL error
        // Handle error
        echo "Error: ";
    } else {
        // Response received successfully, handle it

        $decoded_response = json_decode($response, true);


//        error_log("==========================================================================================");
//        error_log("==================================== Results =============================================");
//        error_log("==========================================================================================");

//        error_log("Number of result: ". $decoded_response['number_of_results']);

        $results = $decoded_response['result'];

        // Get nodes properties
        $logements = array();
        foreach ($results as $node){
            $node['nodes']['ID'] = $node['nodes']['ID_Post'];
            $logements[] = $node['nodes'];
        }

//        error_log("Nodes: ".print_r($logements, true));

        foreach ($logements as $logement) {
            $fields_logement = get_fields($logement['ID_Post']);
            $fields_logement['link'] = get_permalink($logement['ID_Post']); // Ajoute le lien du logement au tableau
            $list_logements[] = $fields_logement;
        }
    }
}

//error_log(print_r($logements, true));

$json_logements = json_encode($list_logements);