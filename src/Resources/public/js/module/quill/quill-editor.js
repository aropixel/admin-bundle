/* ------------------------------------------------------------------------------
*
*  # Admin Quill editor
*
*  Gathers the whole configuration of the WYSIWYG editor:
*   - toolbars (defaults + custom ones via window.aropixelQuillToolbars)
*   - "image" and "file" buttons wired to the media libraries
*   - soft line break (<br>) with Shift + Enter
*   - clean paste (only the structuring formats are kept)
*   - HTML source editing (the <> button swaps the rendering for the code)
*   - link target choice ("New tab" checkbox in the link tooltip)
*   - translated tooltips on the toolbar controls
*   - translation between the stored HTML (<ul>, no interface nodes) and
*     Quill 2's internal representation (<ol><li data-list="bullet">, .ql-ui)
*   - preservation of the HTML Quill does not know (<div>, <button>, <hr>…),
*     see html-preservation.js
*
* ---------------------------------------------------------------------------- */

import { registerHtmlBlots, prepareHtml, restoreHtml, exitHtmlBlock, preventContentActions, BLOCK_CLASS, EMBED_SELECTOR } from './html-preservation.js';

/**
 * Formats kept on paste.
 * Can be overridden with window.aropixelQuillPasteFormats.
 */
const DEFAULT_PASTE_FORMATS = [
    'bold', 'italic', 'underline', 'strike',
    'link', 'list', 'indent', 'header', 'blockquote',
    'code', 'code-block', 'script', 'align',
    'softbreak', 'image', 'video',
];

const DEFAULT_TOOLBARS = {
    'full': [
        [{ 'header': [1, 2, 3, 4, 5, 6, false] }],
        ['bold', 'italic', 'underline', 'strike'],
        ['blockquote', 'code-block'],
        [{ 'list': 'ordered'}, { 'list': 'bullet' }],
        [{ 'script': 'sub'}, { 'script': 'super' }],
        [{ 'size': ['small', false, 'large', 'huge'] }],
        [{ 'color': [] }, { 'background': [] }],
        [{ 'align': [] }],
        ['clean'],
        ['link', 'image', 'video', 'file']
    ],
    'simple': [
        ['bold', 'italic', 'underline'],
        [{ 'list': 'ordered'}, { 'list': 'bullet' }],
        ['link', 'clean']
    ]
};

/**
 * English fallback tooltip labels, keyed by CSS selector.
 * Labels in the admin locale come through window.aroQuillI18n (rendered by
 * base.html.twig with |trans, like aroDialogI18n); the integrator override
 * goes through window.aropixelQuillTooltips and wins over everything.
 */
const DEFAULT_TOOLTIPS = {
    'button.ql-bold': 'Bold',
    'button.ql-italic': 'Italic',
    'button.ql-underline': 'Underline',
    'button.ql-strike': 'Strikethrough',
    'button.ql-blockquote': 'Quote',
    'button.ql-code-block': 'Code block',
    'button.ql-list[value="ordered"]': 'Numbered list',
    'button.ql-list[value="bullet"]': 'Bulleted list',
    'button.ql-script[value="sub"]': 'Subscript',
    'button.ql-script[value="super"]': 'Superscript',
    'button.ql-link': 'Link',
    'button.ql-image': 'Image',
    'button.ql-video': 'Video',
    'button.ql-file': 'File',
    'button.ql-clean': 'Clear formatting',
    'button.ql-html': 'HTML source code',
    '.ql-picker.ql-header': 'Heading style',
    '.ql-picker.ql-size': 'Text size',
    '.ql-picker.ql-align': 'Alignment',
    '.ql-color-picker.ql-color': 'Text color',
    '.ql-color-picker.ql-background': 'Background color'
};

/**
 * Translated editor strings, rendered by base.html.twig.
 */
function quillI18n() {
    return window.aroQuillI18n || {};
}

const HTML_ICON = '<svg viewBox="0 0 18 18">'
    + '<polyline class="ql-stroke" points="6 5 3 9 6 13"></polyline>'
    + '<polyline class="ql-stroke" points="12 5 15 9 12 13"></polyline>'
    + '</svg>';

const FILE_ICON = '<svg viewBox="0 0 18 18">'
    + '<path class="ql-stroke" fill="none" d="M5,2 H10 L14,6 V16 H5 Z"></path>'
    + '<path class="ql-stroke" fill="none" d="M10,2 V6 H14"></path>'
    + '</svg>';

const VOID_TAGS = /^(AREA|BASE|BR|COL|EMBED|HR|IMG|INPUT|LINK|META|PARAM|SOURCE|TRACK|WBR)$/;

/**
 * Elements whose content is output as is, never reformatted: their spaces and
 * line breaks matter (<pre>, a <script>), or their markup is not HTML (<svg>,
 * <math>).
 */
const RAW_TAGS = /^(PRE|TEXTAREA|SCRIPT|STYLE|TEMPLATE|SVG|MATH|NOSCRIPT)$/i;
const BLOCK_TAGS = /^(P|DIV|H[1-6]|UL|OL|LI|BLOCKQUOTE|PRE|HR|TABLE|CAPTION|THEAD|TBODY|TFOOT|TR|TD|TH|FIGURE|FIGCAPTION|SECTION|ARTICLE|ASIDE|HEADER|FOOTER|NAV|MAIN|ADDRESS|DETAILS|SUMMARY|CENTER|FIELDSET|FORM|DL|DT|DD|IFRAME|VIDEO|ARO-LINE)$/;

/**
 * Parses a fragment of stored HTML. The explicit <body> keeps in the body what
 * a bare fragment would send to the <head> (a <style>, a <meta> at the start
 * of the content), and that the next save would lose.
 */
function parseHtml(html) {
    return new DOMParser().parseFromString('<!doctype html><html><head></head><body>' + (html || ''), 'text/html');
}

let setupDone = false;

/**
 * Global registrations, done once.
 */
function setupQuill() {

    if (setupDone || typeof Quill === 'undefined') {
        return;
    }
    setupDone = true;

    // Soft line break: a real <br> rather than a new block.
    const Embed = Quill.import('blots/embed');

    class SoftBreak extends Embed {
        // Parchment would remove the <br>, seen as empty
        optimize() {}
        length() { return 1; }
    }

    SoftBreak.blotName = 'softbreak';
    SoftBreak.tagName = 'BR';
    Quill.register(SoftBreak);

    // Icons missing from Quill's own set
    const icons = Quill.import('ui/icons');
    if (!icons['html']) {
        icons['html'] = HTML_ICON;
    }
    if (!icons['file']) {
        icons['file'] = FILE_ICON;
    }

    // Quill 2 forces target="_blank" (and rel) on every link. The format is
    // extended so that the target becomes a choice — the "New tab" checkbox
    // of the link tooltip (see enhanceLinkTooltip) — kept in the HTML.
    const Link = Quill.import('formats/link');

    class AroLink extends Link {

        static create(value) {

            const href = (value && typeof value === 'object') ? value.href : value;
            const target = (value && typeof value === 'object') ? value.target : '_blank';
            const node = super.create(href);

            if (target) {
                node.setAttribute('target', target);
            }
            else {
                node.removeAttribute('target');
                node.removeAttribute('rel');
            }

            return node;
        }

        static formats(domNode) {
            return {
                href: domNode.getAttribute('href'),
                target: domNode.getAttribute('target') || ''
            };
        }

        format(name, value) {

            if (name !== this.statics.blotName || !value) {
                super.format(name, value);
                return;
            }

            const href = (typeof value === 'object') ? value.href : value;
            this.domNode.setAttribute('href', this.constructor.sanitize(href));

            if (typeof value === 'object') {
                if (value.target) {
                    this.domNode.setAttribute('target', value.target);
                    this.domNode.setAttribute('rel', 'noopener noreferrer');
                }
                else {
                    this.domNode.removeAttribute('target');
                    this.domNode.removeAttribute('rel');
                }
            }
        }
    }

    Quill.register(AroLink, true);

    registerHtmlBlots(Quill);

    // Snapshot-based history. Quill's own replays deltas, which describe
    // neither preserved containers (<div>, <section>…) nor the attributes
    // Quill does not model (a link's class): undoing the deletion of a block
    // of buttons would bring back bare links, outside their <div>. The editor
    // HTML is recorded instead, and undo reloads it through the same path as
    // the initial load.
    const History = Quill.import('modules/history');

    class AroHistory extends History {

        constructor(quill, options) {
            super(quill, options);
            this.current = this.snapshot();
        }

        snapshot() {
            return this.quill.root.innerHTML;
        }

        clear() {
            super.clear();
            this.current = this.snapshot();
        }

        record(changeDelta) {

            if (!changeDelta.ops.length) {
                return;
            }

            const previous = this.current;
            this.current = this.snapshot();
            this.stack.redo = [];

            // Keystrokes close together make a single undo step
            const timestamp = Date.now();
            if (this.lastRecorded + this.options.delay > timestamp && this.stack.undo.length) {
                return;
            }
            this.lastRecorded = timestamp;

            this.stack.undo.push({ html: previous, range: this.currentRange });
            if (this.stack.undo.length > this.options.maxStack) {
                this.stack.undo.shift();
            }
        }

        transform() {
            this.current = this.snapshot();
        }

        change(source, dest) {

            const item = this.stack[source].pop();
            if (!item || typeof item.html !== 'string') {
                return;
            }

            this.stack[dest].push({ html: this.snapshot(), range: this.quill.getSelection() });
            this.lastRecorded = 0;

            this.ignoreChange = true;
            this.quill.root.innerHTML = toEditorHtml(toStorageHtml(item.html));
            this.quill.update(Quill.sources.USER);
            this.ignoreChange = false;
            this.current = this.snapshot();

            if (item.range) {
                this.quill.setSelection(item.range, Quill.sources.USER);
            }
        }
    }

    Quill.register('modules/history', AroHistory, true);
}

/**
 * Keeps only the structuring formats of a pasted delta.
 * This is what keeps out <span style="background-color: white"> and friends.
 */
function cleanPasteMatcher(node, delta) {

    const Delta = Quill.import('delta');
    const allowed = window.aropixelQuillPasteFormats || DEFAULT_PASTE_FORMATS;

    const ops = delta.ops.map(function (op) {

        if (!op.attributes) {
            return op;
        }

        const attributes = {};
        Object.keys(op.attributes).forEach(function (format) {
            if (allowed.indexOf(format) !== -1) {
                attributes[format] = op.attributes[format];
            }
        });

        return Object.keys(attributes).length
            ? { insert: op.insert, attributes: attributes }
            : { insert: op.insert };
    });

    return new Delta(ops);
}

/**
 * Turns pasted (or loaded) <br> into soft line breaks.
 */
function softBreakMatcher() {
    const Delta = Quill.import('delta');
    return new Delta().insert({ softbreak: true });
}

/**
 * Quill 2 represents *every* list as an <ol> whose <li> carry a data-list
 * attribute, and injects <span class="ql-ui"> nodes to draw bullets and
 * numbers. The two functions below translate to and from the HTML actually
 * stored in the database, which must stay plain semantic HTML.
 */

const BULLET_KINDS = ['bullet', 'checked', 'unchecked'];

/**
 * Stored HTML -> HTML expected by Quill.
 */
export function toEditorHtml(html) {

    if (!html) {
        return '';
    }

    const doc = parseHtml(html);

    // Leftovers of an earlier save: Quill regenerates them itself
    doc.querySelectorAll('.ql-ui').forEach(function (node) {
        node.remove();
    });

    prepareHtml(doc);

    doc.querySelectorAll('ul').forEach(function (ul) {

        // List preserved as is (nested, paragraphs in its items…), or
        // content of an element preserved as is
        if (ul.classList.contains(BLOCK_CLASS) || ul.closest(EMBED_SELECTOR)) {
            return;
        }

        const ol = doc.createElement('ol');
        Array.from(ul.attributes).forEach(function (attr) {
            ol.setAttribute(attr.name, attr.value);
        });

        while (ul.firstChild) {
            const child = ul.firstChild;
            if (child.nodeType === Node.ELEMENT_NODE && child.tagName === 'LI' && !child.hasAttribute('data-list')) {
                child.setAttribute('data-list', 'bullet');
            }
            ol.appendChild(child);
        }

        ul.parentNode.replaceChild(ol, ul);
    });

    return doc.body.innerHTML;
}

/**
 * Elements that are content on their own, even without text.
 */
const CONTENT_ELEMENTS = 'img, iframe, video, audio, object, embed, hr, table, ' + EMBED_SELECTOR;

/**
 * An empty editor is not empty to Quill: it always holds one line, rendered
 * `<p><br></p>` (or `<h2><br></h2>`, `<p class="ql-align-center"><br></p>`…
 * depending on the line format). Stored as is, that line makes "filled" a
 * field the user left empty, and the site renders an empty block. HTML with
 * no text (non-breaking spaces included) and no content element is therefore
 * considered empty.
 */
export function isBlankHtml(html) {

    if (!html) {
        return true;
    }

    const doc = parseHtml(html);

    return !doc.body.textContent.replace(/[\s\u00a0\u200b\ufeff]/g, '')
        && !doc.body.querySelector(CONTENT_ELEMENTS);
}

/**
 * Quill HTML -> stored HTML. An empty editor gives an empty string.
 */
export function toStorageHtml(html) {

    if (isBlankHtml(html)) {
        return '';
    }

    const doc = parseHtml(html);

    // Interface nodes (bullets, numbers, checkboxes, language picker)
    doc.querySelectorAll('.ql-ui').forEach(function (node) {
        if (!node.closest(EMBED_SELECTOR)) {
            node.remove();
        }
    });

    doc.querySelectorAll('ol').forEach(function (ol) {

        if (ol.classList.contains(BLOCK_CLASS) || ol.closest(EMBED_SELECTOR)) {
            return;
        }

        // Quill merges adjacent lists: one <ol> may mix bullets and
        // numbering. It is split back into homogeneous runs.
        const groups = [];

        Array.from(ol.children).forEach(function (li) {

            const kind = li.getAttribute('data-list') || 'ordered';
            const tag = BULLET_KINDS.indexOf(kind) !== -1 ? 'UL' : 'OL';
            const last = groups[groups.length - 1];

            if (!last || last.tag !== tag) {
                groups.push({ tag: tag, items: [li] });
            }
            else {
                last.items.push(li);
            }
        });

        if (!groups.length) {
            return;
        }

        const fragment = doc.createDocumentFragment();

        groups.forEach(function (group) {

            const list = doc.createElement(group.tag);
            Array.from(ol.attributes).forEach(function (attr) {
                list.setAttribute(attr.name, attr.value);
            });

            group.items.forEach(function (li) {
                // "bullet" and "ordered" are now carried by the tag
                if (['bullet', 'ordered'].indexOf(li.getAttribute('data-list')) !== -1) {
                    li.removeAttribute('data-list');
                }
                list.appendChild(li);
            });

            fragment.appendChild(list);
        });

        ol.parentNode.replaceChild(fragment, ol);
    });

    restoreHtml(doc);

    return doc.body.innerHTML;
}

/**
 * Indents the HTML to make it readable in the source view.
 */
function prettyHtml(html) {

    const doc = parseHtml(html);

    const escapeText = function (text) {
        return text.replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/\u00a0/g, '&nbsp;');
    };

    const escapeAttr = function (value) {
        return value.replace(/&/g, '&amp;').replace(/"/g, '&quot;').replace(/\u00a0/g, '&nbsp;');
    };

    const isBlock = function (node) {
        return node && node.nodeType === Node.ELEMENT_NODE && BLOCK_TAGS.test(node.tagName);
    };

    const walk = function (parent, depth) {

        let out = '';
        const pad = '    '.repeat(depth);

        Array.from(parent.childNodes).forEach(function (node) {

            if (node.nodeType === Node.TEXT_NODE) {
                if (node.textContent.trim()) {
                    out += escapeText(node.textContent);
                }
                // Space between two inline elements: it separates two words,
                // two links… Only the one next to a block is layout.
                else if (node.textContent && !isBlock(node.previousSibling) && !isBlock(node.nextSibling)
                    && node.previousSibling && node.nextSibling) {
                    out += ' ';
                }
                return;
            }

            if (node.nodeType === Node.COMMENT_NODE) {
                out += '<!--' + node.data + '-->';
                return;
            }

            if (node.nodeType !== Node.ELEMENT_NODE) {
                return;
            }

            const tag = node.tagName.toLowerCase();
            const attrs = Array.from(node.attributes).map(function (attr) {
                return ' ' + attr.name + '="' + escapeAttr(attr.value) + '"';
            }).join('');
            const open = '<' + tag + attrs + '>';
            const lead = BLOCK_TAGS.test(node.tagName) ? '\n' + pad : '';

            if (VOID_TAGS.test(node.tagName)) {
                out += lead + open;
                return;
            }

            if (RAW_TAGS.test(node.tagName)) {
                out += lead + node.outerHTML;
                return;
            }

            const inner = walk(node, depth + 1);

            if (BLOCK_TAGS.test(node.tagName)) {
                const multiline = inner.indexOf('\n') !== -1;
                out += lead + open + inner + (multiline ? '\n' + pad : '') + '</' + tag + '>';
            }
            else {
                out += open + inner + '</' + tag + '>';
            }
        });

        return out;
    };

    return walk(doc.body, 0).replace(/^\n/, '');
}

/**
 * Reverse of prettyHtml: removes the indentation and line breaks added for
 * readability (or typed by hand in the source view), so that they pollute
 * neither the stored HTML nor the editor rendering (white-space: pre-wrap).
 * Meaningful spaces — between inline elements — are preserved.
 *
 * A line break in text is worth a single space in HTML, but the editor shows
 * it as is: CKEditor saved `line<br>\nline`, which doubled every soft line
 * break. Next to a <br> or at the edge of a block it is removed; elsewhere it
 * becomes the space it stands for again.
 */
const CONTAINER_TAGS = /^(BODY|UL|OL|TABLE|THEAD|TBODY|TFOOT|TR)$/;

const isLineEdge = function (sibling, parent) {
    if (sibling) {
        return sibling.nodeType === Node.ELEMENT_NODE && (sibling.tagName === 'BR' || BLOCK_TAGS.test(sibling.tagName));
    }
    return parent.tagName === 'BODY' || BLOCK_TAGS.test(parent.tagName);
};

const collapseNewlines = function (node, parent) {

    if (node.textContent.indexOf('\n') === -1 || parent.closest('pre, .ql-code-block')) {
        return;
    }

    let text = node.textContent;

    text = text.replace(/^[^\S\u00a0]*\n[^\S\u00a0]*/, isLineEdge(node.previousSibling, parent) ? '' : ' ');
    text = text.replace(/[^\S\u00a0]*\n[^\S\u00a0]*$/, isLineEdge(node.nextSibling, parent) ? '' : ' ');
    text = text.replace(/[^\S\u00a0]*\n[^\S\u00a0]*/g, ' ');

    if (text) {
        node.textContent = text;
    }
    else {
        node.remove();
    }
};

function compactHtml(html) {

    const doc = parseHtml(html);

    const strip = function (parent) {

        Array.from(parent.childNodes).forEach(function (node) {

            if (node.nodeType === Node.ELEMENT_NODE) {
                if (!RAW_TAGS.test(node.tagName) && !node.classList.contains('ql-code-block-container')) {
                    strip(node);
                }
                return;
            }

            if (node.nodeType !== Node.TEXT_NODE) {
                return;
            }

            if (node.textContent.trim()) {
                collapseNewlines(node, parent);
                return;
            }

            const prev = node.previousSibling;
            const next = node.nextSibling;
            const nearBlock = (prev && prev.nodeType === Node.ELEMENT_NODE && BLOCK_TAGS.test(prev.tagName))
                || (next && next.nodeType === Node.ELEMENT_NODE && BLOCK_TAGS.test(next.tagName))
                || CONTAINER_TAGS.test(parent.tagName);

            if (nearBlock) {
                node.remove();
            }
            else {
                collapseNewlines(node, parent);
            }
        });
    };

    strip(doc.body);

    return doc.body.innerHTML;
}

/**
 * Builds (on demand) the HTML source editing panel.
 * When open, it replaces the rendered text; the code is editable there, kept
 * in sync with the submitted field, and applied back to the editor on close.
 */
function buildHtmlSource(quill, container, $target) {

    if (quill.__htmlSource) {
        return quill.__htmlSource;
    }

    const i18n = quillI18n();
    const copyLabel = i18n.copy || 'Copy';

    const panel = document.createElement('div');
    panel.className = 'quill-html-source d-none';
    panel.innerHTML = ''
        + '<div class="quill-html-source-header">'
        +     '<span class="quill-html-source-title"></span>'
        +     '<button type="button" class="quill-html-source-copy"></button>'
        + '</div>'
        + '<textarea class="quill-html-source-code" spellcheck="false"></textarea>';

    panel.querySelector('.quill-html-source-title').textContent = i18n.sourceTitle || 'HTML source code';

    container.appendChild(panel);

    const textarea = panel.querySelector('textarea');
    const copy = panel.querySelector('.quill-html-source-copy');
    copy.textContent = copyLabel;

    copy.addEventListener('click', function () {
        const text = textarea.value;
        const done = function () {
            copy.textContent = i18n.copied || 'Copied!';
            setTimeout(function () { copy.textContent = copyLabel; }, 1500);
        };

        if (navigator.clipboard && navigator.clipboard.writeText) {
            navigator.clipboard.writeText(text).then(done);
        }
        else {
            const area = document.createElement('textarea');
            area.value = text;
            document.body.appendChild(area);
            area.select();
            document.execCommand('copy');
            document.body.removeChild(area);
            done();
        }
    });

    // While the source view is open, its textarea is the source of truth:
    // submitting the form without closing the panel loses nothing.
    textarea.addEventListener('input', function () {
        $target.val(compactHtml(textarea.value));
    });

    const toolbarButton = function () {
        const toolbar = quill.getModule('toolbar');
        return toolbar ? toolbar.container.querySelector('button.ql-html') : null;
    };

    const source = {
        panel: panel,
        textarea: textarea,
        isOpen: function () { return !panel.classList.contains('d-none'); },
        open: function () {
            textarea.value = prettyHtml(toStorageHtml(quill.root.innerHTML));
            panel.classList.remove('d-none');
            container.classList.add('quill-source-mode');
            const button = toolbarButton();
            if (button) {
                button.classList.add('ql-active');
            }
            textarea.focus();
        },
        close: function () {
            quill.root.innerHTML = toEditorHtml(compactHtml(textarea.value));
            quill.update();
            $target.val(toStorageHtml(quill.root.innerHTML));
            panel.classList.add('d-none');
            container.classList.remove('quill-source-mode');
            const button = toolbarButton();
            if (button) {
                button.classList.remove('ql-active');
            }
        },
        toggle: function () {
            if (source.isOpen()) {
                source.close();
            }
            else {
                source.open();
            }
        }
    };

    quill.__htmlSource = source;

    return source;
}

/**
 * Toggles `.is-editing` on the container while the editor is being worked in.
 *
 * At rest the content area is capped (320px, see `components/_editor.css`) so that a very
 * long field does not push the rest of the form off screen. Entering the editor lifts the
 * cap and makes the toolbar sticky: the content can then scroll with the page without
 * losing sight of the controls.
 *
 * The listener is on the container, not on `.ql-editor`. Clicking a toolbar button briefly
 * blurs the editor; reading that as leaving would collapse the area right when the user
 * wants to bold the selection they just made. `focusout` therefore only counts when focus
 * leaves the whole container.
 */
function watchEditingState(container) {

    container.addEventListener('focusin', function () {
        container.classList.add('is-editing');
    });

    container.addEventListener('focusout', function (event) {
        // `relatedTarget` is null when focus goes nowhere (click on blank space, another
        // window): collapse, as for a plain exit.
        if (event.relatedTarget && container.contains(event.relatedTarget)) {
            return;
        }

        container.classList.remove('is-editing');
    });
}

/**
 * Sets the tooltips (title attributes) on the toolbar controls.
 */
function applyTooltips(quill) {

    const toolbar = quill.getModule('toolbar');
    if (!toolbar || !toolbar.container) {
        return;
    }

    const tooltips = Object.assign(
        {},
        DEFAULT_TOOLTIPS,
        quillI18n().tooltips || {},
        window.aropixelQuillTooltips || {}
    );

    Object.keys(tooltips).forEach(function (selector) {
        toolbar.container.querySelectorAll(selector).forEach(function (element) {
            element.setAttribute('title', tooltips[selector]);
        });
    });
}

/**
 * Adds to the Snow theme link tooltip a "New tab" checkbox driving the link's
 * target attribute (see AroLink in setupQuill). A new link opens in a new tab
 * by default — the historical behaviour — but the choice is now kept and
 * restored when the link is edited again.
 */
function enhanceLinkTooltip(quill) {

    const tooltip = quill.theme && quill.theme.tooltip;
    if (!tooltip || !tooltip.root || tooltip.root.querySelector('.quill-link-target')) {
        return;
    }

    const label = document.createElement('label');
    label.className = 'quill-link-target';
    const checkbox = document.createElement('input');
    checkbox.type = 'checkbox';
    label.appendChild(checkbox);
    label.appendChild(document.createTextNode(' ' + (quillI18n().linkNewTab || 'New tab')));
    tooltip.root.insertBefore(label, tooltip.root.querySelector('a.ql-action'));

    // Target of the link under the selection: '' for a link without target, null when no link
    const editedLinkTarget = function () {

        const range = tooltip.linkRange || quill.getSelection();
        if (!range) {
            return null;
        }

        const LinkBlot = Quill.import('formats/link');
        const found = quill.scroll.descendant(LinkBlot, range.index);

        return found[0] ? (found[0].domNode.getAttribute('target') || '') : null;
    };

    const originalEdit = tooltip.edit.bind(tooltip);
    tooltip.edit = function (mode, preview) {

        mode = mode || 'link';
        originalEdit(mode, preview === undefined ? null : preview);

        if (mode === 'link') {
            const target = editedLinkTarget();
            checkbox.checked = target === null ? true : target === '_blank';
        }
    };

    // BaseTooltip's save rewritten for link mode: same logic, but the
    // format value becomes { href, target }.
    const originalSave = tooltip.save.bind(tooltip);
    tooltip.save = function () {

        if (tooltip.root.getAttribute('data-mode') !== 'link') {
            originalSave();
            return;
        }

        const href = tooltip.textbox.value;

        if (href) {
            const value = { href: href, target: checkbox.checked ? '_blank' : '' };
            const scrollTop = quill.root.scrollTop;

            if (tooltip.linkRange) {
                quill.formatText(tooltip.linkRange, 'link', value, Quill.sources.USER);
                delete tooltip.linkRange;
            }
            else {
                tooltip.restoreFocus();
                quill.format('link', value, Quill.sources.USER);
            }

            quill.root.scrollTop = scrollTop;
        }

        tooltip.textbox.value = '';
        tooltip.hide();
    };
}

/**
 * Resolves the requested toolbar (known name, JSON, or array).
 */
function resolveToolbar(toolbar) {

    const toolbars = Object.assign({}, DEFAULT_TOOLBARS, window.aropixelQuillToolbars || {});

    // jQuery.data() already deserialises the JSON of data attributes
    if (Array.isArray(toolbar)) {
        return toolbar;
    }

    if (typeof toolbar === 'string' && toolbar.startsWith('[') && toolbar.endsWith(']')) {
        try {
            return JSON.parse(toolbar);
        }
        catch (e) {
            console.error('Could not parse the custom Quill toolbar:', e);
        }
    }

    return toolbars[toolbar] || toolbars['full'];
}

/**
 * Adds the HTML source editing button to the toolbar.
 */
function withHtmlButton(container) {

    if (window.aropixelQuillHtmlPreview === false || !Array.isArray(container)) {
        return container;
    }

    // Already in the toolbar: not added a second time
    if (JSON.stringify(container).indexOf('"html"') !== -1) {
        return container;
    }

    return container.concat([['html']]);
}

/**
 * Builds the "toolbar" module configuration, whether the toolbar was declared
 * as a plain array or as a full Quill object.
 */
function buildToolbarModule(toolbar, handlers) {

    const config = (toolbar && !Array.isArray(toolbar) && typeof toolbar === 'object')
        ? Object.assign({}, toolbar)
        : { container: toolbar };

    config.container = withHtmlButton(config.container);
    config.handlers = Object.assign({}, config.handlers, handlers);

    return config;
}

export function activateQuillEditor($elements) {

    setupQuill();

    $elements.each(function () {

        const $this = $(this);

        // Avoid initialising twice
        if ($this.data('quill-initialized')) {
            return;
        }

        const targetSelector = $this.data('target');
        const $target = $(targetSelector.replace(/(:)/g, "\\$1"));

        const quill = new Quill($this[0], {
            theme: 'snow',
            modules: {
                clipboard: {
                    matchVisual: false,
                    matchers: [
                        ['BR', softBreakMatcher],
                        [Node.ELEMENT_NODE, cleanPasteMatcher]
                    ]
                },
                keyboard: {
                    bindings: {
                        // Enter on an empty line at the end of a preserved <div>,
                        // <section>…: leave the container (see html-preservation.js)
                        exitHtmlBlock: {
                            key: 'Enter',
                            collapsed: true,
                            empty: true,
                            handler: function (range) {
                                return exitHtmlBlock(this.quill, range);
                            }
                        },
                        // Shift + Enter: soft line break (<br>)
                        softBreak: {
                            key: 'Enter',
                            shiftKey: true,
                            handler: function (range) {
                                if (range.length) {
                                    this.quill.deleteText(range.index, range.length, Quill.sources.USER);
                                }
                                this.quill.insertEmbed(range.index, 'softbreak', true, Quill.sources.USER);
                                this.quill.setSelection(range.index + 1, 0, Quill.sources.SILENT);

                                return false;
                            }
                        }
                    }
                },
                toolbar: buildToolbarModule(resolveToolbar($this.data('toolbar')), {
                    html: function () {
                        buildHtmlSource(this.quill, $this.parent()[0], $target).toggle();
                    },
                    // "file" is not a Quill format: its handler MUST be given here.
                    // An addHandler('file') after `new Quill` would come too late, the
                    // button would get no listener. "image" is a known format: its
                    // handler can still be overridden below.
                    file: function () {
                        const quill = this.quill;
                        if ($.fn.FileManager) {
                            $($target[0]).FileManager({
                                editor: quill,
                                category: $target.attr('data-class')
                            });
                        } else {
                            // Fallback: type a file URL by hand
                            const range = quill.getSelection(true);
                            const value = prompt('Veuillez entrer l\'URL du fichier :');
                            if (value) {
                                quill.insertText(range.index, value, 'link', value, 'user');
                            }
                        }
                    }
                })
            }
        });

        $this.data('quill-initialized', true);

        preventContentActions(quill.root);

        // Override the image handler to use the ImageManager when available
        if (quill.getModule('toolbar')) {
            quill.getModule('toolbar').addHandler('image', function () {
                if (window.initImageManager) {
                    $target.attr('data-im-type', 'editor');
                    window.initImageManager($target[0], {
                        editor: quill,
                        category: $target.attr('data-class'),
                        attach_path: $target.attr('data-attach-path')
                    });
                } else {
                    // Fallback to default image handler if initImageManager is not available
                    const range = this.quill.getSelection();
                    const value = prompt('Veuillez entrer l\'URL de l\'image :');
                    if (value) {
                        this.quill.insertEmbed(range.index, 'image', value, Quill.sources.USER);
                    }
                }
            });
        }

        applyTooltips(quill);
        enhanceLinkTooltip(quill);

        // The toolbar Quill just inserted is a sibling of `.ql-container`: the container
        // to watch is therefore their common parent.
        const editorContainer = $this[0].closest('.quill-editor-container');
        if (editorContainer) {
            watchEditingState(editorContainer);
        }

        // Initialize content. The stored HTML may carry line breaks between blocks
        // (CKEditor saved `<p>…</p>\n\n<p>…</p>`): Quill would turn each of them
        // into an empty paragraph, which the next save would then persist.
        quill.root.innerHTML = toEditorHtml(compactHtml($target.val()));
        quill.update(Quill.sources.SILENT);

        // Loading is not an edit: without this, the first Ctrl + Z would
        // empty the editor.
        quill.history.clear();

        // The field is only rewritten on the first edit: a form saved without
        // touching the editor keeps the stored HTML, byte for byte.
        quill.on('text-change', function () {
            $target.val(toStorageHtml(quill.root.innerHTML));
        });
    });
}
