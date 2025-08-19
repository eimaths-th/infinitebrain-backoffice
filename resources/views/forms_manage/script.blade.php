<script>
    $(document).ready(function() {
        const $form = $('#infinite');

        function containerFor($el) {
            const $focus = $el.closest('.form-focus');
            if ($focus.length) return $focus;
            const $group = $el.closest('.form-group');
            return $group.length ? $group : $el.parent();
        }

        function showError($el) {
            const $c = containerFor($el);
            $c.addClass('form-focus-error');
            $c.find('.form-error-message').css('display', 'block');
            $el.css('border', '1px solid red');
        }

        function hideError($el) {
            const $c = containerFor($el);
            $el.css('border', '');
            // If no other invalids inside, hide the message
            const hasOtherInvalid = $c.find('input, textarea, select').filter(function() {
                return $(this).data('invalid') === true;
            }).not($el).length > 0;
            if (!hasOtherInvalid) {
                $c.removeClass('form-focus-error');
                $c.find('.form-error-message').css('display', 'none');
            }
        }

        function isEmpty($el) {
            const type = ($el.attr('type') || '').toLowerCase();
            const tag = $el.prop('tagName');

            if (type === 'file') return ($el[0].files || []).length === 0;
            if (type === 'checkbox' || type === 'radio') {
                const name = $el.attr('name');
                return $form.find(`input[name="${name}"]:checked`).length === 0;
            }
            if (tag === 'SELECT') return !$el.val();
            return ($.trim($el.val()) === '');
        }

        function validateEmail(val) {
            const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            return re.test(String(val).toLowerCase());
        }

        function validateField($el) {

            let invalid = false;
            if ($el.is('[required]')) {
                invalid = isEmpty($el);
            }
            // Specific email check

            if (invalid) {
                $el.data('invalid', true);
                showError($el);
            } else {
                $el.data('invalid', false);
                hideError($el);
            }
            return !invalid;
        }

        // Live handlers
        $form.on('input change', 'input, textarea, select', function() {
            validateField($(this));
        });

        // Submit via AJAX with client-side validation
        $form.on('submit', function(e) {
            e.preventDefault();

            let firstInvalid = null;
            // Validate all required fields
            $form.find('input, textarea, select').each(function() {
                const ok = validateField($(this));
                if (!ok && !firstInvalid) firstInvalid = this;
            });

            if (firstInvalid) {
                $('#error_div').show();
                $('html, body').animate({
                    scrollTop: $(firstInvalid).offset().top - 120
                }, 300);
                return;
            }

            $('#error_div').hide();

            // Prepare FormData (preserves files and all values)
            const formEl = $form.get(0);
            const formData = new FormData(formEl);

            // Show loader and disable submit
            $('#loader-container').show();
            const $submitBtn = $('#send_data').prop('disabled', true);

            $.ajax({
                url: $form.attr('action'),
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                headers: {
                    'X-CSRF-TOKEN': $('input[name="_token"]').val(),
                    'Accept': 'application/json'
                },
                success: function(resp, status, xhr) {
                    // If backend returns JSON with redirect/url, follow it; otherwise reload
                    if (resp && (resp.status === 'success')) {
                        //redirect to form-manage
                        toastr.success(resp.message, {
                            timeOut: 3000,
                        });

                        // swal success
                        Swal.fire({
                            icon: 'success',
                            title: 'Success...',
                            text: resp.message,
                            showConfirmButton: false,
                            timer: 3000
                        });

                        setTimeout(function() {
                            window.location.href =
                                '{{ route('form-manage.index') }}';
                        }, 3000);

                        //3 seconds delay

                    } else {
                        $('#loader-container').hide();
                        const $submitBtn = $('#send_data').prop('disabled', false);
                        console.log(resp);
                        // If it redirected server-side, we may get HTML; replace document
                        //each error message show with toastr
                        $.each(resp.errors, function(key, value) {
                            toastr.error(value[0], {
                                timeOut: 5000
                            });
                        });

                        //show each alert class danger

                        $('.alert-danger').show();
                        $.each(resp.errors, function(key, value) {
                            $('.alert-danger').append('<strong><li>' + value +
                                '</li></strong>');
                        });

                        $.each(resp.errors, function(key, value) {
                            const $field = $form.find('[name="' + key + '"]');
                            if ($field.length) {
                                $field.each(function() {
                                    $(this).data('invalid', true);
                                    showError($(this));
                                    if (!firstErrEl) firstErrEl = this;
                                });
                            }
                        });
                    }
                },
                error: function(xhr) {
                    console.log(xhr);
                    // Validation errors (Laravel 422)
                    if (xhr.status === 422 && xhr.responseJSON && xhr.responseJSON.errors) {
                        const errors = xhr.responseJSON.errors;
                        let firstErrEl = null;
                        Object.keys(errors).forEach(function(name) {
                            // Find field by name (support array names)
                            const $field = $form.find('[name="' + name + '"]');
                            if ($field.length) {
                                $field.each(function() {
                                    $(this).data('invalid', true);
                                    showError($(this));
                                    if (!firstErrEl) firstErrEl = this;
                                });
                                //each show error message with toastr
                                toastr.error(errors[name][0], {
                                    timeOut: 5000
                                });
                            }
                        });
                        $('#error_div').show();
                        const target = firstErrEl || firstInvalid;
                        if (target) $('html, body').animate({
                            scrollTop: $(target).offset().top - 120
                        }, 300);
                    } else {
                        alert('Submission failed. Please try again later.');
                    }
                },
                complete: function() {
                    $('#loader-container').hide();
                    $submitBtn.prop('disabled', false);
                }
            });
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

    function showPackage(id, package, price) {
        indexoptionPackage++;
        getOptionPackage(indexoptionPackage, package, price, id);
    }

    @if (isset($data->packages))
        @foreach ($data->packages as $index => $package)
            showPackage('{{ $package->id }}', '{{ $package->package }}', '{{ $package->price }}');
        @endforeach
    @endif

    function getOptionPackage(index, package, price, id) {
        document.getElementById('options_package').style.display = 'block';
        let option = document.createElement('div');
        option.classList.add(`option${index}`);
        option.innerHTML = `
            <label>Option Package ${index}</label>
            <div class="form-group">
                <div class="row">
                    <div class="col-md-5">
                        <input type="number" class="form-control" name="optionPackage[${index}][value]" placeholder="Option Value" value="${price}">
                    </div>
                    <div class="col-md-5">
                        <input type="text" class="form-control" name="optionPackage[${index}][name]" placeholder="Option Name" value="${package}">
                    </div>
                    <div class="col-md-2">
                        <button type="button" onclick="removeOptionPackage(${index})" class="btn btn-danger btn-sm mt-1">
                            <i class="fa fa-trash"></i>
                        </button>
                    </div>
                </div>
            </div>

        `;
        document.getElementById('options_package').appendChild(option);
    }

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

    let package_id;

    function editOptionPackage(id) {
        package_id = id;
        var value = document.getElementById('optionPackageName' + package_id).value;
        var price = document.getElementById('optionPackagePrice' + package_id).value;
        $.ajax({
            url: '{{ route('edit-option-package') }}', //update option package
            type: 'POST',
            data: {
                _token: '{{ csrf_token() }}',
                id: package_id,
                value: value,
                price: price
            },
            success: function(response) {
                console.log(response);
                //change icon
                let icon = document.getElementById('changeIcon' + package_id);
                icon.classList.remove('fa-edit');
                icon.classList.add('fa-check');

                setTimeout(() => {
                    icon.classList.remove('fa-check');
                    icon.classList.add('fa-edit');
                }, 2000);
            }
        });
    }

    function deleteOptionPackage(id, index) {
        $.ajax({
            url: '{{ route('delete-option-package') }}', //delete option package
            type: 'POST',
            data: {
                _token: '{{ csrf_token() }}',
                id: package_id,
                index: index
            },
            success: function(response) {
                removeOptionPackage(response.index);
            }
        });
    }
</script>
