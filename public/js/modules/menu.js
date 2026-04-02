var Menu = (function($){

    const init = function() {
        $(".burger").off("click").on("click", function(){
            $(".nav").slideToggle();
        });
    };

    return {
        init: init
    };

})(jQuery);
