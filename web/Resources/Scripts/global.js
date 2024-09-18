var app = {
    inProgress: false,

    setProgress: function(on){
        app.inProgress = on;
        if(!on) {
            $('.btn-progress').each(function() {
                var $this = $(this);
                $this.html($this.attr('data-orig-caption')).removeAttr('disabled').removeAttr('data-orig-caption');
            });
            //$('#ajax-modal .modal-footer .btn').removeAttr('disabled').removeClass('disabled');
            $('.card-progress').hide();
        }else{
            $('.btn-progress').each(function() {
                var $this = $(this);
                if(!$this.attr('data-orig-caption')) {
                    $this.attr('data-orig-caption', $this.html());
                }

                $this.html('<i class="fas fa-circle-notch fa-spin"></i>');
            });

            $('.card-progress').removeClass('d-none').show();
            //$('#ajax-modal .modal-footer .btn').attr('disabled', 'disabled').addClass('disabled');
        }
    },

    setTag:function(id, tag, mode){
        if(id && tag){
            $.ajax({
                url: "/ajax/tagslist/" + mode + "/?id=" + id + "&tag=" + tag,
                success: function () {}
            });
        }
    },

    initControls: function(){
        $('form:not(.frm-submit-enter)').on('keyup keypress', function(e) {
            var keyCode = e.keyCode || e.which;
            if (keyCode === 13 && !$(e.target).is('textarea')) {
                e.preventDefault();
                return false;
            }
        });

        $(document).on('keyup', 'input[type="password"]', function(){
            if($(this).next('[data-toggle="password"]').length > 0) {
                if ($(this).val() !== '') {
                    $(this).next('[data-toggle="password"]').removeClass('d-none').show();
                } else {
                    $(this).next('[data-toggle="password"]').hide();
                }
            }
        });

        document.addEventListener('click', e => {
            if (e.target.closest('[data-toggle="password"]')) {
                let input = e.target.closest('[data-toggle="password"]').parentNode.querySelector('input');

                $(e.target).toggleClass('fa-eye fa-eye-slash');

                input.type = input.type === 'password' ? 'text' : 'password';
            }
        });

        document.addEventListener('click', e => {
            if (e.target.closest('[data-toggle="clear"]')) {
                e.target.closest('[data-toggle="clear"]').previousElementSibling.value = '';
            }
        });

        $(document).on('click', '.btn-modal-submit', function(){
            var $this = $(this);
            var $modal = $this.parents('.modal-content');
            var $form = $modal.find('form');
            var value = $this.val() || 1;
            var action = $form.attr('action');

            $form.attr('action', action.split('?')[0] + '?' + $this.attr('name') + '=' + value);
            $form.submit();
        });

        $(document).on('keyup', '.alphanumeric-only', function(){
            this.value = this.value.replace(/[^a-z0-9]/ig, "");
        });

        $(document).on('blur', '.alphanumeric-only', function(){
            this.value = this.value.replace(/[^a-z0-9]/ig, "");
        });

        $(document).on('blur', '.time-format', function () {
            var $this = $(this);
            var time = $this.val();
            $this.val(moment(time.toString(), 'LT').format('HH:mm'));
        });

        $(document).on('keyup', '.numbersonly', function(){
            var chars = $(this).data('chars');
            var replaceCharFrom = $(this).data('replace-from');
            var replaceCharTo = $(this).data('replace-to');

            if(replaceCharFrom && replaceCharTo){
                this.value = this.value.replace(replaceCharFrom, replaceCharTo);
            }

            if(chars === '' || typeof chars === 'undefined'){
                chars = '\\-.,';
            }
            var pattern = '[^0-9' + chars + ']';
            var re = new RegExp(pattern, 'ig');
            this.value = this.value.replace(re, "");
        });

        $(document).on('blur', '.numbersonly', function(){
            var chars = $(this).data('chars');
            if(chars === '' || typeof chars === 'undefined'){
                chars = '\\-.,';
            }

            var pattern = '[^0-9' + chars + ']';
            var re = new RegExp(pattern, 'ig');
            this.value = this.value.replace(re, "");
        });

        $(document).on('change', '.change-label', function () {
            var $this = $(this);
            if($this.val() != '0') {
                var text = $this.find('option:selected').text();
                $('.has-label').parents('.input-group').find('.input-group-text').html(text);
            }
        });

        $(document).on('change', '.change-state', function (e) {
            e.stopPropagation();

            var $this = $(this);
            var options = $this.data('stateOptions');
            var value, found = false;

            if(options) {
                if (this.type && this.type === 'checkbox') {
                    value = ($this.is(':checked') ? 1 : 0);
                } else if (this.type && this.type === 'radio') {
                    value = ($this.is(':checked') ? $this.val() : 0);
                } else {
                    value = $this.val();
                }

                /*
                if(typeof options !== 'object'){
                    options = JSON.parse(options);
                }
                */

                $.each(options, function (val, opt) {
                    if (val == value) {
                        found = true;
                        $.each(opt, function (action, elements) {
                            if (action === 'Show') {
                                $(elements).removeClass('d-none').show();
                            } else if (action === 'Hide') {
                                $(elements).hide();
                            } else if (action === 'Disable') {
                                $(elements).attr('disabled', 'disabled');
                            } else if (action === 'Enable') {
                                $(elements).removeAttr('disabled');
                            } else if (action === 'Readonly') {
                                $(elements).attr('readonly', 'readonly');
                            } else if (action === 'Editable') {
                                $(elements).removeAttr('readonly');
                            } else if (action === 'SetValue') {
                                $(elements.el).val(elements.val).trigger('change');
                            }
                        });
                    }
                });

                if (!found) {
                    var def = $this.data('stateDefault');
                    if (def) {
                        $.each(def, function (action, elements) {
                            if (action === 'Show') {
                                $(elements).removeClass('d-none').show();
                            } else if (action === 'Hide') {
                                $(elements).hide();
                            } else if (action === 'Disable') {
                                $(elements).attr('disabled', 'disabled');
                            } else if (action === 'Enable') {
                                $(elements).removeAttr('disabled');
                            } else if (action === 'Readonly') {
                                $(elements).attr('readonly', 'readonly');
                            } else if (action === 'Editable') {
                                $(elements).removeAttr('readonly');
                            } else if (action === 'SetValue') {
                                $(elements.el).val(elements.val);
                            }
                        });
                    }
                }
            }
        });

        if(jQuery().selectpicker) {
            function toggleClear(select, el) {
                el.style.display = select.value == '' ? 'none' : 'inline'
                const optionText = select.parentNode.querySelector('.filter-option')
                select.value == '' ? optionText.classList.remove('mr-4') : optionText.classList.add('mr-4')
            }

            for (const el of document.querySelectorAll('select.select-picker')) {
                let config = { style: 'btn' }

                // creatable
                if (el.dataset.bsSelectCreatable === 'true') {
                    config.liveSearch = true;
                    config.noneResultsText = el.dataset.helpText + ': <b>{0}</b>';
                }
                // sizing
                if (el.dataset.bsSelectSize) {
                    config.style = 'btn btn-' + el.dataset.bsSelectSize;
                    el.classList.add('form-control-' + el.dataset.bsSelectSize);
                }
                // clearable
                if (el.dataset.bsSelectClearable === 'true') {
                    el.insertAdjacentHTML('afterend', '<span class="bs-select-clear"></span>');
                }

                // run
                $(el).selectpicker(config);

                const bs = el.closest('.bootstrap-select');

                // creatable
                if (el.dataset.bsSelectCreatable === 'true') {
                    const bsInput = bs.querySelector('.bs-searchbox .form-control');
                    bsInput.addEventListener('keyup', function (e) {
                        if (bs.querySelector('.no-results')) {
                            if (e.keyCode === 13) {
                                el.insertAdjacentHTML('afterbegin', `<option value="${this.value}">${this.value}</option>`);
                                let newVal = $(el).val();
                                Array.isArray(newVal) ? newVal.push(this.value) : newVal = this.value;
                                $(el).val(newVal);
                                $(el).selectpicker('toggle');
                                $(el).selectpicker('refresh');
                                $(el).selectpicker('render');
                                bs.querySelector('.dropdown-toggle').focus();
                                this.value = '';
                            }
                        }

                        if (e.keyCode === 13) {
                            if($(el).parents('form').hasClass('frm-submit-enter')){
                                var $form = $(el).parents('form');
                                var formName = $form.attr('id').substring(0, ($form.attr('id').length - 5));
                                $form = $form.first();
                                $form.append('<input type="hidden" name="' + formName + '[save]' + '" value="1" />');
                                $form.submit();
                            }
                        }
                    })
                }

                // clearable
                const clearEl = el.parentNode.nextElementSibling;
                if (clearEl && clearEl.classList.contains('bs-select-clear')) {
                    toggleClear(el, clearEl);
                    el.addEventListener('change', () => toggleClear(el, clearEl));
                    clearEl.addEventListener('click', () => {
                        $(el).selectpicker('val', '');
                        el.dispatchEvent(new Event('change'));
                    })
                }
            }
            // $('.select-picker:not(.inited)').addClass('inited').selectpicker();
        }

        if(jQuery().select2) {
            $('.select2:not(.inited)').addClass('inited').select2();
        }

        this.reInit();
    },

    reInit: function(){
        if(jQuery().selectpicker) {
            $('.select-picker:not(.inited)').addClass('inited').selectpicker();
        }

        if(jQuery().select2) {
            $('.select2:not(.inited)').select2({
                dropdownParent: $('#ajax-modal .modal-content')
            });
        }

        $('.change-state').not('.skip-init').trigger('change');

        $('[data-bs-toggle="tooltip"]').tooltip();

        $('.autocomplete').autoComplete({
            'minLength': 2
        });

        $(document).on('autocomplete.select', '.autocomplete', function (e, item){
            var $this = $(this);
            var $parent = $this.parents('div');
            $parent.find('input[type="hidden"]').val(item.value);
        });

        $(document).on('blur', '.autocomplete', function (e){
            var $this = $(this);
            var $parent = $this.parents('div');
            if($this.val() === ''){
                $parent.find('input[type="hidden"]').val('');
            }
        });
    },

    initModals: function(){
        $('#preview-modal').on('show.bs.modal', function (e) {
            var url = '';
            var modal = $(this);
            if(e.relatedTarget) {
                var button = $(e.relatedTarget);
                if (button.data('size')) {
                    $('#preview-modal .modal-dialog').addClass('modal-' + button.data('size'));
                }

                if (button.attr('href') != '#' && button.attr('href') != '') {
                    url = button.attr('href');
                } else if (button.data('href')) {
                    url = button.data('href');
                }
            }

            if (url != '') {
                modal.find('.modal-content').load(url);
            }
        });

        $('#preview-modal').on('hidden.bs.modal', function (e) {
            $(e.target).removeData('bs.modal');
            $('#preview-modal .modal-content').html('');
        });

        $('#ajax-modal, #file-modal').on('show.bs.modal', function (e) {
            var url = '';
            var modal = $(this);
            if(e.relatedTarget) {
                var button = $(e.relatedTarget);
                if (button.data('size')) {
                    $('#ajax-modal .modal-dialog').addClass('modal-' + button.data('size'));
                }
                if (button.data('backdrop') === 'static') {
                    //$('#ajax-modal').data('bs.config').options.backdrop = 'static';
                    //$('#ajax-modal').data('bs.modal').options.keyboard = false;
                }

                if (button.attr('href') != '#' && button.attr('href') != '') {
                    url = button.attr('href');
                } else if (button.data('href')) {
                    url = button.data('href');
                }
            }

            if (url != '') {
                modal.find('.modal-content').load(url);
            }

            if($('.select2').length) {
                $('.select2').select2({
                    dropdownParent: $('#ajax-modal .modal-content')
                });
            }
        });

        $('#ajax-modal').on('hidden.bs.modal', function (e) {
            $(e.target).removeData('bs.modal');
            $('#ajax-modal .modal-dialog').removeClass('modal-sm modal-lg modal-xl');
            $('#ajax-modal .modal-content').html('');

            $('#ajax-modal').removeAttr('data-bs-backdrop');
            $('#ajax-modal').removeAttr('data-bs-keyboard');
        });

        $('#file-modal').on('hidden.bs.modal', function (e) {
            $(e.target).removeData('bs.modal');
            $('#file-modal .modal-dialog').removeClass('modal-sm modal-lg modal-xl');
            $('#file-modal .modal-content').html('');
        });

        $('#confirm-delete').on('show.bs.modal', function(e) {
            e.stopPropagation();

            var $confirm_button = $(this).find('.danger');
            var color = $(e.relatedTarget).data('color');

            $confirm_button.removeClass('btn-primary btn-secondary btn-info btn-success btn-warning btn-danger');
            $('#confirm-delete .modal-header').removeClass('bg-warning bg-success bg-primary bg-info bg-danger bg-secondary');
            $('#confirm-delete .modal-content').removeClass('border-warning border-success border-primary border-info border-danger border-secondary');

            if(color){
                $confirm_button.addClass('btn-' + color);
                $('#confirm-delete .modal-header').addClass( 'bg-' + color );
                $('#confirm-delete .modal-content').addClass('border-' + color);
            }else{
                $confirm_button.addClass('btn-danger');
                $('#confirm-delete .modal-header').addClass('bg-danger');
                $('#confirm-delete .modal-content').addClass('border-danger');
            }

            if ($(e.relatedTarget).data('confirm-reason')) {
                $(this).find('.confirm-reason').removeClass('d-none').show();
                var $confirmInput = $(this).find('.confirm-reason-input');

                if($confirmInput.val().length == 0) {
                    $confirm_button.prop('disabled', true);
                }else{
                    $confirm_button.prop('disabled', false);
                }

                if ($(e.relatedTarget).data('confirm-reason-field')) {
                    var $field = $($(e.relatedTarget).data('confirm-reason-field'));
                    $confirmInput.on('keyup', function () {
                        $field.val( $confirmInput.val() );
                    })
                }
            }else{
                $(this).find('.confirm-reason').hide();
                $(this).find('.confirm-reason-input').val('');
                $confirm_button.prop('disabled', false);
            }

            if ($(e.relatedTarget).data('href') != undefined) {
                $confirm_button.on('click',  function(){
                    document.location = $(e.relatedTarget).data('href');
                });
            } else if ($(e.relatedTarget).data('confirm-action') != undefined) {
                $confirm_button.attr('onclick', $(e.relatedTarget).data('confirm-action'));
            }
            if($(e.relatedTarget).data('confirm-button') != undefined) {
                $confirm_button.html($(e.relatedTarget).data('confirm-button'));
            } else {
                $confirm_button.html($confirm_button.data('default-caption'));
            }
            if($(e.relatedTarget).data('title') != undefined) {
                $('#confirm-delete .modal-title').html( $(e.relatedTarget).data('title') );
            }else{
                $('#confirm-delete .modal-title').html( $('#confirm-delete .modal-title').data('default-title') );
            }

            $('.confirm-question').html( $(e.relatedTarget).data('confirm-question') );
            $('.debug-data').html(  $(e.relatedTarget).data('confirm-data')  );
        });

        // Keep modal scrollable if a confirm modal opened
        $('#confirm-delete').on('hidden.bs.modal', function () {
            $('.modal-footer .btn:not(.btn-approve), .modal-header button').show();
            $('.modal-footer .modal-loader').remove();

            if($('#ajax-modal').hasClass('in')){
                $('body').addClass('modal-open')
            }
        });

        $(document).on('focus', '.input-group > input, .input-group > select', function(e){
            $(this).parents('.input-group').addClass("input-group-focus");
        }).on('blur', '.input-group > input, .input-group > select', function(e){
            $(this).parents('.input-group').removeClass("input-group-focus");
        });
    },

    showMessage: function(value){
        var options = {
            allowToastClose: false,
            showHideTransition: 'slide',
            heading: value.title,
            text: value.message,
            position: 'top-right',
            icon: value.type,
            hideAfter: 3000,
            stack: 10
        };

        //$.toast().reset('all');
        $.toast(options);
    },

    showMessages: function(){
        if(typeof _messages !== 'undefined'){
            $.each( _messages, function(idx, value) {
                app.showMessage(value);
            });
        }
    },

    initToaster: function(){
        setTimeout(function(){ app.showMessages(); }, 500);
    },

    init: function(){
        this.initControls();
        this.initModals();
        this.initToaster();

        showAddress();
    }
};

$(function() {
    $.fn.modal.Constructor.prototype._enforceFocus = function () { };

    app.init();
});

function postModalForm(form, btnValue, btnName) {
    if(!btnValue) btnValue = 1;
    if(!btnName) btnName = 'save';
    var formName = $(form).attr('id').substring(0, ($(form).attr('id').length - 5));
    var data = new FormData($(form).get(0));
    data.append(formName + '[' + btnName + ']', btnValue);
    app.setProgress(true);

    $.ajax({
        type: 'POST',
        url: $(form).attr('action'),
        data: data,
        cache: false,
        processData: false,
        contentType: false,
        success: function(data) {
            $('#confirm-delete').modal('hide');
            if(data) {
                processJSONResponse(data);
                app.setProgress(false);
                app.reInit();
            }
        }
    });
}

function pageRedirect(url){
    document.location = url;
}

function modalFormPageRefresh(newParams) {
    //$('#ajax-modal').modal('hide');
    var url = new URL(window.location.href);

    if(typeof newParams === 'object' && newParams !== null){
        $.each(newParams, function(key, value){
            url.searchParams.append(key, value);
        });
    }

    window.location.href = url.href;
}

/**
 * @deprecated
 * @param data
 */
function modalFormPageUpdate(data){
    $('#ajax-modal').modal('hide');

    if(data){
        if(typeof data !== 'object'){
            data = JSON.parse(data);
        }
        processJSONResponse(data);
    }
}

function processJSONResponse(data){
    if(typeof data !== 'object'){
        data = JSON.parse(data);
    }

    $.each(data, function(selector, action){
        if(selector !== 'error') {

            $.each(action, function (method, value) {

                if (typeof window[selector] === 'object') {
                    if (typeof window[selector][method] === 'function') {
                        window[selector][method](value);
                    }
                } else if (selector === 'messages') {
                    $.each(action, function (idx, val) {
                        app.showMessage({
                            'type': val.type,
                            'message': val.message
                        });
                    });
                } else {
                    if (method === 'show') {
                        if (value === true) {
                            $(selector).hide().removeClass('d-none').show();
                        } else {
                            $(selector).hide();
                        }
                    } else if (method === 'tagsInput') {
                        $.each(value, function (i, aValue) {
                            if (aValue) {
                                $(selector).tagsinput('add', aValue);
                            }
                        });
                    } else if (method === 'summernote') {
                        $(selector).summernote('code', value);
                        crm.isdirty = false;
                    } else if (method === 'addClass') {
                        $(selector).addClass(value);
                    } else if (method === 'removeClass') {
                        $(selector).removeClass(value);
                    } else if (method === 'remove') {
                        $(selector).remove();
                    } else if (method === 'replace') {
                        $(selector).replaceWith(value);
                    } else if (method === 'html') {
                        $(selector).html(value);
                    } else if (method === 'closeModal') {
                        $(selector).modal('hide');
                    } else if (method === 'attr') {
                        $.each(value, function (attr, aValue) {
                            if (aValue) {
                                $(selector).attr(attr, aValue);
                            } else {
                                $(selector).removeAttr(attr, '');
                            }
                        });
                    } else if (method === 'value') {
                        if ($(selector).is(':checkbox') || $(selector).is(':radio')) {
                            $(selector).prop('checked', value);
                        } else {
                            $(selector).val(value);
                        }
                    } else if (method === 'options') {
                        $(selector).find('option').remove();
                        $(selector).append(value.map(function (val) {
                            return '<option value="' + val.id + '">' + val.name + '</option>'
                        }));
                    } else if (method === 'functions') {
                        if (value.callback) {
                            var fn = window[value.callback];
                            if (typeof fn === 'function') {
                                fn(value.arguments);
                            }
                        }
                    } else {
                        if (typeof window[method] === 'object') {
                            if (typeof window[method][selector] === 'function') {
                                window[method][selector](value);
                            }
                        }
                    }
                }
            });
        }
    });

    return data;
}

function hexToRgbA(hex, opacity){
    var c;
    if(/^#([A-Fa-f0-9]{3}){1,2}$/.test(hex)){
        c= hex.substring(1).split('');
        if(c.length== 3){
            c= [c[0], c[0], c[1], c[1], c[2], c[2]];
        }
        c= '0x'+c.join('');
        return 'rgba('+[(c>>16)&255, (c>>8)&255, c&255].join(',')+',' + opacity + ')';
    }
    throw new Error('Bad Hex');
}

function jsonToUrl(params) {
    var query = '';
    for (var key in params) {
        query += '&' + encodeURIComponent(key) + '=' + encodeURIComponent(params[key]);
    }
    return query;
}

function showAddress() {
    var part1 = "info";
    var part2 = Math.pow(2,6);
    var part3 = String.fromCharCode(part2);
    var part4 = "pandaero.com";
    var part5 = part1 + String.fromCharCode(part2) + part4;

    $('.show-email').html("<a href=" + "mai" + "lto" + ":" + part5 + ">" + part1 + part3 + part4 + "</a>");
}

