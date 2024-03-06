<?php


?>


<form style="margin-top:30px;margin-bottom:50px" class="accueil_form d-flex align-items-end justify-content-center flex-row" action="<?php echo esc_url(get_permalink(27)); ?>" method="post">
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
        <div class="d-flex flex-column pop-pup-content" style="height:80%;width:60%;overflow:auto;">
            <p class="croix_filtre" > + </p>
            <h3 style="margin-bottom:25px">Filtres Avancées</h3>

            <h4 style="text-align:center;padding-top:20px;"><label id="type_place_label" name="type_place" for="type_place">Type of place</label></h4>
            <div class="flex-column d-flex" style="margin-bottom: 20px">
                <div class="d-flex flex-raw row">
                    <?php
                    // TODO select type place
                    ?>
                </div>
            </div>

            <h4 style="text-align:center;padding-top:20px;">Price Range</h4>
            <div class="d-flex flex-raw row justify-content-between">
                <div class="d-flex flex-column">
                    <label id="Min_Price_Label" name="Min_Price_Label" for="Min_Price">Minimum</label>
                    <input type="number" min="0.00" step="0.01" name="Min_Price" id="Min_Price">
                </div>
                <div class="d-flex flex-column">
                    <label id="Max_Price_Label" name="Max_Price_Label" for="Max_Price">Maximum</label>
                    <input type="number" min="0.00" step="0.01" name="Max_Price" id="Max_Price">
                </div>
            </div>

            <div class="flex-column d-flex justify-content-center" style="margin-bottom: 20px">
                <h4><label id="pet_label" name="pet" for="pet">Allow pet</label></h4>
                <input type="checkbox" id="pet" name="pet">
            </div>

            <div class="flex-row d-flex" style="margin-bottom: 20px">
                <h4><label id="plain-pied_label" name="plain-pied" for="plain-pied">Plain-pied</label></h4>
                <input type="checkbox" id="plain-pied" name="plain-pied">
            </div>

            <div class="flex-column d-flex" style="margin-bottom: 20px">
                <h4><label id="user_request_label" name="user_request" for="user_request">AI Request</label></h4>
                <textarea id="user_request" name="user_request"></textarea>
            </div>
        </div>
    </div>
</form>