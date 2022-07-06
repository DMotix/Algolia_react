jQuery(document).ready(function ($) {


    //unbind default search module
    $('#search_query_top').unbind();



    var my_category_title = $('#columns').find('h1.page-heading');
    var ag_category_content = $('.hook_category_top').html();
    var subcategories = $('#subcategories').html();

    window.traductions = {
        'price_tax_incl_asc' : {
            'en' : "Price - Low to High"
        },
        'price_tax_incl_desc' : {
            'en' : "Price - High to Low"
        },
        'price': {
            'fr': 'Prix',
            'en': 'Price'
        },
        'price_tax_incl': {
            'fr': 'Prix',
            'en': 'Price'
        },
        'categories': {
            'fr': 'Categories',
            'en': 'Categories'
        },
        'products': {
            'fr': 'Produits',
            'en': 'Products'
        },
        'price_asc': {
            'fr': 'Moins cher',
            'en': 'Lowest price first'
        },
        'name_asc': {
            'fr': 'Moins cher',
            'en': 'Name'
        },
        'available_now_asc': {
            'fr': 'Moins cher',
            'en': 'Availability'
        },
        'pageviews_desc': {
            'fr': 'Moins cher',
            'en': 'Popular'
        },
        'sales_desc': {
            'fr': 'Moins cher',
            'en': 'Best Sellers'
        },
        'stock_qty_desc': {
            'fr': 'Moins cher',
            'en': 'In stock'
        },
        'reference_asc': {
            'fr': 'Moins cher',
            'en': 'Reference'
        }
    };

    var autocomplete = true;
    var instant = true;

    if (algoliaSettings.type_of_search.indexOf("autocomplete") !== -1)
    {
        var $autocompleteTemplate = Hogan.compile($('#autocomplete-template').text());

        var hogan_objs = [];
        algoliaSettings.indices.sort(indicesCompare);

        var indices = [];
        for (var i = 0; i < algoliaSettings.indices.length; i++)
            indices.push(algolia_client.initIndex(algoliaSettings.indices[i].index_name));

        for (var i = 0; i < algoliaSettings.indices.length; i++)
        {

            var category_title = traductions[algoliaSettings.indices[i].name] != undefined
            && traductions[algoliaSettings.indices[i].name][algoliaSettings.language] != undefined ?
            traductions[algoliaSettings.indices[i].name][algoliaSettings.language]
            : algoliaSettings.indices[i].name;
            hogan_objs.push({
                source: indices[i].ttAdapter({hitsPerPage: algoliaSettings.indices[i].nbHits}),
                displayKey: 'name',
                templates: {
                    header: '<div class="category">' + category_title + '</div>',
                    suggestion: function (hit) {
                        hit.currency = algoliaSettings.currency;
                        return $autocompleteTemplate.render(hit);
                    }
                }
            });

        }


        hogan_objs.push({
            source: getBrandingHits(),
            displayKey: 'name',
            templates: {
                suggestion: function (hit) {
                    return '<div class="footer"></div>';
                }
            }
        });

        function activateAutocomplete()
        {

            $(algoliaSettings.search_input_selector).each(function (i) {
                $(this).typeahead({hint: false}, hogan_objs);

                $(this).on('typeahead:selected', function (e, item) {
                    autocomplete = false;
                    instant = false;

                    window.location.href = item.link ? item.link : item.url;
                });
            });
        }
        activateAutocomplete();

        function desactivateAutocomplete()
        {
            $(algoliaSettings.search_input_selector).each(function (i) {
                $(this).typeahead('destroy');
            });
        }
    }

    if (algoliaSettings.type_of_search.indexOf("instant") !== -1)
    {

        for (var i = 0; i < algoliaSettings.sorting_indices.length; i++)
        {
            var label = window.traductions != undefined && window.traductions[algoliaSettings.sorting_indices[i].label] != undefined
            && window.traductions[algoliaSettings.sorting_indices[i].label][algoliaSettings.language] != undefined ?
            window.traductions[algoliaSettings.sorting_indices[i].label][algoliaSettings.language]
            : algoliaSettings.sorting_indices[i].label;

            algoliaSettings.sorting_indices[i].label = label;
        }

        /**
         * Variables Initialization
         */

         var old_content         = $(algoliaSettings.instant_jquery_selector).html();

         var resultsTemplate     = Hogan.compile($('#instant-content-template').text());
         var facetsTemplate      = Hogan.compile($('#instant-facets-template').text());
         var paginationTemplate  = Hogan.compile($('#instant-pagination-template').text());

         var conjunctive_facets  = [];
         var disjunctive_facets  = [];

         for (var i = 0; i < algoliaSettings.facets.length; i++)
         {

            if (algoliaSettings.facets[i].type == "conjunctive")
                conjunctive_facets.push(algoliaSettings.facets[i].tax);

            if (algoliaSettings.facets[i].type == "disjunctive")
                disjunctive_facets.push(algoliaSettings.facets[i].tax);

            if (algoliaSettings.facets[i].type == "slider")
                disjunctive_facets.push(algoliaSettings.facets[i].tax);

            if (algoliaSettings.facets[i].type == "menu")
                disjunctive_facets.push(algoliaSettings.facets[i].tax);
        }


        if(typeof(valid_facets) != 'undefined' && valid_facets.length > 0)
        {
            for (var i = 0; i < conjunctive_facets.length; i++)
            {
                if(valid_facets.indexOf(conjunctive_facets[i]) == -1) // not found, remove it
                    conjunctive_facets[i] = '';
            }
            for (var i = 0; i < disjunctive_facets.length; i++)
            {
                if(valid_facets.indexOf(disjunctive_facets[i]) == -1) // not found, remove it
                    disjunctive_facets[i] = '';
            }
        }


        // TODO get rid of facets not ticked for a category, if any


        algoliaSettings.facets = algoliaSettings.facets.sort(facetsCompare);


        helper = algoliasearchHelper(algolia_client, algoliaSettings.index_name + 'all_' + algoliaSettings.language, {
            facets: conjunctive_facets,
            disjunctiveFacets: disjunctive_facets,
            hitsPerPage: algoliaSettings.number_by_page
        });

        engine.setHelper(helper);

        /**
         * Functions
         */

         function performQueries(push_state)
         {
            engine.helper.search(engine.helper.state.query, searchCallback);

            engine.updateUrl(push_state);
        }

        function searchCallback(content)
        {
            var html_content = "";

            html_content += "<div id='algolia_instant_selector' class='row'>";

            var facets = [];
            var pages = [];
            var facets_html = '';

            if (content.hits.length > 0)
            {
                facets = engine.getFacets(content);
                pages = engine.getPages(content);

                for (var i = 0; i < facets.length; i++)
                {
                    if(facets[i].facet_categorie_name == 'manufacturer')
                        facets[i].facet_categorie_name = 'brand';
                    else if (facets[i].facet_categorie_name.toLowerCase() == 'categories')
                    {
                        facets.splice(i, 1);
                    }
                }

                if (!algoliaSettings.use_left_column) {
                    if ($('#left_column').length == 0) {
                        $('#center_column').attr('class', 'center_column col-xs-12 col-xs-6 col-sm-9 col-md-9');
                        $(algoliaSettings.instant_jquery_selector).parent('div').eq(0).prepend($('<div>').attr({id:'left_column', class:'column col-xxs-12 col-xs-6 col-sm-3 col-md-3'}).append($('<div>').attr({id:'facets-container'})));
                    }
                }

                facets_html = engine.getHtmlForFacets(facetsTemplate, facets);
                $('#facets-container').html(facets_html);
            }

            /*
            if (content.hits.length > 0)
            {
                var pagination_html = engine.getHtmlForPagination(paginationTemplate, content, pages, facets);
                html_content += pagination_html;
            }
            */
            // MARK: show/hide the slider
            if (typeof($('#owl_carousel_container_1')) !== 'undefined') {
                if (content.query == "") {
                    $('#owl_carousel_container_1').show();
                } else {
                    $('#owl_carousel_container_1').hide();
                }
            }
            
            var date_now = new Date();
            for (var i = 0; i < content.hits.length; i++)
            {


                content.hits[i]._highlightResult.name = content.hits[i].name.replace('"', '\'\'');
                content.hits[i]._highlightResult.link = content.hits[i].link;

                content.hits[i].price = parseFloat(content.hits[i].price).toFixed(2)
                if(content.hits[i].name.length > 40)
                    content.hits[i].name = content.hits[i].name.substr(0,40) + '...';
                var date_product = new Date(content.hits[i].date_add);


                var days_diff =  parseInt((date_now.getTime()-date_product.getTime())/(24*3600*1000));
                if(days_diff <= 30)
                    content.hits[i].newproduct = 1;

            } // nemo

            html_content += engine.getHtmlForResults(resultsTemplate, content, facets);

            if (content.hits.length > 0)
            {
                var pagination_html = engine.getHtmlForPagination(paginationTemplate, content, pages, facets);
                html_content += pagination_html;
            }


            html_content += "</div>";

            // if(typeof(algolia_results_url) != 'undefined' && !window.location.hash.length  && typeof(replace_center) == 'undefined')
            // {

            //  $('#left_column').html($("<div id='algolia_instant_selector'>").append($(facets_html).removeClass('col-sm-3 col-xs-12').css('float','none')));

            // } else // only replace the left column
            // {
            // Process it to grab the right category title


            if(my_category_title.length > 0)
            {


                if(typeof(ag_category_content) != 'undefined' && ag_category_content.length > 0)
                    cat_title = ag_category_content + '<h1 class="page-heading product-listing">' + ($(algoliaSettings.search_input_selector).val()?  $(algoliaSettings.search_input_selector).val() : $(my_category_title[0]).html()) + '</h1>';
                else cat_title = '<h1 class="page-heading product-listing">' + ($(algoliaSettings.search_input_selector).val()?  $(algoliaSettings.search_input_selector).val() : $(my_category_title[0]).html()) + '</h1>';


                if(typeof(subcategories) != 'undefined' && subcategories.length > 0 && $(algoliaSettings.search_input_selector).val().length <= 0)
                    cat_title =  cat_title + '<div id="subcategories">' + subcategories + '</div>';
                
                
                if($(algoliaSettings.search_input_selector).val())
                    var cat_desc_short = '';
                else var cat_desc_short = $('#category_description_short');

                $(algoliaSettings.instant_jquery_selector).html($(html_content).append(cat_desc_short)).find('.center_column').prepend(cat_title);

                if($('#rcplanetmodelsearch').length > 0)
                {

                    // model search
                    getMakeModel('7021');

                    $('#rc_make').change(function(){
                        $('#rc_model').html('');
                        var newid = $(this).val();
                        getMakeModel(newid);
                    })
                    
                    $('#rc_model').change(function(){
                        var rewrite = $(this).find(':selected').attr('title');
                        var id = $(this).val();
                        $('#makemodelsearchbtn').attr('href','/'+rewrite+'_s/'+id+'.htm');
                    })

                    // end model search 
                }
                

            } else {
                $(algoliaSettings.instant_jquery_selector).html(html_content)
            }

            // }


            

            window.responsiveflag = false;
            responsiveResize(); // nemo


            // nemo reset button
            var standard_nofilter = '#q=[theq]&page=0&refinements=[{"categories"%3A"Shop All "}]&numerics_refinements={}&index_name="all_en"';
            

            

            var current_search = window.location.hash;
            var patt = /q=([a-zA-Z0-9_\-]+)/i;
            var matched = current_search.match(patt);
            var new_link = '';


            

            if(matched != null && matched.length > 0)
            {
                var q = matched[1];
                new_link = standard_nofilter.replace('[theq]', q);

            }

            if(window.location.hash != new_link && window.location.hash.length > 0)
                $('#facets-reset-btn').show();

            $('#facets-reset-btn').click(function() {

                // redirect back to the main category 
                if(typeof(algolia_results_url) != 'undefined')
                {
                    window.location.href = 'http://' + window.location.hostname + window.location.pathname;
                    return -1;
                }
                
                if (new_link.length > 0)
                {
                    window.location.href = new_link;
                    return -1;
                }



                // var new_search = current_search.repla
            });


            updateSliderValues();
            $("input[type='checkbox']:not(.comparator)").uniform();

            
            // bind title clicks to toggle content
            $('.facets .name').unbind('click').bind('click', function ()
            {

                $(this).parent('.facet').find('.filters-container').slideToggle('fast');

            });

            $('#categories_block_left').css('height', 'auto').find('.block_content').slideUp('fast');

            $('#categories_block_left .title_block').unbind('click').on('click', function(e){
                $(this).toggleClass('active').parent().find('.block_content').stop().slideToggle('medium');
            });
            $('.rcplanetcategories .grower').click(function(){
                if($(this).hasClass("CLOSE")){
                    $(this).removeClass("CLOSE").addClass("OPEN");
                }else{
                    $(this).removeClass("OPEN").addClass("CLOSE");
                }
                $(this).parent().find("ul").toggle("fast");
            });

        }

        function activateInstant()
        {
            helper.on('result', searchCallback);
        }

        activateInstant();

        function desactivateInstant()
        {
            helper.removeAllListeners();

            // location.replace('#');


            if(window.location.href.indexOf('address') == -1 && window.location.href.indexOf('login') == -1 && window.location.href.indexOf('quick-order') == -1 && $('body').attr('id') != 'index')
                $(algoliaSettings.instant_jquery_selector).html(old_content);
        }

        /**
         * Custom Facets Types
         */

         custom_facets_types["slider"] = function (engine, content, facet) {

            if (content.getFacetByName(facet.tax) != undefined)
            {
                var min = content.getFacetByName(facet.tax).stats.min;
                var max = content.getFacetByName(facet.tax).stats.max;

                var current_min = engine.helper.state.getNumericRefinement(facet.tax, ">=");
                var current_max = engine.helper.state.getNumericRefinement(facet.tax, "<=");

                if (current_min == undefined)
                    current_min = min;

                if (current_max == undefined)
                    current_max = max;

                var params = {
                    type: {},
                    current_min: Math.floor(current_min),
                    current_max: Math.ceil(current_max),
                    count: min == max ? 0 : 1,
                    min: Math.floor(min),
                    max: Math.ceil(max)
                };

                params.type[facet.type] = true;

                return [params];
            }

            return [];
        };

        custom_facets_types["menu"] = function (engine, content, facet) {

            var data = [];

            var all_count = 0;
            var all_unchecked = true;

            var content_facet = content.getFacetByName(facet.tax);

            if (content_facet == undefined)
                return data;

            for (var key in content_facet.data)
            {
                var checked = engine.helper.isRefined(facet.tax, key);

                all_unchecked = all_unchecked && !checked;

                var nameattr = key;
                var explode = nameattr.split(' /// ');
                var name = explode[explode.length - 1];

                var params = {
                    type: {},
                    checked: checked,
                    nameattr: nameattr,
                    name: name,
                    print_count: true,
                    count: content_facet.data[key]
                };

                all_count += content_facet.data[key];

                params.type[facet.type] = true;

                data.push(params);
            }

            var params = {
                type: {},
                checked: all_unchecked,
                nameattr: 'all',
                name: 'All',
                print_count: false,
                count: all_count
            };

            params.type[facet.type] = true;

            data.unshift(params);

            return data;
        };

        /**
         * Bindings
         */

         $("body").on("click", ".sub_facet.menu", function (e) {

            e.stopImmediatePropagation();

            if ($(this).attr("data-name") == "all")
                engine.helper.state.clearRefinements($(this).attr("data-tax"));

            $(this).find("input[type='checkbox']").each(function (i) {
                $(this).prop("checked", !$(this).prop("checked"));

                if (false == engine.helper.isRefined($(this).attr("data-tax"), $(this).attr("data-name")))
                    engine.helper.state.clearRefinements($(this).attr("data-tax"));

                if ($(this).attr("data-name") != "all")
                    engine.helper.toggleRefine($(this).attr("data-tax"), $(this).attr("data-name"));
            });

            performQueries(true);
        });

         $("body").on("click", ".sub_facet", function () {

            $(this).find("input[type='checkbox']").each(function (i) {
                $(this).prop("checked", !$(this).prop("checked"));

                engine.helper.toggleRefine($(this).attr("data-tax"), $(this).attr("data-name"));
            });

            performQueries(true);
        });


         $("body").on("slide", "", function (event, ui) {
            updateSlideInfos(ui);
        });

         $("body").on("change", "#index_to_use", function () {
            engine.helper.setIndex($(this).val());

            engine.helper.setCurrentPage(0);

            performQueries(true);
        });

         $("body").on("slidechange", ".algolia-slider-true", function (event, ui) {

            var slide_dom = $(ui.handle).closest(".algolia-slider");
            var min = slide_dom.slider("values")[0];
            var max = slide_dom.slider("values")[1];

            if (parseInt(slide_dom.slider("values")[0]) >= parseInt(slide_dom.attr("data-min")))
                engine.helper.addNumericRefinement(slide_dom.attr("data-tax"), ">=", min);
            if (parseInt(slide_dom.slider("values")[1]) <= parseInt(slide_dom.attr("data-max")))
                engine.helper.addNumericRefinement(slide_dom.attr("data-tax"), "<=", max);

            if (parseInt(min) == parseInt(slide_dom.attr("data-min")))
                engine.helper.removeNumericRefinement(slide_dom.attr("data-tax"), ">=");

            if (parseInt(max) == parseInt(slide_dom.attr("data-max")))
                engine.helper.removeNumericRefinement(slide_dom.attr("data-tax"), "<=");

            updateSlideInfos(ui);
            performQueries(true);
        });

         $("body").on("click", ".algolia-pagination a", function (e) {
            e.preventDefault();

            engine.gotoPage($(this).attr("data-page"));
            performQueries(true);


            $("body").scrollTop($('.infos').offset().top);

            return false;
        });

         $('body').on('click', '.clear', function () {
            engine.helper.clearRefinements();
            $(algoliaSettings.search_input_selector).val('').keyup();
        });

         $(algoliaSettings.search_input_selector).keydown(function (e) {
            $(algoliaSettings.search_input_selector).attr('autocomplete', 'off').attr('autocorrect', 'off').attr('spellcheck', 'false').attr('autocapitalize', 'off');
        });



         $(algoliaSettings.search_input_selector).keyup(function (e) {
            e.preventDefault();

            if (instant === false)
                return;


            var $this = $(this);

            engine.helper.setQuery($(this).val());

            $(algoliaSettings.search_input_selector).each(function (i) {
                if ($(this)[0] != $this[0])
                    $(this).val(engine.helper.state.query);
            });

            if (e.keyCode === 27) {

                clearTimeout(history_timeout);

                location.replace('#');

                $(algoliaSettings.instant_jquery_selector).html(old_content);

                return;
            }

            /* Uncomment to clear refinements on keyup */

            engine.helper.clearRefinements('categories');
            // engine.helper.clearNumericRefinements();


            performQueries(false);

            return false;
        });

         function updateSliderValues()
         {
            $(".algolia-slider-true").each(function (i) {
                var min = $(this).attr("data-min");
                var max = $(this).attr("data-max");

                var new_min = engine.helper.state.getNumericRefinement($(this).attr("data-tax"), ">=");
                var new_max = engine.helper.state.getNumericRefinement($(this).attr("data-tax"), "<=");

                if (new_min != undefined)
                    min = new_min;

                if (new_max != undefined)
                    max = new_max;

                $(this).slider({
                    min: parseInt($(this).attr("data-min")),
                    max: parseInt($(this).attr("data-max")),
                    range: true,
                    values: [min, max]
                });
            });
        };

        function updateSlideInfos(ui)
        {
            var infos = $(ui.handle).closest(".algolia-slider").nextAll(".algolia-slider-info");

            infos.find(".min").html(ui.values[0]);
            infos.find(".max").html(ui.values[1]);
        }

        /**
         * Initialization
         */

         engine.getRefinementsFromUrl(searchCallback);
         engine.getRefinementsFromVariable(searchCallback);

         window.addEventListener("popstate", function(e) {
            engine.getRefinementsFromUrl(searchCallback);
        });

         if (algoliaSettings.type_of_search.indexOf("autocomplete") !== -1)
         {
            if (location.hash.length <= 1)
            {

                if(typeof(algolia_results_url) == 'undefined')
                {
                    // desactivateInstant();
                    // instant = false;
                }
                
            }
            else
            {
                autocomplete = false;
                desactivateAutocomplete();
                $(algoliaSettings.search_input_selector+':first').focus();
            }
        }
    }
});