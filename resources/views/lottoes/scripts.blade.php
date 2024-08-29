@section('script')
    <script>
        $(document).ready(function() {
            document.getElementById('AddNumbers').addEventListener('input', function(e) {
                // Remove any non-numeric characters
                this.value = this.value.replace(/[^0-9]/g, '');
            });
            document.getElementById('AddMax').addEventListener('input', function(e) {
                // Remove any non-numeric characters
                this.value = this.value.replace(/[^0-9]/g, '');
            });


            var token = ''
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });

            var table = $('#Listview').DataTable({
                ajax: '', // Provide the correct route or URL
                serverSide: true,
                processing: true,
                language: {
                    loadingRecords: '&nbsp;',
                    processing: '<div class="spinner"></div>',
                },
                aaSorting: [
                    [0, "desc"]
                ],
                iDisplayLength: 10,
                lengthMenu: [10, 25, 50, 75, 100],
                stateSave: true,
                sPaginationType: "full_numbers",
                columns: [{
                        data: 'id',
                        name: 'id'
                    },
                    {
                        data: 'date',
                        name: 'date'
                    },
                    {
                        data: 'numbers',
                        name: 'numbers'
                    },
                    {
                        data: 'length',
                        name: 'length'
                    },
                    {
                        data: 'type',
                        name: 'type'
                    },
                    {
                        data: 'max',
                        name: 'max'
                    },
                    {
                        data: 'remain',
                        name: 'remain'
                    },
                    {
                        data: 'action',
                        name: 'action',
                        className: 'text-nowrap'
                    },
                ]
            });

            $(document).on('click', '#CreateButton', function(e) {
                e.preventDefault();
                $('.alert-danger').html('');
                $('.alert-danger').hide();
                $('.alert-success').html('');
                $('.alert-success').hide();
                $('#CreateModal').modal('show');
            });
            let Add_Numbers = '';
            let type = '';
            $(document).on('change', '#AddType', function() {

                type = this.value;
                if (type === '0') {
                    $('#number-contrainer').hide();
                } else {
                    $('#number-contrainer').show();
                }

            });

            let id;
            $(document).on('click', '#getEditData', function(e) {
                e.preventDefault();

                $('.alert-danger').html('');
                $('.alert-danger').hide();
                $('.alert-success').html('');
                $('.alert-success').hide();

                id = $(this).data('id');
                $.ajax({
                    url: "users/edit/" + id,
                    method: 'GET',
                    success: function(res) {
                        $('#EditModalBody').html(res.html);
                        $('#EditModal').modal('show');
                    }
                });

            })

            // Create product Ajax request.
            $('#SubmitCreateForm').click(function(e) {
                e.preventDefault();
                $('.alert-danger').html('');
                $('.alert-danger').hide();
                $('.alert-success').html('');
                $('.alert-success').hide();

                console.log(Add_Numbers);

                if (type === '0') {
                    $('#AddNumbers').val('');
                    Add_Numbers = '';

                } else {
                    Add_Numbers = $('#AddNumbers').val();

                }


                $.ajax({
                    url: "{{ route('lotto.store') }}",
                    method: 'post',
                    data: {
                        numbers: Add_Numbers,
                        max: $('#AddMax').val(),
                        date: $('#AddDate').val(),
                        type: $('#AddType').val(),
                        _token: token,
                    },
                    success: function(result) {
                        console.log(result);

                        if (result.errors) {
                            $('.alert-danger').html('');
                            $.each(result.errors, function(key, value) {
                                $('.alert-danger').show();
                                $('.alert-danger').append('<strong><li>' + value +
                                    '</li></strong>');
                            });
                        } else {
                            $('.alert-danger').hide();
                            $('.alert-success').show();
                            $('.alert-success').append('<strong><li>' + result.success +
                                '</li></strong>');
                            $('#Listview').DataTable().ajax.reload();
                            $('.form').trigger('reset');

                        }
                    },
                    error: function(xhr) {
                        var errors = xhr.responseJSON.errors;
                        var errorList = '';
                        $.each(errors, function(key, messages) {
                            $.each(messages, function(index, message) {
                                errorList += '<li>' + message + '</li>';
                            });
                        });
                        $('.alert-danger').show().html('<strong>' + errorList +
                            '</strong>');
                        toastr.error(errorList, {
                            timeOut: 5000
                        });
                    }
                });
            });

            $('#SubmitEditForm').click(function(e) {
                if (!confirm("Are you sure?")) return;
                e.preventDefault();

                $('.alert-danger').html('');
                $('.alert-danger').hide();
                $('.alert-success').html('');
                $('.alert-success').hide();


                $.ajax({
                    url: "users/save/" + id,
                    method: 'PUT',
                    data: {
                        name: $('#editName').val(),
                        password: $('#EditPassword').val(),
                        password_confirmation: $('#EditPasswordC').val(),
                        email: $('#editEmail').val(),
                        level: $('#editLevel').val(),
                    },

                    success: function(result) {
                        //console.log(result);
                        if (result.errors) {
                            $('.alert-danger').html('');
                            $.each(result.errors, function(key, value) {
                                $('.alert-danger').show();
                                $('.alert-danger').append('<strong><li>' + value +
                                    '</li></strong>');
                            });
                        } else {
                            $('.alert-danger').hide();
                            $('.alert-success').show();
                            $('.alert-success').append('<strong><li>' + result.success +
                                '</li></strong>');
                            $('#Listview').DataTable().ajax.reload();
                            //setTimeout(function() {
                            //$('.alert-success').hide();
                            //   $('#EditModal').modal('hide');
                            //}, 10000);

                        }
                    },
                    error: function(xhr) {
                        var errors = xhr.responseJSON.errors;
                        var errorList = '';
                        $.each(errors, function(key, messages) {
                            $.each(messages, function(index, message) {
                                errorList += '<li>' + message + '</li>';
                            });
                        });
                        $('.alert-danger').show().html('<strong>' + errorList +
                            '</strong>');
                        toastr.error(errorList, {
                            timeOut: 5000
                        });
                    }
                });
            });
        })
    </script>
    <script>
        document.querySelectorAll('.numeric-only').forEach(function(element) {
            element.addEventListener('input', function(e) {
                // Remove any non-numeric characters
                this.value = this.value.replace(/[^0-9]/g, '');
            });
        });

        $(document).ready(function() {

            var table = $('#Listview2').DataTable({
                ajax: '', // Provide the correct route or URL
                serverSide: true,
                processing: true,
                language: {
                    loadingRecords: '&nbsp;',
                    processing: '<div class="spinner"></div>',
                },
                aaSorting: [
                    [0, "desc"]
                ],
                iDisplayLength: 10,
                lengthMenu: [10, 25, 50, 75, 100],
                stateSave: true,
                sPaginationType: "full_numbers",
                columns: [{
                        data: 'id',
                        name: 'id'
                    },
                    {
                        data: 'date',
                        name: 'date'
                    },
                    {
                        data: '1st',
                        name: '1st'
                    },
                    {
                        data: '3front_1',
                        name: '3front_1'
                    },
                    {
                        data: '3front_2',
                        name: '3front_2'
                    },
                    {
                        data: '3back_1',
                        name: '3back_1'
                    },
                    {
                        data: '3back_2',
                        name: '3back_2'
                    },
                    {
                        data: '2numbers',
                        name: '2numbers'
                    },
                    {
                        data: 'action',
                        name: 'action',
                        className: 'text-nowrap'
                    },
                ]
            });
            $(document).ready(function() {
                $('#AddDate2').datepicker({
                    format: 'mm/dd/yyyy',
                    autoclose: true,
                    todayHighlight: true
                });
            });
            $(document).on('click', '#CreateButton2', function(e) {
                e.preventDefault();
                $('.alert-danger').html('');
                $('.alert-danger').hide();
                $('.alert-success').html('');
                $('.alert-success').hide();
                $('#CreateModal2').modal('show');
            });

            $('#SubmitCreateForm2').click(function(e) {
                e.preventDefault();
                $('.alert-danger').html('');
                $('.alert-danger').hide();
                $('.alert-success').html('');
                $('.alert-success').hide();

                $.ajax({
                    url: "{{ route('lotto.addresult') }}",
                    method: 'post',
                    data: {
                        date: $('#AddDate2').val(),
                        add1st: $('#add1st').val(),
                        add3front_1: $('#add3front_1').val(),
                        add3front_2: $('#add3front_2').val(),
                        add3back_1: $('#add3back_1').val(),
                        add3back_2: $('#add3back_2').val(),
                        add2numbers: $('#add2numbers').val(),
                        _token: token,
                    },
                    success: function(result) {

                        if (result.errors) {
                            $('.alert-danger').html('');
                            $.each(result.errors, function(key, value) {
                                $('.alert-danger').show();
                                $('.alert-danger').append('<strong><li>' + value +
                                    '</li></strong>');
                            });
                        } else {
                            $('.alert-danger').hide();
                            $('.alert-success').show();
                            $('.alert-success').append('<strong><li>' + result.success +
                                '</li></strong>');
                            $('#Listview').DataTable().ajax.reload();
                            $('.form').trigger('reset');
                        }
                    },
                    error: function(xhr) {
                        if (xhr.status === 422) { // Laravel validation error
                            var errors = xhr.responseJSON.errors;
                            $('.alert-danger').html('');
                            $.each(errors, function(key, value) {
                                $('.alert-danger').show();
                                $('.alert-danger').append('<strong><li>' + value[0] +
                                    '</li></strong>');
                            });
                        }
                    }
                });
            });
        });
    </script>
@endsection
