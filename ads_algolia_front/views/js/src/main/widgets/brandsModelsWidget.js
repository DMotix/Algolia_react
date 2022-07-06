var BrandsModelsWidgetComponent = require('./../../jsx/BrandsModelsWidgetComponent.jsx');

instantsearch.widgets.brandsModelsWidget = function (item) {
    function isBrandExists(allBrands, brand) {
        for (var keyBrand in allBrands)
            if (allBrands[keyBrand].name == brand)
                return keyBrand;
        return !1
    }
    if (!item.container)
        throw new Error("brandsModelsWidget: usage: brandsModelsWidget({container})");
    var container_item = $(item.container);
    if (0 === container_item.length)
        throw new Error("brandsModelsWidget: cannot select '" + item.container + "'");
    return {
        getConfiguration: function () {
            return {
                disjunctiveFacets: ["modele.fullNames"],
                facets: ["modele.fullNames"],
                maxValuesPerFacet: 9999
            }
        },
        init: function (initOptions) {},
        render: function (renderOptions) {
            var brandCount;
            var helpers = renderOptions.helper,
                    results = renderOptions.results,
                    disjunctiveFacetsTab = "";
            for (var disjunctiveFacetKey in results.disjunctiveFacets)
                "modele.fullNames" == results.disjunctiveFacets[disjunctiveFacetKey].name && (disjunctiveFacetsTab = results.disjunctiveFacets[disjunctiveFacetKey].data);
            var regex_modelefullname = /^(.*) > (.*)/,
                    allBrands = [];
            for (var modelFullNamesKey in disjunctiveFacetsTab) {
                var match_modelefullname = modelFullNamesKey.match(regex_modelefullname);
                if (void 0 != match_modelefullname && void 0 != match_modelefullname[1]) {
                    var brand_name = match_modelefullname[1],
                            model_name = match_modelefullname[2],
                            model_count = brandCount = disjunctiveFacetsTab[modelFullNamesKey],
                            model_fullnames = match_modelefullname[0];
                    model_name == allModelString && (brandCount = 0);
                    var brand_exists = isBrandExists(allBrands, brand_name);
                    brand_exists ? (allBrands[brand_exists].models.push({
                        name: model_name,
                        fullNames: model_fullnames,
                        count: model_count
                    }), allBrands[brand_exists].count += brandCount) : allBrands.push({
                        name: brand_name,
                        count: brandCount,
                        models: [{
                                name: model_name,
                                fullNames: model_fullnames,
                                count: model_count
                            }]
                    })
                }
            }
            ReactDOM.render(React.createElement(BrandsModelsWidgetComponent, {
                brands: allBrands,
                helper: helpers
            }), container_item[0])
        }
    }
};