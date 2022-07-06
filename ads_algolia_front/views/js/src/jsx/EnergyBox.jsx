"use strict";
import _ from 'lodash';
import slugify from 'slugify';
import { Scrollbars } from 'react-custom-scrollbars';

class EnergyBox extends React.Component {
    constructor(props) {
        super(props);
        this.state = {
            energy: [],
            selectedEnergy: [],
            allEnergySelected: !1
        };
    }
    componentDidMount() {
        this.loadEnergyFromServer();
    }

    loadEnergyFromServer() {
        algoliaVehicleIndex.searchForFacetValues({
            facetName: "energie",
            facetQuery: "",
            maxFacetHits: 100,
            filters: algolia_prefiltered
        }, function (err, content) {
            if (!err) {
                var objEnergy = content.facetHits.map(function (obj) {
                    return obj;
                });
                this.setState({
                    energy: objEnergy
                    });
            }
        }.bind(this))
    }

    refreshAllEnergySelected(selectedEnergy) {
        var allEnergy = this.state.energy.length == selectedEnergy.length;
        this.setState({
            allEnergySelected: allEnergy
        })
    }

    toggleSelectAllEnergy() {
        var allEnergySelected = this.state.allEnergySelected,
            energy = this.state.energy,
            selectedEnergy = this.state.selectedEnergy;
        allEnergySelected ? selectedEnergy = [] : _.forOwn(energy, function(res) {
            this.isEnergySelected(res.value) || selectedEnergy.push(res.value)
        }.bind(this)), this.setState({
            selectedEnergy: selectedEnergy
        }), this.refreshAllEnergySelected(selectedEnergy)
    }

    toggleSelectEnergy(objEnergy) {
        var selectedEnergy = this.state.selectedEnergy;
        this.isEnergySelected(objEnergy) ? selectedEnergy = _.without(selectedEnergy, objEnergy) : selectedEnergy.push(objEnergy), this.setState({
            selectedEnergy: selectedEnergy
        }), this.refreshAllEnergySelected(selectedEnergy)
    }

    renderEnergy(objEnergy) {
        var className = "homepage-search_engine-selection--category__item btn motor-btn-filter motor-btn-filter--energy mb-3 p-0 px-2 col-4";
        return this.isEnergySelected(objEnergy.value) && (className += " active"), className = className + " energy-" + slugify(objEnergy.value.toLowerCase()), React.createElement("div", {
            key: objEnergy.value,
            onClick: this.toggleSelectEnergy.bind(this, objEnergy.value),
            className: className
        }, React.createElement("div", { className: "item-container"}, objEnergy.value, " ", React.createElement("span", {
            className: "motor-count"
        }, "", objEnergy.count, "")))
    }
    
    isEnergySelected(energy) {
        return -1 !== this.state.selectedEnergy.indexOf(energy)
    }

    search() {
        var selectedEnergy = this.state.selectedEnergy,
                mappedEnergy = _.map(selectedEnergy, function (EnergyObj) {
                    return EnergyObj
                }),
                route = path_to_list + "?" + $.param({
                    dFR: {
                        energie: mappedEnergy
                    }
                });
        "" != route && window.location.replace(route)
    }

    render() {
        var element = this;
        const element_return =
                <div className="row">
                    <div className="homepage-search_engine-selection homepage-search_engine-selection--energy-selection col">
                        <span className="homepage-search_engine-selection__header">Energies</span>
                        <div className="homepage-search_engine-selection__checkbox content-checkbox" onClick={this.toggleSelectAllEnergy.bind(this)}>
                            <input type="checkbox" id="allCategory" name="search_home_category" placeholder="" value="1" checked={this.state.allEnergySelected} />
                            <label className="control-label">Tout sélectionner</label>
                        </div>             
                        <div className="homepage-search_engine-selection__list homepage-search_engine-selection__list--category">
                            <Scrollbars hideTracksWhenNotNeeded style={{height: 325 }}>
                                <div className="d-flex flex-wrap align-content-stretch">
                                {this.state.energy.map(function (energy) {
                                                    return element.renderEnergy(energy);
                                })}
                                </div>
                            </Scrollbars>
                        </div>
                    </div>
                    <div className="homepage-search_engine-submit clearfix col-12 pr-0 text-right">
                        <span className="mr-3">{this.state.selectedEnergy.length} énergie{this.state.selectedEnergy.length > 1 && "s"} sélectionné{this.state.selectedEnergy.length > 1 && "s"}</span>
                        <button className="btn" onClick={this.search.bind(this)}>Chercher</button>
                    </div>
                </div>;

                                return element_return;
                    }
                }

                void 0 != document.getElementById("energy-selection") && ReactDOM.render(React.createElement(EnergyBox, null), document.getElementById("energy-selection"));