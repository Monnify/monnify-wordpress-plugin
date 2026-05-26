jQuery(function ($) {

    var $wrapper = $('#monnify-split-wrapper');
    var $tableBody = $('#monnify-split-table tbody');
    var $addBtn = $('#monnify-add-split-row');

    var checkboxSelectors = [
        '#woocommerce_monnify_split_payment_enabled',
        'input[name="woocommerce_monnify[split_payment_enabled]"]',
        'input[name$="[split_payment_enabled]"]',
        '#split_payment_enabled'
    ];

    function getCheckbox() {
        for (var i = 0; i < checkboxSelectors.length; i++) {
            var $el = $(checkboxSelectors[i]);
            if ($el.length) return $el.first();
        }
        return $();
    }

    function toggleSplitFields() {
        var $cb = getCheckbox();
        var $fieldRow = $('#monnify-split-wrapper').closest('tr'); // WP settings row that contains the label/title

        // If checkbox not found, hide the entire settings row + inner wrapper to avoid showing label
        if (!$cb.length) {
            if ($fieldRow.length) $fieldRow.hide();
            $wrapper.hide();
            return;
        }

        if ($cb.is(':checked')) {
            if ($fieldRow.length) $fieldRow.show();
            $wrapper.show();
        } else {
            if ($fieldRow.length) $fieldRow.hide();
            $wrapper.hide();
        }
    }

    function escAttr(v) {
        if (v === null || typeof v === 'undefined') return '';
        return String(v).replace(/&/g,'&amp;').replace(/"/g,'&quot;').replace(/</g,'&lt;').replace(/>/g,'&gt;');
    }

    function updateRowUI($row) {
        var type = $row.find('select[name="monnify_splitType[]"]').val();
        var $value = $row.find('input[name="monnify_splitValue[]"]');
        var $unit = $row.find('.monnify-split-unit');

        if (!type || !$value.length) return;

        if (type === 'percentage') {
            $value.attr('placeholder', 'Enter percentage').attr('max', 100).attr('step', '0.01');
            $unit.text('%');
        } else {
            $value.attr('placeholder', 'Enter amount').removeAttr('max').attr('step', '0.01');
            $unit.text('NGN');
        }
    }

    function addSplitRow(data) {
        data = data || {};
        var checked = (data.feeBearer == 1 || data.feeBearer === true) ? 'checked' : '';
        var sub = data.subAccountCode || '';
        var fee = (typeof data.feePercentage !== 'undefined') ? data.feePercentage : '';
        var splitType = data.splitType || (typeof data.splitPercentage !== 'undefined' ? 'percentage' : (typeof data.splitAmount !== 'undefined' ? 'amount' : 'percentage'));
        var splitValue = (typeof data.splitValue !== 'undefined') ? data.splitValue : (data.splitAmount ?? data.splitPercentage ?? '');

        var $tr = $(
            '<tr>' +
                '<td><input type="text" name="monnify_subAccountCode[]" value="' + escAttr(sub) + '" class="regular-text" /></td>' +
                '<td>' +
                  '<select name="monnify_splitType[]">' +
                    '<option value="percentage">Percentage</option>' +
                    '<option value="amount">Amount</option>' +
                  '</select>' +
                '</td>' +
                '<td>' +
                  '<div style="display:flex;align-items:center;gap:6px;">' +
                    '<input type="number" step="0.01" min="0" name="monnify_splitValue[]" value="' + escAttr(splitValue) + '" class="small-text" style="flex:1;" />' +
                    '<span class="monnify-split-unit" style="white-space:nowrap;padding-left:6px;">%</span>' +
                  '</div>' +
                '</td>' +
                '<td>' +
                    '<input type="hidden" name="monnify_feeBearer[]" value="0" />' +
                    '<input type="checkbox" name="monnify_feeBearer[]" value="1" ' + checked + ' />' +
                '</td>' +
                '<td><input type="number" step="0.01" min="0" name="monnify_feePercentage[]" value="' + escAttr(fee) + '" class="small-text" /></td>' +
                '<td><button type="button" class="button monnify-remove-row">Remove</button></td>' +
            '</tr>'
        );

        $tr.find('select[name="monnify_splitType[]"]').val(splitType);
        $tableBody.append($tr);
        updateRowUI($tr);
    }

    function initSplitTable() {
        if (!$tableBody.length) return;
        var saved = (typeof monnify_saved_splits !== 'undefined' && Array.isArray(monnify_saved_splits)) ? monnify_saved_splits : [];
        if (saved.length) {
            saved.forEach(function (s) { addSplitRow(s); });
        } else {
            addSplitRow();
        }
    }

    /* Validation: ensure split percentage values and fee percentage values are between 0 and 100
       and total split percentage does not exceed 100, and total fee-bearer percentage <= 100 */
    function validateSplitPercentages() {
        var valid = true;
        var $firstInvalid = null;
        var totalSplitPercent = 0;
        var totalFeeBearerPercent = 0;

        function showError($input, message) {
            $input.css('border', '1px solid #d00');
            $input.next('.monnify-admin-error').remove();
            $('<div class="monnify-admin-error" style="color:#d00;font-size:12px;margin-top:4px;">' + message + '</div>').insertAfter($input);
        }

        function clearError($input) {
            $input.css('border', '');
            $input.next('.monnify-admin-error').remove();
        }

        // remove previous totals errors
        $('#monnify-total-error, #monnify-fee-total-error').remove();

        $tableBody.find('tr').each(function () {
            var $tr = $(this);

            // Validate split value if percentage type
            var type = $tr.find('select[name="monnify_splitType[]"]').val();
            var $splitInput = $tr.find('input[name="monnify_splitValue[]"]');
            if (type === 'percentage') {
                var val = $splitInput.val();
                var num = parseFloat(val);
                if (!isFinite(num) || num < 0 || num > 100) {
                    valid = false;
                    showError($splitInput, 'Split percentage must be a number between 0 and 100.');
                    if (!$firstInvalid) $firstInvalid = $splitInput;
                } else {
                    clearError($splitInput);
                    totalSplitPercent += num;
                }
            } else {
                clearError($splitInput);
            }

            // Validate fee percentage always (ensure number between 0 and 100)
            var $feeInput = $tr.find('input[name="monnify_feePercentage[]"]');
            if ($feeInput.length) {
                var feeVal = $feeInput.val();
                var feeNum = parseFloat(feeVal);
                if (!isFinite(feeNum) || feeNum < 0 || feeNum > 100) {
                    valid = false;
                    showError($feeInput, 'Fee percentage must be a number between 0 and 100.');
                    if (!$firstInvalid) $firstInvalid = $feeInput;
                } else {
                    clearError($feeInput);
                    // include in total only if this row is marked fee-bearer
                    var isBearer = $tr.find('input[name="monnify_feeBearer[]"]').last().is(':checked');
                    if (isBearer) totalFeeBearerPercent += feeNum;
                }
            }
        });

        // check split total
        if (totalSplitPercent > 100) {
            valid = false;
            var $err = $('<div id="monnify-total-error" style="color:#d00;font-size:13px;margin-top:8px;">Total split percentage exceeds 100% (current: ' + totalSplitPercent + '%). Adjust values so total ≤ 100%.</div>');
            $('#monnify-split-wrapper').append($err);
            if (!$firstInvalid) {
                var $firstPct = $tableBody.find('input[name="monnify_splitValue[]"]').filter(function () {
                    return $(this).closest('tr').find('select[name="monnify_splitType[]"]').val() === 'percentage';
                }).first();
                if ($firstPct.length) $firstInvalid = $firstPct;
            }
        } else {
            $('#monnify-total-error').remove();
        }

        // check fee-bearer total
        if (totalFeeBearerPercent > 100) {
            valid = false;
            var $feeErr = $('<div id="monnify-fee-total-error" style="color:#d00;font-size:13px;margin-top:8px;">Total fee % for fee-bearers exceeds 100% (current: ' + totalFeeBearerPercent + '%). Adjust fee % or fee bearers so total ≤ 100%.</div>');
            $('#monnify-split-wrapper').append($feeErr);
            if (!$firstInvalid) {
                var $firstFee = $tableBody.find('input[name="monnify_feePercentage[]"]').first();
                if ($firstFee.length) $firstInvalid = $firstFee;
            }
        } else {
            $('#monnify-fee-total-error').remove();
        }

        if (!valid) {
            if ($firstInvalid) {
                $('html, body').animate({ scrollTop: $firstInvalid.offset().top - 120 }, 200);
                $firstInvalid.focus();
            }
            alert('Please fix the highlighted percentage fields (must be between 0 and 100 and totals ≤ 100).');
        }

        return valid;
    }

    /* EVENTS */
    $(document).on('click', '#monnify-add-split-row', function (e) { e.preventDefault(); addSplitRow(); });
    $(document).on('click', '.monnify-remove-row', function (e) { e.preventDefault(); $(this).closest('tr').remove(); });
    $(document).on('change', 'select[name="monnify_splitType[]"]', function () { updateRowUI($(this).closest('tr')); });

    // bind change on possible checkbox selectors
    checkboxSelectors.forEach(function (sel) {
        $(document).on('change', sel, toggleSplitFields);
    });

    // Hook into the settings form submit to validate before saving
    setTimeout(function () {
        toggleSplitFields();
        initSplitTable();

        // find containing form and bind once
        var $form = $wrapper.closest('form');
        if ($form && $form.length) {
            $form.on('submit', function (e) {
                // only validate when split config is visible / enabled
                var $cb = getCheckbox();
                if ($cb.length && $cb.is(':checked')) {

                    // Normalize fee-bearer inputs: ensure exactly one monnify_feeBearer[] value per row
                    $('#monnify-split-table tbody tr').each(function () {
                        var $tr = $(this);
                        var $hidden = $tr.find('input[name="monnify_feeBearer[]"]').filter('[type="hidden"]');
                        var $checkbox = $tr.find('input[name="monnify_feeBearer[]"]').filter('[type="checkbox"]');

                        // if no hidden input, create one
                        if (!$hidden.length) {
                            $hidden = $('<input>', { type: 'hidden', name: 'monnify_feeBearer[]', value: '0' }).prependTo($tr.find('td').first());
                        }

                        // set hidden value according to checkbox
                        var val = ($checkbox.length && $checkbox.is(':checked')) ? '1' : '0';
                        $hidden.val(val);

                        // disable checkbox so it won't also be submitted (keeps only the hidden input)
                        if ($checkbox.length) {
                            $checkbox.prop('disabled', true);
                        }
                    });

                    if (!validateSplitPercentages()) {
                        e.preventDefault();
                        e.stopImmediatePropagation();
                        return false;
                    }
                }
                // allow normal submit
            });
        }
    }, 200);

});
