<a href="{$href|escape:'html':'UTF-8'}" title="{$action|escape:'html':'UTF-8'}"{if isset($name)} name="{$name|escape:'html':'UTF-8'}"{/if} class="default" target="_blank">
    <i class="icon-AdminParentLocalization"></i> {$action|escape:'html':'UTF-8'}
</a>
<script src="https://cdnjs.cloudflare.com/ajax/libs/clipboard.js/1.5.10/clipboard.min.js"></script>
<script type="text/javascript">
    new Clipboard('[data-clipboard-text]');
</script>
<a href="#" class="btn-copy-to-clipboard" data-clipboard-text="{$href|escape:'html':'UTF-8'}">
    <i class="icon-copy"></i> Copier le lien
</a>