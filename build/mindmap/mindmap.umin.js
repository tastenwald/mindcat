(() => {
    "use strict";
    var t = 0,
        e = [];
    jQuery(document).ready(function () {
        var Add_Subterm;
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

                    // Build new subterm
                    var subterm = '<span>' + value + '</span>'
                        + '<input type="hidden" value="' + value + '" />'
                        + '<span class="dashicons dashicons-yes-alt" title="' + Mindmap.save + '"></span>'
                        + '<span class="mindcat-cancel dashicons dashicons-dismiss" title="' + Mindmap.cancel + '"></span>'
                        + '<span class="dashicons dashicons-welcome-write-blog" title="' + Mindmap.rename + '"></span>'
                        + '<span class="dashicons dashicons-plus-alt" title="' + Mindmap.add + '"></span>'
                        + '<span class="mindcat-remove dashicons dashicons-dismiss" title="' + Mindmap.remove + '"></span>';
                    var siblings = jQuery(Add_Subterm).parent().siblings("ul"),
                        height = jQuery(Add_Subterm).closest(".mindcat").data("size") * 10 / (2 * (jQuery(Add_Subterm).parentsUntil(".mindcat-top","ul").length + 1)),
                        append;
                    if (!siblings.length)  {
                        var style = jQuery(Add_Subterm).parent().attr("style").split(';');
                        append = jQuery(Add_Subterm).parent().parent().append('<ul style="' + style[3].trim() + '"><li class="mindcat_child" style="height:' + height + 'px"><a>' + subterm + '</a></li></ul>');
                        siblings = jQuery(Add_Subterm).parent().siblings("ul");
                    } else {
                        append = jQuery(siblings).append('<li class="mindcat_child" style="height:' + height + 'px"><a>' + subterm + '</a></li>');
                    }
                    var count = jQuery(siblings).children("li").length,
                        random = 90 * Math.random();
                    jQuery(siblings).children("li").each(function (index) {
                        var d = jQuery(this).parent().parent(),
                            h = d.offset();
                        (h.height = d.height()), (h.width = d.width()), (h.centerx = h.left + h.width / 2), (h.centery = h.top + h.height / 2);
                        var o = jQuery(this).children("a").outerWidth(),
                            l = (360 / count) * index + random;
                        jQuery(this).children("a").css({ left: "-" + o / 2 + "px" }),
                            console.log(l),
                            jQuery(this)
                                .css({ "-transform": "rotate(" + l + "deg)", "-moz-transform": "rotate(" + l + "deg)", "-webkit-transform": "rotate(" + l + "deg)" }),
                            jQuery(this)
                                .children("a")
                                .css({ transform: "rotate(-" + l + "deg)", "-moz-transform": "rotate(-" + l + "deg)", "-webkit-transform": "rotate(-" + l + "deg)" });
                    });

                    // Attach click events
                    jQuery(append).find(".dashicons-welcome-write-blog").click(function(){mindcat_rename_term(this)}),
                    jQuery(append).find(".dashicons-yes-alt").click(function(){mindcat_save_changes(this)}),
                    jQuery(append).find(".mindcat-cancel.dashicons-dismiss").click(function(){mindcat_cancel_changes(this)}),
                    jQuery(append).find(".dashicons-plus-alt").click(function(){mindcat_add_subterm(this)}),
                    jQuery(append).find(".mindcat-remove.dashicons-dismiss").click(function(){mindcat_remove_term(this)});

                    // Return to standard view
                    jQuery(Add_Subterm).siblings(".mindcat-remove").detach(),
                    jQuery(this).closest(".mindcat").find(".mindcat-layer").hide();

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
                    var anchor = jQuery(parent).find("a");
                    jQuery(anchor).append('<span class="mindcat-remove dashicons dashicons-dismiss" title="' + Mindmap.remove + '"></span>');
                    jQuery(anchor).children(".mindcat-remove.dashicons-dismiss").click(function(){mindcat_remove_term(this)});
                }
                if (jQuery(e).length) {
                    jQuery(e).detach();
                }
            });
        }

        // Initiate mindmaps
        jQuery(".mindcat").each(function () {
            var i = jQuery(this),
                n = i.data("size"),
                t = 0;
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
                                    var o = jQuery(this).children("a").outerWidth(),
                                        l = jQuery(this).children("a").outerHeight();
                                    jQuery(this)
                                        .children("a")
                                        .css({ left: "-" + o / 2 + "px" }),
                                        0 == t
                                            ? (jQuery(this).offset({ top: h.centery, left: h.centerx }), (t = 1))
                                            : ((l = (360 / s) * r + c),
                                              console.log(l),
                                              jQuery(this)
                                                  .height(a)
                                                  .css({ "-transform": "rotate(" + l + "deg)", "-moz-transform": "rotate(" + l + "deg)", "-webkit-transform": "rotate(" + l + "deg)" }),
                                              jQuery(this)
                                                  .children("a,ul")
                                                  .css({ transform: "rotate(-" + l + "deg)", "-moz-transform": "rotate(-" + l + "deg)", "-webkit-transform": "rotate(-" + l + "deg)" })),
                                        r++,
                                        i(jQuery(this), a / 2, 0, 8 * s + c);
                                });
                })(jQuery(this), 10 * n, 0, 90 * Math.random());
        }),
        jQuery(".mindcat-color-field").length > 0 && jQuery(".mindcat-color-field").wpColorPicker(),

        // Mindmap options
        jQuery(".mindcat a .dashicons-welcome-write-blog").each(function(){mindcat_rename_term(this)}),
        jQuery(".mindcat a .dashicons-yes-alt").each(function(){mindcat_save_changes(this)}),
        jQuery(".mindcat a .mindcat-cancel.dashicons-dismiss").each(function(){mindcat_cancel_changes(this)}),
        jQuery(".mindcat a .dashicons-plus-alt").each(function(){mindcat_add_subterm(this)}),
        jQuery(".mindcat-layer .dashicons-yes-alt").each(function(){mindcat_save_subterm(this)}),
        jQuery(".mindcat-layer .dashicons-dismiss").each(function(){mindcat_cancel_subterm(this)}),
        jQuery(".mindcat a .mindcat-remove.dashicons-dismiss").each(function(){mindcat_remove_term(this)}),

        // General options
        jQuery(".mindcat-options .dashicons-fullscreen-alt").each(function () {
            jQuery(this).click(function () {
                var t = jQuery(this),
                    e = jQuery(t).closest(".mindcat-wrapper").clone(!0);
                jQuery(e).addClass("mindcat-wrapper-fullscreen"),
                    jQuery(e).find(".mindcat-options").addClass("mindcat-options-fullscreen"),
                    jQuery(e).find(".dashicons-fullscreen-alt").hide(),
                    jQuery(e).find(".dashicons-fullscreen-exit-alt").css( "display", "inline-block" ),
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
