///////////////////////
// Default variables //

// const { tmpdir } = require("os");



///////////////////////
console.log("MAP.JS VERSION v 0.3");
// alert("map.js chargé !");
// Taille de l'image de la carte  non sticky


function getWidthAndHeight() {
    map_mpw = document.getElementById('mappicture').naturalWidth;
    map_mph = document.getElementById('mappicture').naturalHeight;
    // Impossible d'extraire la taille de l'image si elle n'est pas chargée...
    if (typeof(map_mpw) === 'undefined' || map_mpw === 0) { map_mpw = 1749; };
    if (typeof(map_mph) === 'undefined' || map_mph === 0) { map_mph = 1176; };

    // console.log("getWidthAndHeight\n\tmap_mpw = " + map_mpw + "\n\tmap_mph = " + map_mph);
    // stickyDisplay();
    // showPosition();
}

function initVar() {
    premierChargement = false;
    // map_path = ".";
    map_path = ".";
    stickymode = true;
    rsc_img_carte_sticky = map_path + "/carte_sticky.png";
    // rsc_img_carte_sticky = "https://cdn.glitch.me/15d49394-748d-4f7b-91c0-57f9024892d5/carte_sticky.jpg";
    rsc_img_carte = map_path + "/carte.jpg";
    // rsc_img_carte = "https://cdn.glitch.me/15d49394-748d-4f7b-91c0-57f9024892d5/carte.jpg";

    // Possibilité d'indiquer sa propre URL de fichier json si le js de l'éditeur de twine est exécuté avant ce script ?
    if (typeof(rsc_json_map) === 'undefined') { rsc_json_map = map_path + "/map.json"; };
    // console.log("rsc_json_map = " + rsc_json_map);
    // rsc_json_map = "https://cdn.glitch.me/15d49394-748d-4f7b-91c0-57f9024892d5/map.json?v=1639867578785";
    rsc_img_here = map_path + "/position.png";
    //rsc_img_here = "https://cdn.glitch.me/15d49394-748d-4f7b-91c0-57f9024892d5/position.png";
    rsc_img_close = map_path + "/close.png";
    // rsc_img_close = "https://cdn.glitch.me/15d49394-748d-4f7b-91c0-57f9024892d5/close.png";
    rsc_img_position = map_path + "/position.png";
    // rsc_img_position = "https://cdn.glitch.me/15d49394-748d-4f7b-91c0-57f9024892d5/position.png";
    // console.log("Language: " + lang);
    // console.log("maplocation: " + loc);
    // getWidthAndHeight();

}



/* Liste des lieux :
    Abri_du_pèlerin
    Chapelle_Façade_OUEST
    Chapelle_Façade_SUD_porte
    Chapelle_Façade_SUD_vitraux
    Départ
    jeux
    Le_Campanile
    Le_Monastère
    Maison_du_Chapelain
    Pyramide
    Quitter_le_site
*/

function changeLoc(locnum) {
    // concordance légende / lieux
    // Change de lieu en fonction du numéro de lieu envoyé par le bouton de la carte
    // console.log('locnnum = ' + locnum);
    // console.log('loc = ' + loc);
    // console.log(typeof(datamap.num[locnum]));
    // console.log(datamap.num[locnum]);
    loc = datamap.num[locnum];
    // Change d'emplacement dans Twine
    console.log("\nwindow.story.show(\"" + loc + "\");");
    try { window.story.show(loc); } catch (e) {
        console.error("Location or 360 photo of location: " + loc + " (loc n°" + locnum + ") is not foundable.\n" + e);
    }

    showPosition();
    switchSticky();
}

function loadJSON(callback) {
    if (typeof(rsc_json_map) === "undefined") { initVar(); };
    console.log('rsc_json_map=' + rsc_json_map);
    var xobj = new XMLHttpRequest();
    xobj.overrideMimeType("application/json; charset=utf-8");
    xobj.open('GET', rsc_json_map, true);
    xobj.onreadystatechange = function() {
        if (xobj.readyState == 4 && xobj.status == "200") {
            // Required use of an anonymous callback as .open will NOT return a value but simply returns undefined in asynchronous mode
            callback(xobj.responseText);
        }
    };
    xobj.send(null);
}

function getTitle(code) {
    // TODO: à partir du code du passage, retrouver la traduction littérale
    return datamap.lang[lang].loc[code].name;
}

function getName() {
    // TODO: à partir du code du passage, retrouver la traduction littérale
    return datamap.lang[lang].loc[code].name;
}

function showPosition() {
    // Parse JSON string into object

    // récupération des données json

    //console.log(datamap.lang);
    // console.log(Object.keys(datamap.lang)[0]);
    // if (typeof(lang) === "undefined") { lang = datamap.default.lang }; // langue par défaut est la première qui arrive dans le json
    // if (typeof(loc) === "undefined") { loc = datamap.default.loc }; // langue par défaut est la première qui arrive dans le json
    // if (typeof(lang) === "undefined") { lang = Object.keys(datamap.lang)[0] }; // langue par défaut est la première qui arrive dans le json
    // if (typeof(loc) === "undefined") { loc = Object.keys(datamap.lang[lang].loc)[0] }; // Forcer ici la case départ. Ex. : "Abri_du_pèlerin"
    // obtien le nom de l'actuel passage.

    if (typeof(pos) === 'undefined') {
        console.log("showPosition is waiting for json data.");
        initJson();
        return;
    }; // ne s'exécute pas si pas de données json
    try {
        // console.log(typeof(window.passage));
        // console.log(typeof(window.passage.name));
        //if (typeof(window.passage) !== 'undefined' && typeof(window.passage.name) !== 'undefined') {
        loc = window.passage.name;
        //}
    } catch (error) {
        console.error(error);
    }

    console.log("LANGUAGE: " + lang);
    if (typeof(loc) === 'undefined') { loc = title; };
    console.log("maplocation: " + loc);
    // console.log("datamap: " + typeof(datamap));
    // if (typeof(title) === 'undefined') {
    if (typeof(pos) === 'undefined') { initJson(); }; // si pas de données
    if (typeof(title) != 'string') { title = window.passage.name; };
    if (typeof(loc) != 'string') { loc = title; };
    console.log("\tloc1=" + loc);
    try {
        title = datamap.lang[lang].loc[loc].name;
        //console.log("datamap.coord=" + datamap.coord.loc[loc]);
    } catch (error) {
        console.log(error + "\nMISSED DATA from map.json with: " + "\n\tlang = " + lang + "\n\t& loc = " + loc + "\n\tdatamap: " + typeof(datamap));
        title = window.passage.name;
        loc = title;
    }

    try {
        if (typeof(loc) != 'string') { loc = title; };
        console.log("\tloc2=" + loc);
        console.log("\ttypeof(loc)=" + typeof(loc));
        if (typeof(datamap.coord[loc].x) != 'undefined') { x = datamap.coord[loc].x; };
        if (typeof(datamap.coord[loc].y) != 'undefined') { y = datamap.coord[loc].y; };
        if (typeof(datamap.coord[loc].pos) != 'undefined') { pos = datamap.lang[lang].pos; };
    } catch (error) {
        console.log(error + "\nErreur dans map.json avec: " + "\n\tlang = " + lang + "\n\tloc3 = " + loc + "\n\tdatamap.coord[loc]=" + datamap.coord[loc]);

    }


    // }

    // if (typeof(datamap.lang[lang].loc[loc].name) !== 'undefined') { title = datamap.lang[lang].loc[loc].name; };
    if (typeof(pos) === 'undefined') { return }; // si pas de données


    // affichage des données
    // document.getElementById("map-title").innerHTML = title + ' (x=' + x + ', y=' + y +')';
    document.getElementById("map-title").innerHTML = title;
    document.getElementById("map-pos").innerHTML = pos;
    document.getElementById("map-lang").innerHTML = lang;

    for (i = 0; i < datamap.lang[lang].area.length; i++) {
        document.getElementById("map.items." + i).innerHTML = datamap.lang[lang].area[i];
    }

    // get the size of the actual map picture
    pl = document.getElementById('mappicture').offsetLeft;
    pt = document.getElementById('mappicture').offsetTop;
    pw = document.getElementById('mappicture').offsetWidth;
    ph = document.getElementById('mappicture').offsetHeight;

    // get the width of the position picto
    pictow = document.getElementById('here').offsetWidth;
    pictoh = document.getElementById('here').offsetHeight;

    // calcul de la marge pour positionner le picto de position sur la carte
    if (typeof(map_mpw) === 'undefined' || map_mpw === 0) { getWidthAndHeight() };
    try {
        posl = Math.round(pw / (map_mpw / x) + pl - (pictow / 2)) + "px";
        post = Math.round(ph / (map_mph / y) + pt - (pictoh)) + "px";
    } catch (error) {
        console.log(error + "\nErreur dans map.json, x is not defined ? Avec: " + "\n\tlang = " + lang + "\n\t& loc = " + loc);
        title = window.passage.name;
        loc = title;
        posl = 0;
        post = 0;

    }

    document.getElementById("here").style.marginLeft = posl;
    document.getElementById("here").style.marginTop = post;
    document.getElementById("map-pos").innerHTML = pos;


}

function switchLanguage() {
    // TODO: Utiliser le json plutôt.
    if (lang === "en") {
        lang = "fr";
    } else {
        lang = "en";
    };
    console.log("Language is now: " + lang);
    showPosition();
    // document.getElementById("mappicture").onload = function () { showPosition(); };
    // showPosition();
    // changeLoc(locnum);
}

function switchSticky() {
    if (stickymode) { stickymode = false } else { stickymode = true };
    stickyDisplay();
}

function computeStickySize() {
    if (typeof(map_mph) === "undefined") { getWidthAndHeight(); };
    if (map_mph === 0) { getWidthAndHeight(); };
    // map_mpw = document.getElementById('mappicture').naturalWidth;
    // map_mph = document.getElementById('mappicture').naturalHeight;
    console.log("map_mph = " + map_mph);
    // Calcul de la taille du sticky
    spw = Math.round(window.innerWidth / 6);
    sph = Math.round(map_mph / (map_mpw / spw));
    hw = Math.round(spw / 6);
    if (hw < 30) { hw = 30 };
    chw = Math.round(hw / 2);
    sph += "px";
    spw += "px";
    hw += "px";
    document.getElementById("here").style.height = hw;
    document.getElementById("closeimg").style.height = hw;
    document.getElementById("div-map-legende").style.marginTop = hw;
}

function stickyDisplay() {

    // TODO: raccourcir le code ci-dessous à l'aie d'une fonction d'affichage commune (doublons dans le if else)).
    if (stickymode) {
        // Map: Sticky mode
        // console.log("Sticky mode actif avec " + rsc_img_carte_sticky);

        document.getElementById("mappicture").src = rsc_img_carte_sticky;

        document.getElementById("div-map-legende").style.display = "none";
        document.getElementById("map-map").style.visibility = "visible";
        computeStickySize();
        // sticky en action
        //console.log("taille du sticky : spw x sph")
        document.getElementById("mappicture").style.height = sph;
        document.getElementById("mappicture").style.width = spw;
        document.getElementById("mappicture").style.maxwidth = "";
        document.getElementById("mappicture").style.border = "solid 2px";
        document.getElementById("mappicture").style.margin = hw;
        document.getElementById("mappicture").style.borderColor = "white";
        // ajoute un cadre pour un effet hover
        document.getElementById("mappicture").classList.add('stickyHover');

        document.getElementById("buttonSwitchLang").style.display = "none";
        document.getElementById("buttonSwitchLang").style.visibility = "hidden";
        document.getElementById("close").style.visibility = "hidden";

        // clic sur l'image pour sortir
        document.getElementById('mappicture').onclick = function() { switchSticky(); };
        document.getElementById('close').onclick = function() { switchSticky(); };
        // console.log("Canvas caché.");
        document.getElementById("canvasbackground").style.display = "none";

        // nécessaire pour cacher les boutons en provenance des aframes
        try {
            // nécessaire pour cacher les boutons en provenance des aframes
            var arButtons = document.getElementsByClassName('a-enter-ar-button');
            for (var i = 0; i < arButtons.length; i++) {
                arButtons[i].style.visibility = 'visible';
            }
            var vrButtons = document.getElementsByClassName('a-enter-vr-button');
            for (var i = 0; i < vrButtons.length; i++) {
                vrButtons[i].style.visibility = 'visible';
            }
        } catch (e) {
            // au cas où ces id ne soient pas disponibles
            console.log(e);
        }

    } else {
        // Map: fullScreen mode
        // console.log("Map is now fullscreen.");
        document.getElementById("mappicture").src = rsc_img_carte;

        // dessine un fond blanc
        // var ratio = window.devicePixelRatio || 1;
        ratio = 1;
        var canvWidth = window.innerWidth * ratio;
        var canvHeight = window.innerHeight * ratio;
        canvasbackground.width = canvWidth;
        canvasbackground.height = canvHeight;
        // console.log("Canvas affiché.");
        document.getElementById("canvasbackground").style.display = "block";
        // Affiche la légende
        document.getElementById("div-map-legende").style.display = "flex";
        document.getElementById("map-map").style.visibility = "visible";

        document.getElementById("mappicture").style.margin = "4px";
        document.getElementById("mappicture").style.border = "";
        document.getElementById("mappicture").classList.remove('stickyHover');

        document.getElementById("buttonSwitchLang").style.display = "block";
        document.getElementById("buttonSwitchLang").style.visibility = "visible";
        document.getElementById("close").style.visibility = "visible";

        // Calcul de la taille du sticky
        computeStickySize();

        function aEffacer() {
            spw = Math.round(window.innerWidth / 6);
            sph = Math.round(map_mph / (map_mpw / spw));
            hw = Math.round(spw / 3);
            if (hw < 30) { hw = 30 };
            sph += "px";
            spw += "px";
            hw += "px";
            document.getElementById("here").style.height = hw;
        }

        // nécessaire pour cacher les boutons en provenance des aframes
        var arButtons = document.getElementsByClassName('a-enter-ar-button');
        for (var i = 0; i < arButtons.length; i++) {
            arButtons[i].style.visibility = 'hidden';
        }
        var vrButtons = document.getElementsByClassName('a-enter-vr-button');
        for (var i = 0; i < vrButtons.length; i++) {
            vrButtons[i].style.visibility = 'hidden';
        }

        // Prend en compte l'orientation
        if (window.innerHeight < window.innerWidth) {
            console.log("Landscape mode");
            document.getElementById("mappicture").style.height = "100vh";
            document.getElementById("mappicture").style.maxwidth = "100%";
            document.getElementById("mappicture").style.width = "";
        } else {
            console.log("Portrait mode");
            document.getElementById("mappicture").style.width = "100vh";
            document.getElementById("mappicture").style.maxwidth = "100%";
            document.getElementById("mappicture").style.height = "100%";
        }
    };
    showPosition();
}

function chargement() {
    //    if (premierChargement !== 'true') {
    // Premier chargement, une seule fois.
    premierChargement = 'true';
    //document.getElementById('mappicture').onload(
    document.getElementById("mappicture").onload = function() { getWidthAndHeight(); };


    document.getElementById("here").src = rsc_img_here;
    document.getElementById("closeimg").src = rsc_img_close;
    document.getElementById("position").src = rsc_img_position;
    document.getElementById("mappicture").src = rsc_img_carte_sticky;

    //showPosition();
    document.getElementById("here").style.visibility = "visible";
    // console.log("map_mpw = " + map_mpw + " ; pw = " + document.getElementById('mappicture').naturalWidth);
    // console.log("map_mph = " + map_mph + " ; ph = " + document.getElementById('mappicture').naturalHeight);
    map_mpw = document.getElementById('mappicture').naturalWidth;
    map_mph = document.getElementById('mappicture').naturalHeight;

    document.querySelector("a-scene").style.display = "contents"; // nécessaire pour reach ?
    try {
        if (typeof (lang) === 'undefined') { initJson(); };
        if (typeof (datamap) != 'undefined') {
            if (typeof (areas) === 'undefined') { areas = datamap.lang[lang].area; };
            for (var i = 0; i < areas.length; i++) {
                console.error(i);
                balise = "map.items." + i;
                document.getElementById(balise).innerHTML = areas[i];
            }
        }
    } catch (e) {
        console.error("TEST " + e);
    }
    stickyDisplay();
    // console.log("show2")
    showPosition();
    //}

    //} catch (error) {
    //    console.log("JQ: " + error)
    //}
    // console.log("JQ OK")

}

function initJson() {
    if (typeof(datamap) === 'undefined') {
        loadJSON(function(response) {
            // console.log("Loading JSON file");
            datamap = JSON.parse(response);
            // Récupérez éventuellement la langue ailleurs.
            if (typeof(lang) === "undefined") { lang = datamap.default.lang }; // langue par défaut est la première qui arrive dans le json
            if (typeof(loc) === "undefined") { loc = datamap.default.loc }; // langue par défaut est la première qui arrive dans le json
            if (typeof(locnum) === "undefined") { locnum = datamap.default.locnum }; // langue par défaut est la première qui arrive dans le json
            pos = datamap.lang[lang].pos;
            console.log("datamap loaded. Ex. : lang = " + datamap.default.lang);
            console.log("\tlang = " + lang);
            console.log("\tpos = " + pos);

        });
    } else {
        console.log("datamap already loaded. Ex. : lang = " + datamap.default.lang);
        console.log("\tlang = " + lang);
        console.log("\tpos = " + pos);

    }

}



function checkNewMapLocation() {
    if (typeof(window.passage) != 'undefined') {
        if (loc != window.passage.name) {
            loc = window.passage.name;
            showPosition();
            // chargement();
            console.log("checkNewMapLocation: new passage!")
        }
    }
}


function test() {
    console.log("sm.passage.shown: passage changed!")
        // $(document).on('sm.passage.shown', test());
        // alert("changement de lieu !!!")
}
// //console.error("lancement de JQ")
// //try {
// // $(document).on('sm.passage.shown', showPosition);
// $(document).on('sm.passage.shown', test());

function altStart() {

    // TODO: Timer à remplacer par le listener ci-dessous qui lui ne marche pas...
    var intervalId = window.setInterval(function() {
        checkNewMapLocation();
    }, 2000);

    // console.log("setTimeout started!")

    // Si la fonction onload est trop lente
    if (typeof(mapdata) === 'undefined') {
        chargement();
    }

    // // Essai 1 : ne marche pas


    // $(window).on('sm.passage.shown', function(event, eventObject) {
    //     // Shown Passage object
    //     console.error(eventObject.passage.name);
    // });

    // // Essai 2 : ne marche pas
    // function detectPassageChange() {
    //     // Détecter le changement de passage
    //     // var sky = document.querySelector("a-sky").getAttribute("src");

    //     sky = document.querySelector("a-sky");
    //     observer = new MutationObserver((changes) => {
    //         changes.forEach(change => {
    //             if (change.attributeName.includes('src')) {
    //                 console.error("New a-sky src: " + sky.getAttribute("src"));
    //             }
    //         });
    //     });
    //     observer.observe(sky, { attributes: true });
    // }
    // document.querySelector('a-scene').addEventListener('loaded', detectPassageChange())

}

// TODO: Comment s'en passer ? Avec un onloading outil.
// Au bout de 4000ms, lancer quand même le chargement.
function delay(time) {
    return new Promise(resolve => setTimeout(resolve, time));
}

initVar();

window.addEventListener('resize', function(event) {
    // en cas de redimensionnement manuel de la fenêtre
    showPosition();
}, true);

window.onload = function() {
    document.getElementById("mappicture").onload = function() { getWidthAndHeight(); };

    if (typeof(mapdata) === 'undefined') {
        initJson();
        chargement();
    }
    //document.getElementById("mappicture").onload = function() {
    //chargement();
    //};
};
initJson();
delay(4000).then(() => altStart());

// function tmp() {
//     try {
//         // Import JQ
//         var script = document.createElement('script');
//         script.src = 'https://code.jquery.com/jquery-3.4.1.min.js';
//         script.type = 'module';
//         document.getElementsByTagName('head')[0].appendChild(script);
//         import $ from "jquery";
//         // use JQ
//         $(document).on('sm.passage.shown', showPosition);
//     } catch (e) {
//         console.error("Bad JQ!");
//         console.error(e);
//     }

//     var targetProxy = new Proxy(window.passage.name, {
//         set: function(target, key, value) {
//             console.log(`${key} set to ${value}`);
//             // showPosition();
//             target[key] = value;
//             return true;
//         }
//     });

// }
// document.addEventListener("click", showPosition());
// document.querySelector('a-scene').addEventListener('loaded', showPosition() );

/*
document.addEventListener("DOMContentLoaded", function() {
    stickyDisplay();
});
*/