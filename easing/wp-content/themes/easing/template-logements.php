<?php /* Template Name: Liste logements */

require_once 'header.php';

require_once 'Code_API/UtilsAPI.php';

echo "<main>";

//echo "<pre>".print_r($_POST, true)."</pre>";


// Default parameters
$destination=$_POST["destination"];
$debut=$_POST["debut"];
$fin=$_POST["fin"];
$nombre_personne=$_POST["champNombre"];

// Advanced parameter
if (isset($_POST["Min_Price"])) $Min_Price = $_POST["Min_Price"];
if (isset($_POST["Max_Price"])) $Max_Price = $_POST["Max_Price"];
if (isset($_POST["pet"])) $pet = $_POST["pet"];
if (isset($_POST["plain-pied"])) $plain_pied = $_POST["plain-pied"];
if (isset($_POST["user_request"])) $user_request = $_POST["user_request"];

$taxonomy = 'accessibilite';

// Récupérer tous les termes de la taxonomie (excluant les termes sans post associé)
$terms = get_terms(array(
    'taxonomy'   => $taxonomy,
    'hide_empty' => false,
    'pad_counts' => true, // Exclure les termes sans post associé
));
$filtered_terms = wp_list_filter($terms, array('count' => 0), 'NOT');

$is_AI_recomandation = !empty($user_request);
if (!$is_AI_recomandation){
//    error_log("=====================================================================================================================");
//    error_log("                                                 Filter Without IA");
//    error_log("=====================================================================================================================");
//    error_log("Filter Without IA");

    $args = array(
        'post_type'      => 'logement',
        'posts_per_page' => -1,
    );

    if (!empty($nombre)) {
        $args['meta_query'][] = array(
            'key'     => 'nombre_personnes',
            'value'   => $nombre,
            'compare' => '>=',
            'type'    => 'NUMERIC',
        );
    }

    if (!empty($destination)) {
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
    }

    if (!empty($nombre) && !empty($destination)) {
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

    $body = array(
        "destination"=>$destination,
        "start"=>$debut,
        "end"=>$fin,
        "number_person"=>intval($nombre_personne),
        "n_result"=>30,
        "method"=>"TF-IDF",
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

?>

<?php require_once "FilterForm.php"?>

<?php $startDate = new DateTime($debut);
$endDate = new DateTime($fin);
// Calculez la différence en jours
$difference = $startDate->diff($endDate);
$nombre_nuit=$difference->days; ?>

<div class="d-flex flex-row" style="min-height: calc(100vh - 60px - 40px - 75px);max-height: calc(100vh - 60px - 40px - 75px)"> 

    <div class="liste-logements d-flex"> 
        <?php
        if (count($logements)==0) {
            echo "Aucun logement ne correspond a vos filtres";
        }

        $i=0;
        foreach ($logements as $logement) {
            $logement_id = $is_AI_recomandation ? $logement['ID'] : $logement->ID;
            $fields_logement=get_fields($logement_id);

            $lien = get_permalink($logement->ID) . "?nombre=$nombre_personne&destination=$destination&debut=";
            ?>

            <a id="<?php echo $i ?>" class="card_logement"   style="width:32%" href="<?php echo $lien ?>" target="_blank">

                <div class="owl-carousel carousel-logement owl-theme">
                    <?php foreach($fields_logement['photos'] as $photo){ ?>
                        <img  src="<?php echo $photo['url']  ?>" >
                    <?php } ?>
                </div> 

                <div class="informations" >
                <p class="ville"><?php echo $fields_logement['ville'] ?>, France </p>
                <p><?php echo $fields_logement['titre'] ?> </p>
                <p class="prix"><span class="bold"> <?php echo $fields_logement['prix_nuit'] ?> </span> € par nuit </p>
                <?php if($nombre_nuit!=0){ ?>
                    <p><span class="bold"><?php echo $fields_logement['prix_nuit']*$nombre_nuit ?></span>€ au total </p>
                <?php }else{ ?>
                    <p><span class="bold"><?php echo $fields_logement['prix_nuit']*7 ?></span>€ au total </p>
                <?php } ?>
                <div class="coeur" > </div>
                </div>

            </a>
            
        <?php $i++;} ?>
    </div> 

    <div style="height:auto;width:40%" id="map"></div>

    <!-- Utiliser un attribut data pour stocker la chaîne JSON -->
    <div id="logements" data-logements='<?php echo htmlspecialchars($json_logements, ENT_QUOTES, 'UTF-8'); ?>'></div>

 </div>

</main>

<?php
include 'footer.php';
?>
