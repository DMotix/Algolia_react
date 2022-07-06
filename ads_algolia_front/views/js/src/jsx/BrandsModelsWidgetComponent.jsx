"use strict";
import _ from 'lodash';

class BrandsModelsWidgetComponent extends React.Component {
    constructor(props) {
        super(props);
        this.state = {
            brandsOpened: []
        };
    }
    componentDidMount() {
        this.initializeBrandOpened();
    }

    componentDidUpdate() {
        this.initializeBrandOpened();
    }

    initializeBrandOpened() {
        var modelFullNamesRList = this.getModelFullNamesRefinmentList();
        for (var model in modelFullNamesRList) {
            var modele_selected = this.getBrandFromModelFullNames(modelFullNamesRList[model]);
            this.isBrandOpened(modele_selected) || this.openBrand(modele_selected)
        }
    }

    toggleBrandOpened(brand) {
        this.isBrandOpened(brand) ? this.closeBrand(brand) : this.openBrand(brand)
    }

    openBrand(brand) {
        var brandsOpened = this.state.brandsOpened;
        brandsOpened.push(brand), this.setState({
            makersOpened: brandsOpened
        })
    }

    closeBrand(brand) {
        var brandsOpened = this.state.brandsOpened,
                selectedBrand = brandsOpened.indexOf(brand);
        brandsOpened.splice(selectedBrand, 1), this.setState({
            brandsOpened: brandsOpened
        })
    }

    isBrandOpened(brand) {
        return -1 !== this.state.brandsOpened.indexOf(brand)
    }

    toggleModel(model, t) {
        var brandfn = this.getBrandFromModelFullNames(model);
        t != allModelString || this.isModelSelected(model) || this.resetBrandSelection(brandfn);
        var brandModel = brandfn + " > " + allModelString;
        t != allModelString && this.isModelSelected(brandModel) && this.props.helper.removeDisjunctiveFacetRefinement("modele.fullNames", brandModel), this.isModelSelected(model) ? this.props.helper.removeDisjunctiveFacetRefinement("modele.fullNames", model) : this.props.helper.addDisjunctiveFacetRefinement("modele.fullNames", model), this.props.helper.search()
    }

    isBrandSelected(brand) {
        var models = this.getModelFullNamesRefinmentList();
        for (var model in models)
            if (this.getBrandFromModelFullNames(models[model]) == brand)
                return !0;
        return !1
    }

    isModelSelected(modelcurrent) {
        var models = this.getModelFullNamesRefinmentList();
        for (var model in models)
            if (models[model] == modelcurrent)
                return !0;
        return !1
    }

    resetBrandSelection(brandselection) {
        var models = this.getModelFullNamesRefinmentList();
        for (var model in models) {
            var current_model = models[model];
            this.getBrandFromModelFullNames(current_model) == brandselection && this.getModelFromModelFullNames(current_model) != allModelString && this.props.helper.removeDisjunctiveFacetRefinement("modele.fullNames", current_model)
        }
    }

    getModelFullNamesRefinmentList() {
        return this.props.helper.state.disjunctiveFacetsRefinements["modele.fullNames"]
    }

    getBrandFromModelFullNames(model) {
        return this.getInfosFromFullNames(model).brand
    }

    getModelFromModelFullNames(model) {
        return this.getInfosFromFullNames(model).model
    }

    getInfosFromFullNames(model) {
        var model_matches = model.match(/^(.*) > (.*)/);
        return {
            brand: void 0 != model_matches && void 0 != model_matches[1] && model_matches[1],
            model: void 0 != model_matches && void 0 != model_matches[2] && model_matches[2]
        }
    }

    renderBrand(brand) {
        var current = this;
        return this.isBrandSelected(brand.name), React.createElement("div", {
            key: brand.name,
            className: "ais-hierarchical-menu--item",
            onClick: function () {
                current.toggleBrandOpened(brand.name)
            }
        }, React.createElement("div", {
            className: "ais-hierarchical-menu--link"
        }, brand.name, " ", React.createElement("span", {
            className: "ais-hierarchical-menu--count motor-count"
        }, "(", brand.count, ")")), React.createElement("div", {
            className: "ais-hierarchical-menu--list ais-hierarchical-menu--list__lvl1"
        }, brand.models.map(function (event) {
            return current.renderModel(event)
        })))
    }

    renderModel(model) {
        var current = this,
                className = "ais-hierarchical-menu--item";
        return this.isModelSelected(model.fullNames) && (className += " ais-hierarchical-menu--item__active"), this.isBrandOpened(this.getBrandFromModelFullNames(model.fullNames)) || (className += " hidden"), React.createElement("div", {
            key: model.fullNames,
            className: className,
            onClick: function () {
                current.toggleModel(model.fullNames, model.name)
            }
        }, React.createElement("div", {
            className: "ais-hierarchical-menu--link"
        }, model.name, " ", React.createElement("span", {
            className: "ais-hierarchical-menu--count motor-count"
        }, "(", model.count, ")")))
    }

    render() {
        var element = this;
        const element_return =
                <div className="ais-root ais-hierarchical-menu ais-root__collapsible">
                    <div className="ais-hierarchical-menu--header ais-header">Marque / Modèle</div>
                    <div className="ais-body ais-hierarchical-menu--body">
                        <div className="ais-hierarchical-menu--list ais-hierarchical-menu--list__lvl0">
                            {
                                this.props.brands.map(function (obj) {
                                    return element.renderBrand(obj);
                            })
                            }
                        </div>
                    </div>
                </div>;

            return element_return;
    }
}

module.exports = BrandsModelsWidgetComponent;