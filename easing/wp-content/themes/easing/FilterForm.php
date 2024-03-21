<?php

if (!isset($pet)) $pet = false;
if (!isset($plain_pied)) $plain_pied = false;

?>


<form style="margin-top:30px;margin-bottom:50px" class="accueil_form d-flex align-items-end justify-content-center flex-row" action="<?php echo esc_url(get_permalink(27)); ?>" method="post">
        <!-- Champ nombre -->
    <div class="d-flex flex-column champ">
        <label class="bold" for="destination">Destination</label>
        <input type="text" id="destination" name="destination" value="<?php if (isset($destination)) echo $destination ?>">
    </div>
    <div class="d-flex flex-column champ">
        <label class="bold" for="debut">Date de début </label>
        <input type="date" id="debut" name="debut" min="<?php echo date('Y-m-d'); ?>" value="<?php if (isset($debut)) echo $debut ?>"/>
    </div>
    <div class="d-flex flex-column champ">
        <label class="bold" for="fin">Date de fin </label>
        <input type="date" id="fin" name="fin" min="<?php echo date('Y-m-d', strtotime('+1 day')); ?>" value="<?php if (isset($fin)) echo $fin ?>" >
    </div>
    <!-- Champ nombre -->
    <div class="d-flex flex-column champ">
        <label class="bold" for="champNombre">Voyageurs</label>
        <input type="number" id="champNombre" name="champNombre" value="<?php if (isset($nombre_personne)) echo $nombre_personne ?>">
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

            <h4 style="text-align:center;padding-top:20px;">Fourchette de prix</h4>
            <div class="d-flex flex-raw row justify-content-between">
                <div class="d-flex flex-column">
                    <label id="Min_Price_Label" name="Min_Price_Label" for="Min_Price">Minimum</label>
                    <input type="number" min="0.00" step="0.01" name="Min_Price" id="Min_Price" value="<?php if (isset($Min_Price)) echo $Min_Price ?>">
                </div>
                <div class="d-flex flex-column">
                    <label id="Max_Price_Label" name="Max_Price_Label" for="Max_Price">Maximum</label>
                    <input type="number" min="0.00" step="0.01" name="Max_Price" id="Max_Price" value="<?php if (isset($Max_Price)) echo $Max_Price ?>">
                </div>
            </div>

            <h4 style="text-align:center;padding-top:20px;">
                <label id="type_label" for="type">Type de réservation</label>
            </h4>
            <select id="type">
                <option value="Logement entier">Logement entier</option>
                <option value="Chambre">Chambre</option>
            </select>

            <div class="row d-flex flex-row mt-5">
                <div class="col-6 d-flex flex-column justify-content-center">
                    <h4 style="text-align:center;">
                        <label id="reservation_auto_label" for="reservation_auto">
                            Réservation automatique
                        </label>
                    </h4>
                    <input type="checkbox" id="reservation_auto" name="reservation_auto">
                </div>
                <div class="col-6 d-flex flex-column justify-content-center">
                    <h4 style="text-align:center;">
                        <label id="arrive_auto_label" for="arrive_auto">
                            Arrivée autonome
                        </label>
                    </h4>
                    <input type="checkbox" id="arrive_auto" name="arrive_auto">
                </div>
            </div>

            <h4 style="text-align:center;padding-top:20px;">
                <label id="type_label" for="type">Catégories de logement</label>
            </h4>
            <select id="type">
                <option value="Maison">Maison</option>
                <option value="Appartement">Appartement</option>
                <option value="Gîtes & Maison d'hôtes">Gîtes & Maison d'hôtes</option>
                <option value="Hébergements insolites & alternatifs">Hébergements insolites & alternatifs</option>
            </select>

            <h4 style="text-align:center;padding-top:20px;">Pièces et couchages</h4>
            <div class="flex-row d-flex justify-content-between" style="margin-bottom: 20px">
                <h6>
                    <label id="nbr_chambre_label" for="nbr_chambre">
                        Chambre
                    </label>
                </h6>
                <input type="number" id="nbr_chambre" name="nbr_chambre" min="0" step="1" value="0">
            </div>

            <div class="flex-row d-flex justify-content-between" style="margin-bottom: 20px">
                <h6>
                    <label id="nbr_salle_bain_label" for="nbr_salle_bain">
                        Salles de bain
                    </label>
                </h6>
                <input type="number" id="nbr_salle_bain" name="nbr_salle_bain" min="1" step="1" value="1">
            </div>

            <div class="flex-row d-flex justify-content-between" style="margin-bottom: 20px">
                <h6>
                    <label id="nbr_lits_label" for="nbr_lits">
                        Lits & Couchages
                    </label>
                </h6>
                <input type="number" id="nbr_lits" name="nbr_lits" min="1" step="1" value="1">
            </div>

            <h4 style="text-align:center;padding-top:20px;">
                <label id="type_label" for="type">Équipements</label>
            </h4>
            <div class="flex-row d-flex" style="margin-bottom:20px">
                Work In Progress
            </div>

            <h4 style="text-align:center;padding-top:20px;">
                <label id="type_label" for="type">Adaptations et éléments d’accessibilité</label>
            </h4>
            <div class="flex-row d-flex" style="margin-bottom:20px">
                Work In Progress
            </div>

        </div>
    </div>
</form>