$(document).ready(function () {
    //refresh Fancybox
    parent.$.fancybox.update();

    //Form submit
    $(document).on('submit', '#alert_vehicle_form', function (e) {
        e.preventDefault();
        e.stopPropagation();
        $(this).adsaf_search_alert({
            task: "search_alert_add"
        });
    });
});

$.fn.adsaf_search_alert = function (params) {
    var object = $(this).serializeObject();
    var dataObject = _.toPlainObject(object.alert_vehicle);
    $.ajaxq('search_alert', {
        type: 'post',
        url: path_search_alert,
        data: {ajax: 1, task: params.task, token: object._token, parameters: JSON.stringify(dataObject)},
        mode: 'abort',
        cache: true,
        dataType: 'json',
        beforeSend: function () {
        },
        success: function (data) {
            if (data.error) {
                $('#search-alert-block .alert-danger').html(data.message).fadeIn();
                parent.$.fancybox.update();
            }
            else {
                $('#search-alert-block .modal__body').remove();
                $('#search-alert-block .success').fadeIn();
                var refreshIntervalId = setInterval(function () {
                        parent.$.fancybox.close();
                        clearInterval(refreshIntervalId);
                    }
                    , 5000);
            }
        },
        error: function () {
            $('#search-alert-block .alert-danger').html("Une erreur s'est produite<br/>Veuillez renouveler votre essai plus tard.").fadeIn();
            parent.$.fancybox.update();
        }
    });
};

$.fn.serializeObject = function () {
    var arrayData, objectData;
    arrayData = this.serializeArray();
    objectData = {};

    $.each(arrayData, function () {
        this.value = !this.value ? '' : this.value;
        processObject(objectData, this.name, this.value);
    });

    return objectData;
};

function processObject(obj, key, value) {
    if (key.indexOf('.') != -1) {
        var attrs = key.split('.');
        var tx = obj;
        for (var i = 0; i < attrs.length - 1; i++) {
            var isArray = attrs[i].indexOf('[') != -1;
            var isNestedArray = isArray && (i != attrs.length - 1);
            var nestedArrayIndex = null;
            if (isArray) {
                nestedArrayIndex = attrs[i].substring(attrs[i].indexOf('[') + 1, attrs[i].indexOf(']'));
                attrs[i] = attrs[i].substring(0, attrs[i].indexOf('['));
                if (tx[attrs[i]] == undefined) {
                    tx[attrs[i]] = [];
                }
                tx = tx[attrs[i]];
                if (isNestedArray) {
                    if (tx[nestedArrayIndex] == undefined) {
                        tx[nestedArrayIndex] = {};
                    }
                    tx = tx[nestedArrayIndex];
                }

            } else {
                if (tx[attrs[i]] == undefined) {
                    tx[attrs[i]] = {};
                }
                tx = tx[attrs[i]];
            }
        }
        processObject(tx, attrs[attrs.length - 1], value);
    } else {
        var finalArrayIndex = null;
        if (key.indexOf('[') != -1) {
            finalArrayIndex = key.substring(key.indexOf('[') + 1, key.indexOf(']'));
            key = key.substring(0, key.indexOf('['));
        }
        if (finalArrayIndex == null) {
            obj[key] = value;
        } else {
            if (obj[key] == undefined) {
                obj[key] = [];
            }
            obj[key][finalArrayIndex] = value;
        }
    }
}