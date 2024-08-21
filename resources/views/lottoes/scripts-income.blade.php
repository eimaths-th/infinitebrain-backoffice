@section('script')
    <script>
        $(document).ready(function() {

            document.querySelectorAll('.numeric-only').forEach(function(element) {
                element.addEventListener('input', function(e) {
                    // Remove any non-numeric characters
                    this.value = this.value.replace(/[^0-9]/g, '');
                });
            });
            
            var table = $('#Listview3').DataTable({
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
                        data: 'name',
                        name: 'name'
                    },
                    {
                        data: '3numbers',
                        name: '3numbers'
                    },
                    {
                        data: 'type',
                        name: 'type'
                    },
                    {
                        data: 'status',
                        name: 'status'
                    },
                    {
                        data: 'value',
                        name: 'value'
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

            $(document).ready(function() {
                $('#AddDate2').datepicker({
                    format: 'mm/dd/yyyy',
                    autoclose: true,
                    todayHighlight: true
                });
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

            let numberOfItem = $('#numberOfItem').val();
            newTr()

            $(document).on('change', '#numberOfItem', function() {
                numberOfItem = $(this).val();
                $('#newtr').html('');
                newTr()
            });

            function newTr() {
                for (let index = 0; index < numberOfItem; index++) {

                    let newtr = `
                        <tr>
                            <td id="index-${index}">${index + 1}</td>
                            <td>
                                <input type="text" name="name-${index}" id="name-${index}" class="form-control ">
                            </td>

                            <td>
                                <input type="text" name="numbers-${index}" id="numbers-${index}" class="form-control numeric-only">
                            </td>
                            <td>
                                <select name="type-${index}" id="type-${index}" class="form-control">
                                    <option value="0">โต๊ด</option>
                                </select>
                            </td>
                            <td>
                                <input type="text" name="value-${index}" id="value-${index}" class="form-control numeric-only">
                            </td>
                            <td id="status-${index}"></td>
                            <td id="remain-${index}"></td>
                            <td id="note-${index}"></td>
                            <td>
                                <button class="btn btn-info" id="submitBtn-${index}">submmit</button>
                            </td>
                        </tr>

                    `;

                    $('#newtr').append(newtr);
                }
            }

            $(document).on('click', '[id^=submitBtn-]', function() {
                // Get the index from the button ID
                let index = $(this).attr('id').split('-')[1];

                // Collect data from the row
                let data = {
                    name: $(`#name-${index}`).val(),
                    numbers: $(`#numbers-${index}`).val(),
                    type: $(`#type-${index}`).val(),
                    value: $(`#value-${index}`).val(),
                    _token: $('meta[name="csrf-token"]').attr('content')
                };

                $.ajax({
                    url: '{{ route('lotto.income.store') }}',
                    method: 'POST',
                    data: data,
                    success: function(response) {
                        console.log(response);

                        $(`#status-${index}`).text(response.status);
                        $(`#remain-${index}`).text(response.remain);
                        $(`#note-${index}`).text(response.note);
                    },
                    error: function(response) {
                        // Handle error
                        $(`#status-${index}`).text('Error');
                    }
                });
            });


            $('#submitBtn-all').click(function() {
                let allData = [];

                for (let index = 0; index < numberOfItem; index++) {
                    let rowData = {
                        name: $(`#name-${index}`).val(),
                        numbers: $(`#numbers-${index}`).val(),
                        type: $(`#type-${index}`).val(),
                        value: $(`#value-${index}`).val()
                    };
                    allData.push(rowData);
                }

                // Send all data to the controller
                $.ajax({
                    url: '{{ route('lotto.income.store-all') }}', // Replace with your route
                    method: 'POST',
                    data: {
                        rows: allData,
                        _token: $('meta[name="csrf-token"]').attr(
                            'content') // Assuming you have CSRF token in your meta tag
                    },
                    success: function(response) {
                        // Handle success (e.g., update all status columns)
                        for (let index = 0; index < numberOfItem; index++) {
                            $(`#status-${index}`).text('Submitted');
                        }
                    },
                    error: function(response) {
                        // Handle error
                        for (let index = 0; index < numberOfItem; index++) {
                            $(`#status-${index}`).text('Error');
                        }
                    }
                });
            });

        });
    </script>
@endsection
