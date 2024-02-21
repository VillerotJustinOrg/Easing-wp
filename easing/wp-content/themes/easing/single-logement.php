
<?php
include 'header.php';
$fields=get_fields();
?>

<?php
$nombre = $_GET["nombre"];
$destination = $_GET["destination"];
$debut = $_GET["debut"];
$fin = $_GET["fin"];

?>

<!--<pre style="display:none">--><?php //print_r($fields) ?><!-- </pre>-->

<?php $data = array(
    'latitude' => $fields['latitude'],
    'longitude' => $fields['longitude']
); 

$json_data = json_encode($data); ?>


<main id="singleLogement" class="container">
    <div> 

        <h1 class="bold" ><?php echo $fields['titre'] ?> </h1>

        <div class="wrapper">
            <?php $i=1; foreach($fields['photos'] as $photo){ if($i<8){?>

                <?php if($i!=3){ ?> 
                    <div style="background-size:cover;background-position:center center;background-image: url(<?php echo $photo['url'] ?>);" class="case-<?php echo $i ?>"></div>
                <?php } ?>

                <?php if($i==3){ ?> 
                    <a target=”_blank” href="<?php echo get_bloginfo('template_url'); ?>/visite/Maison.html" style="position:relative;background-size:cover;background-position:center center;background-image: url(<?php echo $photo['url'] ?>);" class="case-<?php echo $i ?>">
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

                <img style="width:100%" src="<?php echo get_bloginfo('template_url'); ?>/img/Calendrier.png" >
                
                <div class="line"> </div>

                <h2 class="bold"> Ce que propose le logement </h2>

                <p style="margin-bottom:10px" > • Rampe d'Accès: Une rampe d'accès facilement installable à l'entrée principale de la maison pour faciliter l'entrée aux personnes en fauteuil roulant. </p>
                <p style="margin-bottom:10px"> • Portes Larges: Les portes intérieures ont été élargies pour permettre un accès aisé aux différentes pièces de la maison. </p>
                <p style="margin-bottom:10px"> • Salle de Bains Accessible : Une salle de bains spécialement aménagée avec des barres d'appui, une douche à l'italienne, et des toilettes surélevées pour assurer un confort optimal. </p>
                <p style="margin-bottom:10px"> • Chambre au Rez-de-Chaussée : Une chambre située au rez-de-chaussée, équipée pour répondre aux besoins des personnes à mobilité réduite, avec un espace suffisant pour la circulation en fauteuil roulant. </p>
                <p> • Cuisine Accessible : Une cuisine entièrement équipée avec des plans de travail ajustables en hauteur, des espaces dégagés sous l'évier, et des ustensiles faciles à manipuler.</p>
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

        <h2 class="bold" style="margin-bottom:50px"> Commentaires </h2>

        <div class="d-flex flex-row justify-content-between" style="flex-flow: row wrap">
            <div class="one-commentaire"> 
                <div class="photo d-flex flex-row align-items-center"> 
                    <img src="<?php echo get_bloginfo('template_url'); ?>/img/femme1.jpeg" >
                    <p class="nom bold"> Sophie </p>
                </div>
                <p> Nous avons passé un séjour absolument merveilleux dans cette maison à Montbéliard. La décoration est à la fois élégante et chaleureuse, créant une atmosphère accueillante. Les chambres étaient confortables, la cuisine bien équipée, et le jardin était un véritable havre de paix. La proximité des attractions locales a rendu nos journées encore plus agréables. Nous recommandons vivement cette maison pour une escapade inoubliable à Montbéliard! </p>
            </div>

            <div class="one-commentaire"> 
                <div class="photo d-flex flex-row align-items-center"> 
                    <img src="<?php echo get_bloginfo('template_url'); ?>/img/homme.jpeg" >
                    <p class="nom bold"> Antoine </p>
                </div>
                <p> Un séjour parfait dans cette maison pleine de charme ! L'emplacement est idéal, à quelques pas du centre-ville, et pourtant l'endroit est incroyablement paisible. Les espaces communs sont spacieux et joliment décorés. Les chambres étaient d'un confort absolu, et la cour extérieure était parfaite pour nos soirées en plein air. Une adresse que je recommande vivement pour découvrir Montbéliard dans les meilleures conditions.  </p>
            </div>

            <div class="one-commentaire"> 
                <div class="photo d-flex flex-row align-items-center"> 
                    <img src="<?php echo get_bloginfo('template_url'); ?>/img/femme3.jpeg" >
                    <p class="nom bold"> Isabelle </p>
                </div>
                <p> Cette maison est un véritable bijou au cœur de Montbéliard. Nous avons été séduits par l'élégance de la décoration et la propreté impeccable des lieux. Les propriétaires ont pensé à tout pour rendre notre séjour agréable, des petites attentions aux équipements modernes. L'emplacement central nous a permis de visiter facilement les sites historiques et de profiter des délices culinaires locaux. Une expérience à renouveler, sans aucun doute ! </p>
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