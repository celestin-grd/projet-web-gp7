var Wishlist = (function($){

    const render = function(btn, value){
        if (value == 1) {
            btn.text("★");
            btn.attr("title", "Retirer de la wishlist");
        } else {
            btn.text("☆");
            btn.attr("title", "Ajouter à la wishlist");
        }
        btn.data("inwishlist", value);
    };

    const init = function(){

        $(".btn-icon.wishlist").each(function(){
            const btn = $(this);
            const value = btn.data("inwishlist") == 1 ? 1 : 0;

            // 🔥 synchro UI au chargement (important)
            render(btn, value);
        });

        $(".btn-icon.wishlist").on("click", function(e){
            e.preventDefault();

            const btn = $(this);
            const offreId = btn.data("id");
            const current = btn.data("inwishlist") == 1 ? 1 : 0;

            // 🎯 état cible
            const newValue = current === 1 ? 0 : 1;

            const url = `/wishlist/create/${offreId}`;
            const csrf = window.csrf_token || '';

            $.ajax({
                url: url,
                method: "POST",
                data: {
                    csrf_token: csrf,
                    value: newValue,
                    id_offre: offreId
                },
                success: function(response){

                    // 🔥 MAJ UI basée sur CE QU'ON A ENVOYÉ
                    render(btn, newValue);

                    btn.addClass("pulse");
                    setTimeout(() => btn.removeClass("pulse"), 300);
                },
                error: function(xhr){
                    console.error("Erreur AJAX wishlist", xhr);

                    // ❗ rollback visuel si erreur
                    render(btn, current);

                    alert("Une erreur est survenue.");
                }
            });
        });

        $(".btn-icon.apply").on("click", function(e){
            e.preventDefault();

            const offreId = $(this).data("id");

            window.location.href = `/postule/create/${offreId}`;
        });
    };

    return {
        init: init
    };

})(jQuery);


$(document).ready(function(){
    Wishlist.init();
    console.log("Wishlist init OK");
});