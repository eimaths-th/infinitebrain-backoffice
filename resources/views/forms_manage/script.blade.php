<script>
    $(document).ready(function() {
        const $form = $('#infinite');
        const fieldConfig = window.fieldConfig || {};
        const fieldSettings = $.extend(true, {}, window.initialFieldSettings || {});

        function containerFor($el) {
            const $focus = $el.closest('.form-focus');
            if ($focus.length) return $focus;
            const $group = $el.closest('.form-group');
            return $group.length ? $group : $el.parent();
        }

        function ensureFieldSetting(fieldKey) {
            if (!fieldSettings[fieldKey]) {
                const defaults = fieldConfig[fieldKey] || {};
                fieldSettings[fieldKey] = {
                    enabled: defaults.default_enabled !== undefined ? !!defaults.default_enabled : true,
                    required: defaults.default_required !== undefined ? !!defaults.default_required : false,
                };
            } else {
                const state = fieldSettings[fieldKey];
                state.enabled = state.enabled === true || state.enabled === '1' || state.enabled === 1;
                state.required = state.required === true || state.required === '1' || state.required === 1;
            }

            if (!fieldSettings[fieldKey].enabled) {
                fieldSettings[fieldKey].required = false;
            }

            return fieldSettings[fieldKey];
        }

        function findFieldWrapper($el) {
            if (!$el || !$el.length) return $();
            const columnSelectors = [
                '.col-xl-12', '.col-lg-12', '.col-md-12', '.col-sm-12', '.col-12',
                '.col-xl-11', '.col-lg-11', '.col-md-11', '.col-sm-11', '.col-11',
                '.col-xl-10', '.col-lg-10', '.col-md-10', '.col-sm-10', '.col-10',
                '.col-xl-9', '.col-lg-9', '.col-md-9', '.col-sm-9', '.col-9',
                '.col-xl-8', '.col-lg-8', '.col-md-8', '.col-sm-8', '.col-8',
                '.col-xl-7', '.col-lg-7', '.col-md-7', '.col-sm-7', '.col-7',
                '.col-xl-6', '.col-lg-6', '.col-md-6', '.col-sm-6', '.col-6',
                '.col-xl-5', '.col-lg-5', '.col-md-5', '.col-sm-5', '.col-5',
                '.col-xl-4', '.col-lg-4', '.col-md-4', '.col-sm-4', '.col-4',
                '.col-xl-3', '.col-lg-3', '.col-md-3', '.col-sm-3', '.col-3',
                '.col-xl-2', '.col-lg-2', '.col-md-2', '.col-sm-2', '.col-2',
                '.col-xl-1', '.col-lg-1', '.col-md-1', '.col-sm-1', '.col-1',
            ];

            for (const selector of columnSelectors) {
                const $wrapper = $el.closest(selector);
                if ($wrapper.length) {
                    return $wrapper.first();
                }
            }

            const $group = $el.closest('.form-group');
            if ($group.length) {
                return $group.first();
            }

            return $el.parent();
        }

        function storeOriginalRequired($inputs) {
            $inputs.each(function() {
                const $input = $(this);
                if ($input.data('original-required') === undefined) {
                    $input.data('original-required', $input.prop('required'));
                }
            });
        }

        function setRequiredState($inputs, shouldRequire) {
            $inputs.each(function() {
                $(this).prop('required', !!shouldRequire);
            });
        }

        function clearValidationState($inputs) {
            $inputs.each(function() {
                const $input = $(this);
                $input.data('invalid', false).css('border', '');
                hideError($input);
            });
        }

        function applyFieldState(fieldKey) {
            const config = fieldConfig[fieldKey];
            if (!config || !config.builder_input) {
                $('[data-field-enabled="' + fieldKey + '"]').prop('disabled', true).prop('checked', false);
                $('[data-field-required="' + fieldKey + '"]').prop('disabled', true).prop('checked', false);
                return;
            }

            const $elements = $form.find(config.builder_input);
            if (!$elements.length) {
                $('[data-field-enabled="' + fieldKey + '"]').prop('disabled', true).prop('checked', false);
                $('[data-field-required="' + fieldKey + '"]').prop('disabled', true).prop('checked', false);
                return;
            }

            const settings = ensureFieldSetting(fieldKey);
            const $wrapper = findFieldWrapper($elements.first());
            const $inputs = $wrapper.find('input, select, textarea');

            storeOriginalRequired($inputs);

            if (!settings.enabled) {
                $wrapper.attr('data-field-visible', '0').hide();
                setRequiredState($inputs, false);
                clearValidationState($inputs);

                $('[data-field-required="' + fieldKey + '"]').prop('checked', false).prop('disabled', true);
                $('[data-field-enabled="' + fieldKey + '"]').prop('checked', false);
            } else {
                $wrapper.attr('data-field-visible', '1').show();
                setRequiredState($inputs, settings.required);
                $('[data-field-enabled="' + fieldKey + '"]').prop('checked', true);
                $('[data-field-required="' + fieldKey + '"]').prop('disabled', false).prop('checked', settings.required);
            }
        }

        function applyAllFieldStates() {
            Object.keys(fieldConfig).forEach(function(fieldKey) {
                applyFieldState(fieldKey);
            });
        }

        applyAllFieldStates();

        $('.field-toggle-enabled').on('change', function() {
            const fieldKey = $(this).data('field');
            const enabled = $(this).is(':checked');
            const settings = ensureFieldSetting(fieldKey);
            settings.enabled = enabled;
            if (!enabled) {
                settings.required = false;
            } else if (settings.required === undefined) {
                const defaults = fieldConfig[fieldKey] || {};
                settings.required = defaults.default_required !== undefined ? !!defaults.default_required : false;
            }

            applyFieldState(fieldKey);
        });

        $('.field-toggle-required').on('change', function() {
            const fieldKey = $(this).data('field');
            const settings = ensureFieldSetting(fieldKey);
            settings.required = $(this).is(':checked');
            applyFieldState(fieldKey);
        });

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


