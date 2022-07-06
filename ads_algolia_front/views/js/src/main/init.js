var algoliaClient = algoliasearch(algoliaSettings.app_id, algoliaSettings.search_key),
        algoliaVehicleIndex = algoliaClient.initIndex(algoliaSettings.index_name + 'all_' + algoliaSettings.language);
function stopCloseDropdownHomeSearch() {
    var extraHeight = 0;
    $(".homepage-motor__dropdown .dropdown-btn").on("click", function (event) {
        if ($(".navbar.fixed-top").length > 0)
            extraHeight = $(".navbar.fixed-top").height();
        $("html, body").animate({
            scrollTop: ($(this).offset().top - extraHeight)
        }, 'slow');
        $(".homepage-motor__dropdown").each(function () {
            $(this).removeClass("open")
        }), $(this).parent().toggleClass("open");
    }), $("body").on("click", function (e) {
        $(e.target).parents(".homepage-motor__dropdown").length || $(".homepage-motor__dropdown").removeClass("open");
    })
}

$(document).ready(function () {
    initHomeACInputTextSearch();
    stopCloseDropdownHomeSearch();
});

function initHomeACInputTextSearch() {
    0 != $(".autocomplete-search").length && $(".autocomplete-search").autocomplete(
            {
                hint: false,
                templates: {
                    dropdownMenu: '#aa-custom-menu-template'
                }
            }, [{
            name: "manufacturer",
            displayKey: 'my_attribute_manufacturer',
            source: function (queries, callback) {
                algoliaVehicleIndex.search({
                    query: queries,
                    facets: ["marque.name"],
                    filters: algolia_prefiltered,
                    maxValuesPerFacet: 5
                }).then(function (answer) {
                    var res = answer.facets["marque.name"],
                            array_res = [];
                    for (key in res)
                        array_res.push({
                            name: key,
                            count: res[key]
                        });
                    callback(array_res)
                }, function () {
                    callback([]);
                })
            },
            templates: {
                header: '<div class="label-title">Marque</div><ul class="list-group">',
                footer: '</ul>',
                suggestion: function (suggestion) {
                    return '<li class="list-group-item">' + suggestion.name + ' <span class="motor-count">' + suggestion.count + "</span></li>"
                }
            }
        }, {
            name: "model",
            source: function (queries, callback) {
                algoliaVehicleIndex.search({
                    query: queries,
                    facets: ["modele.name"],
                    filters: algolia_prefiltered,
                    maxValuesPerFacet: 5
                }).then(function (answer) {
                    var res = answer.facets["modele.name"],
                            array_res = [];
                    for (key in res)
                        array_res.push({
                            name: key,
                            count: res[key]
                        });
                    callback(array_res)
                }, function () {
                    callback([]);
                })
            },
            templates: {
                header: '<div class="label-title">Modèle</div><ul class="list-group">',
                footer: '</ul>',
                suggestion: function (suggestion) {
                    return '<li class="list-group-item">' + suggestion.name + ' <span class="motor-count">' + suggestion.count + "</span></li>"
                }
            }
        }, {
            name: "bodywork",
            source: function (queries, callback) {
                algoliaVehicleIndex.search({
                    query: queries,
                    facets: ["type_de_vehicule.name"],
                    filters: algolia_prefiltered,
                    maxValuesPerFacet: 10
                }).then(function (answer) {
                    var res = answer.facets["type_de_vehicule.name"],
                            array_res = [], bodywork_filter = [];
                    for (key in res) {
                        array_res.push({
                            name: key,
                            count: res[key]
                        });
                        bodywork_filter.push(key);
                        if (bodywork_filter.length == 1)
                            $("#search-home-input").attr('data-bodywork-filter', JSON.stringify(bodywork_filter));
                        else
                            $("#search-home-input").attr('data-bodywork-filter', JSON.stringify([]));
                    }
                    callback(array_res)
                }, function () {
                    callback([]);
                })
            },
            templates: {
                header: '<div class="label-title col-12">Type de véhicule</div>',
                suggestion: function (suggestion) {
                    return "<span class='chip float-left mr-2'>" + suggestion.name + ' (' + suggestion.count + ")</span>"
                }
            }
        }]).on("autocomplete:selected", function (event, suggestion, dataset) {
        var route = "";
        if ("manufacturer" == dataset) {
            route = path_to_list + "?" + $.param({
                dFR: {
                    "marque.name": [suggestion.name],
                    "type_de_vehicule.name": JSON.parse($("#search-home-input").attr('data-bodywork-filter'))
                }
            });
        } else if ("model" == dataset) {
            route = path_to_list + "?" + $.param({
                dFR: {
                    "modele.name": [suggestion.name]
                }
            });
        } else if ("bodywork" == dataset) {
            route = path_to_list + "?" + $.param({
                dFR: {
                    "type_de_vehicule.name": [suggestion.name]
                }
            });
        }

        "" != route && window.location.replace(route);
    })
}