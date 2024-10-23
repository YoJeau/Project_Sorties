
document.addEventListener('DOMContentLoaded', function() {
    const table = new DataTable('#participant-datatable', {
        language: {
            processing: "Traitement...",
            search: "Rechercher :",
            lengthMenu: "Afficher _MENU_ entrées",
            info: "Affichage de l'entrée _START_ à _END_ sur _TOTAL_ entrées",
            infoEmpty: "Aucune entrée à afficher",
            infoFiltered: "(filtré de _MAX_ entrées au total)",
            loadingRecords: "Chargement...",
            zeroRecords: "Aucun enregistrement correspondant trouvé",
            paginate: {
                first: "Premier",
                last: "Dernier",
                next: "Suivant",
                previous: "Précédent"
            },
            aria: {
                sortAscending:  ": activer pour trier la colonne par ordre croissant",
                sortDescending: ": activer pour trier la colonne par ordre décroissant"
            }
        }
    });
});