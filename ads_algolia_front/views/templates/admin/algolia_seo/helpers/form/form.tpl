{extends file="helpers/form/form.tpl"}
{block name="input"}
    {if $input.type == 'multiselect'}
        <script type="text/javascript">
            {literal}
            $(document).ready(function () {
                $("#multiselectcross_links").multiselect({
                    locale: {
                        addAll: 'Tout ajouter',
                        removeAll: 'Tout supprimer',
                        itemsCount: '#{count} éléments sélectionnés',
                        itemsTotal: '#{count} élements au total',
                        busy: 'Veuillez patienter...',
                        errorDataFormat: 'Impossible d\'ajouter cette option, format de donnée inattendu',
                        errorInsertNode: "Il y a eu un problème lors de l\'ajout de l\'élément:\n\n\t[#{key}] => #{value}\n\nOpération annulée.",
                        errorReadonly: 'L\'option #{option} est en lecture seule',
                        errorRequest: 'Désolé! Il semble qu\'il y a eu un problème lors de l\'appel distant. (Type: #{status})',
                        sInputSearch: 'Entrez les premières lettres de votre recherche',
                        sInputShowMore: 'Afficher plus',
                        searchAtStart: true
                    },
                    remoteUrl: admin_agf_ajax_url + "&ajax=true&action=displaySeoSearchOptions&id_seo_origin={/literal}{$input.id_seo_origin}{literal}",
                    remoteLimit: 50,
                    remoteStart: 0,
                    remoteLimitIncrement: 20,
                    triggerOnLiClick: true,
                    displayMore: true,
                    searchDelay: 800
                });
            });
            {/literal}
        </script>
        <select id="multiselect{$input.name}" class="multiselect" multiple="multiple" name="{$input.name}[]">
            {if $input.selected_options && is_array($input.selected_options) && sizeof($input.selected_options)}
                {foreach from=$input.selected_options key='value' item='option'}
                    <option value="{$value|escape:html:'UTF-8'}" selected="selected">
                        {$option|escape:html:'UTF-8'}
                    </option>
                {/foreach}
            {/if}
        </select>
    {else}
        {$smarty.block.parent}
    {/if}
{/block}