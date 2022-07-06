{*
* 2007-2016 PrestaShop
*
* NOTICE OF LICENSE
*
* This source file is subject to the Academic Free License (AFL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/afl-3.0.php
* If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email
* to license@prestashop.com so we can send you a copy immediately.
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade PrestaShop to newer
* versions in the future. If you wish to customize PrestaShop for your
* needs please refer to http://www.prestashop.com for more information.
*
* @author    PrestaShop SA <contact@prestashop.com>
* @copyright 2007-2016 PrestaShop SA
* @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
* International Registered Trademark & Property of PrestaShop SA
*}

<script type="text/javascript">
    $('document').ready(function () {
        $('a[rel^=ajax_id_searchalert_]').click(function () {
            var id = $(this).attr('rel').replace('ajax_id_searchalert_', '');
            var parent = $(this).parent().parent();
            $.ajax({
                url: "{$link->getModuleLink('ads_algolia_front', 'actions', ['process' => 'remove'])|addslashes}",
                type: "POST",
                data: {
                    'id_search_alert': id
                },
                success: function (result) {
                    if (result == '1') {
                        toastr.success("{l s='Votre alerte a bien été supprimée.'}");
                        parent.fadeOut("normal", function () {
                            parent.remove();
                        });
                    }
                    else {
                        toastr.error("{l s='Une erreur est survenue.'}");
                    }
                }
            });
        });
    });
</script>

{capture name=path}
    <li class="breadcrumb-item"><i class="fa fa-angle-right" aria-hidden="true"></i></li><li class="breadcrumb-item" itemprop="itemListElement" itemscope itemtype="http://schema.org/ListItem"><a class="black-text" itemprop="item" href="{$link->getPageLink('my-account', true)|escape:'html':'UTF-8'}"><span itemprop="name">{l s='Mon compte'}</span></a></li>
    <li class="breadcrumb-item"><i class="fa fa-angle-right" aria-hidden="true"></i></li><li class="breadcrumb-item" itemprop="itemListElement" itemscope itemtype="http://schema.org/ListItem"><span class="black-text" itemprop="name">{l s='Mes alertes' mod='ads_algolia_front'}</span></li>
{/capture}

<div id="searchalerts_block_account">
    <h2>{l s='Mes alertes' mod='ads_algolia_front'}</h2>
    <a href="#" class="quick-view btn btn-sm" rel="{$link->getModuleLink('ads_algolia_front', 'adsalertagl')}">
        <i class="fa fa-bell mr-2" aria-hidden="true"></i>
        {l s='Ajouter une alerte' mod='ads_algolia_front'}
    </a>
    {if $searchAlerts}
    <div class="col-md-8 p-0 mt-4">
        <ul class="list-group list-alerts">
            {foreach from=$searchAlerts item=searchAlert name=searchAlerts}
                <li class="list-group-item list-alert clearfix">
                    <div class="list-alert__item__content">
                        <a href="#" class="btn-close"
                           title="{l s='Supprimer' mod='ads_algolia_front'}"
                           onclick="return confirm('{l s="Voulez-vous vraiment supprimer cette alerte ?" mod="ads_algolia_front"}');"
                           rel="ajax_id_searchalert_{$searchAlert["id_ads_af_search_alert"]}"><i
                                    class="fa fa-times-circle" aria-hidden="true"></i>
                        </a>
                        <div class="clearfix">
                            <span class="h5 my-3"><i class="fa fa-bell-o" aria-hidden="true"></i>
                                {l s='Alerte' mod='ads_algolia_front'} n°{$smarty.foreach.searchAlerts.iteration}</span>
                        </div>
                        <span class="">{l s='Créée le' mod='ads_algolia_front'} {$searchAlert["date_add"]|date_format:"%d/%m/%Y"}</span><br>
                        <div class="">
                            <span>{l s='Critères' mod='ads_algolia_front'} :</span>
                            {if $searchAlert["marque"]}
                                <span class="chip badge-light">{$searchAlert["marque"]}</span>
                            {/if}
                            {if $searchAlert["modele"]}
                                <span class="chip badge-light">{$searchAlert["modele"]}</span>
                            {/if}
                            {if $searchAlert["energie"]}
                                <span class="chip badge-light">{$searchAlert["energie"]}</span>
                            {/if}
                            {if $searchAlert["kilometrage_max"]}
                                <span class="chip badge-light">{$searchAlert["kilometrage_max"]|number_format:0:'.':' '|cat:' Km'} {l s='max.' mod='ads_algolia_front'}</span>
                            {/if}
                            {if $searchAlert["annee_max"]}
                                <span class="chip badge-light">{$searchAlert["annee_max"]} {l s='max.' mod='ads_algolia_front'}</span>
                            {/if}
                            {if $searchAlert["prix_ttc_max"]}
                                <span class="chip badge-light">{convertPrice price=$searchAlert["prix_ttc_max"]|floatval} {l s='max.' mod='ads_algolia_front'}</span>
                            {/if}
                        </div>
                    </div>
                </li>
            {/foreach}
        </ul>
    </div>
</div>
{else}
    <p class="alert alert-warning mt-4">{l s='Aucune(s) alerte(s) enregistrée(s) pour le moment.' mod='ads_algolia_front'}</p>
{/if}

<ul class="footer_links clearfix mb-0 list-unstyled d-flex flex-wrap align-items-stretch justify-content-center justify-content-md-start">
    <li>
        <a class="btn" href="{$link->getPageLink('my-account', true)|escape:'html'}" title="{l s='Retour à votre compte' mod='ads_algolia_front'}" rel="nofollow">
            <i class="fa fa-user mr-2"></i><span class="d-inline d-md-none">{l s='Retour'}</span><span class="d-none d-md-inline">{l s='Retour à votre compte' mod='ads_algolia_front'}</span>
        </a>
    </li>
</ul>