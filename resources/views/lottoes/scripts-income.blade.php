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
                        data: 'create_at',
                        name: 'create_at'
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
                        data: 'bet',
                        name: 'bet'
                    },
                    {
                        data: 'net',
                        name: 'net'
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
                ],
                drawCallback: function(settings) {
                    var api = this.api();
                    var $tbody = $(api.table().body());

                    // Insert the custom HTML at the end of the table body
                    $tbody.append(`
            <tr>
                <td>#</td>
                <td colspan="5" class ="text-center">รวม</td>
                <td>{{ number_format($sum_value) }} บาท</td>
                <td>{{ number_format($sum_bet) }} บาท</td>
                <td>{{ number_format($sum_net) }} บาท</td>
                <td>{{ number_format($sum_remain) }} บาท</td>
                <td>#</td>
            </tr>
        `);
                }
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

            let numbersOfType = [];

            function newTr() {

                for (let index = 0; index < numberOfItem; index++) {

                    let newtr = `
            <tr>
                <td id="index-${index}">${index + 1}</td>
                <td>
                    <input type="text" name="name-${index}" id="name-${index}" class="form-control">
                </td>

                <td>
                    <input type="text" name="numbers-of-type_0-${index}" id="numbers-of-type_0-${index}" class="form-control numeric-only">
                </td>
                <td>
                    <input type="text" name="numbers-of-type_1-${index}" id="numbers-of-type_1-${index}" class="form-control numeric-only">
                </td>
                <td>
                    <input type="text" name="value-${index}" id="value-${index}" class="form-control numeric-only">
                </td>
                <td id="status-${index}"></td>
                <td id="remain-${index}"></td>
                <td id="note-${index}"></td>
                <td>
                    <button class="btn btn-info" id="submitBtn-${index}">submit</button>
                </td>
            </tr>
        `;

                    $('#newtr').append(newtr);
                }
            }

            // Example function to populate numbersOfType after inputs are populated
            function populateNumbersOfType(index) {


                let numbersOfType = [];

                let numbersType0 = $(`#numbers-of-type_0-${index}`).val();
                let numbersType1 = $(`#numbers-of-type_1-${index}`).val();

                numbersOfType.push({
                    numbers: numbersType0,
                    type: 0
                });

                numbersOfType.push({
                    numbers: numbersType1,
                    type: 1
                });
                // if (!numbersOfType[index]) {
                //     numbersOfType[index] = [];
                // }

                // numbersOfType[index].push({
                //     numbers: numbersType0,
                //     type: 0
                // });

                // numbersOfType[index].push({
                //     numbers: numbersType1,
                //     type: 1
                // });

                console.log(numbersOfType);
                return numbersOfType;
            }

            $(document).on('click', '[id^=submitBtn-]', function() {
                let index = $(this).attr('id').split('-')[1];

                var NumbersOfType = populateNumbersOfType(index);

                let data = {
                    name: $(`#name-${index}`).val(),
                    numbers: NumbersOfType,
                    // type: $(`#type-${index}`).val(),
                    value: $(`#value-${index}`).val(),
                    _token: $('meta[name="csrf-token"]').attr('content'),
                };

                $.ajax({
                    url: '{{ route('lotto.income.store') }}',
                    method: 'POST',
                    data: data,
                    success: function(response) {

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


            $('#all-submitBtn').click(function() {
                let allData = [];

                for (let index = 0; index < numberOfItem; index++) {
                    var NumbersOfType = populateNumbersOfType(index);

                    let rowData = {
                        name: $(`#name-${index}`).val(),
                        numbers: NumbersOfType,
                        type: $(`#type-${index}`).val(),
                        value: $(`#value-${index}`).val()
                    };
                    allData.push(rowData);
                }
                $.ajax({
                    url: '{{ route('lotto.income.store-all') }}',
                    method: 'POST',
                    data: {
                        rows: allData,
                        _token: $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function(response) {
                        response.results.forEach(function(result, index) {
                            $(`#status-${index}`).text(result.status);
                            $(`#remain-${index}`).text(result.remain);
                            $(`#note-${index}`).text(result.note);
                        });
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
