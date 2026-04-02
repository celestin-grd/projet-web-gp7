var FormValidation = (function($){

    const rules = {

        optional: function(value){
            return true;
        },

        required: function(value){
            return value.trim() !== "";
        },

        alpha: function(value){
            return /^[A-Za-zÀ-ÖØ-öø-ÿ0-9\s\-.'&]{2,100}$/.test(value);
        },

        txt: function(value){
            return /^[A-Za-zÀ-ÖØ-öø-ÿ0-9\s.,;:!?()'"\-]{2,1000}$/.test(value);
        },

        phone: function(value){
            return /^\+?[0-9\s\-]{10,20}$/.test(value);
        },

        integer: function(value){
            return /^-?[0-9]+$/.test(value);
        },

        email: function(value){
            return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(value);
        },

        float: function(value){
            return /^-?[0-9]+(\.[0-9]+)?$/.test(value);
        },

        date: function(value){
            return /^(((\d\d)(([02468][048])|([13579][26]))-02-29)|(((\d\d)(\d\d)))-((((0\d)|(1[0-2]))-((0\d)|(1\d)|(2[0-8])))|((((0[13578])|(1[02]))-31)|(((0[1,3-9])|(1[0-2]))-(29|30)))))$/.test(value);
        },

        min: function(value, length){
            return value.length >= parseInt(length);
        }

    };


    const validateField = function($field){

        const value = $field.val();
        const ruleString = $field.data("validate");

        if(!ruleString) return true;

        const ruleList = ruleString.split("|");

        // Si optional et champ vide -> on ignore les autres règles
        if(ruleList.includes("optional") && value.trim() === ""){
            return true;
        }

        for(let rule of ruleList){

            let ruleName = rule;
            let param = null;

            if(rule.includes(":")){
                const parts = rule.split(":");
                ruleName = parts[0];
                param = parts[1];
            }

            if(rules[ruleName]){

                const valid = param
                    ? rules[ruleName](value, param)
                    : rules[ruleName](value);

                if(!valid){
                    return false;
                }
            }
        }

        return true;
    };


    const init = function(){

        $("form[data-validate='true']").on("submit", function(e){

            let valid = true;

            $(this).find("[data-validate]").each(function(){

                if(!validateField($(this))){
                    valid = false;
                    $(this).css("border", "2px solid red");
                } else {
                    $(this).css("border", "1px solid #ccc");
                }

            });

            if(!valid){
                e.preventDefault();
                alert("Erreur de validation");
            }

        });
    };

    return {
        init: init
    };

})(jQuery);
