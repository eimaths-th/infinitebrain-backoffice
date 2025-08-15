<script>
    $(document).ready(function() {
        var inputlist = ["firstname", "lastname", "address", "postcode"];
        $(".form-control").focusout(function() {
            //$(".form-control").focus();
            let inputxx;
            let nameinput = $(this).attr("name");

            if (inputlist.indexOf($(this).attr("name")) >= 0) {
                inputxx = ".form-focus";
            } else {
                inputxx = ".form-group";
            }

            if (nameinput == 'firstname') {
                if ($(this).val() == '' || $("#lastname").val() == '') {
                    //alert();
                    $(this).closest(inputxx).addClass('form-focus-error');
                    $(this).closest(inputxx).find('.form-error-message').css("display", "block");
                    if ($(this).val() == '') {
                        $(this).css('border', 'solid 1px red');
                    } else {
                        $(this).css('border', 'solid 1px green');
                    }

                    if ($("#lastname").val() == '') {
                        $("#lastname").css('border', 'solid 1px red');
                    } else {
                        $("#lastname").css('border', 'solid 1px green');
                    }

                    //$(this).parents().css( "background-color", "red" );
                } else {
                    // If it is not blank.
                    $(this).css('border', 'solid 1px green');
                    $("#lastname").css('border', 'solid 1px green');
                    $(this).closest(inputxx).removeClass('form-focus-error');
                    $(this).closest(inputxx).find('.form-error-message').css("display", "none");
                }

            } else if (nameinput == 'lastname') {
                if ($(this).val() == '' || $('#firstname').val() == '') {
                    //alert();
                    $(this).closest(inputxx).addClass('form-focus-error');
                    $(this).closest(inputxx).find('.form-error-message').css("display", "block");
                    if ($(this).val() == '') {
                        $(this).css('border', 'solid 1px red');
                    } else {
                        $(this).css('border', 'solid 1px green');
                    }

                    if ($("#firstname").val() == '') {
                        $("#firstname").css('border', 'solid 1px red');
                    } else {
                        $("#firstname").css('border', 'solid 1px green');
                    }
                    //$(this).parents().css( "background-color", "red" );
                } else {
                    // If it is not blank.
                    $(this).css('border', 'solid 1px green');
                    $("#firstname").css('border', 'solid 1px green');
                    $(this).closest(inputxx).removeClass('form-focus-error');
                    $(this).closest(inputxx).find('.form-error-message').css("display", "none");
                }

            } else if (nameinput == 'address') {
                if ($(this).val() == '' || $('#postcode').val() == '') {
                    //alert();
                    $(this).closest(inputxx).addClass('form-focus-error');
                    $(this).closest(inputxx).find('.form-error-message').css("display", "block");
                    if ($(this).val() == '') {
                        $(this).css('border', 'solid 1px red');
                    } else {
                        $(this).css('border', 'solid 1px green');
                    }

                    if ($("#postcode").val() == '') {
                        $("#postcode").css('border', 'solid 1px red');
                    } else {
                        $("#postcode").css('border', 'solid 1px green');
                    }
                    //$(this).parents().css( "background-color", "red" );
                } else {
                    // If it is not blank.
                    $(this).css('border', 'solid 1px green');
                    $("#postcode").css('border', 'solid 1px green');
                    $(this).closest(inputxx).removeClass('form-focus-error');
                    $(this).closest(inputxx).find('.form-error-message').css("display", "none");
                }

            } else if (nameinput == 'postcode') {
                if ($(this).val() == '' || $('#address').val() == '') {
                    //alert();
                    $(this).closest(inputxx).addClass('form-focus-error');
                    $(this).closest(inputxx).find('.form-error-message').css("display", "block");
                    if ($(this).val() == '') {
                        $(this).css('border', 'solid 1px red');
                    } else {
                        $(this).css('border', 'solid 1px green');
                    }

                    if ($("#address").val() == '') {
                        $("#address").css('border', 'solid 1px red');
                    } else {
                        $("#address").css('border', 'solid 1px green');
                    }
                    //$(this).parents().css( "background-color", "red" );
                } else {
                    // If it is not blank.
                    $(this).css('border', 'solid 1px green');
                    $("#address").css('border', 'solid 1px green');
                    $(this).closest(inputxx).removeClass('form-focus-error');
                    $(this).closest(inputxx).find('.form-error-message').css("display", "none");
                }

            } else {
                if ($(this).attr('type') == 'email') {
                    var validRegex =
                        /^[a-zA-Z0-9.!#$%&'*+/=?^_`{|}~-]+@[a-zA-Z0-9-]+(?:\.[a-zA-Z0-9-]+)*$/;
                    if ($(this).val() == '' || !$(this).val().match(validRegex)) {
                        $(this).closest(inputxx).addClass('form-focus-error');
                        $(this).closest(inputxx)
                            .find('.form-error-message').css("display", "block");
                        $(this).css('border', 'solid 1px red');
                        //$(this).parents().css( "background-color", "red" );
                    } else {
                        // If it is not blank.
                        $(this).css('border', 'solid 1px green');
                        $(this).closest(inputxx).removeClass('form-focus-error');
                        $(this).closest(inputxx).find('.form-error-message').css("display", "none");
                    }

                } else {
                    if ($(this).val() == '') {
                        $(this).closest(inputxx).addClass('form-focus-error');
                        $(this).closest(inputxx)
                            .find('.form-error-message').css("display", "block");
                        $(this).css('border', 'solid 1px red');
                        //$(this).parents().css( "background-color", "red" );
                    } else {
                        // If it is not blank.
                        $(this).css('border', 'solid 1px green');
                        $(this).closest(inputxx).removeClass('form-focus-error');
                        $(this).closest(inputxx).find('.form-error-message').css("display", "none");
                    }
                }

            }
        });
    });
</script>
{{--  --}}
<script>
    let indexoption = 0;

    document.getElementById('addOptionGrade').addEventListener('click', function() {
        indexoption++;
        addOptionGrade(indexoption, '');
    });

    function addOptionGrade(index, value) {
        document.getElementById('options_grade').style.display = 'block';
        let option = document.createElement('div');
        option.classList.add(`option${index}`);
        option.innerHTML = `
            <label>Option Grade ${index}</label>
            <div class="form-group">
                <div class="row">
                    <div class="col-md-8">
                        <input type="text" class="form-control" name="optionGrade[]" value="${value}">
                    </div>
                    <div class="col-md-4">
                        <button type="button" onclick="removeOptionGrade(${index})" class="btn btn-danger btn-sm mt-1">
                            <i class="fa fa-trash"></i>
                        </button>
                    </div>
                </div>
            </div>
        `;
        document.getElementById('options_grade').appendChild(option);
    }

    function removeOptionGrade(index) {
        let optionDiv = document.querySelector(`.option${index}`);
        if (optionDiv) optionDiv.remove();

        if (document.getElementById('options_grade').children.length === 0) {
            document.getElementById('options_grade').style.display = 'none';
        }
    }

    function showGrade(valueGrade, id) {
        indexoption++;
        getOptionGrade(indexoption, valueGrade, id);
    }
    @if (isset($data->grades))
        @foreach ($data->grades as $index => $grade)
            showGrade('{{ $grade->grade }}', '{{ $grade->id }}');
        @endforeach
    @endif

    function getOptionGrade(index, value, id) {
        document.getElementById('options_grade').style.display = 'block';
        let option = document.createElement('div');
        option.classList.add(`option${index}`);
        option.innerHTML = `
            <label>Option Grade ${index}</label>
            <div class="form-group">
                <div class="row">
                    <div class="col-md-8">
                        <input type="text" class="form-control" id="optionGrade${id}" name="optionGrade[]" value="${value}">
                    </div>
                    <div class="col-md-4">
                        <div class="row">
                            <div class="col-md-4">
                                <button type="button" onclick="editOptionGrade(${id})" data-id="${id}" class="editOptionGrade btn btn-warning btn-sm mt-1">
                                    <i id="changeIcon${id}" class="fa fa-edit"></i>
                                </button>
                            </div>
                            <div class="col-md-4">
                                <button type="button" onclick="deleteOptionGrade(${id},${index})" data-id="${id}" class="removeOptionGrade btn btn-danger btn-sm mt-1">
                                    <i class="fa fa-trash"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        `;
        document.getElementById('options_grade').appendChild(option);
    }

    let id;

    function editOptionGrade(id) {
        id = id;
        var value = document.getElementById('optionGrade' + id).value;
        $.ajax({
            url: '{{ route('edit-option-grade') }}', //update option grade
            type: 'POST',
            data: {
                _token: '{{ csrf_token() }}',
                id: id,
                value: value
            },
            success: function(response) {
                console.log(response);
                //change icon
                let icon = document.getElementById('changeIcon' + id);
                icon.classList.remove('fa-edit');
                icon.classList.add('fa-check');

                setTimeout(() => {
                    icon.classList.remove('fa-check');
                    icon.classList.add('fa-edit');
                }, 2000);
            }
        });
    }

    function deleteOptionGrade(id, index) {
        $.ajax({
            url: '{{ route('delete-option-grade') }}', //delete option grade
            type: 'POST',
            data: {
                _token: '{{ csrf_token() }}',
                id: id,
                index: index
            },
            success: function(response) {
                removeOptionGrade(response.index);
            }
        });
    }
</script>

<script>
    let indexoptionPackage = 0;

    document.getElementById('addOptionPackage').addEventListener('click', function() {
        indexoptionPackage++;
        document.getElementById('options_package').style.display = 'block';
        console.log(indexoptionPackage);
        var option = document.createElement('div');
        option.classList.add(`option${indexoptionPackage}`);
        option.innerHTML = `
            <label>Option Package ${indexoptionPackage}</label>
            <div class="form-group">
                <div class="row">
                    <div class="col-md-5">
                        <input type="number" class="form-control" name="optionPackage[${indexoptionPackage}][value]" placeholder="Option Value">
                    </div>
                    <div class="col-md-5">
                        <input type="text" class="form-control" name="optionPackage[${indexoptionPackage}][name]" placeholder="Option Name">
                    </div>
                    <div class="col-md-2">
                        <button type="button" onclick="removeOptionPackage(${indexoptionPackage})" class="btn btn-danger btn-sm mt-1">
                            <i class="fa fa-trash"></i>
                        </button>
                    </div>
                </div>
            </div>

        `;

        document.getElementById('options_package').appendChild(option);
    });

    function removeOptionPackage(index) {
        let optionDiv = document.querySelector(`.option${index}`);
        if (optionDiv) {
            optionDiv.remove();
        }

        // ถ้าไม่มี option เหลือ ให้ซ่อน container
        if (document.getElementById('options_package').children.length === 0) {
            document.getElementById('options_package').style.display = 'none';
        }
    }
</script>
