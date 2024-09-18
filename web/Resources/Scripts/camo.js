var camo = {
    inProgress: false,

    setProgress: function(on){
        camo.inProgress = on;
    },

    sendRequest: function(action, params){
        camo.setProgress(true);

        return $.ajax({
            method: 'POST',
            url: '/ajax/tasks/' + action + '/',
            data: jsonToUrl(params)
        }).done(function (data) {
            if (data) {
                processJSONResponse(data);
            }

            camo.setProgress(false);
        });
    },

    initControls: function(){

    },

    init: function(){
        this.initControls();
    }
}

$(function() {
    camo.init();
});

function createWorkOrder(aircraftId, componentId, maintenanceId){
    var params = {};

    params.aircraftId = parseInt(aircraftId);
    params.componentId = parseInt(componentId);
    params.maintenanceId = parseInt(maintenanceId);

    $('#confirm-delete').modal('hide');

    camo.sendRequest('create', params);
}