// Loads Quill and the editor modules of the bundle into a jsdom window, the way
// app.js does in the admin, and exposes small helpers for the tests.
import { JSDOM } from 'jsdom';
import fs from 'node:fs';
import path from 'node:path';
import { createRequire } from 'node:module';
import { fileURLToPath } from 'node:url';

const here = path.dirname(fileURLToPath(import.meta.url));
const PUBLIC = path.resolve(here, '../../src/Resources/public');
const require = createRequire(import.meta.url);

export function createEditorEnv() {

    const dom = new JSDOM('<!doctype html><body></body>', { pretendToBeVisual: true, runScripts: 'outside-only' });
    const w = dom.window;

    // jsdom has no layout: Quill only needs these to exist
    const rect = () => ({ top: 0, left: 0, right: 0, bottom: 0, width: 0, height: 0, x: 0, y: 0 });
    w.Range.prototype.getBoundingClientRect = rect;
    w.Range.prototype.getClientRects = () => [];
    w.Element.prototype.scrollIntoView = () => {};

    w.eval(fs.readFileSync(path.join(PUBLIC, 'modules/quilljs/quill.js'), 'utf8'));
    w.eval(fs.readFileSync(require.resolve('jquery/dist/jquery.js'), 'utf8'));

    // The modules are ES modules served as-is: inline them into one script of
    // the window, so that they share its realm (DOM classes, Quill).
    const strip = (code) => code.replace(/^import .*$/gm, '').replace(/^export /gm, '');
    w.eval('(function () {'
        + strip(fs.readFileSync(path.join(PUBLIC, 'js/module/quill/html-preservation.js'), 'utf8'))
        + strip(fs.readFileSync(path.join(PUBLIC, 'js/module/quill/quill-editor.js'), 'utf8'))
        + '\nwindow.__editor = { activateQuillEditor, toStorageHtml, toEditorHtml, buildHtmlSource };'
        + '})();');

    let count = 0;

    /**
     * Renders an EditorType field holding `html` and activates it.
     */
    function load(html) {

        const id = 'field' + (count++);
        const box = w.document.createElement('div');
        box.className = 'quill-editor-container';
        box.innerHTML = '<textarea id="' + id + '" class="d-none"></textarea>'
            + '<div class="quill-editor" id="' + id + '_quill" data-target="#' + id + '" data-toolbar="full"></div>';
        w.document.body.appendChild(box);

        const textarea = w.document.getElementById(id);
        textarea.value = html;

        w.__editor.activateQuillEditor(w.jQuery('#' + id + '_quill'));
        const quill = w.Quill.find(w.document.getElementById(id + '_quill'));

        return {
            quill,
            textarea,
            box,
            /** What a save would store right now. */
            stored: () => w.__editor.toStorageHtml(quill.root.innerHTML),
            /** Index of a text in the editor. */
            indexOf: (needle) => quill.getText().indexOf(needle),
            source: () => w.__editor.buildHtmlSource(quill, box, w.jQuery(textarea)),
        };
    }

    return { window: w, load };
}
