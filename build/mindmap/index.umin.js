(() => {
    "use strict";
    var e = {
        n: (t) => {
            var n = t && t.__esModule ? () => t.default : () => t;
            return e.d(n, { a: n }), n;
        },
        d: (t, n) => {
            for (var o in n) e.o(n, o) && !e.o(t, o) && Object.defineProperty(t, o, { enumerable: !0, get: n[o] });
        },
        o: (e, t) => Object.prototype.hasOwnProperty.call(e, t),
    };
    const t = window.wp.blocks,
        n = window.wp.i18n,
        o = window.wp.blockEditor,
        a = window.wp.element,
        l = window.wp.components,
        i = window.wp.serverSideRender;
    var r = e.n(i);
    const s = window.ReactJSXRuntime;
    (0, t.registerBlockType)("mindcat/mindmap", {
        edit: function ({ className: e, attributes: t, setAttributes: i, isSelected: d, id: c }) {
            const m = (0, o.useBlockProps)(),
                { size: u, cat: w, count: p, hide_empty: _, max_level: x } = t;
            return (0, s.jsx)("div", {
                className: e,
                ...m,
                children: (0, s.jsxs)(a.Fragment, {
                    children: [
                        d &&
                            (0, s.jsx)(o.InspectorControls, {
                                children: (0, s.jsxs)(l.PanelBody, {
                                    title: (0, n.__)("Display", "mindcat"),
                                    children: [
                                        (0, s.jsx)(l.TextControl, { label: (0, n.__)("Size", "mindcat"), value: u, onChange: (e) => i({ size: e }) }),
                                        (0, s.jsx)(l.TextControl, { label: (0, n.__)("Cat", "mindcat"), value: w, onChange: (e) => i({ cat: e }) }),
                                        (0, s.jsx)(l.TextControl, { label: (0, n.__)("Count", "mindcat"), value: p, onChange: (e) => i({ count: e }) }),
                                        (0, s.jsx)(l.ToggleControl, { label: (0, n.__)("Hide empty", "mindcat"), value: _, onChange: (e) => i({ hide_empty: e }) }),
                                        (0, s.jsx)(l.TextControl, { label: (0, n.__)("Max level", "mindcat"), value: x, onChange: (e) => i({ max_level: e }) }),
                                    ],
                                }),
                            }),
                        (0, s.jsx)(r(), { block: "mindcat/mindmap", attributes: t }),
                    ],
                }),
            });
        },
        save: function ({ attributes: e }) {
            return null;
        },
    });
})();
