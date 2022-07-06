"use strict";
import _ from 'lodash';
import { Scrollbars } from 'react-custom-scrollbars';

class ManufacturerModelBox extends React.Component {
    constructor(props) {
        super(props);
        this.state = {
            lastManufacturerSelected: "",
            manufacturers: [],
            models: [],
            selectedModels: [], //Redux ?
            allModelsSelected: !1
        };
    }
    componentDidMount() {
        this.loadManufacturersFromServer();
    }

    loadManufacturersFromServer() {
        algoliaVehicleIndex.searchForFacetValues({
            facetName: "marque.name",
            facetQuery: "",
            maxFacetHits: 100,
            filters: algolia_prefiltered
        }, function (err, content) {
            if (!err) {
                var objManufacturers = content.facetHits.map(function (obj) {
                    return obj;
                });
                this.setState({
                    manufacturers: objManufacturers
                }), "" == this.state.lastManufacturerSelected && this.toggleSelectManufacturer(objManufacturers[0].value)
            }
        }.bind(this))
    }

    loadModelsFromServer(manufacturer) {
        if ("" == manufacturer)
            return !1;
        algoliaVehicleIndex.searchForFacetValues({
            facetName: "modele.name",
            facetQuery: "",
            filters: algolia_prefiltered + " AND marque.name:\"" + manufacturer + "\"",
            maxFacetHits: 100
        }, function (err, content) {
            err || (this.setState({
                models: content.facetHits
            }), this.refreshAllModelsSelected())
        }.bind(this))
    }

    refreshAllModelsSelected() {
        var lastManufacturerSelected = this.state.lastManufacturerSelected,
                models = this.state.models,
                selectedModels = this.state.selectedModels,
                selectedModelsFiltered = _.filter(selectedModels, function (model) {
                    if (model.manufacturerName == lastManufacturerSelected)
                        return model
                }).length == models.length && models.length > 0;
        this.setState({
            allModelsSelected: selectedModelsFiltered
        })
    }

    toggleSelectManufacturer(libelleMarque) {
        this.setState({
            lastManufacturerSelected: libelleMarque
        }), this.loadModelsFromServer(libelleMarque)
    }

    toggleSelectAllModel() {
        var allModelsSelected = this.state.allModelsSelected,
                models = this.state.models,
                lastManufacturer = this.state.lastManufacturerSelected,
                selectedModels = this.state.selectedModels;
        allModelsSelected ? _.remove(selectedModels, {
            manufacturerName: lastManufacturer
        }) : _.forOwn(models, function (res) {
            this.isModelSelected(lastManufacturer, res.value) || selectedModels.push({
                manufacturerName: lastManufacturer,
                modelName: res.value
            })
        }.bind(this)), this.setState({
            selectedModels: selectedModels
        }), this.refreshAllModelsSelected()
    }

    renderManufacturer(objMarque) {
        var className = "homepage-search_engine-selection--maker__item list-group-item-action list-group-item d-flex justify-content-between align-items-center pl-2 py-3 mb-1",
                selectedModels = this.state.selectedModels,
                lastManufacturer = this.state.lastManufacturerSelected;
        return (_.some(selectedModels, function (res) {
            return res.manufacturerName === objMarque.value
        }) || lastManufacturer == objMarque.value) && (className += " active"), React.createElement("div", {
            key: objMarque.value,
            onClick: this.toggleSelectManufacturer.bind(this, objMarque.value),
            className: className
        }, objMarque.value, " ", React.createElement("span", {
            className: "motor-count"
        }, "", objMarque.count, ""))
    }

    renderModel(objModel) {
        var className = "homepage-search_engine-selection--model__item btn motor-btn-filter motor-btn-filter--model mb-3 p-0 px-2 col-4",
                lastManufacturer = this.state.lastManufacturerSelected;
        return this.isModelSelected(lastManufacturer, objModel.value) && (className += " active"), React.createElement("div", {
            key: objModel.value,
            onClick: this.toggleSelectModel.bind(this, objModel.value),
            className: className
        }, React.createElement("div", { className: "item-container"}, objModel.value, " ", React.createElement("span", {
            className: "motor-count"
        }, "", objModel.count, "")))
    }

    toggleSelectModel(objModel) {
        var selectedModels = this.state.selectedModels,
                lastManufacturer = this.state.lastManufacturerSelected;
        this.isModelSelected(lastManufacturer, objModel) ? _.remove(selectedModels, {
            manufacturerName: lastManufacturer,
            modelName: objModel
        }) : selectedModels.push({
            manufacturerName: lastManufacturer,
            modelName: objModel
        }), this.setState({
            selectedModels: selectedModels
        }), this.refreshAllModelsSelected()
    }

    isModelSelected(manufacturerSelected, modelValue) {
        var selectedModels = this.state.selectedModels;
        return _.some(selectedModels, function (sModels) {
            return sModels.manufacturerName === manufacturerSelected && sModels.modelName == modelValue
        })
    }

    search() {
        var selectedModels = this.state.selectedModels,
                mappedModels = _.map(selectedModels, function (ModelObj) {
                    return ModelObj.manufacturerName + " > " + ModelObj.modelName
                }),
                route = path_to_list + "?" + $.param({
                    dFR: {
                        "modele.fullNames": mappedModels
                    }
                });
        "" != route && window.location.replace(route)
    }

    render() {
        var element = this;
        const element_return =
                <div className="row">
                    <div className="homepage-search_engine-selection homepage-search_engine-selection--maker-selection col col-sm-4">
                        <span className="homepage-search_engine-selection__header">Marques</span>
                        <div className="homepage-search_engine-selection__list homepage-search_engine-selection__list--manufacturers mr-2">
                            <Scrollbars hideTracksWhenNotNeeded style={{height: 340 }}>
                                <div className="list-group">
                                {this.state.manufacturers.map(function (make) {
                                                    return element.renderManufacturer(make);
                                })}
                                </div>
                            </Scrollbars>
                        </div>
                    </div>
                    <div className="homepage-search_engine-selection homepage-search_engine-selection--model-selection col col-sm-8">
                        <span className="homepage-search_engine-selection__header">Modèles</span>
                        <div className="homepage-search_engine-selection__checkbox content-checkbox" onClick={this.toggleSelectAllModel.bind(this)}>
                            <input type="checkbox" id="mallModels" name="search_home_models" placeholder="" value="1" checked={this.state.allModelsSelected} />
                            <label className="control-label">Tout sélectionner</label>
                        </div>
                        <div className="homepage-search_engine-selection__list homepage-search_engine-selection__list--models">
                            <Scrollbars hideTracksWhenNotNeeded style={{height: 340 }}>
                                <div className="d-flex flex-wrap align-content-stretch">
                                {this.state.models.map(function (obj_model) {
                                                            return element.renderModel(obj_model);
                                })}
                                </div>
                            </Scrollbars>
                        </div>
                    </div>
                    <div className="homepage-search_engine-submit clearfix col-12 pr-0 text-right">
                        <span className="mr-3">{this.state.selectedModels.length} modèle{this.state.selectedModels.length > 1 && "s"} sélectionné{this.state.selectedModels.length > 1 && "s"}</span>
                        <button className="btn" onClick={this.search.bind(this)}>Chercher</button>
                    </div>
                </div>;

                                return element_return;
                    }
                }

                void 0 != document.getElementById("manufacturer-model-selection") && ReactDOM.render(React.createElement(ManufacturerModelBox, null), document.getElementById("manufacturer-model-selection"));