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

            document.querySelectorAll('.number').forEach(function(element) {
                element.addEventListener('input', function(e) {
                    // Remove any non-numeric characters
                    this.value = this.value.replace(/[^0-9]/g, '');
                });
            });

            //$.noConflict();
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

                $.ajax({
                    url: "{{ route('lotto.store') }}",
                    method: 'post',
                    data: {
                        numbers: $('#AddNumbers').val(),
                        max: $('#AddMax').val(),
                        date: $('#AddDate').val(),
                        type: $('#AddType').val(),
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
                            //$('#SubmitCreateForm').hide();
                            //setTimeout(function() {
                            //$('.alert-success').hide();
                            //$('#CreateModal').modal('hide');
                            //}, 10000);

                        }
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
                    }
                });
            });
        })
    </script>
    <script>
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
        $(document).on('click', '#CreateButton2', function(e) {
            e.preventDefault();
            $('.alert-danger').html('');
            $('.alert-danger').hide();
            $('.alert-success').html('');
            $('.alert-success').hide();
            $('#CreateModal2').modal('show');
        });
    </script>
@endsection
