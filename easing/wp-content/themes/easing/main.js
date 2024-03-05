// Declare the map letiable outside the initialize function
let map;

/* Filtre */
$(document).ready(function() {
    $('.champ_filtre').on('click', function() {
        console.log("Popup")
        $('.filtre-popup').css('display', 'flex');
    });

    $('.croix_filtre').on('click', function() {
        $('.filtre-popup').css('display', 'none');
    });
});

$(document).ready(function () {
  // Utilisation de la délégation d'événements pour gérer les clics sur les éléments de classe tag-noselect
  $(document).on('click', '.tag-noselect', function () {
      console.log("no-select");

      let elementADeplacer = $(this);

      // Ajoute la classe tag-select
      elementADeplacer.removeClass('tag-noselect');
      elementADeplacer.addClass('tag-select');

      // Sélectionnez le div destination
      let divDestination = $('.tags-utilise');
      // Déplacez l'élément vers le div destination
      divDestination.append(elementADeplacer);
  });

  // Utilisation de la délégation d'événements pour gérer les clics sur les éléments de classe tag-select
  $(document).on('click', '.tag-select', function () {
      console.log("select");

      let elementADeplacer = $(this);

      // Sélectionnez le div destination
      let divDestination = $('.tags');
      // Déplacez l'élément vers le div destination
      divDestination.append(elementADeplacer);

      elementADeplacer.removeClass('tag-select');
      // Ajoute la classe tag-noselect
      elementADeplacer.addClass('tag-noselect');
  });
});

function initialize() {
  let logementsData = document.getElementById('logements').getAttribute('data-logements');
  let logements = JSON.parse(logementsData);

  // Check if the map is already initialized
  if (!map) {
      map = L.map('map');

      let osmLayer = L.tileLayer('http://{s}.tile.osm.org/{z}/{x}/{y}.png', {
          attribution: '© OpenStreetMap contributors',
          maxZoom: 19
      });

      map.addLayer(osmLayer);
  }

  // Clear all existing markers
  map.eachLayer(function (layer) {
      if (layer instanceof L.Marker) {
          map.removeLayer(layer);
      }
  });

  let i=0;
  logements.forEach(function (coordonnees) {
      let latitude = parseFloat(coordonnees.latitude);
      let longitude = parseFloat(coordonnees.longitude);

      let customSvgIcon = L.divIcon({
        html: `<svg xmlns="http://www.w3.org/2000/svg" width="27.794" height="45.757" viewBox="0 0 47.794 65.757">
                <g id="Groupe_424" data-name="Groupe 424" transform="translate(1638.819 712.838)">
                    <path class="marker-map" id="${i}" data-name="Soustraction 1" d="M23.4,63.807v0l-.9-1.269c-1.767-2.478-3.681-4.982-5.65-7.54l-.088-.116-.016-.02c-1.854-2.424-3.771-4.926-5.581-7.47l-.007-.015c-.176-.249-.395-.554-.645-.906l-.284-.4-.964-1.351-.006-.009C4.79,38.484,.177,32.046,.007,23.848L0,23.4A23.4,23.4,0,0,1,39.943,6.857,23.333,23.333,0,0,1,46.794,23.4l-.007.009v.438c-.169,8.2-4.788,14.641-9.255,20.873-.129.179-.256.358-.381.535-.109.152-.216.3-.321.451-.455.64-.888,1.25-1.186,1.673l-.007.015c-1.9,2.667-3.908,5.285-5.68,7.591l-.012.015-.258.334-.112.146c-1.767,2.306-3.595,4.692-5.273,7.06L23.4,63.8Zm0-54.253a13.855,13.855,0,1,0,9.788,4.055A13.755,13.755,0,0,0,23.4,9.553Z" transform="translate(-1638.319 -712.338)" fill="#6300FF" stroke="rgba(0,0,0,0)" stroke-miterlimit="10" stroke-width="1"></path>
                </g>
              </svg>`,
        className: "",
        iconSize: [24, 40],
        iconAnchor: [14, 42],
    });  

      let marker = L.marker([latitude, longitude], { icon: customSvgIcon });
      let popupContent = "<a style='width:100%' target=”_blank” href='"+ coordonnees.link +"'>"
                        + "<img src='" + coordonnees.photos[0].url + "' alt='Description de l'image' width='100' height='100'>"
                        + "<div class='text-pop'>" + coordonnees.titre + " - <span class='bold'> " + coordonnees.ville 
                        + "</span> <br><span class='bold'>" + coordonnees.prix_nuit + " </span> € par nuit </div>"
                        + "</a>";
      marker.bindPopup(popupContent);
      marker.addTo(map);
      i++;
  });

  if (logements.length > 1) {
      let bounds = new L.LatLngBounds();

      logements.forEach(function (coordonnees) {
          let latitude = parseFloat(coordonnees.latitude);
          let longitude = parseFloat(coordonnees.longitude);
          bounds.extend([latitude, longitude]);
      });

      map.fitBounds(bounds);
  } else if (logements.length === 1) {
      map.setView([parseFloat(logements[0].latitude), parseFloat(logements[0].longitude)], 13);
  } else {
      map.setView([48.833, 2.333], 6);
  }



  /* Changement couleur marqueur */

$('.card_logement').mouseenter(function () {
  console.log("hello");
  let id = $(this).attr('id');
  $('.marker-map[id="' + id + '"]').css('fill', 'red'); // Change background color, for example
});

$('.card_logement').mouseleave(function () {
  let id = $(this).attr('id');
  $('.marker-map[id="' + id + '"]').css('fill', '#6300FF'); // Reset background color
});

$('.owl-prev').click(function (event) {
  event.preventDefault();
});

$('.owl-next').click(function (event) {
  event.preventDefault();
});

}

function initializeLogement() {

    const logements = document.querySelector('#map-log');
    let logementInfo = null;
    if (logements) {
        let logementsData = logements.getAttribute('data-logements');
        logementInfo = JSON.parse(logementsData);
    } else {
        console.log('Logement map data does not exist.');
        return
    }

    console.log(logementInfo);

    let map = L.map('map-log').setView([logementInfo.latitude, logementInfo.longitude], 7);

    let osmLayer = L.tileLayer('http://{s}.tile.osm.org/{z}/{x}/{y}.png', {
        attribution: '© OpenStreetMap contributors',
        maxZoom: 19
    });

    map.addLayer(osmLayer);

    let customSvgIcon = L.divIcon({
        html: `<svg xmlns="http://www.w3.org/2000/svg" width="27.794" height="45.757" viewBox="0 0 47.794 65.757">
               <g id="Groupe_424" data-name="Groupe 424" transform="translate(1638.819 712.838)">
               <path class="marker-map" data-name="Soustraction 1" d="M23.4,63.807v0l-.9-1.269c-1.767-2.478-3.681-4.982-5.65-7.54l-.088-.116-.016-.02c-1.854-2.424-3.771-4.926-5.581-7.47l-.007-.015c-.176-.249-.395-.554-.645-.906l-.284-.4-.964-1.351-.006-.009C4.79,38.484,.177,32.046,.007,23.848L0,23.4A23.4,23.4,0,0,1,39.943,6.857,23.333,23.333,0,0,1,46.794,23.4l-.007.009v.438c-.169,8.2-4.788,14.641-9.255,20.873-.129.179-.256.358-.381.535-.109.152-.216.3-.321.451-.455.64-.888,1.25-1.186,1.673l-.007.015c-1.9,2.667-3.908,5.285-5.68,7.591l-.012.015-.258.334-.112.146c-1.767,2.306-3.595,4.692-5.273,7.06L23.4,63.8Zm0-54.253a13.855,13.855,0,1,0,9.788,4.055A13.755,13.755,0,0,0,23.4,9.553Z" transform="translate(-1638.319 -712.338)" fill="#6300FF" stroke="rgba(0,0,0,0)" stroke-miterlimit="10" stroke-width="1"></path>
               </g>
               </svg>`,
        className: "",
        iconSize: [24, 40],
        iconAnchor: [14, 42],
    });

    let marker = L.marker([logementInfo.latitude, logementInfo.longitude], { icon: customSvgIcon }).addTo(map);

    // Center the map on the marker
    map.setView(marker.getLatLng(), map.getZoom());
}

$('#debut, #fin').on('input', function() {
  let debut = $('#debut').val();
  let fin = $('#fin').val();

  // Vérifiez si l'un des champs de date est rempli
  if ((debut && !fin) || (!debut && fin)) {
      // Rendez les deux champs obligatoires
      console.log("required");
      $('#debut').prop('required', true);
      $('#fin').prop('required', true);
  } else {
      console.log("pas required");
      // Aucun des deux champs n'est rempli, retirez le required
      $('#debut').prop('required', false);
      $('#fin').prop('required', false);
  }
});



/* Formulaire AJAX */

$('.search_form').submit(function (e) {
    e.preventDefault();

    let nombre = $('#champNombre').val();
    let destination = $('#destination').val();
    let debut = $('#debut').val();
    let fin = $('#fin').val();
    let formAction = $(this).attr('action');

    $.ajax({
        type: 'POST',
        url: formAction,
        data: { 
          champNombre: nombre,
          destination: destination,
          debut: debut,
          fin: fin},
        success: function (data) {
          let listeLogementsElement = $(data).find('.liste-logements');
          $('.liste-logements').html(listeLogementsElement.html());
          let updatedData = $(data).find('#logements').attr('data-logements');

          $('.owl-carousel').owlCarousel({
            loop:true,
            margin:0,
            nav:false,
            mouseDrag: true,
            responsive:{
                0:{
                    items:1
                },
            }
          })
      
          // Update the data attribute of #logements
          $('#logements').attr('data-logements', updatedData);
      
          // Call initialize function to reload the map with updated data
          initialize();
      },
      
        error: function () {
            console.log('Erreur lors de la requête AJAX');
        }
    });
});


/* Date formulaire */

let startDateInput = $('#debut');
let endDateInput = $('#fin');

// Ajoutez un gestionnaire d'événements pour la date de début
let debutInput = $('#debut');
let finInput = $('#fin');

// Fonction pour mettre à jour la date minimale pour le champ de date de fin
function updateMinDate() {
  // Obtenez la date de début sélectionnée
  let debutValue = debutInput.val();

  // Vérifiez si la date de début est définie
  if (debutValue) {
    // Convertissez la date de début en objet Date
    let dateDebut = new Date(debutValue);

    // Ajoutez un jour à la date de début
    dateDebut.setDate(dateDebut.getDate() + 1);

    // Formattez la date pour l'attribut min
    let minDateFin = formatDate(dateDebut);

    // Mettez à jour la date minimale pour le champ de date de fin
    finInput.attr('min', minDateFin);
  }
}

// Attachez l'événement change au champ de date de début
debutInput.on('change', updateMinDate);

updateMinDate();


// Attachez l'événement change au champ de date de début
debutInput.on('change', updateMinDate);

/*finInput.on('change', function() {
      // Mettez à jour la date maximale pour la date de début en fonction de la date de fin sélectionnée
      debutInput.attr('max', finInput.val());
    });*/

// Fonction pour formater la date au format "YYYY-MM-DD"
function formatDate(date) {
  let year = date.getFullYear();
  let month = ('0' + (date.getMonth() + 1)).slice(-2);
  let day = ('0' + date.getDate()).slice(-2);
  return year + '-' + month + '-' + day;
}

/* Coeur rouge */

$('.coeur').click(function (event) {
  // Empêcher la propagation de l'événement de clic vers les parents
  event.preventDefault();

  // Ajouter ici le code pour basculer la classe "rouge" sur la div .coeur
  $(this).toggleClass('rouge');
});

/*Carrousel logement */

$('.owl-carousel').owlCarousel({
  loop:false,
  margin:0,
  nav:true,
  mouseDrag: true,
  responsive:{
      0:{
          items:1
      },
  }
})

$('.owl-prev').click(function (event) {
  event.preventDefault();
});

$('.owl-next').click(function (event) {
  event.preventDefault();
});


/*Calcul prix d'un logement */

function calculerNombreJours() {
  let debutInput = $('#debut').val();
  let finInput = $('#fin').val();

  // Vérifier si les champs de date de début et de fin sont vides
  if (!debutInput || !finInput) {
      const elementPrix = document.querySelector('#prix');
      if (elementPrix) {
          // Element exists, perform actions on it
          // Afficher le message spécial
          elementPrix.innerHTML = 'Sélectionner une date pour afficher le prix';
      } else {
          console.log('Element prix does not exist.');
      }
    return; // Sortir de la fonction si les dates sont vides
  }

  let dateDebut = new Date(debutInput);
  let dateFin = new Date(finInput);

  // Calcul du nombre de millisecondes entre les deux dates
  let differenceEnMillisecondes = dateFin - dateDebut;

  // Calcul du nombre de jours
  let differenceEnJours = Math.floor(differenceEnMillisecondes / (1000 * 60 * 60 * 24));

  let prixNuit = document.getElementById('prix').getAttribute('data-prix');
  let elementPrix = document.getElementById('prix');
  let nombreNuit = document.getElementById('nombreNuit');

  // Mettez à jour la valeur du prix dans le HTML
  elementPrix.innerHTML = 'Prix : ' + differenceEnJours * prixNuit + ' €';
  nombreNuit.innerHTML = differenceEnJours;

}

// Attachez l'événement onchange aux champs de date
$('#debut, #fin').change(calculerNombreJours);

// Appel initial pour afficher le nombre de jours si les dates sont déjà remplies
// try {
//     calculerNombreJours();
// } catch (error) {
//     console.log(error)
// }

calculerNombreJours();



