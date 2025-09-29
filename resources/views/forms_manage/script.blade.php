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


