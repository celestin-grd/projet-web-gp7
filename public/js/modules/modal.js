var Modal = (function($){

    const init = function() {

        $("#btnAffiner").on("click", function(){
            $(".modal").fadeIn();
        });

        $(".close").on("click", function(){
            $(".modal").fadeOut();
        });

        $(window).on("click", function(e){
            if($(e.target).hasClass("modal")){
                $(".modal").fadeOut();
            }
        });
    };

    return {
        init: init
    };

})(jQuery);
