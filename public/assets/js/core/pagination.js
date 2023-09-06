$(function() {
    $('#limit').change(function() {
        let params = new URLSearchParams(window.location.search);
        let selected = $(this).val();
        let url = window.location.href.split("?")[0] + "?";

        let found = false;

        params.forEach((value, key) => {
            if (key == "limit") {
                url += "limit=" + selected + "&";
                found = true;
            } else if (key == "page") {
                url += "page=1&";
            } else {
                url += key + "=" + value + "&";
            }
        });

        if (!found) {
            url += "limit=" + selected + "&";
        }
        url = url.substring(0, url.length - 1);

        window.location.href = url;        
    });

    $("#media-page-limit").change(() => {
        getImages(1);
    });

    $('.media-page-item').click(function () {
        let page = parseInt($(this).attr('data-page'));
        getImages(page);
    });
});

function getImages(page) {
    if (page < 1) {
        page = 1;
    }    
    $("#media-pagination li").removeClass('active');
    $("#media-pagination li[data-page=" + page + "]").addClass('active');

    $("#processing").modal("show");
    $('#processing').on('shown.bs.modal', function () {
        $.ajax({
            url: TENGU_URL + "/api/v1/files/images/" +
                page + "/" + 30,
            headers: {
                "TENGU-API-KEY": TENGU_API_KEY
            }
        })
        .done(function(response) {
            $("#media-widget #media-tiles-available").html("");
            if (response.data) {
                response.data.items.forEach((item) => {
                    $("#media-widget #tile-template .clipboard-copy").attr("data-clipboard", item.url);
                    $("#media-widget #tile-template .media-img").attr("src", item.thumb_url);
                    $("#media-widget #tile-template .media-img").attr("alt", item.name);
                    $("#media-widget #tile-template .btn-edit-media-label").attr("data-label", item.label);
                    $("#media-widget #tile-template .btn-edit-media-label").attr("data-id", item.id);
                    $("#media-widget #tile-template .btn-edit-media-label").attr("data-tags", item.tags);
                    $("#media-widget #tile-template .media-label").html(item.label);
                    
                    $("#media-widget #tile-template input[name=banner_image]").attr("value", item.id);
                    if (item.id == $("#template-banner-id").html()) {
                        $("#media-widget #tile-template input[name=banner_image]").attr("checked", "checked");
                    } else {
                        $("#media-widget #tile-template input[name=banner_image]").removeAttr("checked");
                    }

                    $("#media-widget #tile-template input[name=cover_image]").attr("value", item.id);
                    if (item.id == $("#template-cover-id").html()) {
                        $("#media-widget #tile-template input[name=cover_image]").attr("checked", "checked");
                    } else {
                        $("#media-widget #tile-template input[name=cover_image]").removeAttr("checked");
                    }

                    $("#media-widget #tile-template .media-tags").html("");
                    item.tags.forEach((tag) => {
                        $("#media-widget #tile-template .media-tags").append(
                            $("#media-widget #tile-template #tag-template badge").html(tag.tag)
                        );
                    });

                    $("#media-widget #media-tiles-available").append($("#media-widget #tile-template").html());
                });
            }
            $("#processing").modal("hide");
        })
        .fail(function(response) {
            $("#processing").modal("hide");
            throwError(response);
        });
    });
}