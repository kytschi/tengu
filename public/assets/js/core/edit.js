let form_changed = false;
let form_submitable = false;

$(function () {
    $("#form-edit input, #form-edit select, #form-edit textarea").on("change keyup paste", function() {
        form_changed = true;
    });

    $("#btn-cancel").click(function() {
        if (!form_changed) {
            editCancel();
            return;
        }

        $(this).popover({
            html: true,
            sanitize: false,
            trigger: "focus",
            title: "Are you sure?",
            content: '<span class="btn btn-secondary">No</span>&nbsp;<span class="btn btn-danger" onclick="editCancel()">Yes</span>'
        }).popover("show");
    });

    $("#btn-save, .btn-save").click(function(event) {
        formSave(event);
	});

    $('#form-edit').submit (function() {
		return form_submitable;
	});

    try {
        $("#form-page #summernote").summernote(
            {
                placeholder: $('#summernote').attr("placeholder"),
                tabsize: 2,
                height: 400
            }
        );
    } catch (err) {
        //
    }

	$(".nav-tabs a").on("click", function (e) {
	  	e.preventDefault();
	  	$(this).tab("show");
	});

    $("#form-edit .nav a").on("click", function (e) {
        e.preventDefault();
        $(this).tab("show");
        
        const parser = new URL(window.location.origin + $("#form-edit").attr("action"));
        parser.searchParams.set("from", window.location.pathname + window.location.search);
        $("#form-edit").attr("action", parser.pathname + parser.search);
    });

    $('.tags').tagify();
});

function editCancel() {
    window.location.href = $("#btn-cancel").data("url");
};

function editDelete() {
    window.location.href = window.location.href.replace("/edit/", "/delete/");
};

function editRecover() {
    window.location.href = window.location.href.replace("/edit/", "/recover/");
};

function formSave(event) {
    $("#processing").modal("show");

    let form = "form-edit";
    if ($(event.currentTarget).data("form")) {
        form = $(event.currentTarget).data("form");
    }

    $("#" + form).append('<input type="hidden" name="' + $(event.target).attr("name") + '" value="true"/>');

    event.preventDefault();
    form_submitable = true;
    $('#processing').on('shown.bs.modal', function () {
        $("#" + form).submit();
    });
}