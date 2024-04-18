(function (_, $) {
    $.ceEvent('on', 'ce.commoninit', function (context) {
        // init
        $(".cm-image-gallery-malma").each(function() {
            const total = $(this).find(".owl-controls .owl-pagination >").length;

            if (total) {
                let items = [];
                for (let i=0; i < total; i++) {
                    items.push($("<div/>", {
                        "class" : "ty-malma__item cm-malma__item",
                    }));
                }

                let malmaWrap = $("<div/>", {
                    "class" : "ty-malma__wrap",
                    "html" : items
                });

                $(this).find(".owl-wrapper-outer").append(malmaWrap);
            }
        });

        // hover
        $(".cm-malma__item").hover(function() {
            const index = $(this).index();
            $(this).parents(".cm-image-gallery-malma").find(".owl-controls .owl-pagination .owl-page").eq(index).trigger("mouseup");
        });
        $(".cm-malma__item").click(function() {
            window.location.href = $(this).parents(".cm-image-gallery-malma").find("a").first().attr('href');
        });
    });
})(Tygh, Tygh.$);
