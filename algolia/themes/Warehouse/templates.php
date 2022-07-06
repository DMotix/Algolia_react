<script type="text/template" id="autocomplete-template">
    <div class="result">
        <div class="title">
            {{#image_link_small}}
            <div class="thumb">
                <img style="width: 30px" src="//{{{image_link_small}}}" />
            </div>
            {{/image_link_small}}
            <div class="info{{^image_link_small}}-without-thumb{{/image_link_small}}">
            {{#_highlightResult.path}}
                {{{_highlightResult.path.value}}} ({{product_count}})
            {{/_highlightResult.path}}
            {{^_highlightResult.path}}
                {{{ _highlightResult.name }}}
            {{/_highlightResult.path}}

            {{#price_tax_incl}}
                <div class="algoliasearch-autocomplete-price">{{price_tax_incl}}{{currency}}</div>
            {{/price_tax_incl}}
            {{#_highlightResult.category}}
                <div class="algoliasearch-autocomplete-price">{{{_highlightResult.category.value}}}</div>
            {{/_highlightResult.category}}
            </div>
            <div style="clear: both;"></div>
        </div>
    </div>
</script>

<script type="text/template" id="instant-content-template">
    <div class="hits{{#facets_count}} with_facets{{/facets_count}} center_column col-xs-12 col-sm-12">
        {{#hits.length}}
        <div class="infos">
            <div style="float: left">
                {{nbHits}} result{{^nbHits_one}}s{{/nbHits_one}} {{#query}}found matching "<strong>{{query}}</strong>"{{/query}} in {{processingTimeMS}} ms
            </div>
            {{#sorting_indices.length}}
            <div style="float: right; margin-right: 10px;">
                Order by
                <select id="index_to_use">
                    <option {{#sortSelected}}{{relevance_index_name}}{{/sortSelected}} value="{{relevance_index_name}}">relevance</option>
                    {{#sorting_indices}}
                    <option {{#sortSelected}}{{index_name}}{{/sortSelected}} value="{{index_name}}">{{label}}</option>
                    {{/sorting_indices}}
                </select>
            </div>
            {{/sorting_indices.length}}
            <div style="clear: both;"></div>
        </div>
        {{/hits.length}}

        <ul class="product_list grid row">
            {{#hits}}
                <li class="ajax_block_product result-wrapper col-xs-12 col-sm-6 col-md-3">
                    <div class="product-container" itemscope>
                        <div class="left-block">
                            <div class="product-image-container">
                                <a class="product_img_link" href="{{link}}" title="{{{ _highlightResult.name }}}" >
                                    <img class="replace-2x img-responsive img_0 lazy" src="//{{{ image_link_large }}}" alt="{{{ _highlightResult.name }}}" title="{{{ _highlightResult.name }}}" />  
                                </a>
                                <div class="product-flags">
                                    {{#newproduct}}
                                        <span class="new-label">New</span>
                                    {{/newproduct}}
                                    {{#on_sale}}
                                        <span class="sale-label">Sale!</span>
                                    {{/on_sale}}
                                </div>
                            <div class="functional-buttons functional-buttons-grid clearfix">
                                <div class="quickview col-xs-6">
                                    <a class="quick-view" href="{{link}}" rel="{{link}}" title="Quick view">
                                        Quick view
                                    </a>
                                </div>
                                <div class="wishlist">
                                    <a class="addToWishlist wishlistProd_{{objectID}}" href="#" rel="{{objectID}}" onclick="WishlistCart('wishlist_block_list', 'add', '{{objectID}}', false, 1); return false;">Add to Wishlist </a>
                                </div>
                                <div class="compare col-xs-3">
                                    <a class="add_to_compare" href="{{link}}" data-id-product="{{objectID}}" title="Add to Compare">Add to Compare</a>
                                </div>
                            </div>
                        </div>
                        <div class="right-block">
                            <h5  class="product-name-container">
                                <a class="product-name" href="{{link}}" title="{{{ _highlightResult.name }}}" >
                                    {{{ _highlightResult.name }}}
                                </a>
                            </h5>
                            <div itemscope class="content_price">
                                    <span class="price product-price">
                                        {{currency}}{{price_tax_excl}}
                                    </span>
                                    <meta content="{$currency->iso_code}" />
                            </div>

                            <div class="yotpo bottomLine" 
                            data-appkey=""
                            data-domain=""
                            data-product-id="{{objectID}}"
                            data-product-models=""
                            data-name="{{{ _highlightResult.name }}}" 
                            data-url="{{link}}" 
                            data-image-url="//{{{ image_link_large }}}" 
                            data-bread-crumbs="">
                            </div> 
                            <div class="button-container">
                                    <a class="button ajax_add_to_cart_button btn btn-default" rel="nofollow" title="Add to cart" data-id-product="{{objectID}}">
                                        <span>Add to cart</span>
                                    </a>
                                    <div class="pl-quantity-input-wrapper">
                                        <input type="text" name="qty" class="form-control qtyfield quantity_to_cart_{{objectID}}" value="1"/>
                                        <div class="quantity-input-b-wrapper">
                                            <a href="#" data-field-qty="quantity_to_cart_{{objectID}}" class="transition-300 pl_product_quantity_down">
                                                <span><i class="icon-caret-down"></i></span>
                                            </a>
                                            <a href="#" data-field-qty="quantity_to_cart_{{objectID}}" class="transition-300 pl_product_quantity_up ">
                                                <span><i class="icon-caret-up"></i></span>
                                            </a>
                                        </div>
                                    </div>                      
                                </a>
                            </div>
                        
                        </div>

                    </div><!-- .product-container> -->
                
                </li>
            {{/hits}}
        </ul>
        {{^hits.length}}
        <div class="infos">
            No results found matching "<strong>{{query}}</strong>". <span class="clear">Clear query and filters</span>
        </div>
        {{/hits.length}}
        <div style="clear: both;"></div>
    </div>
</script>

<script type="text/template" id="instant-facets-template">
<div class="facets{{#count}} with_facets{{/count}}">
    <h2 class="title_block">Refine Your Search</h2>
    <div class="block_content">
           <div class="facets-reset"><a href="javascript:void(0)" title="Reset All Filters" style="display:none" id="facets-reset-btn">Reset All Filters</a></div>
    {{#facets}}
    {{#count}}
    <div class="facet">
        <div class="name">
            {{ facet_categorie_name }}
        </div>
        <div class="filters-container">
            {{#sub_facets}}

                {{#type.menu}}
                <div data-tax="{{tax}}" data-name="{{nameattr}}" data-type="menu" class="{{#checked}}checked {{/checked}}sub_facet menu">
                    <input style="display: none;" data-tax="{{tax}}" {{#checked}}checked{{/checked}} data-name="{{nameattr}}" class="facet_value" type="checkbox" />
                    {{name}} {{#print_count}}({{count}}){{/print_count}}
                </div>
                {{/type.menu}}

                {{#type.conjunctive}}
                <div data-name="{{tax}}" data-type="conjunctive" class="{{#checked}}checked {{/checked}}sub_facet conjunctive">
                    <input data-tax="{{tax}}" {{#checked}}checked{{/checked}} data-name="{{nameattr}}" class="facet_value" type="checkbox" />
                    {{name}} ({{count}})
                </div>
                {{/type.conjunctive}}

                {{#type.slider}}
                <div class="algolia-slider algolia-slider-true" data-tax="{{tax}}" data-min="{{min}}" data-max="{{max}}"></div>
                <div class="algolia-slider-info">
                    <div class="min" style="float: left;">{{current_min}}</div>
                    <div class="max" style="float: right;">{{current_max}}</div>
                    <div style="clear: both"></div>
                </div>
                {{/type.slider}}

                {{#type.disjunctive}}
                <div data-name="{{tax}}" data-type="disjunctive" class="{{#checked}}checked {{/checked}}sub_facet disjunctive">
                    <input data-tax="{{tax}}" {{#checked}}checked{{/checked}} data-name="{{nameattr}}" class="facet_value" type="checkbox" />
                    {{name}} ({{count}})
                </div>
                {{/type.disjunctive}}

            {{/sub_facets}}
        </div>
    </div>
    {{/count}}
    {{/facets}}
</div>
</script>

<script type="text/template" id="instant-pagination-template">
<div class="pagination-wrapper{{#facets_count}} with_facets{{/facets_count}}">
    <div class="text-center">
        <ul class="algolia-pagination">
            <a href="#" data-page="{{prev_page}}">
                <li {{^prev_page}}class="disabled"{{/prev_page}}>
                    &laquo;
                </li>
            </a>

            {{#pages}}
            <a href="#" data-page="{{number}}" return false;">
                <li class="{{#current}}active{{/current}}{{#disabled}}disabled{{/disabled}}">
                    {{ number }}
                </li>
            </a>
            {{/pages}}

            <a href="#" data-page="{{next_page}}">
                <li {{^next_page}}class="disabled"{{/next_page}}>
                    &raquo;
                </li>
            </a>
        </ul>
    </div>
</div>
</script>