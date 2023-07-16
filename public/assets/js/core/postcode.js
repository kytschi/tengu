$(function () {     
    $("input.postcode").keypress(function(event) {
        if ((event.keyCode ? event.keyCode : event.which) == '13') {
            $.ajax({
                url: TENGU_URL + "/api/v1/postcodes/" + $(event.target).val(),
                headers: {
                    "TENGU-API-KEY": TENGU_API_KEY
                }
            })
            .done(function(response) {
                if (typeof response == "undefined") {
                    return;
                }

                let target = $(event.target).data('target');

                var html = '<option value="" selected disabled>Please select an address</option>';

                $.each(response.data, function (key, value) {
                    html += '<option value="' +
                            value['address_line_1'] +
                            '|' + value['address_line_2'] +
                            '|' + value['town'] +
                            '|' + value['county'] +
                            '|' + value['country'] +
                            '|' + value['postcode'] +
                        '">' +
                            value['address_line_1'] +
                        "</option>";
                });

                $("#" + target + " .postcodes").show();
                $("#" + target + " .postcodes select").html(html);

                $("select[name=postcodes] option").on("click", function(event) {
                    postcodeClick(event);
                });
            })
            .fail(function(response) {
                throwError(response);
            });
        }
    });

    $("#same_billing").change(function(event) {
        if (!$(event.target).is(":checked")) {
            $("#delivery input").prop("disabled", false);
        } else {
            $("#delivery input").prop("disabled", true);
        }
    });
});

function postcodeClick(event) {
    let splits = ($(event.target).val()).split("|");
    let target = $(event.target).parent().data("target");

    $("#" + target + " input[name=" + target + "_address_line_1]").val(splits[0]);
    $("#" + target + " input[name=" + target + "_address_line_2]").val(splits[1]);
    $("#" + target + " input[name=" + target + "_town]").val(splits[2]);
    $("#" + target + " input[name=" + target + "_county]").val(splits[3]);
    $("#" + target + " input[name=" + target + "_country]").val(splits[4]);
    $("#" + target + " input[name=" + target + "_postcode]").val(splits[5]);
}