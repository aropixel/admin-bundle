// The editor must store back exactly the HTML it was given — on load, after a
// small edit, after an undo — including the HTML Quill has no format for.
import { test, describe } from 'node:test';
import assert from 'node:assert/strict';
import { createEditorEnv } from './editor-env.mjs';

const { window: w, load } = createEditorEnv();

/**
 * Stored HTML that must survive the editor unchanged. Each entry is either the
 * HTML itself, or { html, stored } when loading legitimately normalises it.
 */
const CORPUS = {
    'paragraphs and bold': '<p>Simple <strong>text</strong></p><p>Second</p>',
    'div of button links (TW-4789)': '<div class="btns"><a class="uk-button uk-button-primary" href="https://example.org/vip" target="_blank">Become VIP</a> <a class="uk-button" href="https://example.org/pmr" target="_blank">Accessibility</a></div>',
    'div between paragraphs': '<p>before</p><div style="text-align:center">in a div</div><p>after</p>',
    'button': '<p>before</p><p><button class="b" type="button">btn</button></p><p>after</p>',
    'div wrapping paragraphs': '<div class="wrap"><p>first</p><p>second</p></div>',
    'nested divs': '<div class="a"><div class="b">nested</div></div>',
    'section': '<section><p>in a section</p></section>',
    'blockquote of paragraphs': '<blockquote><p>one</p><p>two</p></blockquote>',
    'nested list': '<ul><li>a<ul><li>a1</li></ul></li><li>b</li></ul>',
    'paragraph in a list item': '<ul><li><p>paragraph</p></li></ul>',
    'table with header and paragraphs': '<table><thead><tr><th>T</th></tr></thead><tbody><tr><td><p>p1</p><p>p2</p></td></tr></tbody></table>',
    'table cell attributes': '<table><tbody><tr><td colspan="2" style="width:50%">c</td></tr></tbody></table>',
    'horizontal rule': '<p>a</p><hr><p>b</p>',
    'span with class, empty icon': '<p>a <span class="x" style="color:red">red</span> <i class="fa fa-check"></i> end</p>',
    'paragraph attributes': '<p class="intro" id="i1" style="text-align:center">attrs</p>',
    'pre keeps its whitespace': '<h2 style="color:#c00">Title</h2><pre>code\n  indented</pre>',
    'small, mark, font': '<p>x<sup>2</sup> <small>small</small> <mark>m</mark> <font color="red">f</font></p>',
    'definition list': '<dl><dt>term</dt><dd>definition</dd></dl>',
    'comment': '<p>with <!-- a comment --> comment</p>',
    'leading style element': '<style>.b{color:red}</style><p>with style</p>',
    'script keeps its line breaks': '<p>a</p><script>var a = 1; // one\nvar b = 2;</script>',
    'svg': '<p>icon <svg viewBox="0 0 10 10" width="10"><path d="M0 0L10 10"></path></svg></p>',
    'bare text': 'Bare text <strong>bold</strong>',
    'empty div': '<div class="spacer"></div><p>after spacer</p>',
    'plain lists': '<ul><li>one</li><li>two</li></ul><ol><li>three</li></ol>',
    'link wrapping a div': '<a href="https://example.org"><div class="btn">Become VIP</div></a>',
    'adjacent links to the same page': '<p><a class="b1" href="https://example.org" target="_blank">A</a><a class="b2" href="https://example.org" target="_blank">B</a></p>',
    'video in a div': '<div class="videodetector"><iframe class="ql-video" src="https://www.youtube.com/embed/x"></iframe></div>',
    'soft line break': '<p>line<br>next</p>',
    'formats inside and around buttons': '<p><strong><button type="button">bold</button></strong> <button type="button"><em>italic</em></button></p>',
    'form controls': '<form action="/x"><input name="q" value="v"><button>go</button></form><p>after</p>',
    'entities and non-breaking spaces': '<p>a &lt; b &amp; c&nbsp;d</p>',
    'iframe gets the Quill video class': {
        html: '<p><iframe src="https://www.youtube.com/embed/x"></iframe></p>',
        stored: '<p><iframe src="https://www.youtube.com/embed/x" class="ql-video"></iframe></p>',
    },
    'CKEditor line breaks between blocks': {
        html: '<p>one<br>\ntwo</p>\n\n<div class="x">\n  <p>in div</p>\n</div>\n',
        stored: '<p>one<br>two</p><div class="x"><p>in div</p></div>',
    },
};

/**
 * Index of a position inside a text node, as close to the middle as possible:
 * typing where an embed stands would open a new line instead.
 */
function textIndex(quill) {
    const TextBlot = w.Quill.import('blots/text');
    const length = quill.getLength();
    for (let n = 0, k = Math.floor((length - 1) / 2); n < length; n++, k = (k + 1) % length) {
        const [leaf, offset] = quill.getLeaf(k);
        if (leaf instanceof TextBlot && offset > 0) {
            return k;
        }
    }
    return -1;
}

describe('stored HTML survives the editor', () => {

    for (const [name, entry] of Object.entries(CORPUS)) {

        const html = typeof entry === 'string' ? entry : entry.html;
        const stored = typeof entry === 'string' ? entry : entry.stored;

        test(name + ': loading', () => {
            const field = load(html);
            assert.equal(field.textarea.value, html, 'loading must not rewrite the field');
            assert.equal(field.stored(), stored);
        });

        test(name + ': typing a character', (t) => {
            const field = load(html);
            const at = textIndex(field.quill);
            if (at === -1) {
                t.skip('no text to type into');
                return;
            }
            field.quill.insertText(at, '§', 'user');
            assert.equal(field.textarea.value.replace('§', ''), stored);
        });

        test(name + ': opening and closing the source view', () => {
            const field = load(html);
            const source = field.source();
            source.open();
            source.close();
            assert.equal(field.textarea.value, stored);
        });
    }
});

const BUTTONS = '<p>Intro</p><div class="btns"><a class="uk-button" href="https://example.org/vip" target="_blank">VIP</a> <a class="uk-button" href="https://example.org/pmr" target="_blank">PMR</a></div><p>End</p>';

describe('editing preserved HTML', () => {

    test('Enter inside a div splits it into paragraphs, inside the div', () => {
        const field = load(BUTTONS);
        field.quill.insertText(field.indexOf('PMR'), '\n', 'user');
        assert.equal(field.textarea.value, '<p>Intro</p><div class="btns"><p><a class="uk-button" href="https://example.org/vip" target="_blank">VIP</a> </p><p><a class="uk-button" href="https://example.org/pmr" target="_blank">PMR</a></p></div><p>End</p>');
    });

    test('Backspace after a div joins the next line into the div, the div stays', () => {
        const field = load(BUTTONS);
        field.quill.deleteText(field.indexOf('End') - 1, 1, 'user');
        assert.equal(field.textarea.value, '<p>Intro</p><div class="btns"><a class="uk-button" href="https://example.org/vip" target="_blank">VIP</a> <a class="uk-button" href="https://example.org/pmr" target="_blank">PMR</a>End</div>');
    });

    test('Enter on an empty last line leaves the div', () => {
        const field = load(BUTTONS);
        const at = field.indexOf('PMR') + 3;
        field.quill.insertText(at, '\n', 'user');
        field.quill.setSelection(at + 1, 0);
        field.quill.root.dispatchEvent(new w.KeyboardEvent('keydown', { key: 'Enter', bubbles: true, cancelable: true }));
        field.quill.insertText(field.quill.getSelection().index, 'after', 'user');
        assert.equal(field.textarea.value, '<p>Intro</p><div class="btns"><a class="uk-button" href="https://example.org/vip" target="_blank">VIP</a> <a class="uk-button" href="https://example.org/pmr" target="_blank">PMR</a></div><p>after</p><p>End</p>');
    });

    test('bold over a button goes inside it', () => {
        const field = load('<p><button class="b" type="button">btn</button> next</p>');
        field.quill.formatText(0, 3, 'bold', true, 'user');
        assert.equal(field.textarea.value, '<p><button class="b" type="button"><strong>btn</strong></button> next</p>');
    });

    test('a link split by bold keeps its class on both halves', () => {
        const field = load(BUTTONS);
        field.quill.formatText(field.indexOf('VIP'), 2, 'bold', true, 'user');
        assert.match(field.textarea.value, /<strong><a class="uk-button" href="https:\/\/example.org\/vip" target="_blank">VI<\/a><\/strong><a class="uk-button" href="https:\/\/example.org\/vip" target="_blank">P<\/a>/);
    });

    test('text typed in a table cell stays in the cell', () => {
        const field = load('<table><tbody><tr><th>T</th><td>c</td></tr></tbody></table>');
        field.quill.insertText(field.indexOf('c') + 1, ' added', 'user');
        assert.equal(field.textarea.value, '<table><tbody><tr><th>T</th><td>c added</td></tr></tbody></table>');
    });

    test('a new item in a nested list stays in the nested list', () => {
        const field = load('<ul><li>a<ul><li>a1</li></ul></li><li>b</li></ul>');
        field.quill.insertText(field.indexOf('a1') + 2, '\nnew', 'user');
        assert.equal(field.textarea.value, '<ul><li>a<ul><li>a1</li><li>new</li></ul></li><li>b</li></ul>');
    });

    test('emptying the editor stores an empty string', () => {
        const field = load(BUTTONS);
        field.quill.deleteText(0, field.quill.getLength(), 'user');
        assert.equal(field.textarea.value, '');
    });
});

describe('undo', () => {

    test('undo right after loading changes nothing', () => {
        const field = load(BUTTONS);
        field.quill.history.undo();
        assert.equal(field.stored(), BUTTONS);
    });

    test('undoing the deletion of a div brings back the div and the link classes', () => {
        const field = load(BUTTONS);
        field.quill.deleteText(field.indexOf('VIP'), 'VIP PMR\n'.length, 'user');
        field.quill.history.cutoff();
        field.quill.history.undo();
        assert.equal(field.textarea.value, BUTTONS);
    });

    test('undo twice then redo', () => {
        const field = load(BUTTONS);
        field.quill.insertText(field.indexOf('End') + 3, ' A', 'user');
        field.quill.history.cutoff();
        field.quill.deleteText(field.indexOf('VIP'), 'VIP PMR\n'.length, 'user');
        field.quill.history.cutoff();
        field.quill.history.undo();
        field.quill.history.undo();
        field.quill.history.redo();
        assert.equal(field.textarea.value, BUTTONS.replace('End</p>', 'End A</p>'));
    });
});

describe('preserved HTML is inert in the admin form', () => {

    test('a preserved form control is disabled in the preview, not in the stored HTML', () => {
        const field = load('<form action="/x"><input name="q" value="v"><button>go</button></form><p>after</p>');
        assert.equal(field.quill.root.querySelectorAll('input:not([disabled]), button:not([disabled])').length, 0);
        assert.equal(field.stored(), '<form action="/x"><input name="q" value="v"><button>go</button></form><p>after</p>');
    });

    test('clicking a preserved button does not submit the form', () => {
        const field = load('<p><button class="b">btn</button></p>');
        const button = field.quill.root.querySelector('button');
        const click = new w.MouseEvent('click', { bubbles: true, cancelable: true });
        button.dispatchEvent(click);
        assert.equal(click.defaultPrevented, true);
    });

    test('a style element is shown as a label, not applied to the admin', () => {
        const field = load('<style>.b{color:red}</style><p>x</p>');
        assert.equal(field.quill.root.querySelectorAll('style').length, 0);
    });
});
