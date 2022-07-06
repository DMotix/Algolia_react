$(document).ready(function () {
    if ($("#getdisjunctiveFacetsRefinements").length > 0) {
        $("#getdisjunctiveFacetsRefinements").on('click', function (e) {
            e.preventDefault();
            toastr.options = {
                "preventDuplicates": true
            };
            let params = new Object();
            params.aglDFR = instantsearchInstance.helper.state.disjunctiveFacetsRefinements;
            params.aglNR = instantsearchInstance.helper.state.numericRefinements;
            let myObject = {'task': 'serializeObject', 'value': params};
            $.ajax({
                url: ads_gs_path,
                async: false,
                method: "POST",
                data: {
                    ajax: 1,
                    parameters: JSON.stringify(myObject)
                },
                success: function (result) {
                    agl_copy(result);
                }
            });
        })
    }
});

function agl_copy(someText) {
    let clipboard = new ClipboardJS('.btn-copy-seo', {
        text: function () {
            return someText;
        }
    });
    toastr.success('Copiée avec succès', 'Recherche');
}