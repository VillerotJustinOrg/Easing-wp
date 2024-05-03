<?php
require_once 'header.php';
require_once 'Code_API/UtilsAPI.php';

$fields=get_fields();
$post = get_post();
//error_log("==============================================================================================");
//error_log("==============================================================================================");
//error_log("==============================================================================================");
//error_log(print_r($fields, true));
//error_log("==============================================================================================");
//error_log("==============================================================================================");
//error_log("==============================================================================================");

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
if (isset($token['access_token'])) $token_access = $token['access_token'];

$API_URL = getenv('API_URL');
// Data disponibility
// Doesn't work I don't know why
$URL = $API_URL."/q";
//
$query = "MATCH (l:logement)-[r:is_located_by]-() ";
$query.= "WHERE l.ID_Logement = \"$Logement_ID\" ";
$query.= "RETURN id(r) as id, r.start as start, r.end as end;";
//
////$query = "MATCH (l:logement)-[r:is_located_by]-() WHERE l.ID_Logement = \"Logement 1\" RETURN r;";
//
$body = array(
        "cypher_string" => $query
);
//
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

$response = wp_remote_post($URL, $args);

//error_log("Response: ".print_r($response, true));
//error_log("thingy: ".print_r($response['response'][0]['r'][1], true));

$locations = json_decode($response["body"], true)['response'];

error_log("Locations: ".print_r($locations, true));

$events = [];
foreach ($locations as $location){
    // Create a DateTime object from the date string
    $date = new DateTime($location['start']);

    // Subtract one day
//    $date->modify("-1 day");

    // Format the date as a string
    $previousDay = $date->format("Y-m-d");

    $string_builder= "{id: '".$location['id']."', start: '".$previousDay."', end: '".$location['end']."', backgroundColor: '#ff0000', title: 'Réserver'}";
    $events[] = $string_builder;
}

error_log("List: ".print_r($events, true));

// Visit URL
$visite = $fields['3D_Visit'];
if (!empty($visite)){
    $visite_URL = $visite['ID'].'/'.$visite['title'];
}


// Get Client List
$cypher_string = "MATCH (n:client) RETURN n,  ID(n) as ID;";
$body = array(
        "cypher_string" => $cypher_string
);

$args = array(
    'headers' => $header,
    'body' => json_encode($body),
    'method' => 'POST'
);

$response = wp_remote_post($URL, $args);
$clients = json_decode($response['body'], true);
//error_log("Response: ".print_r($clients, true));

$client_list = [];
foreach ($clients["response"] as $client){
    error_log(print_r(array_keys($client), true));
    $client_info = array(
            "node_ID"=>$client["ID"],
            "name"=>$client["n"]["prenom"]." ".$client["n"]["nom"]
    );
    $client_list[] = $client_info;
}

$LG_Node_ID = get_logement_id($post->post_title, $token_access);

$lien = get_permalink($post->ID);

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

                <?php if($i==3 and isset($visite_URL)){ ?>
                    <a target=”_blank” href="<?php echo get_bloginfo('template_url'); ?>/3D_Visits/<?php echo $visite_URL ?>/Maison.html" style="position:relative;background-size:cover;background-position:center center;background-image: url(<?php echo $photo['url'] ?>);" class="case-<?php echo $i ?>">
                        <img alt="IMG-360" class="img-360" src="<?php echo get_bloginfo('template_url'); ?>/img/360.svg" >
                    </a>
                <?php } elseif ($i==3) { ?>
                    <a target="" href="" style="color:white;position:relative;background-size:cover;background-position:center center;background-image: url(<?php echo $photo['url'] ?>);" class="case-<?php echo $i ?>">
                        Pas de visite 3D disponible
                    </a>
                <?php } ?>



            <?php } $i++; } ?>
        </div>

        <div class="d-flex flex-row justify-content-between" style="margin-top:30px;position:relative"> 

            <div style="width:65%">
                <h2 class="bold"> Adresse </h2>
                <p>
                    <?php echo $fields['adresse']; ?>
                    <?php echo $fields['ville']; ?>
                    <?php echo $fields['code_postal']; ?>
                    <?php echo $fields['region']; ?>
                </p>

                <h2 class="bold"> Description </h2>
                <p><?php echo $fields['description']; ?> </p>     
                
                <h2 class="bold"> Disponibilités </h2>

                <script>

                    let list_event=[]

                    <?php foreach ($events as $event) {
                        echo "list_event.push(".$event.");";
                    }?>

                    console.log(list_event)
                    document.addEventListener('DOMContentLoaded', function() {
                        var calendarEl = document.getElementById('calendar');
                        var calendar = new FullCalendar.Calendar(calendarEl, {
                            events:list_event,
                            initialView: 'dayGridMonth'
                        });
                        calendar.render();
                    });

                </script>
                <div id="calendar">

                </div>


<!--                <img style="width:100%" src="--><?php //echo get_bloginfo('template_url'); ?><!--/img/Calendrier.png" >-->
                
                <div class="line"> </div>

                <h2 class="bold"> Pièces </h2>

                <div class="tabs">

                    <?php
                    $pieces = $fields['pieces'];
                    if (empty($pieces)) {
                        echo "Pas de pièces";
                    }
                    $tab_registers = "";
                    $tab_bodies = "";
                    $count = 0;

                    foreach ($pieces AS $piece) {

                        $piece_fields = get_fields($piece->ID);
//                    echo "<pre>".print_r($piece_fields, true)."</pre>";

                        $tab_registers.= "<button ";
                        if ($count==0) $tab_registers.='class="active-tab" ';
                        $tab_registers.=">".$piece_fields['nom']."</button> ";

                        if ($count==0) {
                            $tab_bodies.= '<div style="display:block;">';
                        } else {
                            $tab_bodies.= '<div style="display:none;">';
                        }
                        $tab_bodies.= "[niveau] => " . $piece_fields['niveau'] ."<br>";
                        $tab_bodies.= "[surface] => " . $piece_fields['surface'] ."<br>";
                        $tab_bodies.= "[hauteur_de_plafond] => " . $piece_fields['hauter_de_plafond'] ."<br>";
                        $tab_bodies.= "[accessible_pmr] => ";
                        $tab_bodies.= $fields['accessible_pmr'] ? "Oui" : "Non";
                        $tab_bodies.= "<br>";
                        $tab_bodies.= "[espaces_manoeuvre] => ";
                        $tab_bodies.= $fields['espaces_manoeuvre'] ? "Oui" : "Non";
                        $tab_bodies.= "<br>";
                        $tab_bodies.= "[type_piece] => " . $piece_fields['type_piece'] ."<br>";


                        $tab_bodies.="<h3>Equipements</h3>";
//                        $tab_bodies.="<pre>".print_r($piece_fields, true)."</pre>";
                        $equipements = $piece_fields['equipements'];
                        $tab_bodies.="<div class='row'>";
                        foreach ($equipements AS $equipement) {
                            $equipement_fields = get_fields($equipement->ID);
                            $tab_bodies.="<div class='col-3'>";
                            $tab_bodies.= $equipement_fields['nom'];
                            $tab_bodies.="</div>";
                        }
                        $tab_bodies.="</div>";

                        $tab_bodies.="<h3>Adaptations</h3>";
                        $adaptations = $piece_fields['adaptations'];
                        $tab_bodies.="<div class='row'>";
                        foreach ($adaptations AS $adaptation) {
                            $adaptation_fields = get_fields($adaptation->ID);
                            $tab_bodies.="<div class='col-3'>";
                            $tab_bodies.= $adaptation_fields['nom'];
                            $tab_bodies.="</div>";
                        }
                        $tab_bodies.="</div>";

                        $tab_bodies.="<h3>Ouvertures</h3>";
                        $ouvertures = $piece_fields['ouvertures'];
                        $tab_bodies.="<div class='row'>";
                        foreach ($ouvertures AS $ouverture) {
                            $ouverture_fields = get_fields($ouverture->ID);
                            $tab_bodies.="<div class='col-3'>";
                            $tab_bodies.= $ouverture_fields['nom'];
                            $tab_bodies.="</div>";

                        }
                        $tab_bodies.="</div>";


                        $tab_bodies.= '</div>';
                        $count ++;
                    }

                    //                echo "<pre>".print_r($adaptations, true)."</pre>";

                    ?>

                    <div class="tab-registers">
                        <?php echo $tab_registers ?>
<!--                        <button class="active-tab">Tab 1</button>-->
<!--                        <button>Tab 2</button>-->
<!--                        <button>Tab 2</button>-->
                    </div>
                    <div class="tab-bodies">
                        <?php echo $tab_bodies ?>
<!--                        <div style="display:block;">-->
<!--                            Contenu Tab 1-->
<!--                        </div>-->
<!--                        <div style="display:none;">-->
<!--                            Contenu Tab 2-->
<!--                        </div>-->
<!--                        <div style="display:none;">-->
<!--                            Contenu Tab 3-->
<!--                        </div>-->
                    </div>
                    <script>
                        Array.from(document.querySelectorAll('.tabs')).forEach((tab_container, TabID) => {
                            const registers = tab_container.querySelector('.tab-registers');
                            const bodies = tab_container.querySelector('.tab-bodies');

                            Array.from(registers.children).forEach((el, i) => {
                                el.setAttribute('aria-controls', `${TabID}_${i}`)
                                bodies.children[i]?.setAttribute('id', `${TabID}_${i}`)

                                el.addEventListener('click', (ev) => {
                                    let activeRegister = registers.querySelector('.active-tab');
                                    activeRegister.classList.remove('active-tab')
                                    activeRegister = el;
                                    activeRegister.classList.add('active-tab')
                                    changeBody(registers, bodies, activeRegister)
                                })
                            })
                        })


                        function changeBody(registers, bodies, activeRegister) {
                            Array.from(registers.children).forEach((el, i) => {
                                if (bodies.children[i]) {
                                    bodies.children[i].style.display = el == activeRegister ? 'block' : 'none'
                                }

                                el.setAttribute('aria-expanded', el == activeRegister ? 'true' : 'false')
                            })
                        }
                    </script>
                </div>


                <!-- TODO $fields['equipements_daccessibilite'] -->

                <div class="line"> </div>
                <h2 class="bold"> Information logement </h2>

                Acceptation : <?php echo $fields['acceptation']?><br>
                Étage : <?php echo $fields['etage']?><br>
                Superficie : <?php echo $fields['superficie']?><br>
                Accepte enfant : <?php if($fields['accepte_enfant']){echo "Oui";} else {echo "Non";}?><br>
                Accepte bebe : <?php if($fields['accepte_bebe']){echo "Oui";} else {echo "Non";}?><br>
                Type de reservation : <?php echo $fields['type_reservation']?><br>
                Effets personnels : <?php if($fields['effets_personnels']){echo "Oui";} else {echo "Non";}?><br>
                Nombre lits_simples : <?php echo $fields['nombre_lits_simples']?><br>
                Nombre lits_doubles : <?php echo $fields['nombre_lits_doubles']?><br>
                Type d'habitation : <?php echo $fields['type_habitation']?><br>

                <div class="line"> </div>
                <h2 class="bold"> Equipements </h2>

                <?php
                    $equipements = $fields['equipements'];

                    if (empty($equipements)){
                        echo "Pas d'équipements";
                    }

                    foreach ($equipements as $equipement){
                        $equipement_fields = get_fields($equipement->ID);

                        echo "<li>".$equipement_fields['nom']."</li>";
                    }

                    ?>

                <div class="line"> </div>
                <h2 class="bold"> Adaptations </h2>

                <?php
                $adaptations = $fields['adaptations'];

                if (empty($adaptations)){
                    echo "Pas d'adaptations";
                }

                echo "<ul>";
                foreach ($adaptations as $adaptation){
                    $adaptation_fields = get_fields($adaptation->ID);

                    echo "<li>".$adaptation_fields['nom']."</li>";
                }
                echo "</ul>";


                ?>

            </div>

            <div class="pop-reserver">
                <form action="" method="POST">
                    <input type="hidden" name="ID_Logement" id="ID_Logement" value="<?php echo $post->post_title; ?>">
                    <input type="hidden" name="ID_Post" id="ID_Post" value="<?php echo $post->ID; ?>">
                    <input type="hidden" name="Node_ID" id="Node_ID" value="<?php echo $LG_Node_ID; ?>">
                    <p><span style="font-size:25px" class="bold" > <?php echo $fields['prix_nuitee']; ?></span> € par nuit </p>
                    <div style="margin-top:10px" class="d-flex flex-row justify-content-between">
                        <div class="d-flex flex-column" style="width:48%">
                            <label for="debut">Arrivée</label>
                            <input required type="date" id="debut" name="debut" min="<?php echo date('Y-m-d'); ?>" value="<?php echo $debut ?>" />
                        </div>
                        <div class="d-flex flex-column" style="width:48%">
                            <label for="fin">Départ</label>
                            <input required type="date" id="fin" name="fin" min="<?php echo date('Y-m-d', strtotime('+1 day')); ?>" value="<?php echo $fin ?>" ?>
                        </div>
                    </div>

                    <div class="mt-2 d-flex flex-row justify-content-between">
                        <label for="nbr_person">Nombre de personnes : </label>
                        <input required id="nbr_person" name="nbr_person"
                               type="number"
                               step="1"
                               min="0"
                               max="<?php echo $fields['nombre_personnes'] ?>"
                               value="<?php echo $fields['nombre_personnes'] ?>"
                               maxlength="3"
                               style="width: 4rem;line-height:16px;">
                    </div>

                    <div>
                        <label for="client">Client</label>
                        <select id="client" name="client">
                            <?php
                            foreach ($client_list as $client){
                                echo "<option value='".$client['node_ID']."'>".$client["name"]."</option>";
                            }
                            ?>
                        </select>
                    </div>

                    <button class="button" type="button" id="book">Réserver</button>
                    <script>
                        document.getElementById("book").addEventListener("click", book);
                        function book() {
                            console.log("reserver");
                            let client = document.getElementById('client').value;
                            console.log("Client nodeID: "+client);
                            let logement = document.getElementById('Node_ID').value;
                            console.log("logement nodeID: "+logement);
                            let nbr_person = document.getElementById('nbr_person').value;
                            console.log("nbr_person: "+nbr_person);
                            let start = document.getElementById('debut').value;
                            console.log("nbr_person: "+start);
                            let end = document.getElementById('fin').value;
                            console.log("nbr_person: "+end);

                            // Get token
                            const token = <?php echo json_encode(get_API_Token(),true) ?>;
                            console.log("token: "+JSON.stringify(token))

                            // Create relationship between two node

                            // Assuming you have the bearer token stored in a variable called 'accessToken'
                            const accessToken = token.access_token;

                            // Data to be sent in the request body
                            const requestData = {
                                "relationship_type": "is_located_by",
                                "relationship_attributes":{
                                    "start":start,
                                    "end":end,
                                },
                                "source_node": {
                                    "id": logement,
                                    "label": "logement"
                                } ,
                                "target_node": {
                                    "id": client,
                                    "label": "client"
                                }
                            };

                            // Convert the data to JSON format
                            const jsonData = JSON.stringify(requestData);

                            // URL for the request
                            const url = '<?php echo $API_URL?>/graph/create_relationship';

                            // Creating a new XMLHttpRequest object
                            const xhr = new XMLHttpRequest();

                            // Specify the request method and URL
                            xhr.open('POST', url, true);

                            // Set the request headers
                            xhr.setRequestHeader('Content-Type', 'application/json');
                            xhr.setRequestHeader('Authorization', 'Bearer ' + accessToken);

                            // Define the callback function when the response is received
                            xhr.onload = function() {
                                if (xhr.status >= 200 && xhr.status < 300) {
                                    // Request was successful
                                    const responseData = JSON.parse(xhr.responseText);
                                    console.log(responseData);

                                    alert("Réservation enregistée")

                                } else {
                                    // Request failed
                                    console.error('Request failed with status', xhr.status);

                                    alert("Erreur dans le réservation")
                                }
                            };

                            // Define the callback function for error handling
                            xhr.onerror = function() {
                                console.error('Request failed');

                                alert("Erreur dans le réservation")
                            };

                            // Send the request with the JSON data
                            xhr.send(jsonData);

                        }

                    </script>
                    <p> <?php echo $fields['prix_nuitee']; ?>€ x <span id="nombreNuit"> 5 </span> nuits </p>

                    <p style="margin-top:30px;margin-top:10px;font-size:20px" class="bold" id="prix" data-prix="<?php echo $fields['prix_nuitee']; ?>"> Prix : <?php echo  $fields['prix_nuitee'] ?> € </p>
                </form>
            </div>

        </div>

        <div class="grand_line"> </div>

        <h2 class="bold"> Localisation </h2>

        <div  id="map-log" data-logements='<?php echo htmlspecialchars($json_data, ENT_QUOTES, 'UTF-8'); ?>' style="width:100%; height:400px;margin-top:60px;margin-bottom:90px"></div>

        <div class="grand_line"> </div>
        <h3 class="bold"> Propriétaire </h3>
        <?php
        $proprios = $fields['proprietaire'];

        if (empty($proprios)){
            echo "Pas de propriétaire";
        }

        foreach ($proprios as $proprio){
            $proprio_fields = get_fields($proprio->ID);
            echo "Nom : ".$proprio_fields['nom']."<br>";
            echo "Prénom : ".$proprio_fields['prenom']."<br>";
        }
        ?>
    </div>
</main>

<?php
include 'footer.php';
?>