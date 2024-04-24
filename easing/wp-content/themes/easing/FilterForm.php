<?php

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
        <input type="number" id="champNombre" name="champNombre" value="<?php if (isset($nombre_personne)) echo $nombre_personne ?>"
               class="editable"
               data-toggle="popover"
               data-container="body"
               data-title="Edit"
               data-content="
               <label class='bold' for='adult'>Adultes</label>
               <input type='number' min='1' step='1' name='adult' id='adult' value='<?php if (isset($adult)) { echo $adult; } else { echo 1;} ?>' onchange='updateInput1()'>
               <label class='bold' for='kid'>Enfants</label>
               <input type='number' min='0' step='1' name='kid' id='kid' value='<?php if (isset($kid)) { echo $kid; } else { echo 0;} ?>' onchange='updateInput2()'>
               <small id='kidHelp' class='form-text text-muted'>*Sont considérés comme enfants les voyageurs ayant moins de 12 ans.</small>
               <label class='bold' for='baby'>Bébés</label>
               <input type='number' min='0' step='1' name='baby' id='baby' value='<?php if (isset($baby)) { echo $baby; } else { echo 0;}?>' onchange='updateInput3()'>
               <small id='babyHelp' class='form-text text-muted'>*Sont considérés comme bébés les voyageurs ayant moins de 3 ans.</small>
               <label class='bold' for='pet'>Animaux domestiques</label>
               <input type='number' min='0' step='1' name='pet' id='pet' value='<?php if (isset($pet)) { echo $pet; } else { echo 0;} ?>' onchange='updateInput4()'>
               <small id='petHelp' class='form-text text-muted'>*Il n'est pas nécessaire d'indiquer les animaux d'assistance (dont ceux de soutien émotionnel pour les personnes présentant des troubles psychiques) et les chiens guide.</small>
               <script>

               </script>
        ">
    </div>
    <input id="adult_h" name="adult_h" type="hidden" value="<?php if (isset($adult)) { echo $adult; } else { echo 1;} ?>" onchange="change_nbr()">
    <input id="kid_h" name="kid_h" type="hidden" value="<?php if (isset($kid)) { echo $kid; } else { echo 0;} ?>" onchange="change_nbr()">
    <input id="baby_h" name="baby_h" type="hidden" value="<?php if (isset($baby)) { echo $baby; } else { echo 0;} ?>" onchange="change_nbr()">
    <input id="pet_h" name="pet_h" type="hidden" value="<?php if (isset($pet)) { echo $pet; } else { echo 0;} ?>">

    <script>
        function updateInput1() {
            document.getElementById('adult_h').value = document.getElementById('adult').value;
            change_nbr();
        }
        function updateInput2() {
            document.getElementById('kid_h').value = document.getElementById('kid').value;
            change_nbr();
        }
        function updateInput3() {
            document.getElementById('baby_h').value = document.getElementById('baby').value;
            change_nbr();
        }
        function updateInput4() {
            document.getElementById('pet_h').value = document.getElementById('pet').value;
            change_nbr();
        }
        function change_nbr(){
            let value1 = parseInt(document.getElementById('adult_h').value);
            console.log(value1);
            value1 += parseInt(document.getElementById('kid_h').value);
            console.log(value1);
            value1 += parseInt(document.getElementById('baby_h').value);
            console.log(value1);
            document.getElementById('champNombre').value = value1;
        }
    </script>

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
            <h3 style="margin-bottom:50px">Filtres Avancées</h3>

            <h4 style="text-align:center;">
                <label id="user_request_label" for="user_request">Requête IA</label>
            </h4>
            <div class="flex-row d-flex" style="margin-bottom:100px;width:100%;">
                <textarea id="user_request" style="width:100%;" name="user_request"><?php if (isset($user_request)) echo $user_request; ?></textarea>
            </div>

            <!-- TODO use tab -->
            <div class="dialog-box" style="margin-top: 30px">

                <div class="tab-container">
    <!--                 <div class="tab"> -->
    <!--                 Boutons des onglets -->
    <!--                 </div> -->
<!--                    <button id="submitBtn" type="submit">Rechercher</button>-->
                </div>
                <div class="tab-content-container">
                    <div class="tab">
                        <button class="tablinks" type="button" onclick="EquipementsManager.openTab(event, 'Caracteristiques')">Caractéristiques</button>
                        <button class="tablinks" type="button" onclick="EquipementsManager.openTab(event, 'Equipements')">Équipements</button>
                        <button id="defaultOpen" class="tablinks" type="button" onclick="EquipementsManager.openTab(event, 'Adaptations')">Adaptations</button>

                    </div>
                </div>

                <div class="dialog-content" id="formRech">
                <!-- Votre formulaire de recherche de logement -->

                    <div id="Caracteristiques" class="tabcontent">
                        <h4 style="text-align:center;padding-top:20px;">Fourchette de prix</h4>
                        <div class="d-flex flex-row justify-content-between">
                            <div class="d-flex flex-column">
                                <label id="Min_Price_Label" name="Min_Price_Label" for="Min_Price">
                                    Minimum
                                    <input style="width: 100px;" type="number" min="0.00" step="0.01" name="Min_Price" id="Min_Price" value="<?php if (isset($Min_Price)) echo $Min_Price ?>">
                                </label>
                            </div>
                            <div class="d-flex flex-column">
                                <label id="Max_Price_Label" name="Max_Price_Label" for="Max_Price">
                                    Maximum
                                    <input style="width: 100px;" type="number" min="0.00" step="0.01" name="Max_Price" id="Max_Price" value="<?php if (isset($Max_Price)) echo $Max_Price ?>">
                                </label>
                            </div>
                        </div>

                        <h4>
                            <label id="type_label" for="type">
                                Type de réservation
                                <select id="type" name="type">
                                    <option value=""></option>
                                    <option <?php if (isset($Type) AND $Type == "Logement entier") echo "selected"?> value="Logement entier">Logement entier</option>
                                    <option <?php if (isset($Type) AND $Type == "Chambre") echo "selected"?> value="Chambre">Chambre</option>
                                </select>
                            </label>
                        </h4>

                        <h4>
                            <label id="categorie_label" for="categorie">
                                Catégories de logement
                                <select id="categorie" name="categorie">
                                    <option value=""></option>
                                    <option <?php if (isset($Categorie) AND $Categorie == "Maison") echo "selected"?> value="Maison">Maison</option>
                                    <option <?php if (isset($Categorie) AND $Categorie == "Appartement") echo "selected"?> value="Appartement">Appartement</option>
                                    <option <?php if (isset($Categorie) AND $Categorie == "Hébergement Alternatif") echo "selected"?> value="Hébergement Alternatif">Hébergement Alternatif</option>
                                    <option <?php if (isset($Categorie) AND $Categorie == "Chambre d Hôte") echo "selected"?> value="Chambre d Hôte">Chambre d'Hôte</option>
                                    <option <?php if (isset($Categorie) AND $Categorie == "Hôtel") echo "selected"?> value="Hôtel">Hôtel</option>
                                    <option <?php if (isset($Categorie) AND $Categorie == "Boutique-Hôtel") echo "selected"?> value="Boutique-Hôtel">Boutique-Hôtel</option>

                                </select>
                            </label>
                        </h4>

                        <div class="row d-flex flex-row">
                            <div class="col-6 d-flex flex-column justify-content-center">
                                <h4 style="text-align:center;">
                                    <label id="reservation_auto_label" for="reservation_auto">
                                        Réservation automatique <input type="checkbox" id="reservation_auto" name="reservation_auto" <?php if ($Reservation_auto) echo 'checked'; ?> >
                                    </label>
                                </h4>
                            </div>
                            <div class="col-6 d-flex flex-column justify-content-center">
                                <h4 style="text-align:center;">
                                    <label id="arrive_auto_label" for="arrive_auto">
                                        Arrivée autonome <input type="checkbox" id="arrive_auto" name="arrive_auto" <?php if ($Arrive_auto) echo 'checked'; ?> >
                                    </label>
                                </h4>
                            </div>
                        </div>


                        <h4 style="text-align:center;padding-top:20px;">Pièces et couchages</h4>
                        <div class="flex-row d-flex justify-content-between">
                            <h6>
                                <label id="nbr_chambre_label" for="nbr_chambre">
                                    Chambre
                                    <input type="number" id="nbr_chambre" name="nbr_chambre" min="0" step="1" value="<?php if (isset($nbr_chambre)) { echo $nbr_chambre; } else { echo 0;} ?>">
                                </label>
                            </h6>
                        </div>

                        <div class="flex-row d-flex justify-content-between">
                            <h6>
                                <label id="nbr_salle_bain_label" for="nbr_salle_bain">
                                    Salles de bain
                                    <input type="number" id="nbr_salle_bain" name="nbr_salle_bain" min="1" step="1" value="<?php if (isset($nbr_salle_bain)) { echo $nbr_salle_bain; } else { echo 1;} ?>">
                                </label>
                            </h6>
                        </div>

                        <div class="flex-row d-flex justify-content-between">
                            <h6>
                                <label id="nbr_lits_label" for="nbr_lits">
                                    Lits & Couchages <input type="number" id="nbr_lits" name="nbr_lits" min="1" step="1" value="<?php if (isset($nbr_lits)) { echo $nbr_lits; } else { echo 1;} ?>">
                                </label>
                            </h6>
                        </div>
                    </div>

                    <div id="Equipements" class="tabcontent">
                        <div id="equipementsContent" data-equipements="<?php echo get_bloginfo('template_url'); ?>/equipements.json"></div>
                    </div>

                    <div id="Adaptations" class="tabcontent">
                        <div id="adaptationsContent" data-adaptations="<?php echo get_bloginfo('template_url'); ?>/adaptations.json"></div>
                    </div>

                    <script src="<?php echo get_bloginfo('template_url'); ?>/tabs.js"></script>
    <!--                <script>-->
    <!--                    // Afficher par défaut l'onglet Adaptations-->
    <!--                    document.getElementById("defaultOpen").click();-->
    <!--                </script>-->
                </div>
            </div>

            <!-- END tab -->

        </div>
    </div>
</form>