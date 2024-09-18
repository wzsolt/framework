var calendar;

var cal = {
    progress: false,
    settings: [],

    saveData: function(action, params){
        if(!cal.progress) {
            cal.setProgress(true);

            $.ajax({
                url: "/ajax/calendar/" + action,
                type: 'post',
                data: params,
                success: function (data) {
                    if (data) {
                        processJSONResponse(data);
                        cal.initControls();
                    }

                    cal.setProgress(false);
                }
            });
        }
    },

    setProgress: function(show) {
        cal.progress = show;

        if(show) {
            $('.progress-overlay').removeClass('d-none').show();
        }else{
            $('.progress-overlay').hide();
            $('.progress-animation').hide();
        }
    },

    initControls: function(){
        $('.update-calendar').on('click', function () {
            calendar.refetchEvents();
        });
    },

    checkChange: function(info){
        var apply = true;
        $.ajax({
            url: '/ajax/' + cal.settings.id + '/check/change/' + info.event.id + '/?start=' + moment(info.event.start).format('YYYY-MM-DDTHH:mm:ss') + '&end=' + moment(info.event.end).format('YYYY-MM-DDTHH:mm:ss'),
            processData: false,
            contentType: false,
            success: function (data) {
                data = JSON.parse(data);
                if(data.confirmChange){
                    apply = false;
                    if (confirm(data.confirmQuestion)) {
                        apply = true;
                    }else{
                        info.revert();
                    }
                }

                if(apply){
                    cal.applyChange(info);
                }
            }
        });

    },

    applyChange: function(info){
        $.ajax({
            url: '/ajax/' + cal.settings.id + '/do/change/?id=' + info.event.id + '&start=' + moment(info.event.start).format('YYYY-MM-DDTHH:mm:ss') + '&end=' + moment(info.event.end).format('YYYY-MM-DDTHH:mm:ss'),
            processData: false,
            contentType: false,
            success: function (data) {
                processJSONResponse(data);
                if (cal.settings.prefetchEvents) {
                    calendar.refetchEvents();
                }
                if(data.error){
                    info.revert();
                }
            }
        });
    },

    initCalendar: function(){
        if(cal.settings.id) {
            var calendarEl = document.getElementById(cal.settings.id);
            calendar = new FullCalendar.Calendar(calendarEl, {
                schedulerLicenseKey: 'GPL-My-Project-Is-Open-Source',
                plugins: ['momentTimezone', 'dayGrid', 'timeGrid', 'list', 'bootstrap', 'interaction', 'resourceTimeline'],
                timeZone: cal.settings.timezone,
                themeSystem: 'bootstrap',
                lazyFetching: false,
                header: {
                    left: 'prev,next today',
                    center: 'title',
                    right: cal.settings.views
                },
                slotLabelFormat: {
                    hour: '2-digit',
                    minute: '2-digit',
                    meridiem: false,
                    hour12: false
                },
                slotDuration: cal.settings.slotDuration,
                views: {
                    resourceTimelineDay: {
                        buttonText: 'Timeline day'
                    },
                    resourceTimelineWeek: {
                        buttonText: 'Timeline week'
                    },
                    dayGrid: {
                        titleFormat: { year: 'numeric', month: 'long', day: '2-digit', weekday: 'long' }
                    },
                    day: {
                        titleFormat: { year: 'numeric', month: 'long', day: '2-digit', weekday: 'long' }
                    },
                    week: {
                        titleFormat: { year: 'numeric', month: 'long', day: '2-digit', weekday: 'long' }
                    }
                },

                defaultView: cal.settings.defaultView,
                defaultDate: cal.settings.defaultDate,
                validRange: function(nowDate) {
                    if(cal.settings.minDate) {
                        return {
                            start: cal.settings.minDate
                        };
                    }
                },
                firstDay: parseInt(cal.settings.firstDay),
                nowIndicator: true,
                weekNumbers: true,
                eventLimit: false, // allow "more" link when too many events
                eventOverlap: true,
                navLinks: false, // can click day/week names to navigate views
                filterResourcesWithEvents: true,
                eventStartEditable: true,
                eventDurationEditable: true,
                selectMirror: true,
                displayEventTime: cal.settings.displayEventTime,
                selectable: cal.settings.edit,
                editable: cal.settings.edit,
                eventOrder: cal.settings.order,
                height: cal.settings.height,
                minTime: cal.settings.minTime,
                maxTime: cal.settings.maxTime,
                scrollTime: cal.settings.scrollTime,

                events: {
                    url: '/ajax/' + cal.settings.id + '/get/' + cal.settings.mode + '/',
                    method: 'GET',
                    extraParams: function () {
                        var filters = false;

                        if(jQuery().serializeJSON) {
                            filters = $('#frmFilters').serializeJSON();
                        }

                        return filters;
                    }
                },

                eventRender: function (info) {
                    var tooltip = info.event.extendedProps.tooltip;
                    var html = '';

                    if(info.event.extendedProps.showStatus) {
                        if (info.event.extendedProps.isCancelled) {
                            html += '<b><span class="badge bg-danger border border-white"><i class="fa-solid fa-ban"></i></span> CANCELLED</b><br>';
                        }else if (info.event.extendedProps.isClosed) {
                            html += '<b><span class="badge bg-success border border-white"><i class="fa-solid fa-lock"></i></span> CLOSED</b><br>';
                        } else {
                            html += '<b><span class="badge bg-info border border-white"><i class="fa-solid fa-lock-open"></i></span> OPEN</b><br>';
                        }
                    }

                    if (info.event.extendedProps.showTime && !info.event.extendedProps.isAllDay) {
                        html += '<span class="fc-time">' + moment(info.event.start).format('HH:mm') + ' - ' + moment(info.event.end).format('HH:mm') + '</span>';
                    }
                    //html += '<span class="fc-title d-none">' + htmlEscape(tooltip) + '</span>';
                    html += '<div class="fc-title">' + htmlEscape(info.event.title) + '</div>';

                    $(info.el).find('.fc-content').html(html);

                    if (!info.isMirror) {
                        $(info.el).tooltip({
                            title: tooltip,
                            placement: 'top',
                            trigger: 'hover',
                            container: 'body',
                            html: true
                        });
                    }
                },

                select: function (info) {
                    if(cal.settings.formName) {
                        var startDate = moment(info.startStr);
                        var endDate = moment(info.endStr);

                        /*
                        if(cal.settings.dayExclusive && startDate.format('DD') != endDate.format('DD')){
                            // Do not count the next day of the selection
                            endDate = moment(endDate).subtract(1, 'days');
                        }
                        */

                        $.ajax({
                            url: '/ajax/forms/' + cal.settings.formName + '/?id=0&start=' + startDate.format('YYYY-MM-DDTHH:mm:ss') + '&end=' + endDate.format('YYYY-MM-DDTHH:mm:ss'),
                            processData: false,
                            contentType: false,
                            success: function (data) {
                                $('#ajax-modal .modal-dialog').addClass('modal-lg');
                                $('#ajax-modal .modal-content').html(data);
                                $('#ajax-modal').addClass('form-' + cal.settings.formName).modal('show');
                                app.reInit();
                            }
                        });
                    }
                },

                eventClick: function (info) {
                    var eventId = parseInt(info.event.id);

                    if ((cal.settings.edit || info.event.extendedProps.view) && (info.event.extendedProps.form || cal.settings.formName) && eventId) {
                        var form = (info.event.extendedProps.form ? info.event.extendedProps.form : cal.settings.formName);

                        $.ajax({
                            url: '/ajax/forms/' + form + '/?id=' + eventId,
                            processData: false,
                            contentType: false,
                            success: function (data) {
                                $('#ajax-modal .modal-dialog').addClass('modal-lg');
                                $('#ajax-modal .modal-content').html(data);
                                $('#ajax-modal').addClass('form-' + form).modal('show');
                                app.reInit();
                            }
                        });
                    }
                },

                eventDrop: function (info) {
                    if(cal.settings.checkChange) {
                        cal.checkChange(info);
                    }else {
                        cal.applyChange(info);
                    }
                },

                eventResize: function (info) {
                    $.ajax({
                        url: '/ajax/' + cal.settings.id + '/do/change/?id=' + info.event.id + '&start=' + moment(info.event.start).format('YYYY-MM-DDTHH:mm:ss') + '&end=' + moment(info.event.end).format('YYYY-MM-DDTHH:mm:ss'),
                        processData: false,
                        contentType: false,
                        success: function (data) {
                            processJSONResponse(data);
                            if(cal.settings.refetchEvents) {
                                calendar.refetchEvents();
                            }
                        }
                    });
                },

                /*
                viewSkeletonRender: function (info) {
                    $.ajax({
                        url: '/ajax/' + cal.settings.id + '/view/' + cal.settings.mode + '/?view=' + info.view.type,
                        processData: false,
                        contentType: false,
                        success: function(data) {
                        }
                    });
                },
                */

                datesRender: function (info) {
                    if(moment(info.view.currentStart).format('YYYY-MM-DD') >= moment().format('YYYY-MM-DD') ) {
                        $('#dateFrom').val(moment(info.view.currentStart).format('YYYY-MM-DD'));
                    }else{
                        $('#dateFrom').val(moment().format('YYYY-MM-DD'));
                    }
                },
            });

            calendar.render();
        }
    },

    init:function(options){
        cal.settings.id = options.id || false;
        cal.settings.mode = options.mode || 'events';
        cal.settings.formName = options.formName || 'EventForm';
        cal.settings.edit = options.edit || false;
        cal.settings.prefetchEvents = options.prefetchEvents || false;
        cal.settings.checkChange = options.checkChange || false;
        cal.settings.order = options.order || 'title';
        cal.settings.resources = options.resources || false;
        cal.settings.timezone = options.timezone || 'UTC';
        cal.settings.dayExclusive = options.dayExclusive || true; // Do not count the next day of the selection
        cal.settings.height = options.height || 'auto';
        cal.settings.minDate = options.minDate || false;
        cal.settings.minTime = options.minTime || '00:00:00';
        cal.settings.maxTime = options.maxTime || '24:00:00';
        cal.settings.slotDuration = options.slotDuration || '00:30:00';
        cal.settings.displayEventTime = options.displayEventTime || false;
        cal.settings.scrollTime = options.scrollTime || '08:00:00';

        cal.settings.views = options.views || '';
        cal.settings.defaultView = options.defaultView || 'timeGridWeek';
        cal.settings.firstDay = parseInt(options.firstDay) || 1;
        cal.settings.defaultDate = options.defaultDate || false;

        this.initControls();
        this.initCalendar();
    }
};

function setupEventForm() {
    var type = parseInt( $('input[name="EventForm[ev_type]"], select[name="EventForm[ev_type]"]').val() );

    /*
    $('#ev_instructor').parents('.form-group').find('label.form-label').html($('#ev_instructor').data('label-other'));
    $('#participants').parents('.form-group').find('label.form-label').html($('#participants').data('label-other'));

    $('#ev-training').hide();
    $('#tasks-data').hide();
    $('#tasks-table').hide();

    switch(type) {
        case 1: // Practice
            $('#ev-training').show();
            $('#tasks-data').show();
            $('#tasks-table').show();

            $('#ev_instructor').parents('.form-group').find('label.form-label').html($('#ev_instructor').data('label-1'));
            $('#participants').parents('.form-group').find('label.form-label').html($('#participants').data('label-1'));
            break;
        case 2: // Theory
            $('#ev-training').show();
            $('#tasks-data').show();
            $('#tasks-table').show();

            //$('#ev_students').attr('data-list-url', $('#ev_students').data('list-url1'));

            $('.ev-classroom').show();
            $('.ev-student').hide();

            $('#ev_instructor').parents('.form-group').find('label.form-label').html($('#ev_instructor').data('label-2'));
            $('#participants').parents('.form-group').find('label.form-label').html($('#participants').data('label-2'));
            break;
        default:
            break;
    }
    */
}

function refreshCalendar(){
    calendar.refetchEvents();
}

function htmlEscape(s) {
    return (s + '').replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/'/g, '&#039;')
        .replace(/"/g, '&quot;')
        .replace(/\n/g, '<br />');
}

$(function() {

});
