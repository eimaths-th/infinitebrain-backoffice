$(document).ready(function () {

    $(".delete_all_button").click(function () {

        //var parent = $(this).closest("#delete_all");

        var len = $('input[name="table_records[]"]:checked').length;

        //alert(len);

        if (len > 0) {

            if (confirm("Click OK to Delete?")) {



                $('form#delete_all').submit();

            }

        } else {

            alert("Please Select Record For Delete");



        }

    });

    $('#check-all').click(function () {
        $(':checkbox.flat').prop('checked', this.checked);
    });

    //$.noConflict();
    var token = ''
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });
    var modal = $('.modal')
    var form = $('.form')
    var btnAdd = $('.add'),
        btnSave = $('.btn-save'),
        btnUpdate = $('.btn-update');

    var table = $('#Listview').DataTable({
        ajax: {
            url: '',
            data: function (d) {
                d.department = $('#filter-department').val();
                d.employee_type = $('#filter-employee-type').val();
                d.status = $('#filter-status').val();
            }
        },
        serverSide: true,
        processing: true,
        aaSorting: [[1, "desc"]],
        iDisplayLength: 15,
        lengthMenu: [15, 25, 50, 75, 100],
        stateSave: true,
        sPaginationType: "full_numbers",
        dom: 'T<"clear">lfrtip',
        columns: [
            { data: 'checkbox', name: 'checkbox', orderable: false, searchable: false },
            { data: 'id', name: 'id' },
            { data: 'code', name: 'code' },
            { data: 'name', name: 'name' },
            { data: 'email', name: 'email' },
            { data: 'department', name: 'department' },
            { data: 'position', name: 'position' },
            { data: 'level', name: 'level' },
            { data: 'status', name: 'status', orderable: false },
            { data: 'action', name: 'action', orderable: false, searchable: false },
        ]
    });

    // Filter change events
    $('#filter-department, #filter-employee-type, #filter-status').on('change', function () {
        table.ajax.reload();
    });

    // Clear filters button
    $('#clear-filters').on('click', function () {
        $('#filter-department').val('');
        $('#filter-employee-type').val('');
        $('#filter-status').val('');
        table.ajax.reload();
    });

    btnAdd.click(function () {
        modal.modal()
        form.trigger('reset')
        modal.find('.modal-title').text('Add New')
        btnSave.show();
        btnUpdate.hide()
    })

    btnSave.click(function (e) {
        e.preventDefault();
        var data = form.serialize()
        console.log(data)
        $.ajax({
            type: "POST",
            url: "",
            data: data + '&_token=' + token,
            success: function (data) {
                if (data.success) {
                    table.draw();
                    form.trigger("reset");
                    modal.modal('hide');
                }
                else {
                    alert('Delete Fail');
                }
            }
        }); //end ajax
    })


    $(document).on('click', '.btn-edit', function () {
        btnSave.hide();
        btnUpdate.show();


        modal.find('.modal-title').text('Update Record')
        modal.find('.modal-footer button[type="submit"]').text('Update')

        var rowData = table.row($(this).parents('tr')).data()

        form.find('input[name="id"]').val(rowData.id)
        form.find('input[name="name"]').val(rowData.name)
        form.find('input[name="email"]').val(rowData.email)
        form.find('input[name="level"]').val(rowData.level)

        modal.modal('show');
    })

    btnUpdate.click(function () {
        if (!confirm("Are you sure?")) return;
        var formData = form.serialize() + '&_method=PUT&_token=' + token
        var updateId = form.find('input[name="id"]').val()
        $.ajax({
            type: "POST",
            url: "/" + updateId,
            data: formData,
            success: function (data) {
                if (data.success) {
                    table.draw();
                    modal.modal('hide');
                }
            }
        }); //end ajax
    })


    $(document).on('click', '.btn-delete', function () {
        if (!confirm("Are you sure?")) return;

        var rowid = $(this).data('rowid')
        var el = $(this)
        if (!rowid) return;


        $.ajax({
            type: "POST",
            dataType: 'JSON',
            url: "/users/destroy/" + rowid,
            data: { _method: 'delete', _token: token },
            success: function (data) {
                if (data.success) {
                    table.row(el.parents('tr'))
                        .remove()
                        .draw();
                }
            }
        }); //end ajax
    })
})
