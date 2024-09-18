var tables = {
    inProgress: false,

    setProgress: function(on){
        tables.inProgress = on;
    },

    sendRequest: function(table, id, fkeys, action, params){
        if(id === '' || !id) id = 0;
        if(fkeys === '' || !fkeys) fkeys = 0;
        var alias = false;
        var options = false;
        var $table = $('#table-' + table);

        tables.setProgress(true);

        if($table.length > 0){
            alias = $table.data('alias');
            table = $table.data('table');
            options = $table.data('options');
        }

        $.ajax({
            method: 'POST',
            url: '/ajax/table/' + table + '/',
            data: {
                id: id,
                fkeys: fkeys,
                action: action,
                alias: alias,
                params: params,
                options: options
            }
        }).done(function (data) {
            if(typeof data !== 'object'){
                data = JSON.parse(data);
            }

            for (var selector in data) {
                if(selector.includes('tbody')) {
                    $table.find('tbody').remove();
                    $table.append(data[selector]);
                }else if(selector === 'fields') {
                    processJSONResponse(data[selector]);
                }else {
                    $(selector).replaceWith(data[selector]);
                }
            }

            app.reInit();
            tables.reInit();

            tables.setProgress(false);
        });
    },

    checkBox: function(table, id, fkeys, field, value, method){
        if(method !== 'mark'){
            method = 'check';
        }
        tables.sendRequest(table, id, fkeys, method, {'field': field, 'value': value});
    },

    page: function(table, fkeys, page){
        tables.sendRequest(table, 0, fkeys, 'page', {'page': page});
    },

    delete: function(table, id, fkeys){
        $('#confirm-delete').modal('hide');
        tables.sendRequest(table, id, fkeys, 'delete');
    },

    unDelete: function(table, id, fkeys){
        $('#confirm-delete').modal('hide');
        tables.sendRequest(table, id, fkeys, 'undelete');
    },

    copy: function(table, id, fkeys){
        $('#confirm-delete').modal('hide');
        tables.sendRequest(table, id, fkeys, 'copy');
    },

    action: function(table, id, fkeys, action, params){
        $('#confirm-delete').modal('hide');
        tables.sendRequest(table, id, fkeys, action, params);
    },

    reloadTable: function(params){
        tables.reload(params[0], params[1], params[2], params[3]);
    },

    reload: function(table, fkeys, closeModal, target){
        closeModal = typeof closeModal !== 'undefined' ? closeModal : true;

        if(closeModal) {
            if(!target) target = 'ajax-modal';
            $('#' + target).modal('hide');
        }

        tables.sendRequest(table, 0, fkeys, 'reload');
    },

    selectRow: function(table, fkeys){
        var ids = {};
        var checked = 0;
        var id = 0;
        var hasSelected = false;

        $('#table_' + table).find('.table-row-selector').each(function(index, obj){
            id = parseInt($(obj).val());
            if($(obj).is(':checked')){
                checked = 1;
                hasSelected = true;
            }else{
                checked = 0;
            }

            ids[id] = checked;
        });

        if(hasSelected){
            $('.btn-bulk-edit').removeClass('disabled');
        }else{
            $('.btn-bulk-edit').addClass('disabled');
        }

        tables.sendRequest(table, 0, fkeys, 'select-row', {'ids': ids});
    },

    initControls: function(){
        $(document).on('click', '.tr-clickable', function(e){
            if($(this).data('modal')){
                var $modal = $('#ajax-modal');
                $modal.find('.modal-dialog').addClass('modal-' + $(this).data('size'));
                $modal.find('.modal-content').load($(this).data('href'));
                $modal.modal('show');
            }else if($(this).data('url')){
                if($(this).data('target') === 'self'){
                    document.location = $(this).data('url');
                }else {
                    window.open($(this).data('url'));
                }
            }else{
                var $modal = $("a[data-toggle='modal'] i");
                if (!$(e.target).is($modal)) {
                    window.location.href += $(this).data('edit');
                }
            }
        });

        $('.td-clickable').on('click', function (e) {
            var $this = $(this).parent('tr');
            var page = $this.data('url');
            document.location = page;
        });

        $(document).on('click', '.btn-table-pager', function(e){
            var $this = $(this);
            var table = $this.parents('.pagination').data('table');
            var fkeys = $this.parents('.pagination').data('fkeys');
            var page = $this.data('page');

            if(!$this.hasClass('disabled') && !$this.hasClass('active')){
                tables.page(table, fkeys, page);
            }
        });

        $(document).on('click', '.table-options, .table-check', function(e){
            e.stopImmediatePropagation();
        });

        $(document).on('click', '.table-check input[type=checkbox]', function(e){
            var $this = $(this);
            e.stopImmediatePropagation();

            var checked = ($this.is(':checked')) ? 1 : 0;
            tables.checkBox($this.data('table'), $this.data('id'), $this.data('fkeys'), $this.data('field'), checked, $this.data('method'));
        });

        $(document).on('click', '.table-row-selector', function () {
            var $this = $(this);
            var name = $this.parents('table').data('alias');
            if(!name) {
                name = $this.parents('table').data('table');
            }
            var fkeys = $this.parents('table').data('fkeys');
            var checked = $this.is(':checked');

            if(checked) {
                $this.parents('tr').addClass('tr-selected');
            }else{
                $this.parents('tr').removeClass('tr-selected');
            }

            tables.selectRow(name, fkeys);
        });

        $(document).on('click', '.table-row-selector-all', function (e) {
            var $this = $(this);
            var name = $this.parents('table').data('alias');
            if(!name) {
                name = $this.parents('table').data('table');
            }

            var fkeys = $this.parents('table').data('fkeys');
            var checked = $this.is(':checked');

            if(checked) {
                $this.parents('table').find('.table-row-selector').prop('checked', checked).parents('tr').addClass('tr-selected');
            }else{
                $this.parents('table').find('.table-row-selector').prop('checked', checked).parents('tr').removeClass('tr-selected');
            }

            tables.selectRow(name, fkeys);
        });

        $(document).on('click', '.table-row-selector-menu-all', function (e) {
            $('.table-row-selector-all').trigger('click');
        });

        $(document).on('click', '.table-row-selector-menu-none', function (e) {
            var $this = $(this);
            var name = $this.parents('table').data('table');
            var fkeys = $this.parents('table').data('fkeys');
            $this.parents('table').find('.table-row-selector').prop('checked', false).parents('tr').removeClass('tr-selected');
            $('.table-row-selector-all').prop('checked', false);

            tables.sendRequest(name, 0, fkeys, 'unselect-row');
        });

    },

    reInit: function(){
        $('.table-sort').sortable({
            items: 'tr:not(.no-sort)',
            placeholder: 'table-sort-placeholder',
            stop: function(e, ui){
                var table = $(ui.item).data('table');
                var fkeys = $(ui.item).data('fkeys');
                var groupId = $(ui.item).data('groupid') || 0;
                var itemOrder = $(e.target).sortable("toArray");

                tables.sendRequest(table, 0, fkeys, 'sort', {
                    groupId: groupId,
                    order: itemOrder
                });
            }
        }).disableSelection();

        /*
        if ($('.table-row-selector:checked').length === $('.table-row-selector').length) {
            $('.table-row-selector-all').prop('checked', true);
        }else{
            $('.table-row-selector-all').prop('checked', false);
        }
        */
    },

    init: function(){
        this.initControls();
        this.reInit();
    }
};

$(function() {
    tables.init();
});
