$(function () {
    $("#widget-category-add").click(() => {
        let id = $("#widget-categories option:selected").val();
        $("#widget-categories-table tfoot .widget-category-name").html($("#widget-categories option:selected").html());
        $("#widget-categories-table tfoot input[name^=category_id]").val(id);

        $("#widget-categories-table tfoot tr").attr("id", "category-" + $("#widget-categories option:selected").val());
        $("#widget-categories-table tfoot button").attr("id", "btn-category-" + id);

        $("#widget-categories-table tbody").append($("#widget-categories-table tfoot").html());

        $("#widget-categories-table tfoot button").attr("id", "");
        $("#widget-categories-table tfoot input[name^=category_id]").val("");

        $(".btn-widget-category").on("click", (event) => {
            $("#widget-categories-table tbody #category-" + $(event.currentTarget).attr("id").replace("btn-category-", "")).remove();
        });
    });
    
    $(".btn-widget-category").on("click", (event) => {
        $("#widget-categories-table tbody #category-" + $(event.currentTarget).attr("id").replace("btn-category-", "")).remove();
    });
});