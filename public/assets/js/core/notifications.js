$(function () {
    getNotificationsData();
});

function getNotificationsData() {
	$.ajax({
        url: TENGU_URL + "/api/v1/users/notifications",
        headers: {
            "TENGU-API-KEY": TENGU_API_KEY
        }
    })
    .done(function(response) {
        if (typeof response == "undefined" || !response.length) {
            triggerNotificationsCheck();
            return;
        }
        
        $("#notifications div.notification").removeClass("d-none");
        $("#notifications ul").removeClass("d-none");

        let html = '';
        for (let iLoop = 0; iLoop < response.length; iLoop++) {
            html += '<li class="nav-link">';
            html += '<a href="' + TENGU_URL + '/notifications#' + response[iLoop].id + '" class="nav-item dropdown-item">';
            html += response[iLoop].subject;
            html += "</a></li>";
        }

        $("#notifications ul").html(html);

        triggerNotificationsCheck();
    })
    .fail(function(response) {
        throwError(response);
        triggerNotificationsCheck();
    });
}

function triggerNotificationsCheck() {
	setTimeout(function() {
		getNotificationsData();
	}, 60000);
}