
<?php
require_once 'header.php';
require_once 'Code_API/UtilsAPI.php';

$fields=get_fields();

//error_log(print_r($fields, true));

$nombre = $_GET["nombre"];
$destination = $_GET["destination"];
$debut = $_GET["debut"];
$fin = $_GET["fin"];

//<!--<pre style="display:none">--><?php //print_r($fields) <!--<!-- </pre>-->-->

// Logement info
$post = get_post();
$Logement_ID = $post->post_title;

// Data Location
$data = array(
    'latitude' => $fields['latitude'],
    'longitude' => $fields['longitude']
); 

$json_data = json_encode($data);

// API access token
$token = get_API_Token();
$token_access = $token['access_token'];

// Data disponibility
// Doesn't work I don't know why
$URL = "http://localhost:8000/q";

$query = "MATCH (l:logement)-[r:is_located_by]-() ";
$query.= "WHERE l.ID_Logement = \"$Logement_ID\" ";
$query.= "RETURN r;";

//$query = "MATCH (l:logement)-[r:is_located_by]-() WHERE l.ID_Logement = \"Logement 1\" RETURN r;";

$body = array(
        "cypher_string" => $query
);

$header = array(
    'Content-Type'=>'application/json',
    'Accept' => 'application/json',
    'Authorization' => 'bearer '.$token_access
);

$response = request($body, $header, $URL, 'POST');

error_log("Response: ".print_r($response, true));
//error_log("thingy: ".print_r($response['response']['response'][0]['r'][1], true));

// Visit URL
$visite = $fields['3D_Visit'];
$visite_URL = $visite['ID'].'/'.$visite['title']


?>

<!-- TODO $fields['propretaire'] -->
<!-- TODO $fields['type_de_propriete'] -->
<!-- TODO $fields['contient_pieces'] -->
<!-- TODO Nombre de chambre ... -->

<main id="singleLogement" class="container">
    <div> 

        <h1 class="bold" ><?php echo $fields['titre'] ?> </h1>

        <div class="wrapper">
            <?php $i=1; foreach($fields['photos'] as $photo){ if($i<8){?>

                <?php if($i!=3){ ?> 
                    <div style="background-size:cover;background-position:center center;background-image: url(<?php echo $photo['url'] ?>);" class="case-<?php echo $i ?>"></div>
                <?php } ?>

                <?php if($i==3){ ?> 
                    <a target=”_blank” href="<?php echo get_bloginfo('template_url'); ?>/3D_Visits/<?php echo $visite_URL ?>/Maison.html" style="position:relative;background-size:cover;background-position:center center;background-image: url(<?php echo $photo['url'] ?>);" class="case-<?php echo $i ?>">
                        <img class="img-360" src="<?php echo get_bloginfo('template_url'); ?>/img/360.svg" >
                    </a>
                <?php } ?>



            <?php } $i++; } ?>
        </div>

        <div class="d-flex flex-row justify-content-between" style="margin-top:30px;position:relative"> 

            <div style="width:65%"> 
                <h2 class="bold"> Description </h2>
                <p><?php echo $fields['description']; ?> </p>     
                
                <h2 class="bold"> Disponibilités </h2>

                <script>

                    document.addEventListener('DOMContentLoaded', function() {
                        var calendarEl = document.getElementById('calendar');
                        var calendar = new FullCalendar.Calendar(calendarEl, {
                            initialView: 'dayGridMonth'
                        });
                        calendar.render();
                    });

                </script>
                <div id="calendar">

                </div>


<!--                <img style="width:100%" src="--><?php //echo get_bloginfo('template_url'); ?><!--/img/Calendrier.png" >-->
                
                <div class="line"> </div>

                <h2 class="bold"> Équipements d'accessibilité </h2>
                <?php
                $adaptations = $fields['equipements_daccessibilite'];

                foreach ($adaptations AS $adaptation) {

                    $adaptation_fields = get_fields($adaptation->ID);
//                    echo "<pre>".print_r($adaptation_fields, true)."</pre>";
                    echo "<div class='my-3'>";
                    echo "<h4>" . $adaptation_fields['nom'] . "</h4>";
                    echo "<p class='mb-2'>Type de piece: " . $adaptation_fields['type_de_piece'][0]->post_title . "</p>";
                    echo "<p>" . $adaptation_fields['description'] . "</p>";
                    echo "</div>";
                }

//                echo "<pre>".print_r($adaptations, true)."</pre>";

                ?>
                <!-- TODO $fields['equipements_daccessibilite'] -->

                <div class="line"> </div>

                <h2 class="bold"> Service de proximité </h2>
                <?php
                $service_proximites = $fields['services_de_proximite'];

                foreach ($service_proximites AS $service_proximite) {

                    $service_fields = get_fields($service_proximite->ID);
//                    echo "<pre>".print_r($adaptation_fields, true)."</pre>";
                    echo "<div class='my-3'>";
                    echo "<h4>" . $service_fields['nom'] . "</h4>";
                    echo "<p>" . $service_fields['description'] . "</p>";
                    echo "</div>";
                }
//                echo "<pre>".print_r($adaptations, true)."</pre>";

                ?>
                <!-- TODO $fields['services_de_proximite'] -->

                <div class="line"> </div>

                <h2 class="bold"> Restrictions </h2>
                <?php
                $objects = $fields['restrictions'];

                foreach ($objects AS $object) {

                    $object_fields = get_fields($object->ID);
//                    echo "<pre>".print_r($adaptation_fields, true)."</pre>";
                    echo "<div class='my-3'>";
                    echo "<h4>" . $object_fields['nom'] . "</h4>";
                    echo "<p>" . $object_fields['description'] . "</p>";
                    echo "</div>";
                }

//                echo "<pre>".print_r($adaptations, true)."</pre>";

                ?>
                <!-- TODO $fields['restrictions'] -->

                <div class="line"> </div>

                <h2 class="bold"> Service domotique </h2>
                <?php
                $objects = $fields['services_domotique'];

                foreach ($objects AS $object) {

                    $object_fields = get_fields($object->ID);
//                    echo "<pre>".print_r($adaptation_fields, true)."</pre>";
                    echo "<div class='my-3'>";
                    echo "<h4>" . $object_fields['nom'] . "</h4>";
                    echo "<p>" . $object_fields['description'] . "</p>";
                    echo "</div>";
                }

                //echo "<pre>".print_r($adaptations, true)."</pre>";

                ?>
                <!-- TODO $fields['services_domotique'] -->

                <div class="line"> </div>

                <h2 class="bold"> Équipements domotique </h2>
                <?php
                $objects = $fields['equipements_domotique'];

                foreach ($objects AS $object) {

                    $object_fields = get_fields($object->ID);
//                    echo "<pre>".print_r($adaptation_fields, true)."</pre>";
                    echo "<div class='my-3'>";
                    echo "<h4>" . $object_fields['nom'] . "</h4>";
                    echo "<p>" . $object_fields['description'] . "</p>";
                    echo "</div>";
                }

                //echo "<pre>".print_r($adaptations, true)."</pre>";

                ?>
                <!-- TODO $fields['equipements_domotique'] -->


            </div>

            <div class="pop-reserver" > 
                <p><span style="font-size:25px" class="bold" > <?php echo $fields['prix_nuit']; ?></span> € par nuit </p>
                
                <div style="margin-top:10px" class="d-flex flex-row justify-content-between">

                    <div class="d-flex flex-column" style="width:48%">
                        <p> Arrivée </p>
                        <input type="date" id="debut" name="debut" min="<?php echo date('Y-m-d'); ?>" value="<?php echo $debut ?>" />
                    </div>

                    <div class="d-flex flex-column" style="width:48%">
                        <p> Départ </p>
                        <input type="date" id="fin" name="fin" min="<?php echo date('Y-m-d', strtotime('+1 day')); ?>" value="<?php echo $fin ?>" ?> 
                    </div>

                </div>

                <p style="margin-top:15px" >Nombre de personnes : <?php echo $fields['nombre_personnes'] ?></p>

                <a class="button" href="#"> Réserver </a>

                <p> <?php echo $fields['prix_nuit']; ?>€ x <span id="nombreNuit"> 5 </span> nuits </p>

                <p style="margin-top:30px;margin-top:10px;font-size:20px" class="bold" id="prix" data-prix="<?php echo $fields['prix_nuit']; ?>"> Prix : <?php echo  $fields['prix_nuit'] ?> € </p>


            </div>

        </div>

        <div class="grand_line"> </div>

        <h2 class="bold"> Localisation </h2>

        <div  id="map-log" data-logements='<?php echo htmlspecialchars($json_data, ENT_QUOTES, 'UTF-8'); ?>' style="width:100%; height:400px;margin-top:60px;margin-bottom:90px"></div>


    </div>
</main>

<?php
include 'footer.php';
?>