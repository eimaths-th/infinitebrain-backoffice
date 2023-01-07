$(document).ready(function () {
    $('#Listview').DataTable({
        "aoColumnDefs": [
            {
                'bSortable': true,
                'aTargets': [0]
            } //disables sorting for column one
        ],
        'iDisplayLength': 10,
        "sPaginationType": "full_numbers",
        "dom": 'T<"clear">lfrtip',

    });
});
