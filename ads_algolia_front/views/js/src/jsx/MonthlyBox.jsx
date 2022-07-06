"use strict";
import _ from 'lodash';
import { Scrollbars } from 'react-custom-scrollbars';
import ionRangeSlider from 'ion-rangeslider';

class MonthlyBox extends React.Component {
    constructor(props) {
        super(props);
        this.state = {
            monthlyMin: null,
            monthlyMax: null
        };
    }
    componentDidMount() {
        this.loadPriceFromServer();
    }

    loadPriceFromServer() {
        
        algoliaVehicleIndex.search("", {
            facets: "monthly_payment.amount",
            filters: algolia_prefiltered,
            hitsPerPage: 0
        }).then(function(answer) {
            var res = answer.facets_stats["monthly_payment.amount"],
                monthlyMin = 1e2 * Math.floor(res.min / 1e2),
                monthlyMax = 1e2 * (Math.round(res.max / 1e2) + 1);
            this.setState({
                monthlyMin: monthlyMin,
                monthlyMax: monthlyMax
            }), jQuery("#slider-hp-monthly").ionRangeSlider({
                type: "double",
                grid: !1,
                postfix: "&nbsp;€",
                step: 50,
                min: monthlyMin,
                max: monthlyMax,
                onChange: function(elm) {
                    this.setState({
                        monthlyMin: elm.from,
                        monthlyMax: elm.to
                    })
                }.bind(this)
            })
        }.bind(this))
    }

    search() {
         var monthlyMin = this.state.monthlyMin,
            monthlyMax = this.state.monthlyMax,
                route = path_to_list + "?" + $.param({
                    nR: {
                       'monthly_payment.amount': {
                        ">=": {
                            monthlyMin: monthlyMin
                        },
                        "<=": {
                            monthlyMax: monthlyMax
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
                        <span className="homepage-search_engine-selection__header">Mensualité</span>
                        <div className="homepage-search_engine-selection__list homepage-search_engine-selection__slider--price m-4">
                            <input type="text" id="slider-hp-monthly" name="slider-monthly-name" value="" />
                        </div>
                    </div>
                    <div className="homepage-search_engine-submit clearfix d-flex flex-nowrap align-items-stretch justify-content-end w-100 text-right">
                    <span className="mr-3">Mensualité de <strong>{this.state.monthlyMin} €</strong> à <strong>{this.state.monthlyMax} €</strong></span>
                        <button className="btn" onClick={this.search.bind(this)}>Chercher</button>
                    </div>
                </div>;

                                return element_return;
                    }
                }

                void 0 != document.getElementById("monthly-selection") && ReactDOM.render(React.createElement(MonthlyBox, null), document.getElementById("monthly-selection"));