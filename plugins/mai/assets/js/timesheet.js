$(function () {
    $(".btn-umi-edit-entry").click(function(event) {
        $("#modal-edit-entry input[name=title]").val($(event.currentTarget).data("title"));
        $("#modal-edit-entry input[name=description]").val($(event.currentTarget).data("description"));
        $("#modal-edit-entry input[name=started_at]").val($(event.currentTarget).data("started_at"));
        $("#modal-edit-entry input[name=ended_at]").val($(event.currentTarget).data("ended_at"));
        $("#modal-edit-entry input[name=price]").val($(event.currentTarget).data("price"));

        $("#modal-edit-entry form").attr("action", $(event.currentTarget).data("url"));

        $("#modal-edit-entry").modal("show");
    });
});