const EquipementsManager = {
    init: function() {
        document.addEventListener("DOMContentLoaded", this.onDOMContentLoaded.bind(this));
    },

    onDOMContentLoaded: function() {
        // Assurez-vous que l'élément avec l'ID "formRech" existe avant de sélectionner ".tablinks"
        const formRech = document.getElementById("formRech");
        if (formRech) {
            const tablink = formRech.querySelector(".tablinks");
            if (tablink) {
                tablink.click();
            }
        }

        // Assurez-vous que l'élément avec l'ID "defaultOpen" existe avant de le sélectionner
        const defaultOpen = document.getElementById("defaultOpen");
        if (defaultOpen) {
            defaultOpen.click();
        }

        // Charger les données d'équipements depuis le fichier JSON
        fetch(document.getElementById('equipementsContent').getAttribute('data-equipements'))
            .then(response => response.json())
            // .then((json) => console.log(json))
            .then(data => {
                this.generateContent('equipementsContent', data);
            })
            .catch(error => console.error('Erreur lors du chargement des données d\'équipements :', error));

        // Charger les données d'adaptations depuis le fichier JSON
        fetch(document.getElementById('adaptationsContent').getAttribute('data-adaptations'))
            .then(response => response.json())
            // .then((json) => console.log(json))
            .then(data => {
                this.generateContent('adaptationsContent', data);
            })
            .catch(error => console.error('Erreur lors du chargement des données d\'adaptations :', error));
    },

    openTab: function(evt, tabName) {
        var i, tabcontent, tablinks;
        tabcontent = document.getElementsByClassName("tabcontent");
        for (i = 0; i < tabcontent.length; i++) {
            tabcontent[i].style.display = "none";
        }
        tablinks = document.getElementsByClassName("tablinks");
        for (i = 0; i < tablinks.length; i++) {
            tablinks[i].classList.remove("active");
        }
        const tabElement = document.getElementById(tabName);
        if (tabElement) {
            tabElement.style.display = "block";
        }
        evt.currentTarget.classList.add("active");
    },

    generateContent: function(containerId, data) {
        const contentContainer = document.getElementById(containerId);
        if (!contentContainer) {
            console.error(`Élément ${containerId} non trouvé.`);
            return;
        }
        contentContainer.innerHTML = ""; // Efface le contenu précédent
        this.generateNestedContent(contentContainer, data, 2); // Commence avec un niveau de titre de h2
    },

    generateNestedContent: function(container, data, startingLevel) {
        for (const [category, items] of Object.entries(data).sort(this.compareCategories)) {
            let headingTag = 'h' + startingLevel;
            if (typeof items === 'object') {
                // Extraire le niveau de la catégorie
                const level = items._level || startingLevel;
                headingTag = 'h' + level;
                const heading = document.createElement(headingTag);
                heading.textContent = category;
                container.appendChild(heading);
                this.generateNestedContent(container, items, level + 1); // Incrémente le niveau pour les sous-catégories
            } else {
                const label = document.createElement('label');
                const input = document.createElement('input');
                input.type = 'checkbox';
                label.appendChild(input);
                label.appendChild(document.createTextNode(category));
                container.appendChild(label);
            }
        }
    },

    compareCategories: function(a, b) {
        // Convertit les caractères accentués en caractères non accentués
        const normalize = str => str.normalize("NFD").replace(/[\u0300-\u036f]/g, "");
        const categoryA = normalize(a[0]).toLowerCase();
        const categoryB = normalize(b[0]).toLowerCase();
        return categoryA.localeCompare(categoryB);
    },


    getHeadingTag: function(containerTagName, depth) {
        switch (containerTagName.toLowerCase()) {
            case 'h2':
                return depth === 0 ? 'h2' : 'h3';
            case 'h3':
                return depth === 0 ? 'h2' : 'h3';
            case 'h4':
                return depth === 0 ? 'h3' : 'h4';
            case 'h5':
                return depth === 0 ? 'h4' : 'h5';
            case 'h6':
                return depth === 0 ? 'h5' : 'h6';
            default:
                return 'h2';
        }
    }



};

// Initialisation du gestionnaire d'équipements
EquipementsManager.init();