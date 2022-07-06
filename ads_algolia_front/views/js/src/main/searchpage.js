var paddingScreen = 200,
    minSecondLaunchIS = .2,
    infiniteScrollingEnabled = !0;
firstRender = false;
$(document).ready(function () {
    initInstantSearch();
    initInfiniteScrolling();
    initDisplayFullStock();
});

$(window).load(function () {
    initCheckFullStock();
});

let firstSearchCall = true;
let firstSearchParamsString = "";
let nextSearchParamsString = "";

function searchFunction(helper) {
    let tmpobj = {
        'aglDFR': helper.state.disjunctiveFacetsRefinements,
        'aglNR': helper.state.numericRefinements,
    };
    if (firstSearchCall)
        firstSearchParamsString = JSON.stringify(tmpobj);
    else
        nextSearchParamsString = JSON.stringify(tmpobj);

    if (!firstSearchCall && firstSearchParamsString != nextSearchParamsString) {
        //Hide elements from SEO if search changed
        $("#center_column h1").remove();
        $("#center_column .desc-seo").hide();
    }

    helper.search();
    firstSearchCall = false;
}

function getHeader(headername) {
    return '<header class="collapsible-header">' + headername + '<span class="right blue-text truncate"></span><i class="material-icons close-header-icon">&#xE15B;</i><i class="material-icons open-header-icon">&#xE145;</i></header>'
}

function addAlgoliaWidget(instanceIS, container, attributeName, limit, operator, headername, n, s, o, c, d) {
    var widget = {
        container: container,
        attributeName: attributeName,
        limit: limit,
        operator: operator,
        templates: {
            header: getHeader(headername)
        }
    };
    void 0 !== n && !1 !== n && (widget.showMore = n), void 0 !== s && !1 !== s && (widget.template = s), void 0 !== o && !1 !== o && (widget.sortBy = o), void 0 !== c && !1 !== c && (widget.transformData = c), void 0 !== d && !1 !== d && (widget.collapsible = d), void 0 !== parametersSearch.disjunctiveFacetsRefinements && parametersSearch.disjunctiveFacetsRefinements[a] && (u.operator = "or", u.sortBy = ["isRefined"]), e.addWidget(instantsearch.widgets.refinementList(u))
}

function initInfiniteScrolling() {
    var event = null;
    $(window).on("load resize scroll", function (e) {
        if ($(".ais-infinite-hits--showmore").length > 0) {
            $(".ais-infinite-hits--showmore button").addClass("btn btn-lg mx-auto");
            // let instantpage = instantsearchInstance.helper.state.page + 1;
            // let mod = 3;
            // if (instantpage >= mod && (instantpage % mod == 0)) {
                //Manual
            //     $(".ais-infinite-hits--showmore").removeClass("d-none");
            // }
            // else {
                //Auto
                if (!$(".ais-infinite-hits--showmore").hasClass("d-none"))
                    $(".ais-infinite-hits--showmore").addClass("d-none")
                $("#infinite-hits-container").parent().offset().top + $("#infinite-hits-container").parent().height() - ($(window).scrollTop() + $(window).innerHeight()) < paddingScreen && (void 0 == event || (new Date - event) / 1e3 > minSecondLaunchIS) && 1 == infiniteScrollingEnabled && (event = new Date, $(".ais-infinite-hits--showmore button").click())
            // }
        }
    });
}

function initMonthlyList(id_product) {
    if (id_product == undefined)
        return;
    var current_id_product = id_product;
    var myObject = {'task': 'getCurrentMonthly', 'id_product': id_product};
    $.ajax({
        url: ads_gs_path,
        method: "POST",
        data: {
            ajax: 1,
            parameters: JSON.stringify(myObject)
        },
        success: function (result) {
            $(".item-vehicle_monthly.empty[data-product='" + current_id_product + "']").html(result);
            $(".item-vehicle_monthly.empty[data-product='" + current_id_product + "']").removeClass('empty');
        }
    });
}

function initInstantSearch() {

    var disjunctive_facets = [];
    var disjunctive_facets_refinements = {};
    var numeric_refinements = {};
    if (window.hasOwnProperty("algoliaVehicleParameters")) {
        //disjunctive_facets_refinements
        $.each(algoliaVehicleParameters.aglDFR, function (item, value) {
            disjunctive_facets.push(item);
            if (_.isArray(value)) {
                disjunctive_facets_refinements[item] = value;
            }
            else {
                disjunctive_facets_refinements[item] = [value];
            }
        });
        //numeric_refinements
        $.each(algoliaVehicleParameters.aglNR, function (item, value) {
            if (_.isArray(value)) {
                numeric_refinements[item] = value;
            }
            else {
                numeric_refinements[item] = value;
            }
        });
    }

//window.vehicle_list_parameters.category && "" != window.vehicle_list_parameters.category && (e.push("categoryName"), t.categoryName = [window.vehicle_list_parameters.category]);

    var search_parameters = "";
    search_parameters = {
        filters: algolia_prefiltered,
        hitsPerPage: algoliaSettings.number_by_page
    };
    0 == disjunctive_facets.length && 0 == disjunctive_facets_refinements.length || (search_parameters = {
        filters: algolia_prefiltered,
        disjunctiveFacets: disjunctive_facets,
        disjunctiveFacetsRefinements: disjunctive_facets_refinements,
        numericRefinements: numeric_refinements
    });

    instantsearchInstance = instantsearch({
        appId: algoliaSettings.app_id,
        apiKey: algoliaSettings.search_key,
        indexName: algoliaSettings.indices[0].index_name,
        urlSync: {
            trackedParameters: ["attribute:*", "query", "index"]
        },
        searchParameters: search_parameters,
        searchFunction: function (helper) {
            searchFunction(helper);
        }
    });

    instantsearchInstance.on("render", function () {
        sortBySelector();
        initMonthlyList();
        quick_view();
        autoCollapsed();
        onRenderIS();
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
                },
                {
                    name: "annee",
                    transformData: function (data) {
                        return data.superior = ">=" == data.operator, void 0 != data.numericValue, data
                    },
                    template: '<div class="chip">{{#superior}}Après{{/superior}}{{^superior}}Avant{{/superior}}&nbsp;{{name}} <i class="close fa fa-close"></i></div>'
                },
                {
                    name: "kilometrage",
                    transformData: function (data) {
                        return data.superior = ">=" == data.operator, void 0 != data.numericValue && (data.name = data.numericValue.toLocaleString()), data
                    },
                    template: '<div class="chip">{{#superior}}Plus de{{/superior}}{{^superior}}Moins de{{/superior}}&nbsp;{{name}}&nbsp;km <i class="close fa fa-close"></i></div>'
                },
                {
                    name: "longueur",
                    transformData: function (data) {
                        return data.superior = ">=" == data.operator, void 0 != data.numericValue && (data.name = data.numericValue.toLocaleString()), data
                    },
                    template: '<div class="chip">L = {{#superior}}Plus de{{/superior}}{{^superior}}Moins de{{/superior}}&nbsp;{{name}}&nbsp;m <i class="close fa fa-close"></i></div>'
                },
                {
                    name: "hauteur",
                    transformData: function (data) {
                        return data.superior = ">=" == data.operator, void 0 != data.numericValue && (data.name = data.numericValue.toLocaleString()), data
                    },
                    template: '<div class="chip">H = {{#superior}}Plus de{{/superior}}{{^superior}}Moins de{{/superior}}&nbsp;{{name}}&nbsp;m <i class="close fa fa-close"></i></div>'
                },
                {
                    name: "is_vo",
                    transformData: function (data) {
                        return data.vehicle_is_vo = "false" == data.name, data.vehicle_is_vo = "true" == data.name, data;
                    },
                    template: '<div class="chip">{{#vehicle_is_vo}}' + agl_definitions.label_vo + '{{/vehicle_is_vo}}{{^vehicle_is_vo}}' + agl_definitions.label_vn + '{{/vehicle_is_vo}} <i class="close fa fa-close"></i></div>'
                },
                {
                    name: "nombre_de_places",
                    template: '<div class="chip">Nombre de places : {{name}} <i class="close fa fa-close"></i></div>'
                },
                {
                    name: "nombre_de_couchages",
                    template: '<div class="chip">Nombre de couchages : {{name}} <i class="close fa fa-close"></i></div>'
                },
                {
                    name: "eco_prime",
                    transformData: function (data) {
                        if (data.name == 1)
                            data.label = "Oui";
                        return data;
                    },
                    template: '<div class="chip">Prime à la conversion : {{label}} <i class="close fa fa-close"></i></div>'
                },
                {
                    name: "vd",
                    transformData: function (data) {
                        if (data.name == 1)
                            data.label = "Oui";
                        return data;
                    },
                    template: '<div class="chip">Véhicule de démonstration : {{label}} <i class="close fa fa-close"></i></div>'
                },
                {
                    name: "supplier.code",
                    transformData: function (data) {
                        return {};
                    },
                    template: ''
                },
                {
                    name: "energy_class",
                    template: '<div class="chip">Classe énergétique : {{name}} <i class="close fa fa-close"></i></div>'
                }]
        })
    );

    instantsearchInstance.addWidget(
        instantsearch.widgets.clearAll({
            container: '#clear-all',
            templates: {
                link: '<i class="fa fa-trash"></i>'
            },
            autoHideContainer: true,
            clearsQuery: true,
        })
    );

//Loop through agl_facets
    $.each(JSON.parse(agl_facets), function (idx, obj) {
        if (obj.id == "isvo") {
            instantsearchInstance.addWidget(
                instantsearch.widgets.refinementList({
                    container: '#' + obj.id,
                    attributeName: obj.tax,
                    limit: 5,
                    sortBy: ["name:asc", "isRefined", "count:desc"],
                    collapsible: true,
                    transformData: {
                        item: function (data) {
                            if (data.label == "false")
                                data.label = agl_definitions.label_vn;
                            else
                                data.label = agl_definitions.label_vo;
                            return data;
                        }
                    },
                    templates: {
                        header: "",
                        item: '<div class="btn motor-btn-filter {{#isRefined}}active{{/isRefined}}">{{ label }}</div>'
                    }
                })
            );
            return;
        }
        else if (obj.id == "category") {
            instantsearchInstance.addWidget(
                instantsearch.widgets.refinementList({
                    container: '#' + obj.id,
                    attributeName: obj.tax,
                    limit: 5,
                    sortBy: ["name:asc", "isRefined", "count:desc"],
                    collapsible: true,
                    templates: {
                        header: "",
                        item: '<div class="btn motor-btn-filter {{#isRefined}}active{{/isRefined}}">{{ label }}</div>'
                    }
                })
            );
            return;
        }
        else if (obj.id == "tags") {
            instantsearchInstance.addWidget(
                instantsearch.widgets.refinementList({
                    container: '#' + obj.id,
                    attributeName: obj.tax,
                    limit: 100,
                    sortBy: ["isRefined", "count:desc"],
                    collapsible: true,
                    templates: {
                        header: "",
                        item: '<div class="btn motor-btn-filter {{#isRefined}}active{{/isRefined}}">{{ label }}</div>'
                    }
                })
            );
            return;
        }
        else if (obj.id == "energyclass") {
            instantsearchInstance.addWidget(
                instantsearch.widgets.refinementList({
                    container: '#' + obj.id,
                    sortBy: ["name:asc"],
                    attributeName: obj.tax,
                    limit: 10,
                    collapsible: true,
                    templates: {
                        header: getHeader(obj.name),
                        item: '<div class="form-check p-0">\n' +
                            '        <input type="checkbox" class="form-check-input filled-in ais-refinement-list--checkbox"\n' +
                            '               value="{{ label }}" {{#isRefined}} checked {{/isRefined}}>\n' +
                            '        <label class="form-check-label ais-refinement-list--label d-block">\n' +
                            '            &nbsp;<div class="critair c-{{ label }}"></div>\n' +
                            '            <span class="ais-refinement-list--count">{{ count }}</span>\n' +
                            '        </label>\n' +
                            '    </div>'
                    }
                })
            );
            return;
        }
        else if (obj.id == "ecoprime" || obj.id == "vd") {
            instantsearchInstance.addWidget(
                instantsearch.widgets.refinementList({
                    container: '#' + obj.id,
                    attributeName: obj.tax,
                    sortBy: ["name:desc", "isRefined", "count:desc"],
                    limit: 1,
                    collapsible: true,
                    transformData: {
                        item: function (data) {
                            if (data.label == 1)
                                data.label = "Oui";
                            return data;
                        }
                    },
                    templates: {
                        header: getHeader(obj.name),
                        item: '<div class="btn motor-btn-filter">{{ label }}</div>'
                    }
                })
            );
            return;
        }
        if (obj.type == "conjunctive") {
            var conjunctive_sort = ["isRefined", "count:desc", "name:desc"];
            if (obj.id == "nombredeplaces" || obj.id == "nombredecouchages")
                conjunctive_sort = ["name:asc", "isRefined", "count:desc"];
            instantsearchInstance.addWidget(
                instantsearch.widgets.refinementList({
                    container: '#' + obj.id,
                    attributeName: obj.tax,
                    sortBy: conjunctive_sort,
                    limit: 5,
                    /*searchForFacetValues: {'isAlwaysActive': true, 'placeholder': 'Type to filter...'},*/
                    showMore: {
                        templates: {
                            active: getShowLess("moins"),
                            inactive: getShowMore("plus")
                        }
                    },
                    collapsible: true,
                    templates: {
                        header: getHeader(obj.name),
                        item: getItem('item-filter')
                    }
                })
            );
        }
        else if (obj.type == "slider") {
            let slider_postfix = "&nbsp;€";
            let slider_step = "1e3";
            let slider_grid = false;
            let slider_round_default = false;
            let slider_prettify = true;
            if (obj.id == "longueur" || obj.id == "largeur" || obj.id == "hauteur") {
                slider_postfix = "&nbsp;m";
                slider_step = ".01";
                slider_grid = true;
                slider_round_default = true;
            }
            if (obj.id == "kilometrage") {
                slider_postfix = "&nbsp;Km";
            }
            if (obj.id == "annee") {
                slider_postfix = "";
                slider_step = "1";
                slider_round_default = true;
                slider_prettify = false;
            }
            if (obj.id == "monthlypaymentamount") {
                slider_postfix = "&nbsp;€/mois";
                slider_step = "50";
                slider_round_default = false;
            }

            instantsearchInstance.addWidget(
                instantsearch.widgets.ionRangeSlider({
                    container: '#' + obj.id,
                    attributeName: obj.tax,
                    ionRangeSliderOptions: {
                        type: "double",
                        grid: slider_grid,
                        round_default: slider_round_default,
                        prettify_enabled: slider_prettify,
                        step: slider_step,
                        postfix: slider_postfix
                    }
                })
            );
        }
    });

    var sorting_indices = [];
    sorting_indices.push({
        name: algoliaSettings.indices[0].index_name,
        label: 'Trier par défaut'
    });
    for (var i = 0; i < algoliaSettings.sorting_indices.length; ++i) {
        sorting_indices.push({
            name: algoliaSettings.sorting_indices[i].index_name,
            label: algoliaTranslateLabel[algoliaSettings.sorting_indices[i].label]
        });
    }
    instantsearchInstance.addWidget(
        instantsearch.widgets.sortBySelector({
            container: '#sort-by-container',
            indices: sorting_indices
        })
    );

    if ($('#sort-by-container-mobile').length > 0)
        instantsearchInstance.addWidget(
            instantsearch.widgets.sortBySelector({
                container: '#sort-by-container-mobile',
                indices: sorting_indices
            })
        );

    if ($('.m-stats-container').length > 0)
        instantsearchInstance.addWidget(
            instantsearch.widgets.stats({
                container: '.m-stats-container',
                templates: {
                    body: getItem('m-stats-container')
                }
            })
        );

    instantsearchInstance.addWidget(
        instantsearch.widgets.stats({
            container: '.stats-container',
            templates: {
                body: getItem('stats-container')
            }
        })
    );

    instantsearchInstance.addWidget(
        instantsearch.widgets.infiniteHits({
            showMoreLabel: 'Voir plus de résultats',
            cssClasses: {
                showmore: 'd-none'
            },
            container: '#infinite-hits-container',
            templates: {
                empty: document.querySelector("#no-result").innerHTML,
                item: document.querySelector("#vehicle-item-template").innerHTML
            },
            transformData: {
                item: function (data) {
                    data.price_tax_incl_raw = data.price_tax_incl;
                    data.price_tax_excl_raw = data.price_tax_excl;
                    data.hasMonthlyPayment = false;
                    try {
                        if (data.monthly_payment.amount) {
                            initMonthlyList(data.objectID);
                            data.hasMonthlyPayment = true;
                        }
                    } catch (e) {
                    }
                    data.have_images = true;
                    if (data.number_images == 0) {
                        data.number_images = "";
                        data.have_images = false;
                    }
                    if (data.number_images > 0) {
                        data.number_images = data.number_images;
                    }
                    if (data.is_vo)
                        data.linkTitle = aglLabelVehicle.charAt(0).toUpperCase() + aglLabelVehicle.slice(1) + " " + (typeof data.marque != "undefined" && typeof data.marque.name != "undefined" && data.marque.name != "" ? data.marque.name : "") + " " + (typeof data.modele != "undefined" && typeof data.modele.name != "undefined" && data.modele.name != "" ? data.modele.name : "") + (typeof data.version != "undefined" && data.version != "" ? " " + data.version : "") + " d'occasion " + (typeof data.kilometrage != "undefined" && data.kilometrage != "" ? " " + data.kilometrage.toLocaleString() + " km" : "");
                    else
                        data.linkTitle = aglLabelVehicle.charAt(0).toUpperCase() + aglLabelVehicle.slice(1) + " " + (typeof data.marque != "undefined" && typeof data.marque.name != "undefined" && data.marque.name != "" ? data.marque.name : "") + " " + (typeof data.modele != "undefined" && typeof data.modele.name != "undefined" && data.modele.name != "" ? data.modele.name : "") + (typeof data.version != "undefined" && data.version != "" ? " " + data.version : "") + " neuf";

                    return void 0 != data.tva_recuperable && (data.tva_recuperable = (data.tva_recuperable == "Oui" ? 1 : 0)), void 0 != data.remise && (data.remise = "- " + data.remise + "%"), void 0 != data.old_price && (data.old_price = data.old_price.toLocaleString() + " €"), void 0 != data.old_price_tax_excl && (data.old_price_tax_excl = data.old_price_tax_excl.toLocaleString() + " €"), void 0 != data.old_price_tax_incl && (data.old_price_tax_incl = data.old_price_tax_incl.toLocaleString() + " €"), void 0 != data.price_tax_incl && (data.price_tax_incl = data.price_tax_incl.toLocaleString() + " €"), void 0 != data.price_tax_excl && (data.price_tax_excl = data.price_tax_excl.toLocaleString() + " €"), void 0 != data.kilometrage && (data.kilometrage = data.kilometrage.toLocaleString() + " km"), data
                }
            },
            escapeHits: true
        })
    );

    instantsearchInstance.start();
}

function getHeader(label) {
    return '<header class="collapsible-header">' + label + '<span class="right truncate"></span><i class="fa fa-minus-circle close-header-item"></i><i class="fa fa-plus-circle open-header-item"></i></header>';
}

function getItem(name) {
    return document.querySelector("#" + name + "-template").innerHTML;
}

function getShowMore(label) {
    return '<a class="ais-show-more"><i class="fa fa-chevron-circle-down mr-2" aria-hidden="true"></i>' + "voir " + label + "</a>";
}

function getShowLess(label) {
    return '<a class="ais-show-more"><i class="fa fa-chevron-circle-up mr-2" aria-hidden="true"></i>' + "voir " + label + "</a>";
}

function sortBySelector() {
    if ($("select.ais-sort-by-selector").length > 0 && !$("select.ais-sort-by-selector").hasClass("mdb-select")) {
        $("select.ais-sort-by-selector").addClass('mdb-select md-form my-0').materialSelect();
    }
    $("select.ais-sort-by-selector").on("change", function (event) {
        var value = $(this).val();
        instantsearchInstance.helper.setIndex(value), instantsearchInstance.helper.search()
    })
}

function autoCollapsed() {
    if (!firstRender) {
        $("#search-algolia").find(".auto-collapsed").each(function () {
            $(this).find('.ais-refinement-list.ais-root__collapsible .ais-header').click();
        });
    }
    firstRender = true;
}

function initDisplayFullStock() {
    /* affichage du stock au complet si le choix de la concession est actif sur le site */
    $(document).on('change', '#search-algolia .full_stock_container #display_full_stock', function () {
        var url = window.location.href;
        var search = window.location.search;
        var param_get_full_stosk = (search === "" ? "?full_stock=1" : "&full_stock=1");

        if ($('#search-algolia .full_stock_container #display_full_stock').is(':checked'))
            window.location.replace(url + param_get_full_stosk);
        else {
            if (url.indexOf("?full_stock") >= 0)
                url = url.replace('?full_stock=1', '');
            else if (url.indexOf("&full_stock") >= 0)
                url = url.replace('&full_stock=1', '');

            window.location.replace(url);
        }
    });
}

function initCheckFullStock() {
    var search = window.location.search;
    /* test si check via le getter */
    if (search.indexOf("?full_stock=1") >= 0 || search.indexOf("&full_stock=1") >= 0)
        $('#search-algolia .full_stock_container #display_full_stock').prop('checked', 'checked');
}