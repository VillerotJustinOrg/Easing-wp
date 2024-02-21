<?php /* Template Name: Liste logements */ ?>

<?php include 'header.php';// Récupérer tous les termes de la taxonomie (excluant les termes sans post associé)

$taxonomy = 'accessibilite';

$terms = get_terms(array(
    'taxonomy'   => $taxonomy,
    'hide_empty' => false,
    'pad_counts' => true, // Exclure les termes sans post associé
));
$filtered_terms = wp_list_filter($terms, array('count' => 0), 'NOT'); ?>

<main class="container">

    <form style="margin-top:30px;margin-bottom:30px" class="accueil_form d-flex align-items-end justify-content-center flex-row" action="<?php echo esc_url(get_permalink(27)); ?>" method="post" style="margin-bottom:50px">

        <!-- Champ nombre -->
        <div class="d-flex flex-column champ">
            <label class="bold" for="destination">Destination</label>
            <input type="text" id="destination" name="destination">
          
        </div>

        <div class="d-flex flex-column champ">
            <label class="bold" for="debut">Date de début</label>
            <input type="date" id="debut" name="debut" min="<?php echo date('Y-m-d'); ?>" />
        </div>

        <div class="d-flex flex-column champ">
            <label class="bold" for="fin">Date de fin </label>
            <input type="date" id="fin" name="fin" min="<?php echo date('Y-m-d', strtotime('+1 day')); ?>" ?> 
        </div>
        <!-- Champ nombre -->
        <div class="d-flex flex-column champ">
            <label class="bold" for="champNombre">Voyageurs</label>
            <input type="number" id="champNombre" name="champNombre">
            
        </div>

        <div class="d-flex flex-column champ champ_filtre" style="margin-right:25px">
            <label style="margin-bottom:0" class="bold" for="">Filtres</label>
            <img style="width:33px" src="<?php echo get_bloginfo('template_url'); ?>/img/filter.svg" alt="" >
        </div>

        <!-- Bouton de soumission -->
        <button class="envoyer d-flex justify-content-center align-items-center" type="submit" value=""> 
            <img src="<?php echo get_bloginfo('template_url'); ?>/img/search-white.svg" alt="" >
        </button>

        <div class="filtre-popup align-items-center justify-content-center"> 

            <div class="d-flex flex-column pop-pup-content" >
                
                <p class="croix_filtre" > + </p>

                <h3 style="margin-bottom:25px"> Filtres </h3>
                
                <div style="margin-bottom:10px;flex-flow:row wrap" class="d-flex tags-utilise">
                    
                </div>

                <div  style="flex-flow:row wrap" class="d-flex tags">

                    <?php foreach ($filtered_terms as $term) { ?>

                        <div style="margin-bottom:10px" class="tag tag-noselect"> <?php echo $term->name; ?> </div>
                            
                    <?php  } ?>

                </div>

               
            </div>

        </div> 


    </form>

    <div class="d-flex flex-row" style="flex-flow:row wrap;gap:1%">
        <?php 
            $args = array(
                'post_type'      => 'logement',
                'posts_per_page' => -1,
            );

            $logements = get_posts($args);

            foreach ($logements as $logement) {$fields_logement=get_fields($logement->ID) ?>
                
                <pre style="display:none">
                    <?php print_r($fields_logement) ?>
                </pre>

                <?php
                    
                    // Date actuelle
                    $dateDebut = new DateTime();

                    // Ajout de 2 mois à la date actuelle
                    $dateFin = clone $dateDebut;
                    $dateFin->modify('+2 months');

                    // Conversion en timestamps
                    $dateDebutTimestamp = $dateDebut->getTimestamp();
                    $dateFinTimestamp = $dateFin->getTimestamp();

                    // Génération de la première date au hasard dans la plage spécifiée
                    $dateAleatoire1 = rand($dateDebutTimestamp, $dateFinTimestamp);

                    // Ajout d'une semaine à la première date pour la deuxième date (en secondes)
                    $dateAleatoire2 = $dateAleatoire1 + 60 * 60 * 24 * 7;

                    // Vérifier si les deux dates sont dans le même mois
                    while (date('n', $dateAleatoire1) != date('n', $dateAleatoire2)) {
                        $dateAleatoire1 = rand($dateDebutTimestamp, $dateFinTimestamp);
                        $dateAleatoire2 = $dateAleatoire1 + 60 * 60 * 24 * 7;
                    }

                    $lien = get_permalink($logement->ID) . "?debut=" . date('Y-m-d', $dateAleatoire1) . "&fin=" . date('Y-m-d', $dateAleatoire2);

                ?>

                <a class="card_logement" href="<?php echo $lien ?>" target="_blank">

                    <div class="owl-carousel carousel-logement owl-theme">
                        <?php foreach($fields_logement['photos'] as $photo){ ?>
                            <img  src="<?php echo $photo['url']  ?>" >
                        <?php } ?>
                    </div> 

                   <div class="informations" >
                    <p class="ville"><?php echo $fields_logement['ville'] ?>, France </p>
                    <p><?php echo $fields_logement['titre'] ?> </p>
                    <p><?php echo $fields_logement['statut'] ?> </p>
                    <p><?php echo strftime("%e – ", $dateAleatoire1) . strftime("%e %b", $dateAleatoire2); ?></p>
                    <p class="prix"><span class="bold"> <?php echo $fields_logement['prix_nuit'] ?> </span> € par nuit </p>
                    <div class="coeur" > </div>
                   </div>

                </a>

            <?php } ?>
        </div>

</main>

<?php
include 'footer.php';
?>