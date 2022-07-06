var firstRender = false;
$(document).ready(function () {
    if($('#search-home').length > 0){
        initInstantSearch();
        redispatchSearch();
    }
});

function initInstantSearch() {

    var disjunctive_facets = [];
    var disjunctive_facets_refinements = [];

    var search_parameters = "";
    search_parameters = {
        filters: algolia_prefiltered
    };
    0 == disjunctive_facets.length && 0 == disjunctive_facets_refinements.length || (search_parameters = {
        filters: algolia_prefiltered,
        disjunctiveFacets: disjunctive_facets,
        disjunctiveFacetsRefinements: disjunctive_facets_refinements
    });

    instantsearchInstance = instantsearch({
        appId: algoliaSettings.app_id,
        apiKey: algoliaSettings.search_key,
        indexName: algoliaSettings.indices[0].index_name,
        urlSync: {
            trackedParameters: ["attribute:*", "query", "index"]
        },
        searchParameters: search_parameters
    });

    instantsearchInstance.on("render", function () {
        autoCollapsed();
        removeCollapsed();
    });

    instantsearchInstance.addWidget(
        instantsearch.widgets.currentRefinedValues({
            container: '#current-refined-values',
            // This widget can also contain a clear all link to remove all filters,
            // we disable it in this example since we use `clearAll` widget on its own.
            clearAll: false,
            templates: {
                item: '<div class="chip">{{ name }} <i class="close fa fa-close"></i></div>'
            },
            attributes: [{
                name: "price_tax_incl",
                transformData: function (data) {
                    return data.superior = ">=" == data.operator, void 0 != data.numericValue && (data.name = data.numericValue.toLocaleString()), data
                },
                template: '<div class="chip">{{#superior}}Plus de{{/superior}}{{^superior}}Moins de{{/superior}}&nbsp;{{name}}&nbsp;€ <i class="close fa fa-close"></i></div>'
            },
                {
                    name: "monthly_payment.amount",
                    transformData: function (data) {
                        return data.superior = ">=" == data.operator, void 0 != data.numericValue && (data.name = data.numericValue.toLocaleString()), data
                    },
                    template: '<div class="chip">{{#superior}}Plus de{{/superior}}{{^superior}}Moins de{{/superior}}&nbsp;{{name}}&nbsp;€/mois <i class="close fa fa-close"></i></div>'
                }]
        })
    );

    instantsearchInstance.addWidget(
        instantsearch.widgets.clearAll({
            container: '#clear-all',
            templates: {
                link: '<i class="icon-trash3"></i>'
            },
            autoHideContainer: true,
            clearsQuery: true,
        })
    );

    instantsearchInstance.addWidget(
        instantsearch.widgets.refinementList({
            container: '#vehicle-brand',
            attributeName: 'marque.name',
            limit: 12,
            collapsible: true,
            sortBy: ["count:desc"],
            autoHideContainer: false,
            showMore: {
                templates: {
                    active: getShowLess("moins de marques"),
                    inactive: getShowMore("plus de marques")
                }
            },
            operator: 'or',
            transformData: {
                item: function (data) {
                    return data.nameLowerCase = data.value.toLowerCase(), void 0 !== data.nameLowerCase.normalize && (data.nameLowerCase = data.nameLowerCase.normalize("NFD")), data.nameLowerCase = data.nameLowerCase.replace(/[\u0300-\u036f]/g, "").replace(/\s/g, "-"), data
                }
            },
            templates: {
                header: "<i class='icon-car2 d-inline-block'></i><strong class='d-inline-block text-center w-100 noselect'>Marque</strong>",
                item: '<div class="btn motor-btn-filter motor-btn-filter--brand brand-{{ nameLowerCase }}">{{ label }} <span>{{ count }}</span></div>'
            }
        })
    );

    instantsearchInstance.addWidget(
        instantsearch.widgets.refinementList({
            container: '#vehicle-model',
            attributeName: 'modele.name',
            limit: 12,
            collapsible: true,
            sortBy: ["count:desc"],
            autoHideContainer: false,
            showMore: {
                templates: {
                    active: getShowLess("moins de modèles"),
                    inactive: getShowMore("plus de modèles")
                }
            },
            operator: 'or',
            templates: {
                header: "<i class='icon-tag d-inline-block'></i><strong class='d-inline-block text-center w-100 noselect'>Modèle</strong>",
                item: '<div class="btn motor-btn-filter">{{ label }} <span>{{ count }}</span></div>'
            }
        })
    );

    instantsearchInstance.addWidget(
        instantsearch.widgets.refinementList({
            container: '#feature-energy',
            attributeName: 'energie',
            limit: 12,
            collapsible: true,
            sortBy: ["count:desc"],
            autoHideContainer: false,
            showMore: {
                templates: {
                    active: getShowLess("moins d'énergies"),
                    inactive: getShowMore("plus d'énergies")
                }
            },
            templates: {
                header: "<i class='icon-drop2 d-inline-block'></i><strong class='d-inline-block text-center w-100 noselect'>Energie</strong>",
                item: '<div class="btn motor-btn-filter">{{ label }} <span>{{ count }}</span></div>'
            }
        })
    );

    instantsearchInstance.addWidget(
        instantsearch.widgets.refinementList({
            container: '#feature-type_de_vehicule',
            attributeName: 'type_de_vehicule.name',
            limit: 12,
            collapsible: true,
            sortBy: ["count:desc"],
            autoHideContainer: false,
            showMore: {
                templates: {
                    active: getShowLess("moins de types"),
                    inactive: getShowMore("plus de types")
                }
            },
            transformData: {
                item: function (data) {
                    return data.nameLowerCase = data.value.toLowerCase(), void 0 !== data.nameLowerCase.normalize && (data.nameLowerCase = data.nameLowerCase.normalize("NFD")), data.nameLowerCase = data.nameLowerCase.replace(/[\u0300-\u036f]/g, "").replace(/\s/g, "-"), data
                }
            },
            templates: {
                header: "<i class='icon-car d-inline-block'></i><strong class='d-inline-block text-center w-100 noselect'>Type</strong>",
                item: '<div class="btn motor-btn-filter motor-btn-filter--category cat-{{ nameLowerCase }}">{{ label }}  <span>{{ count }}</span></div>'
            }
        })
    );

    instantsearchInstance.addWidget(
        instantsearch.widgets.stats({
            container: '.stats-container',
            autoHideContainer: false,
            templates: {
                body: document.querySelector("#stats-container-template").innerHTML
            }
        })
    );

    instantsearchInstance.addWidget(
        instantsearch.widgets.ionRangeSlider({
            container: '#price',
            attributeName: 'price_tax_incl',
            ionRangeSliderOptions: {
                type: "double",
                grid: !1,
                postfix: "&nbsp;€",
                step: 1e3
            }
        })
    );

    if (void 0 != document.getElementById("monthly")) {
        instantsearchInstance.addWidget(
            instantsearch.widgets.ionRangeSlider({
                container: '#monthly',
                attributeName: 'monthly_payment.amount',
                ionRangeSliderOptions: {
                    type: "double",
                    grid: !1,
                    postfix: "&nbsp;€/mois",
                    step: 50
                }
            })
        );
    }

    instantsearchInstance.start();
}

function getShowMore(label) {
    return '<a class="ais-show-more"><i class="fa fa-chevron-circle-down mr-2"></i>'+ "voir " + label + "</a>"
}

function getShowLess(label) {
    return '<a class="ais-show-more"><i class="fa fa-chevron-circle-up mr-2"></i>'+ "voir " + label + "</a>"
}

function autoCollapsed() {
    if (!firstRender) {
        $("#search-home").find(".auto-collapsed").each(function () {
            $(this).find('.ais-refinement-list.ais-root__collapsible .ais-header').click();
        });
    }
    firstRender = true;
}

function removeCollapsed() {
    $(".ais-refinement-list.ais-root__collapsible").on("click", function (event) {
        var elm = $(this);
        $(".ais-refinement-list").each(function () {
            $(this).addClass("ais-root__collapsed");
        }), $(this).toggleClass("ais-root__collapsed");
    }), $("body").on("click", function (event) {
        var elm = $(event.target);
        if (elm.hasClass('ais-show-more'))
            return false;
        $(event.target).parents(".ais-refinement-list").length || $(".ais-refinement-list").addClass("ais-root__collapsed");
    });
}

function redispatchSearch() {
    $("#search-home-section").submit(function (e) {
        var isEmpty = true;
        $(this).find('input').each(function () {
            if ($(this).val() != '')
                isEmpty = false;
        });
        if (isEmpty) {
            e.preventDefault();
            window.location.replace(path_to_list + document.location.search);
        }
    });
}