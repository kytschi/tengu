function tableAdd(source, destination) {
    let has_data = false;

    let data = $("#" + source + " option:selected").data();
    
    $.each(
        data,
        function (key, value) {
            if (key.indexOf("column") != -1) {
                $("#" + destination + " .template ." + key).html(value);
                has_data = true;
            } else if (key.indexOf("id") != -1) {
                $("#" + destination + " .template input").val(value);
            }
        }
    );

    $("#" + destination + " tbody").append($("#" + destination + " .template tr").clone());

    $("#" + destination + " .template input").val("");
}

function tableDelete(event) {
    $(event).parent().parent().remove();
}