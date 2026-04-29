// Import du framework Stimulus
//  - Permet de créer un controller JavaScript lié au HTML
//  - C’est la base du système data-controller="" dans Symfony UX
import { Controller } from '@hotwired/stimulus';

// Déclaration du controller Stimulus
// Ce controller sera utilisable dans le HTML via :  data-controller="recettes"
export default class extends Controller {
    // Déclaration d’une “value Stimulus”
    //   - Permet de passer des données du HTML vers JS
    //   - Ici : l’URL de la route Symfony à appeler en AJAX
    //   - accessible dans JS via : this.urlValue
    //   - alimentée dans twig/HTML : data-recettes-url-value="…"
    static values = {
        url: String
    }

    // Méthode appelée au clic sur le bouton (data-action="click->recettes#load")
    // async car on fait un appel HTTP (fetch)
    async load() {
        //         Appel AJAX vers Symfony
        // this.urlValue → URL générée par Symfony
        // fetch() → requête HTTP GET
        // header X-Requested-With :
        //  - indique à Symfony que c’est une requête AJAX
        //  - utile pour adapter la réponse côté backend si besoin
        const response = await fetch(this.urlValue, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        });
        // Conversion de la réponse en JSON
        // Symfony retourne une JsonResponse
        // JS transforme le flux HTTP en objet utilisable
        const data = await response.json();
        // Transmission des données à une autre méthode
        // Sépare logique :
        //  - load() → récupération données
        //  - showModal() → affichage UI
        this.showModal(data);
    }

    // Méthode dédiée à l’affichage des données dans la modale
    showModal(recettes) {
        let html = '<ul>';

        // Pour chaque recette :
        //  - on ajoute un <li>
        // on affiche r.nom (nom de la recette)
        recettes.forEach(r => {
            html += `<li>${r.nom}</li>`;
        });

        html += '</ul>';

        //         Injection du HTML dans la modale
        // - Remplace le contenu du <div id="modal-body">
        // - Affiche dynamiquement les recettes
        document.getElementById('modal-body').innerHTML = html;

        // Affichage de la modale Bootstrap
        // - Création d’une instance Bootstrap Modal
        // - Cible l’élément HTML #recettesModal
        // - show() → ouvre la fenêtre modale
        const modal = new bootstrap.Modal(document.getElementById('recettesModal'));
        modal.show();
    }
}
