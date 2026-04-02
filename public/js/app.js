// Namespace global propre
var Web4All = (function($) {

    // Enregistre le Service Worker de manière fiable
    const registerServiceWorker = function() {
        if ('serviceWorker' in navigator) {
            // Scope explicite à la racine
            if ('serviceWorker' in navigator) {
                navigator.serviceWorker.register('/sw.js', { scope: '/' })
                    .then(reg => console.log("Service Worker enregistré", reg.scope))
                    .catch(err => console.error("Erreur SW", err));
            } else {
                console.log("Service Worker non supporté");
            }
        }
        else  {
            console.log("Service Worker non activable car site pas de confiance (https ou localhost)");
        }
    };

    // Initialisation des modules
    const init = function() {
        // Modules existants
        if (typeof Menu !== 'undefined') Menu.init();
        if (typeof Modal !== 'undefined') Modal.init();
        if (typeof FormValidation !== 'undefined') FormValidation.init();
        if (typeof Stars !== 'undefined') Stars.init();

        // Enregistrement du Service Worker
        registerServiceWorker();
    };
    

    return {
        init: init
    };

})(jQuery);

// Initialisation unique après DOM ready
$(document).ready(function() {
    console.log("Web4All init"); // Vérifie que le JS est bien chargé
    Web4All.init();
});
