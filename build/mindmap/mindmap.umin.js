(() => {
    "use strict";
    jQuery(document).ready(function () {
        var Add_Subterm;
        function mindcat_draw_mindmap(t) {
            var i = jQuery(t).closest(".mindcat"),
                n = (jQuery(t).hasClass("mindcat")) ? i.data("size") : jQuery(t).closest(".mindcat").data("size") / (2 * (jQuery(t).parentsUntil(".mindcat-top","ul").length + 1)),
                e = [],
                root = (jQuery(t).hasClass("mindcat")) ? 0 : 1;
            isNaN(n) && (n = 50), i.height(12 * n);
            var a = i.offset();
            (a.height = i.height()),
                (a.width = i.width()),
                (function i(n, a, r, c) {
                    (e[n.data("id")] = n.children("ul").children("li").length),
                        0 != e[n.data("id")] &&
                            n
                                .children("ul")
                                .children("li")
                                .each(function () {
                                    var s = e[n.data("id")],
                                        d = jQuery(this).parent().parent(),
                                        h = d.offset();
                                    (h.height = d.height()), (h.width = d.width()), (h.centerx = h.left + h.width / 2), (h.centery = h.top + h.height / 2);
                                    var o = jQuery(this).children("div").outerWidth(),
                                        l = jQuery(this).children("div").outerHeight();
                                    jQuery(this)
                                        .children("div")
                                        .css({ left: "-" + o / 2 + "px" }),
                                        0 == root
                                            ? (jQuery(this).offset({ top: h.centery , left: h.centerx }), (root = 1))
                                            : ((l = (360 / s) * r + c),
                                              console.log(l),
                                              jQuery(this)
                                                  .height(a)
                                                  .css({ "-transform": "rotate(" + l + "deg)", "-moz-transform": "rotate(" + l + "deg)", "-webkit-transform": "rotate(" + l + "deg)" }),
                                              jQuery(this)
                                                  .children("div,ul")
                                                  .css({ transform: "rotate(-" + l + "deg)", "-moz-transform": "rotate(-" + l + "deg)", "-webkit-transform": "rotate(-" + l + "deg)" })),
                                        r++,
                                        i(jQuery(this), a / 2, 0, 8 * s + c);
                                });
                })(jQuery(t), 10 * n, 0, 90 * Math.random());
        }
        function mindcat_rename_term(t) {
            jQuery(t).click(function () {
                var t = jQuery(this),
                    e = jQuery(t).prevAll("span").not(".dashicons"),
                    input = jQuery(t).prevAll("input");
                jQuery(t).hide(),
                    jQuery(e).hide(),
                    jQuery(t).siblings(".dashicons-plus-alt").hide(),
                    jQuery(t).siblings(".mindcat-remove.dashicons-dismiss").hide(),
                    jQuery(t).siblings(".dashicons-yes-alt").show(),
                    jQuery(t).siblings(".mindcat-cancel.dashicons-dismiss").show(),
                    jQuery(input).attr("type","text").focus();
            });
        }
        function mindcat_save_changes(t) {
            jQuery(t).click(function () {
                var t = jQuery(this),
                    e = jQuery(t).prevAll("span").not(".dashicons"),
                    input = jQuery(t).prevAll("input");
                jQuery(t).hide(),
                    jQuery(e).html(jQuery(input).val()).show(),
                    jQuery(t).siblings(".dashicons-welcome-write-blog").show(),
                    jQuery(t).siblings(".dashicons-plus-alt").show(),
                    jQuery(t).siblings(".mindcat-remove.dashicons-dismiss").show(),
                    jQuery(t).siblings(".mindcat-cancel.dashicons-dismiss").hide(),
                    jQuery(input).attr("type","hidden");
            });
        }
        function mindcat_cancel_changes(t) {
            jQuery(t).click(function () {
                var t = jQuery(this),
                    e = jQuery(t).prevAll("span").not(".dashicons"),
                    input = jQuery(t).prevAll("input");
                jQuery(t).hide(),
                    jQuery(e).show(),
                    jQuery(t).siblings(".dashicons-welcome-write-blog").show(),
                    jQuery(t).siblings(".dashicons-plus-alt").show(),
                    jQuery(t).siblings(".mindcat-remove.dashicons-dismiss").show(),
                    jQuery(t).siblings(".dashicons-yes-alt").hide(),
                    jQuery(input).attr("type","hidden").val(jQuery(e).html());
            });
        }
        function mindcat_add_subterm(t) {
            jQuery(t).click(function() {
                Add_Subterm = jQuery(this);
                jQuery(this).closest(".mindcat").find(".mindcat-layer").show();
            });
        }
        function mindcat_save_subterm(t) {
            jQuery(t).click(function() {
                var value = jQuery(this).siblings("input").val();
                if (value.length > 0) {

                    // Append new subterm
                    var mindcat = jQuery(Add_Subterm).closest(".mindcat"),
                        data_id = 0,
                        subterm = '<span>' + value + '</span>'
                            + '<input type="hidden" value="' + value + '" />'
                            + '<span class="dashicons dashicons-yes-alt" title="' + Mindmap.save + '"></span>'
                            + '<span class="mindcat-cancel dashicons dashicons-dismiss" title="' + Mindmap.cancel + '"></span>'
                            + '<span class="dashicons dashicons-welcome-write-blog" title="' + Mindmap.rename + '"></span>'
                            + '<span class="dashicons dashicons-plus-alt" title="' + Mindmap.add + '"></span>'
                            + '<span class="mindcat-remove dashicons dashicons-dismiss" title="' + Mindmap.remove + '"></span>',
                        siblings = jQuery(Add_Subterm).parent().siblings("ul"),
                        // height = jQuery(mindcat).data("size") * 10 / (2 * (jQuery(Add_Subterm).parentsUntil(".mindcat-top","ul").length + 1)),
                        append,
                        li = jQuery(Add_Subterm).closest("li");
                    jQuery(mindcat).find("li").each(function(){
                        var id = parseInt(jQuery(this).attr("data-id"));
                        if (!isNaN(id) && id >= data_id) data_id = id + 1;
                    });
                    if (!siblings.length) {
                        var style = jQuery(Add_Subterm).parent().attr("style").split(';'),
                            transform = '';
                        if (style.length > 0) {
                            style.forEach(function (value) {
                                if (value.match("transform"))
                                    transform = value;
                            });
                        }
                        // append = jQuery(Add_Subterm).parent().parent().append('<ul><li class="mindcat_child" style="height:' + height + 'px"><div>' + subterm + '</div></li></ul>');
                        append = jQuery(Add_Subterm).parent().parent().append('<ul style="' + transform + '"><li class="mindcat_child" data-id="' + data_id + '"><div>' + subterm + '</div></li></ul>');
                    } else {
                        // append = jQuery(siblings).append('<li class="mindcat_child" style="height:' + height + 'px"><div>' + subterm + '</div></li>');
                        append = jQuery(siblings).append('<li class="mindcat_child" data-id="' + data_id + '"><div>' + subterm + '</div></li>');
                    }
                    mindcat_draw_mindmap(li);

                    // Attach click events
                    jQuery(append).find(".dashicons-welcome-write-blog").click(function(){mindcat_rename_term(this)}),
                    jQuery(append).find(".dashicons-yes-alt").click(function(){mindcat_save_changes(this)}),
                    jQuery(append).find(".mindcat-cancel.dashicons-dismiss").click(function(){mindcat_cancel_changes(this)}),
                    jQuery(append).find(".dashicons-plus-alt").click(function(){mindcat_add_subterm(this)}),
                    jQuery(append).find(".mindcat-remove.dashicons-dismiss").click(function(){mindcat_remove_term(this)});

                    // Return to standard view
                    jQuery(Add_Subterm).siblings(".mindcat-remove").detach(),
                    jQuery(mindcat).find(".mindcat-layer").hide();

                }
            });
            
        }
        function mindcat_cancel_subterm(t) {
            jQuery(t).click(function() {
                jQuery(this).closest(".mindcat").find(".mindcat-layer").hide();
            });
        }
        function mindcat_remove_term(t) {
            jQuery(t).click(function () {
                var t = jQuery(this),
                    e = jQuery(t).closest("li.mindcat_child");
                if (!jQuery(e).siblings().length) {
                    var parent = jQuery(e).parent().closest("li.mindcat_child");
                    var anchor = jQuery(parent).find("div");
                    jQuery(anchor).append('<span class="mindcat-remove dashicons dashicons-dismiss" title="' + Mindmap.remove + '"></span>');
                    jQuery(anchor).children(".mindcat-remove.dashicons-dismiss").click(function(){mindcat_remove_term(this)});
                }
                if (jQuery(e).length) {
                    jQuery(e).detach();
                }
            });
        }

        // Initiate mindmaps
        jQuery(".mindcat").each(function(){mindcat_draw_mindmap(this)}),
        jQuery(".mindcat-color-field").length > 0 && jQuery(".mindcat-color-field").wpColorPicker(),

        // Attach mindmap options
        jQuery(".mindcat li > div .dashicons-welcome-write-blog").each(function(){mindcat_rename_term(this)}),
        jQuery(".mindcat li > div .dashicons-yes-alt").each(function(){mindcat_save_changes(this)}),
        jQuery(".mindcat li > div .mindcat-cancel.dashicons-dismiss").each(function(){mindcat_cancel_changes(this)}),
        jQuery(".mindcat li > div .dashicons-plus-alt").each(function(){mindcat_add_subterm(this)}),
        jQuery(".mindcat-layer .dashicons-yes-alt").each(function(){mindcat_save_subterm(this)}),
        jQuery(".mindcat-layer .dashicons-dismiss").each(function(){mindcat_cancel_subterm(this)}),
        jQuery(".mindcat li > div .mindcat-remove.dashicons-dismiss").each(function(){mindcat_remove_term(this)}),

        // Attach general options
        jQuery(".mindcat-options .dashicons-fullscreen-alt").each(function () {
            jQuery(this).click(function () {
                var t = jQuery(this),
                    e = jQuery(t).closest(".mindcat-wrapper").clone(!0);
                jQuery(e).addClass("mindcat-wrapper-fullscreen"),
                    jQuery(e).find(".mindcat-options").addClass("mindcat-options-fullscreen"),
                    jQuery(e).find(".dashicons-fullscreen-alt").hide(),
                    jQuery(e).find(".dashicons-fullscreen-exit-alt").css("display","inline-block"),
                    jQuery(e).prependTo("body");
            });
        }),
        jQuery(".mindcat-options .dashicons-fullscreen-exit-alt").each(function () {
            jQuery(this).click(function () {
                var t = jQuery(this);
                jQuery(t).closest(".mindcat-wrapper").detach();
            });
        });

    });
})();
