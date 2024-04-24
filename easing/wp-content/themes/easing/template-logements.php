<?php /* Template Name: Liste logements */

require_once 'header.php';

require_once 'Code_API/UtilsAPI.php';

echo "<main>";

require_once "Filter-Treatment.php";

require_once "FilterForm.php";

$startDate = new DateTime($debut);
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

            $lien = esc_url(get_permalink($logement_id));
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
                <p class="prix"><span class="bold"> <?php echo $fields_logement['prix_nuitee'] ?> </span> € par nuit </p>
                <?php if($nombre_nuit!=0){ ?>
                    <p><span class="bold"><?php echo $fields_logement['prix_nuitee']*$nombre_nuit ?></span>€ au total </p>
                <?php }else{ ?>
                    <p><span class="bold"><?php echo $fields_logement['prix_nuitee']*7 ?></span>€ au total </p>
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
