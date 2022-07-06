<div class="form-group">

    <lavel class="control-label col-lg-3">Faceting Options</lavel>

    <div class="col-lg-5">

        <table class="table">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Active</th>
                </tr>
            </thead>
            <tbody>
                {foreach from=$facets item=facet}
                    <tr>
                        <th>{$facet.name}</th>
                        <th><input type="checkbox" name="facets[{$facet.name}]" value="1" {if isset($facet.active)}checked="checked"{/if}></th>
                    </tr>
                {/foreach}
            </tbody>
        </table>
    </div>

</div>

