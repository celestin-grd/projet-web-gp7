var Stars = (function($){

    const render = function(container, value) {
        container.find("span").each(function(){
            const starValue = $(this).data("value");

            if (starValue <= value) {
                $(this).addClass("active").text("★");
            } else {
                $(this).removeClass("active").text("☆");
            }
        });
    };

    const init = function() {

        $(".stars-rating").each(function(){

            const container = $(this);
            const input = container.prev("#evaluation");

            // 🔹 valeur initiale
            let value = 0;

            if (input.length) {
                value = parseInt(input.val() || 0);
            } else {
                value = parseInt(container.data("value") || 0);
            }

            render(container, value);

            // 🔹 mode readonly
            if (container.data("readonly")) return;

            // 🔹 interaction (formulaire)
            container.find("span").on("click", function(){

                let value = $(this).data("value");

                if (input.length) {
                    input.val(value);
                }

                render(container, value);
            });

        });
    };

    return {
        init: init
    };

})(jQuery);