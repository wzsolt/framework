/* global bootstrap: false */
(function () {
    'use strict'
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
    tooltipTriggerList.forEach(function (tooltipTriggerEl) {
        new bootstrap.Tooltip(tooltipTriggerEl)
    });

    $(".date-picker").flatpickr();

    $('.select-table-options').on('click', function (){
        var $this = $(this);
        var $parent = $this.parents('.table-options');
        var $checkBoxes =  $parent.find('input[type=checkbox]:not(:disabled)').not('.remove-trigger');

        $checkBoxes.prop('checked', !$checkBoxes.prop('checked'));
    });

    $('.select-all').on('click', function (){
        var $checkBoxes =  $('#db-tables input[type=checkbox]:not(:disabled)').not('.remove-trigger');
        $checkBoxes.prop('checked', !$checkBoxes.prop('checked'));
    });

    $('.select-applied').on('click', function (){
        var $checkBoxes =  $('#db-tables input[type=checkbox][data-applied="1"]');

        $checkBoxes.prop('checked', !$checkBoxes.prop('checked'));
    });

    $('.set-api-type').on('click', function (){
        var val = parseInt($('input[name="api[type]"]:checked', '#frmApi').val());
        var id = parseInt($('#id').val());
        if(val){
            if(id > 0) {
                $('#api-key').removeClass('d-none').show();
            }
            $('#api-username').hide();
        }else{
            $('#api-username').removeClass('d-none').show();
            $('#api-key').hide();
        }
    });

    $('#btn-delete').on('click', function (){
        var id = parseInt($('#id').val());
        if(id && confirm('Are you sure you want to delete this user?')){
            window.location = '/tools/?page=api&delete=' + id;
        }
    });

    $('.select-copy').on('click', function (){
        var $this = $(this);
        var msg = '';

        $this.select();

        try {
            var ok = document.execCommand('copy');

            if(ok) {
                msg = 'API key copied to the clipboard!';
            }else{
                msg = 'Unable to copy!';
            }
        } catch (err) {
            msg = 'Unsupported Browser!';
        }

        $('#copy-info').html(msg);
    });

    $('.generate-password').on('click', function (){
        var pwd = generatePassword(10);

        $('#password').val(pwd);
        $('#password2').val(pwd);
    });

    $('.toggle-password').on('click', function (){
        var $this = $(this);
        var $input = $this.parents('.input-group').find('input');
        var type = $input.attr('type') === "password" ? "text" : "password";

        $input.attr('type', type);
        $this.toggleClass('fa-eye-slash', 'fa-eye');
    });

    $('.clear-value').on('click', function (){
        var $this = $(this);
        var $input = $this.parents('.input-group').find('input');
        $input.val('');
    });
})()

function generatePassword(length) {
    var charset = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789.?:=#!$-_",
        retVal = '';

    for (var i = 0, n = charset.length; i < length; ++i) {
        retVal += charset.charAt(Math.floor(Math.random() * n));
    }

    return retVal;
}
