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

    $('.media-page-item').click(function () {
        $.ajax({
            url: TENGU_URL + "/api/v1/files/images/" +
                $(this).data('data-page') + "/" +
                $('#media-page-limit').val(),
            headers: {
                "TENGU-API-KEY": TENGU_API_KEY
            }
        })
        .done(function(response) {
            console.log(response);
        })
        .fail(function(response) {
            throwError(response);            
        });
    });
});