/* ------------------------------------------------------------------------------
*
*  # Preservation of the HTML Quill cannot represent
*
*  Quill 2 only knows a small set of elements (paragraphs, headings, flat
*  lists, bold, links…). Everything else — <div>, <section>, <button>, <small>,
*  <hr>, <pre>, a nested list, a paragraph in a table cell… — is dropped on
*  load, text included, and the next save loses it.
*
*  This module makes those elements representable, losing nothing on the
*  round trip nor after an edit:
*
*   - container (`aro-html-block`): a block-level element, or one holding
*     blocks. It keeps its tag and attributes, and its lines stay editable.
*     Text written straight into it is carried by an implicit line
*     (<aro-line>), removed on save;
*   - inline (`aro-html-inline`): an unknown inline element (<button>,
*     <small>, <span class="…">…). It keeps its tag and attributes, and its
*     text stays editable;
*   - atom (<aro-embed>): what is not edited as text (<hr>, <pre>, <svg>,
*     <script>, a comment, an empty icon <i class="fa …">…). It is kept as is,
*     byte for byte, and only changed in the source view.
*
*  The marking is done by prepareHtml() (stored HTML -> editor HTML) and
*  removed by restoreHtml() (the reverse). Parchment's registry looks a blot
*  up by class before looking it up by tag: the marker class is enough for the
*  right blot to take the element, whatever its tag.
*
* ---------------------------------------------------------------------------- */

export const BLOCK_CLASS = 'aro-html-block';
export const INLINE_CLASS = 'aro-html-inline';
const LINE_TAG = 'ARO-LINE';
const EMBED_TAG = 'ARO-EMBED';
const EMBED_ATTR = 'data-aro-html';
const BLOCK_EMBED_CLASS = 'aro-html-block-embed';

/**
 * Lines Quill represents natively — as long as they only hold text and
 * inline elements.
 */
const NATIVE_LINE_TAGS = /^(P|H[1-6]|BLOCKQUOTE|LI)$/;

/**
 * Inline elements Quill represents natively (bold, italic, link…).
 */
const NATIVE_INLINE_TAGS = /^(A|STRONG|B|EM|I|U|S|STRIKE|SUB|SUP|CODE|SPAN)$/;

/**
 * Void elements Quill represents natively.
 */
const NATIVE_VOID_TAGS = /^(BR|IMG)$/;

/**
 * <span> attributes Quill understands: beyond these, the <span> would be
 * unwrapped and its attributes lost. Colours (style) and ql-* classes.
 */
const NATIVE_SPAN_STYLES = /^(color|background-color)$/;
const NATIVE_SPAN_CLASSES = /^ql-(size|font|align|direction|indent)-/;

/**
 * Block-level elements in the HTML sense: always a container, even when they
 * only hold text.
 */
const BLOCK_LEVEL_TAGS = /^(DIV|SECTION|ARTICLE|ASIDE|HEADER|FOOTER|NAV|MAIN|ADDRESS|FIGURE|FIGCAPTION|DETAILS|SUMMARY|CENTER|HGROUP|FIELDSET|LEGEND|DL|DT|DD|MENU|TABLE|CAPTION|THEAD|TBODY|TFOOT|TR|TD|TH|UL|OL|LI|BLOCKQUOTE|P|H[1-6])$/;

/**
 * Table structure: Quill only handles a reduced version of it (no <thead>, no
 * <th>, a single line per cell). A table is therefore always preserved as
 * nested containers.
 */
const TABLE_TAGS = /^(TABLE|CAPTION|THEAD|TBODY|TFOOT|TR|TD|TH)$/;

/**
 * Elements preserved as is: their content is not edited as text.
 */
const ATOMIC_TAGS = /^(HR|PRE|SVG|MATH|SCRIPT|STYLE|NOSCRIPT|TEMPLATE|VIDEO|AUDIO|OBJECT|EMBED|CANVAS|PICTURE|FORM|INPUT|SELECT|TEXTAREA|COLGROUP|COL|WBR|MAP|AREA|SOURCE|TRACK|LINK|META)$/;

/**
 * Block-level atoms: placed straight in their container, not in a line.
 */
const BLOCK_ATOMIC_TAGS = /^(HR|PRE|FORM|COLGROUP|VIDEO|AUDIO|OBJECT|EMBED|CANVAS|PICTURE|MATH)$/;

/**
 * Atoms with no rendering in the editor: only a label is shown (a <style>
 * would apply its rules to the whole admin).
 */
const LABELLED_ATOMIC_TAGS = /^(SCRIPT|STYLE|NOSCRIPT|TEMPLATE|LINK|META)$/;

const isElement = function (node) {
    return node.nodeType === Node.ELEMENT_NODE;
};

const isBlankText = function (node) {
    return node.nodeType === Node.TEXT_NODE && !node.textContent.replace(/[\s]/g, '');
};

/**
 * A <span> whose attributes Quill all understands stays a Quill <span>.
 */
const isNativeSpan = function (el) {

    return Array.from(el.attributes).every(function (attr) {

        if (attr.name === 'class') {
            return attr.value.split(/\s+/).filter(Boolean).every(function (name) {
                return NATIVE_SPAN_CLASSES.test(name);
            });
        }

        if (attr.name === 'style') {
            return attr.value.split(';').map(function (decl) {
                return decl.split(':')[0].trim().toLowerCase();
            }).filter(Boolean).every(function (prop) {
                return NATIVE_SPAN_STYLES.test(prop);
            });
        }

        return false;
    });
};

/**
 * Role of a node for its parent: 'block' needs a block context, 'inline' fits
 * in a line, 'blank' is meaningless whitespace.
 *
 * Marking goes bottom-up: an element is classified after its children, since
 * an inline element holding a block becomes a container itself.
 */
function classify(node, doc, embeds) {

    if (node.nodeType === Node.TEXT_NODE) {
        return isBlankText(node) ? 'blank' : 'inline';
    }

    if (node.nodeType === Node.COMMENT_NODE) {
        embeds.push({ node: node, block: false });
        return 'inline';
    }

    if (!isElement(node)) {
        return 'blank';
    }

    const tag = node.tagName.toUpperCase();

    // Quill's interface nodes are regenerated by Quill itself
    if (node.classList.contains('ql-ui')) {
        return 'blank';
    }

    // Video: the native blot (iframe.ql-video) is a block, valid in any
    // container.
    if (tag === 'IFRAME') {
        node.classList.add('ql-video');
        return 'block';
    }

    if (NATIVE_VOID_TAGS.test(tag)) {
        return 'inline';
    }

    if (ATOMIC_TAGS.test(tag)) {
        const block = BLOCK_ATOMIC_TAGS.test(tag);
        embeds.push({ node: node, block: block });
        return block ? 'block' : 'inline';
    }

    const roles = Array.from(node.childNodes).map(function (child) {
        return classify(child, doc, embeds);
    });
    const hasBlock = roles.indexOf('block') !== -1;
    const isEmpty = !node.childNodes.length;

    if (TABLE_TAGS.test(tag)) {
        node.classList.add(BLOCK_CLASS);
        return 'block';
    }

    if (tag === 'UL' || tag === 'OL') {
        const items = Array.from(node.children);
        const simple = items.every(function (li) {
            return li.tagName.toUpperCase() === 'LI' && !li.classList.contains(BLOCK_CLASS);
        }) && Array.from(node.childNodes).every(function (child) {
            return isElement(child) || isBlankText(child);
        });

        if (!simple) {
            node.classList.add(BLOCK_CLASS);
            items.forEach(function (li) {
                li.classList.add(BLOCK_CLASS);
            });
        }
        return 'block';
    }

    if (NATIVE_LINE_TAGS.test(tag)) {
        if (hasBlock) {
            node.classList.add(BLOCK_CLASS);
        }
        return 'block';
    }

    if (BLOCK_LEVEL_TAGS.test(tag) || hasBlock) {
        node.classList.add(BLOCK_CLASS);
        return 'block';
    }

    // Inline and empty: nothing to edit (icon <i class="fa …">, anchor <a name>)
    if (isEmpty) {
        embeds.push({ node: node, block: false });
        return 'inline';
    }

    if (!NATIVE_INLINE_TAGS.test(tag) || (tag === 'SPAN' && !isNativeSpan(node))) {
        node.classList.add(INLINE_CLASS);
    }

    return 'inline';
}

/**
 * Replaces an atom with its stand-in in the editor: the original HTML, intact,
 * is carried by an attribute, and the element is only shown as a preview.
 */
function toEmbed(doc, node, block) {

    const embed = doc.createElement(EMBED_TAG);
    embed.setAttribute(EMBED_ATTR, serializeNode(node));
    if (block) {
        embed.setAttribute('data-block', '');
        embed.setAttribute('contenteditable', 'false');
        embed.classList.add(BLOCK_EMBED_CLASS);
    }
    fillPreview(doc, embed, node);

    node.parentNode.replaceChild(embed, node);
}

function serializeNode(node) {
    return node.nodeType === Node.COMMENT_NODE ? '<!--' + node.data + '-->' : node.outerHTML;
}

function fillPreview(doc, embed, node) {

    if (node.nodeType === Node.COMMENT_NODE) {
        embed.setAttribute('data-kind', 'comment');
        return;
    }

    const tag = node.tagName.toUpperCase();
    if (LABELLED_ATOMIC_TAGS.test(tag)) {
        embed.setAttribute('data-kind', 'label');
        embed.setAttribute('data-label', '<' + tag.toLowerCase() + '>');
        return;
    }

    embed.appendChild(neutralize(node.cloneNode(true)));
}

/**
 * The preview lives inside the admin form: an <input name> would be posted
 * with the record, a button would submit it. The preview is therefore inert,
 * with its controls disabled (a disabled control is never posted). Only the
 * preview is changed: the HTML saved is the one in the attribute.
 */
function neutralize(clone) {

    if (clone.nodeType !== Node.ELEMENT_NODE) {
        return clone;
    }

    clone.setAttribute('inert', '');

    const controls = Array.from(clone.querySelectorAll('input, select, textarea, button, fieldset'));
    if (clone.matches('input, select, textarea, button, fieldset')) {
        controls.push(clone);
    }
    controls.forEach(function (control) {
        control.setAttribute('disabled', '');
    });

    return clone;
}

/**
 * A preserved <button> stays editable (its text is text), but a click on it
 * would submit the admin form.
 */
export function preventContentActions(root) {

    root.addEventListener('click', function (event) {
        if (event.target.closest && event.target.closest('button, input[type="submit"], input[type="image"]')) {
            event.preventDefault();
        }
    }, true);

    root.addEventListener('submit', function (event) {
        event.preventDefault();
        event.stopPropagation();
    }, true);
}

/**
 * Inside a container, Quill only accepts lines and blocks: every run of text
 * and inline elements written straight into it is therefore carried by an
 * implicit line. A run of whitespace only, between two blocks, is meaningless
 * and goes away.
 */
function wrapInlineRuns(doc, container) {

    let run = [];

    const flush = function () {

        const meaningful = run.some(function (node) {
            return !isBlankText(node);
        });

        if (meaningful) {
            const line = doc.createElement(LINE_TAG);
            container.insertBefore(line, run[0]);
            run.forEach(function (node) {
                line.appendChild(node);
            });
        }
        else {
            run.forEach(function (node) {
                node.remove();
            });
        }

        run = [];
    };

    Array.from(container.childNodes).forEach(function (node) {

        if (isBlockChild(node)) {
            flush();
            return;
        }

        run.push(node);
    });

    flush();

    // An empty container (<div class="spacer"></div>) must stay: without a
    // line, Parchment would remove it.
    if (!container.childNodes.length) {
        container.appendChild(doc.createElement(LINE_TAG));
    }
}

function isBlockChild(node) {

    if (!isElement(node)) {
        return false;
    }

    const tag = node.tagName.toUpperCase();

    return node.classList.contains(BLOCK_CLASS)
        || (tag === EMBED_TAG && node.hasAttribute('data-block'))
        || tag === 'IFRAME'
        || NATIVE_LINE_TAGS.test(tag)
        || tag === 'UL' || tag === 'OL';
}

/**
 * Stored HTML (already parsed) -> editor HTML, in place.
 */
export function prepareHtml(doc) {

    const embeds = [];

    Array.from(doc.body.childNodes).forEach(function (node) {
        classify(node, doc, embeds);
    });

    // Atoms first: they leave the tree before it is split into lines
    embeds.forEach(function (item) {
        toEmbed(doc, item.node, item.block);
    });

    doc.body.querySelectorAll('.' + BLOCK_CLASS).forEach(function (container) {
        wrapInlineRuns(doc, container);
    });

    // The top level too: without an implicit line, Quill would wrap bare
    // text in a <p>, which the save would add to the content.
    if (Array.from(doc.body.childNodes).some(function (node) { return !isBlockChild(node) && !isBlankText(node); })) {
        wrapInlineRuns(doc, doc.body);
    }
}

/**
 * Editor HTML (already parsed) -> stored HTML, in place.
 */
export function restoreHtml(doc) {

    // Atoms: the original HTML, byte for byte
    Array.from(doc.body.querySelectorAll(EMBED_TAG.toLowerCase())).forEach(function (embed) {

        const html = embed.getAttribute(EMBED_ATTR) || '';
        const template = doc.createElement('template');
        template.innerHTML = html;

        embed.parentNode.replaceChild(template.content, embed);
    });

    // Implicit lines. Alone between two blocks, a line becomes the original
    // bare text again; several in a row mean the user split the line
    // (Enter): they are then real paragraphs.
    const lines = Array.from(doc.body.querySelectorAll(LINE_TAG.toLowerCase()));

    lines.forEach(function (line) {

        const prev = line.previousElementSibling;
        const next = line.nextElementSibling;
        const alone = !(prev && prev.tagName.toUpperCase() === LINE_TAG)
            && !(next && next.tagName.toUpperCase() === LINE_TAG);

        line.setAttribute('data-alone', alone ? '1' : '0');
    });

    lines.forEach(function (line) {

        const alone = line.getAttribute('data-alone') === '1';
        line.removeAttribute('data-alone');

        if (alone) {
            const empty = Array.from(line.childNodes).every(function (child) {
                return (isElement(child) && child.tagName.toUpperCase() === 'BR')
                    || (child.nodeType === Node.TEXT_NODE && !child.textContent);
            });
            if (empty) {
                line.remove();
                return;
            }
            const fragment = doc.createDocumentFragment();
            while (line.firstChild) {
                fragment.appendChild(line.firstChild);
            }
            line.parentNode.replaceChild(fragment, line);
            return;
        }

        const p = doc.createElement('p');
        Array.from(line.attributes).forEach(function (attr) {
            p.setAttribute(attr.name, attr.value);
        });
        while (line.firstChild) {
            p.appendChild(line.firstChild);
        }
        line.parentNode.replaceChild(p, line);
    });

    [BLOCK_CLASS, INLINE_CLASS].forEach(function (marker) {
        doc.body.querySelectorAll('.' + marker).forEach(function (el) {
            el.classList.remove(marker);
            if (!el.classList.length) {
                el.removeAttribute('class');
            }
        });
    });
}

/**
 * Selector of atoms. An atom is content: a field holding a single <hr> is not
 * empty.
 */
export const EMBED_SELECTOR = EMBED_TAG.toLowerCase();

let registered = false;

/**
 * Blots of the three families. To be called once, after Quill is loaded.
 */
export function registerHtmlBlots(Quill) {

    if (registered) {
        return;
    }
    registered = true;

    const Parchment = Quill.import('parchment');
    const Block = Quill.import('blots/block');
    const BlockEmbed = Quill.import('blots/block/embed');
    const Container = Quill.import('blots/container');
    const Inline = Quill.import('blots/inline');
    const Embed = Quill.import('blots/embed');

    /**
     * Container: original tag and attributes, never merged with its
     * neighbour (two <div> side by side stay two <div>).
     */
    class HtmlBlock extends Container {
        checkMerge() {
            return false;
        }
    }
    HtmlBlock.blotName = 'aro-html-block';
    HtmlBlock.className = BLOCK_CLASS;
    // A tag of its own: a real tag (DIV) would make this blot the one of
    // every <div>, marked or not. A container is never created by name: it
    // comes from the loaded HTML, or from a clone (cloneNode, tag kept).
    HtmlBlock.tagName = 'ARO-BLOCK';

    /**
     * Implicit line: text written straight into a container. A tag of its
     * own, so that the delta tells it from a paragraph.
     */
    class HtmlLine extends Block {}
    HtmlLine.blotName = 'aro-line';
    HtmlLine.tagName = LINE_TAG;

    /**
     * Inline: original tag and attributes. No merging with an identical
     * neighbour (two buttons stay two buttons), no unwrapping, no reordering
     * against the bold or italic it holds.
     */
    class HtmlInline extends Inline {

        static create(value) {

            const tag = (value && value.tag) || 'span';
            const node = document.createElement(tag);
            const attrs = (value && value.attrs) || {};

            Object.keys(attrs).forEach(function (name) {
                node.setAttribute(name, attrs[name]);
            });
            node.classList.add(INLINE_CLASS);

            return node;
        }

        static formats(domNode) {

            const attrs = {};
            Array.from(domNode.attributes).forEach(function (attr) {
                attrs[attr.name] = attr.value;
            });

            return { tag: domNode.tagName.toLowerCase(), attrs: attrs };
        }

        formats() {
            const formats = this.attributes.values();
            formats[this.statics.blotName] = this.statics.formats(this.domNode);
            return formats;
        }

        optimize(context) {
            Parchment.ParentBlot.prototype.optimize.call(this, context);
        }
    }
    HtmlInline.blotName = 'aro-html-inline';
    HtmlInline.className = INLINE_CLASS;
    HtmlInline.tagName = 'ARO-INLINE';

    // Inline.compare ranks bold, italic, link… in a fixed nesting order, and
    // Quill reorders elements to follow it. A preserved element keeps its
    // place: it compares to nothing.
    const compare = Inline.compare;
    Inline.compare = function (self, other) {
        if (self === HtmlInline.blotName || other === HtmlInline.blotName) {
            return 0;
        }
        return compare.call(Inline, self, other);
    };

    const createEmbedNode = function (value, tag) {

        const template = document.createElement('template');
        template.innerHTML = value || '';
        const original = template.content.firstChild;

        const node = document.createElement(tag);
        node.setAttribute(EMBED_ATTR, value || '');
        if (original) {
            fillPreview(document, node, original);
        }

        return node;
    };

    /**
     * Inline atom: <i class="fa …"></i>, a comment, a <script>…
     */
    class HtmlEmbed extends Embed {

        static create(value) {
            return createEmbedNode(value, EMBED_TAG);
        }

        static value(domNode) {
            return domNode.getAttribute(EMBED_ATTR) || '';
        }
    }
    HtmlEmbed.blotName = 'aro-html-embed';
    HtmlEmbed.tagName = EMBED_TAG;

    /**
     * Block-level atom: <hr>, <pre>, <form>…
     */
    class HtmlBlockEmbed extends BlockEmbed {

        static create(value) {
            const node = createEmbedNode(value, EMBED_TAG);
            node.setAttribute('data-block', '');
            node.setAttribute('contenteditable', 'false');
            node.classList.add(BLOCK_EMBED_CLASS);
            return node;
        }

        static value(domNode) {
            return domNode.getAttribute(EMBED_ATTR) || '';
        }
    }
    HtmlBlockEmbed.blotName = 'aro-html-block-embed';
    HtmlBlockEmbed.className = BLOCK_EMBED_CLASS;
    HtmlBlockEmbed.tagName = EMBED_TAG;

    HtmlBlock.allowedChildren = [Block, BlockEmbed, Container];

    patchLineMerge(Quill, HtmlBlock);

    Quill.register(HtmlBlock, true);
    Quill.register(HtmlLine, true);
    Quill.register(HtmlInline, true);
    Quill.register(HtmlEmbed, true);
    Quill.register(HtmlBlockEmbed, true);
}

/**
 * Joining two lines (Backspace at the start of a line, Delete at its end,
 * deleting a selection across lines).
 *
 * Quill always moves the first line into the second one, then removes the
 * first. When the first is the last line of a preserved container, the
 * container is left empty and goes away — Backspace at the start of the
 * paragraph following a <div class="buttons"> would delete the <div>. In that
 * case only, the reverse is done: the second line joins the first, in its
 * container.
 */
function patchLineMerge(Quill, HtmlBlock) {

    const Scroll = Quill.import('blots/scroll');
    const BlockEmbed = Quill.import('blots/block/embed');
    const Break = Quill.import('blots/break');
    const scrollBlot = Object.getPrototypeOf(Scroll.prototype);

    const emptiesContainer = function (line) {
        return line.parent instanceof HtmlBlock && line.parent.children.length === 1;
    };

    Scroll.prototype.deleteAt = function (index, length) {

        const [first, offset] = this.line(index);
        const [last] = this.line(index + length);

        scrollBlot.deleteAt.call(this, index, length);

        if (last != null && first !== last && offset > 0) {

            if (first instanceof BlockEmbed || last instanceof BlockEmbed) {
                this.optimize();
                return;
            }

            if (emptiesContainer(first) && !emptiesContainer(last)) {
                last.moveChildren(first);
                last.remove();
            }
            else {
                const ref = last.children.head instanceof Break ? null : last.children.head;
                first.moveChildren(last, ref);
                first.remove();
            }
        }

        this.optimize();
    };
}

/**
 * Enter on an empty line at the end of a preserved container: the line leaves
 * the container, as one leaves a list. Without this, nothing would allow
 * writing after a <div> placed at the end of the content.
 *
 * Returns true when the key must keep its usual behaviour.
 */
export function exitHtmlBlock(quill, range) {

    const Block = Quill.import('blots/block');
    const [line] = quill.getLine(range.index);
    const container = line ? line.parent : null;

    if (!container || !container.domNode.classList || !container.domNode.classList.contains(BLOCK_CLASS)) {
        return true;
    }

    if (line.next || !line.prev || !(line instanceof Block)) {
        return true;
    }

    container.parent.insertBefore(line, container.next);

    if (line.statics.blotName !== 'block') {
        line.replaceWith('block');
    }

    quill.update(Quill.sources.USER);
    quill.setSelection(range.index, 0, Quill.sources.SILENT);

    return false;
}
