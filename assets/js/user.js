var table;
$(function () {
    $("#resource_ids li").hide();
    table = $('#user_tbl').DataTable({
        "order": [
            [0, "desc"]
        ]
    });
    $("#sel_roles").select2().on('change', function (e) {
        $("#resource_ids li").hide();
        $("#sel_roles option:selected").each(function () {
            var resource_names = $.parseJSON($(this).attr('resource_names'));
            for (i = 0; i < resource_names.length; i++) {
                $("#resource_ids li").each(function () {
                    if ($(this).attr('resource_name') == resource_names[i]) {
                        $(this).show();
                    }
                });
            }
        });
    });
});