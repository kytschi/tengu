$(function () {
    $("img").on("error", function (event) {
        onImageError(event.target);
    });

    $(".btn-popover").popover({
        html: true,
        sanitize: false,
        title: "Are you sure?",
    });

    $(".menu-link").each((index, link) => {
        let url = $(link).attr("href");
        if(location.pathname.indexOf(url) != -1 && url != "/") {
            $(link).addClass("active");
        }
    });

    try {
        $(".datepicker").datepicker({
            dateFormat: "dd/mm/yy"
        });
        $(".datepicker-nofuture").datepicker({
            dateFormat: "dd/mm/yy",
            endDate: "currentDate",
            maxDate: new Date()
        });

        $(".datetimepicker").datetimepicker({
            dateFormat: "dd/mm/yy"
        });

        $(".datepicker, .datetimepicker").click(function () {
            $(this).select();
        });
    } catch {
        //
    }

    $('[data-toggle="tooltip"]').tooltip();
    $('[data-toggle="popover"]').popover();

	$("#page-loaded-in span").html(((Date.now() - tengu_start_time) / 1000).toFixed(3) + 's');

    $('a[data-toggle="tab"]').historyTabs();

    $(".clipboard-copy").click((event) => {
        var $temp = $("<input>");
        $("body").append($temp);
        $temp.val($(event.currentTarget).data("clipboard")).select();
        document.execCommand("copy");
        $temp.remove();

        $.toast({
            position: "bottom-right",
            heading: 'Done',
            text: 'Copied to clipboard',
            showHideTransition: 'fade',
            icon: 'success'
        });
    });

    $(".show-processing").click(function(event) {
        $("#processing").modal("show");
    });

    $(".page-preview-btn").click(function(event) {
        let target = $(event.target).attr("data-target");
        let url = $(event.target).attr("data-url");
        if (target == "" || url == "") {
            $(target).collapse({
                toggle: false
            });
            return;
        }

        $(target).collapse();
        $(event.target).attr("data-url", "");
        $(target + " .page-preview").html("<iframe src='" + url + "'></iframe>");

        const parser = new URL(window.location);
        parser.searchParams.set("collapse", target.replace("#", ""));        
        window.history.pushState({path:parser.href}, "", parser.href);

        $("#form-edit input[name=collapse]").val(target.replace("#", ""));
    });

    $(".pdf-render").each(function(key, element) {
        renderPDF(element);
    });
});

function appendUrl(key, value, url = null) {
    const parser = new URL(url || window.location);
    parser.searchParams.set(key, value);

    if (url) {
        return parser.href;
    }

    window.location.href = parser.href;
}

function onImageError(target) {
    $(target).attr("src", TENGU_ASSET_IMG_URL + "no-image.png");
}

function renderPDF(element) {
    let url = $(element).attr("data-url");
    if (url == "") {
        return;
    }

    let id = $(element).attr("id");
    if (typeof(id) == "undefined") {
        id = "pdf-" + Math.floor(Math.random() * 1000000);
        $(element).attr("id", id);
    }
            
    PDFObject.embed(url + "#toolbar=0", "#" + id);
    //$("#" + id).html('<iframe src="' + url + '#toolbar=0" width="100%" height="500px"></iframe>');
    $("#download, #print").hide();
}

function prettyDate(date, with_time = true) {
    if (!date) {
        return "Unknown";
    }

    let [year, month, daytime] = date.split("-");
    let [day, time] = daytime.split(" ");
    date = new Date(year, month - 1, day);

	month = date.getMonth() + 1;
	if (month < 10) {
		month = "0" + month;
	}

	day = date.getDate();
	if (day < 10) {
		day = "0" + day;
	}

    let converted = day + "/" + month + "/" + date.getFullYear();
	if (with_time) {
	 	converted += " " + time;
	}
	return converted;
}

function throwError(response) {
    let message = "An error has occurred";

    if (typeof(response.responseJSON) != "undefined") {
        message = response.responseJSON.message;
    } else {
        message = response;
    }

    $.toast({
        position: "bottom-right",
        heading: 'Error',
        text: message,
        showHideTransition: 'fade',
        icon: 'error'
    });
}

+function ($) {
    'use strict';
    $.fn.historyTabs = function() {
        var that = this;
        window.addEventListener('popstate', function(event) {
            if (event.state) {
                $(that).filter('[href="' + event.state.url + '"]').tab('show');
            }
        });
        return this.each(function(index, element) {
            $(element).on('show.bs.tab', function() {
                const parser = new URL(window.location);
                parser.searchParams.set("tab", $(this).attr('href').replace("#", ""));
                
                let url = parser.pathname;
                if (parser.search) {
                    url += parser.search;
                }

                url += $(this).attr('href');
                
                var stateObject = {'url' : $(this).attr('href')};
                
                if (window.location.hash && stateObject.url !== window.location.hash) {
                    window.history.pushState(stateObject, document.title, url);
                } else {
                    window.history.replaceState(stateObject, document.title, url);
                }
            });

            if (!window.location.hash && $(element).is('.active')) {
                // Shows the first element if there are no query parameters.
                $(element).tab('show');
            } else if ($(this).attr('href') === window.location.hash) {
                $(element).tab('show');
            }
        });
    };
}(jQuery);