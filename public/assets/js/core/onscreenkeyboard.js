let onscreen_keyboard = false;
let onscreen_keyboard_caps = false;
let onscreen_keyboard_target = null;
let scroll_amount = 0;

$(function () {
    $("#onscreen-keyboard-keys").on("show.bs.collapse", function () {
        $("#onscreen-keyboard-hide").hide();
        $("#onscreen-keyboard-show").show();
        onscreen_keyboard = true;
        $("#button-onscreen-keyboard").tooltip('hide');
    });

    $("#onscreen-keyboard-keys").on("hide.bs.collapse", function () {
        $("#onscreen-keyboard-hide").show();
        $("#onscreen-keyboard-show").hide();
        onscreen_keyboard = false;
        $("#button-onscreen-keyboard").tooltip('hide');
    });

    $(".onscreen-keyboard-input").on('focus', function (event) {
        if (onscreen_keyboard_target) {
            $(onscreen_keyboard_target).removeClass("input-highlight");
        }

        onscreen_keyboard_target = event.target;
        $(onscreen_keyboard_target).addClass("input-highlight");
        if (!onscreen_keyboard) {
            $("#onscreen-keyboard-keys").collapse("show");
        }
    });

    $("#onscreen-keyboard-keys .btn").on("click touchstart", function(event) {
        let key = $(event.target).html();
        if ($(event.target).attr("id")) {
            key = $(event.target).attr("id");
        }

        if (key.search("<path") != -1) {
            return;
        }

        let value = null;
        //console.log(key);
        switch (key) {
            case "DEL":
                value = $(onscreen_keyboard_target).val();
                $(onscreen_keyboard_target).val(value.substring(0, value.length - 1));
                break;
            case "ENTER":
                //$(onscreen_keyboard_target).val($(onscreen_keyboard_target).val() + "\n");
                $("#onscreen-keyboard-keys").collapse("hide");
                break;
            case "CAPS":
                if (onscreen_keyboard_caps) {
                    onscreen_keyboard_caps = false;
                    $(".onscreen-keyboard-caps").removeClass("btn-primary");
                    $(".onscreen-keyboard-standard button").each(function(index, button) {
                        $(button).html(($(button).html()).toLowerCase());
                    });
                } else {
                    onscreen_keyboard_caps = true;
                    $(".onscreen-keyboard-caps").addClass("btn-primary");
                    $(".onscreen-keyboard-standard button").each(function(index, button) {
                        $(button).html(($(button).html()).toUpperCase());
                    });
                }
                break;
            case "SHIFT":
                if ($(".onscreen-keyboard-specials").is(":visible")) {
                    $(".onscreen-keyboard-shift").removeClass("btn-primary");
                    $(".onscreen-keyboard-specials").hide();
                    $(".onscreen-keyboard-standard").show();
                } else {
                    $(".onscreen-keyboard-shift").addClass("btn-primary");
                    $(".onscreen-keyboard-specials").show();
                    $(".onscreen-keyboard-standard").hide();
                }
                break;
            case "onscreen-keyboard-down":
                scroll_amount = scroll_amount + 100;
                $("html, body").animate({
                    scrollTop: scroll_amount
                }, 500); 
                break;
            case "onscreen-keyboard-up":
                scroll_amount = scroll_amount - 100;
                if (scroll_amount < 0) {
                    scroll_amount = 0;
                }
                $("html, body").animate({
                    scrollTop: scroll_amount
                }, 500); 
                break;
            default:
                $(onscreen_keyboard_target).val($(onscreen_keyboard_target).val() + key);
                break;
        }        
    });

    scroll_amount = $(window).scrollTop();
    $(window).scroll(function() {
        scroll_amount = $(this).scrollTop();
    });
});