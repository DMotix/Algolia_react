<div class="container p-3" id="search-alert-block">
    <div class="success col-12 w-100 p-4 text-center hidden">
        <i class="fa fa-check-circle-o text-success" aria-hidden="true" style="font-size: 5rem"></i>
        <h2 class="py-3">{l s='Merci !' mod='ads_algolia_front'}</h2>
        <h4>{l s='Votre alerte mail a bien été enregistrée.' mod='ads_algolia_front'}</h4>
        <h4>{l s='Vous pouvez retrouver ou supprimer vos alertes depuis votre espace client.' mod='ads_algolia_front'}</h4>
    </div>
    <div class="modal__body pt-5">
        <h2 class="text-center"><i class="fa fa-bell" aria-hidden="true"></i>{l s='Créer votre alerte mail' mod='ads_algolia_front'}</h2>
        <h4 class="text-center py-2">{l s='Définissez vos critères de recherche et recevez un email d\'alerte si un
véhicule correspond.' mod='ads_algolia_front'}</h4>
        <div class="alert alert-danger hidden" role="alert"></div>
        <form name="alert_vehicle" method="post" action="" role="form" novalidate="novalidate" id="alert_vehicle_form">
            <button type="submit" class="fv-hidden-submit" style="display: none; width: 0px; height: 0px;"></button>

            <div class="row">
                <div class="col-xs-12 col-sm-6">
                    <div class="md-form is_required">
                        <input type="text" class="form-control" name="alert_vehicle[marque]"
                               id="feature.marque" value="">
                        <label for="feature.marque" class="">
                            {l s='Marque' mod='ads_algolia_front'}
                        </label>
                    </div>
                </div>
                <div class="col-xs-12 col-sm-6">
                    <div class="md-form is_required">
                        <input type="text" class="form-control" name="alert_vehicle[modele]"
                               id="feature.modele" value="">
                        <label for="feature.modele" class="">
                            {l s='Modèle' mod='ads_algolia_front'}
                        </label>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-xs-12 col-sm-6">
                    <div class="form-group">
                        <div class="">
                            <select
                                    name="alert_vehicle[energie]"
                                    id="feature.energie"
                            >
                                <option value="">{l s='Energie' mod='ads_algolia_front'}</option>
                                <option value="{l s='Diesel' mod='ads_algolia_front'}">{l s='Diesel' mod='ads_algolia_front'}</option>
                                <option value="{l s='Electrique' mod='ads_algolia_front'}">{l s='Electrique' mod='ads_algolia_front'}</option>
                                <option value="{l s='Essence' mod='ads_algolia_front'}">{l s='Essence' mod='ads_algolia_front'}</option>
                                <option value="{l s='Gaz' mod='ads_algolia_front'}">{l s='Gaz' mod='ads_algolia_front'}</option>
                                <option value="{l s='Hybride' mod='ads_algolia_front'}">{l s='Hybride' mod='ads_algolia_front'}</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="col-xs-12 col-sm-6">
                    <div class="form-group">
                        <div class="">
                            <select
                                    name="alert_vehicle[kilometrage_max]"
                                    id="feature.kilometrage_max"
                            >
                                <option value="">{l s='Kilométrage max.' mod='ads_algolia_front'}</option>
                                <option value="20000">20000</option>
                                <option value="40000">40000</option>
                                <option value="60000">60000</option>
                                <option value="80000">80000</option>
                                <option value="100000">100000</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>
            {assign var="current_year" value=$smarty.now|date_format:"%Y"}
            {assign var="current_year_less20" value=($current_year-20)}
            <div class="row">
                <div class="col-xs-12 col-sm-6">
                    <div class="form-group">
                        <div class="">
                            <select
                                    name="alert_vehicle[annee_max]"
                                    id="feature.annee_max"
                            >
                                <option value="">{l s='Année max.' mod='ads_algolia_front'}</option>
                                {foreach item=i from=$current_year|@range:$current_year_less20}
                                    <option value="{$i}">{$i}</option>
                                {/foreach}
                            </select>
                        </div>
                    </div>
                </div>
                <div class="col-xs-12 col-sm-6">
                    <div class="form-group">
                        <div class="">
                            <select
                                    name="alert_vehicle[prix_ttc_max]"
                                    id="prix_ttc_max"
                            >
                                <option value="">{l s='Prix max.' mod='ads_algolia_front'}</option>
                                <option value="5000">5000</option>
                                <option value="10000">10000</option>
                                <option value="15000">15000</option>
                                <option value="20000">20000</option>
                                <option value="25000">25000</option>
                                <option value="30000">30000</option>
                                <option value="35000">35000</option>
                                <option value="40000">40000</option>
                                <option value="45000">45000</option>
                                <option value="50000">50000</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>
            <div class="text-center">
                <div class="form-group">
                    <button type="submit" id="alert_vehicle_submit" name="alert_vehicle[submit]"
                            class="btn btn-lg btn-primary my-3">{l s='Créer mon alerte' mod='ads_algolia_front'}
                    </button>
                </div>
                {Tools::getRGPDTextFormFooter()}
            </div>
            <input type="hidden" id="alert_vehicle__token" name="_token" class="form-control"
                   value="{$token}"></form>
    </div>
</div>
<script type="text/javascript">
    {literal}
    $(document).ready(function () {
        parent.$.fancybox.update();
    });
    {/literal}
</script>