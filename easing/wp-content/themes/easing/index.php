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

    <?php require_once "FilterForm.php"?>

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

                    $lien = get_permalink($logement->ID);

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
<!--                    <p>--><?php //echo $fields_logement['statut'] ?><!-- </p>-->
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