@section('script')
    <script>
        $(document).ready(function() {

            document.querySelectorAll('.numeric-only').forEach(function(element) {
                element.addEventListener('input', function(e) {
                    // Remove any non-numeric characters
                    this.value = this.value.replace(/[^0-9]/g, '');
                });
            });

            var table = $('#Listview4').DataTable({
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
                        data: 'numbers',
                        name: 'numbers'
                    },
                    {
                        data: 'type',
                        name: 'type'
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
                        data: 'status',
                        name: 'status'
                    },
                    {
                        data: 'win_lose',
                        name: 'win_lose'
                    },
                    {
                        data: 'result',
                        name: 'result'
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
                <td colspan="4" class ="text-center">รวม</td>
                <td>{{ number_format($sum_bet) }} บาท</td>
                <td>{{ number_format($sum_net) }} บาท</td>
                <td colspan="2" class ="text-center"> </td>

                <td>{{ number_format($sum_remain) }} บาท</td>
                <td>#</td>
            </tr>
        `);
                }
            });


        });
    </script>
@endsection
