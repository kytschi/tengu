$(function () {
    $(".list-toolbar input[name=search]").keyup((event) => {
        if (event.key == "Enter") {
            window.location.href = $(event.target).data('url') + "?search=" + $(event.target).val();
        }
    });

    $(".list-checkbox").change((event) => {
        $("#form-list").submit();
    });
});

function formListDelete() {
	$("#form-list").submit();
}

function listDelete(event) {
    window.location.href = window.location.href.split("?")[0] + "/delete/" + $(event).data('id');
};
