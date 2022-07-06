"use strict";
import _ from 'lodash';
import slugify from 'slugify';
import { Scrollbars } from 'react-custom-scrollbars';

class BodyWorkBox extends React.Component {
    constructor(props) {
        super(props);
        this.state = {
            bodyworks: [],
            selectedBodyworks: [],
            allBodyworksSelected: !1
        };
    }
    componentDidMount() {
        this.loadBodyworksFromServer();
    }

    loadBodyworksFromServer() {
        algoliaVehicleIndex.searchForFacetValues({
            facetName: "type_de_vehicule",
            facetQuery: "",
            maxFacetHits: 100,
            filters: algolia_prefiltered
        }, function (err, content) {
            if (!err) {
                var objBodyworks = content.facetHits.map(function (obj) {
                    return obj;
                });
                this.setState({
                    bodyworks: objBodyworks
                    });
            }
        }.bind(this))
    }

    refreshAllBodyworksSelected(selectedBodyworks) {
        var allBodyworks = this.state.bodyworks.length == selectedBodyworks.length;
        this.setState({
            allBodyworksSelected: allBodyworks
        })
    }

    toggleSelectAllCategory() {
        var allBodyworksSelected = this.state.allBodyworksSelected,
            bodyworks = this.state.bodyworks,
            selectedBodyworks = this.state.selectedBodyworks;
        allBodyworksSelected ? selectedBodyworks = [] : _.forOwn(bodyworks, function(res) {
            this.isBodyworkSelected(res.value) || selectedBodyworks.push(res.value)
        }.bind(this)), this.setState({
            selectedBodyworks: selectedBodyworks
        }), this.refreshAllBodyworksSelected(selectedBodyworks)
    }

    toggleSelectBodywork(objBodywork) {
        var selectedBodyworks = this.state.selectedBodyworks;
        this.isBodyworkSelected(objBodywork) ? selectedBodyworks = _.without(selectedBodyworks, objBodywork) : selectedBodyworks.push(objBodywork), this.setState({
            selectedBodyworks: selectedBodyworks
        }), this.refreshAllBodyworksSelected(selectedBodyworks)
    }

    renderBodywork(objBodywork) {
        var className = "homepage-search_engine-selection--category__item btn motor-btn-filter motor-btn-filter--category mb-3 p-0 px-2 col-4";
        return this.isBodyworkSelected(objBodywork.value) && (className += " active"), className = className + " type-" + slugify(objBodywork.value.toLowerCase()), React.createElement("div", {
            key: objBodywork.value,
            onClick: this.toggleSelectBodywork.bind(this, objBodywork.value),
            className: className
        }, React.createElement("div", { className: "item-container"}, objBodywork.value, " ", React.createElement("span", {
            className: "motor-count"
        }, "", objBodywork.count, "")))
    }
    
    isBodyworkSelected(bodywork) {
        return -1 !== this.state.selectedBodyworks.indexOf(bodywork)
    }

    search() {
        var selectedBodyworks = this.state.selectedBodyworks,
                mappedBodyworks = _.map(selectedBodyworks, function (CategoryObj) {
                    return CategoryObj
                }),
                route = path_to_list + "?" + $.param({
                    dFR: {
                        type_de_vehicule: mappedBodyworks
                    }
                });
        "" != route && window.location.replace(route)
    }

    render() {
        var element = this;
        const element_return =
                <div className="row">
                    <div className="homepage-search_engine-selection homepage-search_engine-selection--category-selection col">
                        <span className="homepage-search_engine-selection__header">Types de véhicule</span>
                        <div className="homepage-search_engine-selection__checkbox content-checkbox" onClick={this.toggleSelectAllCategory.bind(this)}>
                            <input type="checkbox" id="allCategory" name="search_home_category" placeholder="" value="1" checked={this.state.allBodyworksSelected} />
                            <label className="control-label">Tout sélectionner</label>
                        </div>             
                        <div className="homepage-search_engine-selection__list homepage-search_engine-selection__list--category">
                            <Scrollbars hideTracksWhenNotNeeded style={{height: 325 }}>
                                <div className="d-flex flex-wrap align-content-stretch">
                                {this.state.bodyworks.map(function (bodywork) {
                                                    return element.renderBodywork(bodywork);
                                })}
                                </div>
                            </Scrollbars>
                        </div>
                    </div>
                    <div className="homepage-search_engine-submit clearfix col-12 pr-0 text-right">
                        <span className="mr-3">{this.state.selectedBodyworks.length} type{this.state.selectedBodyworks.length > 1 && "s"} de véhicule sélectionné{this.state.selectedBodyworks.length > 1 && "s"}</span>
                        <button className="btn" onClick={this.search.bind(this)}>Chercher</button>
                    </div>
                </div>;

                                return element_return;
                    }
                }

                void 0 != document.getElementById("bodywork-selection") && ReactDOM.render(React.createElement(BodyWorkBox, null), document.getElementById("bodywork-selection"));