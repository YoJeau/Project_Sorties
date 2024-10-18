document.addEventListener('DOMContentLoaded', function() {
    // Sélectionner toutes les alertes flash
    const flashMessages = document.querySelectorAll('.flash-message');
    // Pour chaque message flash, on définit un timer
    flashMessages.forEach(function(message) {
        // Après 4 secondes on fait disparaître le message
        setTimeout(function() {
            message.style.transition = "opacity 1s ease";
            message.style.opacity = 0;
            setTimeout(function() {
                message.remove();
            }, 1000);
        }, 4000);
    });
});