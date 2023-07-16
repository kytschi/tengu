$(function () {
    $(".btn-umi-entry-close").click(function(event) {
        window.location.href = $(event.currentTarget).data("url");
    });

    $(".umi-sortable-column").sortable({
        cursor: "move",
        revert: true,
        update: umiSortEntry
    });

    $(".umi-dropable-column").droppable({
        drop: umiDropEntry
    });

    $("#umi-btn-add-comment").click(function(event) {
		$("#processing").modal("show");
		event.preventDefault();
        form_submitable = true;
		$('#processing').on('shown.bs.modal', function () {
			$("#form-umi-edit-entry").submit();
		});
	});
});

function umiDropEntry(event, ui) {
    if ($(ui.draggable[0].outerHTML).data("column") == $(this).data("column")) {
        return;
    }

    $("#processing").modal("show");

    $(this).parent().find("ul").append(ui.draggable[0].outerHTML);
        
    $(this).parent().find("form")[0].submit();
}

function umiShowColumn(column) {
    let id = $(column).data('column');
    
    $("#modal-edit-column input[name=column_id]").val(id);
    $("#modal-edit-column input[name=name]").val($(column).children("div").html());
    $("#modal-edit-column select[name=sort]").val($(column).data('sort'));
    $("#modal-edit-column select[name=entry_status]").val($(column).data('entry-status'));

    $("#modal-edit-column .btn-danger").attr(
        "data-content",
        '<span class="btn btn-secondary">No</span>&nbsp;<a class="btn btn-danger" href="' +
            $("#modal-edit-column .btn-danger").data("url").replace("{id}", id) +
            '">Yes</a>'
    );

    $("#modal-edit-column").modal();
}

function umiShowEntry(entry) {
    $("#form-umi-edit-entry-" + ($(entry).data('id'))).collapse("show");
}

function umiSortEntry(event, ui) {
    $("#processing").modal("show");
    
    let column_id;
    
    $(this).children().not(".ui-sortable-placeholder, .ui-sortable-helper").each(function (sort) {
        $(this).data('sort', sort);
        $(this).children("input.board-entry-sort").val(sort);
        column_id = $(this).data('column');
    });

    $(".ui-sortable-helper").remove();

    $("#umi-column-" + column_id).submit();
}