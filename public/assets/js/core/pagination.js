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
});