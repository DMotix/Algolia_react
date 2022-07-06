$(document).ready(function () {
    let pageSeoDelim = "/";
    $("input.tagify").each(function () {
        let outputDelim = ";";
        if ($(this).hasClass("delim-slash"))
            outputDelim = "/";
        if ($(".tagify").length > 0)
            tagify = $(this).tagify({
                delimiters: [13, 59],
                addTagPrompt: "Ajouter une valeur et appuyer sur entrée",
                outputDelimiter: outputDelim
            });
    });
    $("form.AdminAlgoliaSeoMassive, form.AdminAlgoliaSeo").submit(function () {
        var myform = $(this);
        $("input.tagify").each(function () {
            if ($(this).length > 0)
                $(this).val($(this).tagify("serialize"));
        })
    });

    //Init link SEO Page
    let pageSeoTerms = $(".seo-page-url").val();
    let urlSeo = adsseolandingagl + pageSeoDelim + pageSeoTerms;
    $(".urlPageSeo").html(urlSeo);
    var $link = $("<a>", {id: "externalUrlSeo"});
    $link.attr("href", urlSeo).attr("target", "_blank").html("<i class='icon-external-link-square'></i>");
    $(".urlPageSeo").append("&nbsp;");
    $(".urlPageSeo").append($link);

    //Paste code criterias
    $("body.adminalgoliaseo input[name='criteria']").on("click", function () {
        $(this).select();
    }).on("change", function () {
        //Reload selected-filters box
        $.ajax({
            type: 'POST',
            url: admin_algolia_ajax_url,
            data: {
                controller: 'AdminAlgoliaSeo',
                action: 'getHumanCriteria',
                ajax: true,
                value: $("input[name='criteria']").val()
            },
            success: function (data) {
                $(".selected-filters").html(data);
            }
        });
    });

    //Copy criterias
    $(document).on('click', '.agl-short-code', function () {
        $(this).select();
        document.execCommand("copy");
        $(this).next().addClass('copied');
        setTimeout(function () {
            $('.copied').removeClass('copied');
        }, 2000);
    });

});