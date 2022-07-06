"use strict";
import _ from 'lodash';
import { Scrollbars } from 'react-custom-scrollbars';
import ionRangeSlider from 'ion-rangeslider';

class PricesBox extends React.Component {
    constructor(props) {
        super(props);
        this.state = {
            priceMin: null,
            priceMax: null
        };
    }
    componentDidMount() {
        this.loadPriceFromServer();
    }

    loadPriceFromServer() {
        
        algoliaVehicleIndex.search("", {
            facets: "price_tax_incl",
            filters: algolia_prefiltered,
            hitsPerPage: 0
        }).then(function(answer) {
            var res = answer.facets_stats["price_tax_incl"],
                priceMin = 1e3 * Math.round(res.min / 1e3),
                priceMax = 1e3 * (Math.round(res.max / 1e3) + 1);
            this.setState({
                priceMin: priceMin,
                priceMax: priceMax
            }), jQuery("#slider-hp-price").ionRangeSlider({
                type: "double",
                grid: !1,
                postfix: "&nbsp;€",
                step: 1e3,
                min: priceMin,
                max: priceMax,
                onChange: function(elm) {
                    this.setState({
                        priceMin: elm.from,
                        priceMax: elm.to
                    })
                }.bind(this)
            })
        }.bind(this))
    }

    search() {
         var priceMin = this.state.priceMin,
            priceMax = this.state.priceMax,
                route = path_to_list + "?" + $.param({
                    nR: {
                        price_tax_incl: {
                        ">=": {
                            priceMin: priceMin
                        },
                        "<=": {
                            priceMax: priceMax
                        }
                    }
                    }
                });
        "" != route && window.location.replace(route)
    }

    render() {
        var element = this;
        const element_return =
                <div className="row">
                    <div className="homepage-search_engine-selection homepage-search_engine-selection--price-selection col">
                        <span className="homepage-search_engine-selection__header">Prix</span>
                        <div className="homepage-search_engine-selection__list homepage-search_engine-selection__slider--price m-4">
                            <input type="text" id="slider-hp-price" name="slider-price-name" value="" />
                        </div>
                    </div>
                    <div className="homepage-search_engine-submit clearfix d-flex flex-nowrap align-items-stretch justify-content-end w-100 text-right">
                    <span className="mr-3">Prix de <strong>{this.state.priceMin} €</strong> à <strong>{this.state.priceMax} €</strong></span>
                        <button className="btn" onClick={this.search.bind(this)}>Chercher</button>
                    </div>
                </div>;

                                return element_return;
                    }
                }

                void 0 != document.getElementById("price-selection") && ReactDOM.render(React.createElement(PricesBox, null), document.getElementById("price-selection"));