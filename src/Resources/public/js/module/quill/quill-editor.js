/* ------------------------------------------------------------------------------
*
*  # Éditeur Quill de l'administration
*
*  Regroupe toute la configuration de l'éditeur WYSIWYG :
*   - barres d'outils (par défaut + personnalisées via window.aropixelQuillToolbars)
*   - boutons « image » et « fichier » branchés sur les médiathèques
*   - saut de ligne simple (<br>) avec Shift + Entrée
*   - collage nettoyé (on ne garde que les formats structurants)
*   - prévisualisation du HTML généré
*   - traduction entre le HTML stocké (<ul>, sans nœuds d'interface) et la
*     représentation interne de Quill 2 (<ol><li data-list="bullet">, .ql-ui)
*
* ---------------------------------------------------------------------------- */

/**
 * Formats conservés lors d'un copier/coller.
 * Surchargeable via window.aropixelQuillPasteFormats.
 */
const DEFAULT_PASTE_FORMATS = [
    'bold', 'italic', 'underline', 'strike',
    'link', 'list', 'indent', 'header', 'blockquote',
    'code', 'code-block', 'script', 'align', 'direction',
    'softbreak', 'image', 'video',
];

const DEFAULT_TOOLBARS = {
    'full': [
        [{ 'header': [1, 2, 3, 4, 5, 6, false] }],
        ['bold', 'italic', 'underline', 'strike'],
        ['blockquote', 'code-block'],
        [{ 'list': 'ordered'}, { 'list': 'bullet' }],
        [{ 'script': 'sub'}, { 'script': 'super' }],
        [{ 'indent': '-1'}, { 'indent': '+1' }],
        [{ 'direction': 'rtl' }],
        [{ 'size': ['small', false, 'large', 'huge'] }],
        [{ 'color': [] }, { 'background': [] }],
        [{ 'font': [] }],
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

const HTML_ICON = '<svg viewBox="0 0 18 18">'
    + '<polyline class="ql-stroke" points="6 5 3 9 6 13"></polyline>'
    + '<polyline class="ql-stroke" points="12 5 15 9 12 13"></polyline>'
    + '</svg>';

const FILE_ICON = '<svg viewBox="0 0 18 18">'
    + '<path class="ql-stroke" fill="none" d="M5,2 H10 L14,6 V16 H5 Z"></path>'
    + '<path class="ql-stroke" fill="none" d="M10,2 V6 H14"></path>'
    + '</svg>';

const VOID_TAGS = /^(BR|HR|IMG|INPUT|SOURCE|WBR)$/;
const BLOCK_TAGS = /^(P|DIV|H[1-6]|UL|OL|LI|BLOCKQUOTE|PRE|HR|TABLE|THEAD|TBODY|TFOOT|TR|TD|TH|FIGURE|FIGCAPTION|SECTION|ARTICLE|IFRAME|VIDEO)$/;

let setupDone = false;

/**
 * Enregistrements globaux à ne faire qu'une seule fois.
 */
function setupQuill() {

    if (setupDone || typeof Quill === 'undefined') {
        return;
    }
    setupDone = true;

    // Saut de ligne simple : un vrai <br> plutôt qu'un nouveau bloc.
    const Embed = Quill.import('blots/embed');

    class SoftBreak extends Embed {
        // Parchment supprimerait le <br> considéré comme vide
        optimize() {}
        length() { return 1; }
    }

    SoftBreak.blotName = 'softbreak';
    SoftBreak.tagName = 'BR';
    Quill.register(SoftBreak);

    // Icônes absentes du jeu natif de Quill
    const icons = Quill.import('ui/icons');
    if (!icons['html']) {
        icons['html'] = HTML_ICON;
    }
    if (!icons['file']) {
        icons['file'] = FILE_ICON;
    }
}

/**
 * Ne conserve, dans un delta collé, que les formats structurants.
 * C'est ce qui évite les <span style="background-color: white"> & Cie.
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
 * Convertit les <br> collés (ou chargés) en saut de ligne simple.
 */
function softBreakMatcher() {
    const Delta = Quill.import('delta');
    return new Delta().insert({ softbreak: true });
}

/**
 * Quill 2 représente *toutes* ses listes par un <ol> dont les <li> portent un
 * attribut data-list, et injecte des <span class="ql-ui"> pour dessiner puces
 * et numéros. Les deux fonctions ci-dessous font la traduction avec le HTML
 * réellement stocké en base, qui doit rester du HTML sémantique ordinaire.
 */

const BULLET_KINDS = ['bullet', 'checked', 'unchecked'];

/**
 * HTML stocké -> HTML attendu par Quill.
 */
export function toEditorHtml(html) {

    if (!html || (html.indexOf('<ul') === -1 && html.indexOf('ql-ui') === -1)) {
        return html || '';
    }

    const doc = new DOMParser().parseFromString(html, 'text/html');

    // Résidus d'un enregistrement antérieur : Quill les régénère lui-même
    doc.querySelectorAll('.ql-ui').forEach(function (node) {
        node.remove();
    });

    doc.querySelectorAll('ul').forEach(function (ul) {

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
 * HTML de Quill -> HTML stocké.
 */
export function toStorageHtml(html) {

    if (!html || (html.indexOf('ql-ui') === -1 && html.indexOf('<ol') === -1)) {
        return html || '';
    }

    const doc = new DOMParser().parseFromString(html, 'text/html');

    // Nœuds d'interface (puces, numéros, cases à cocher, sélecteur de langage)
    doc.querySelectorAll('.ql-ui').forEach(function (node) {
        node.remove();
    });

    doc.querySelectorAll('ol').forEach(function (ol) {

        // Quill fusionne les listes adjacentes : un même <ol> peut mélanger
        // puces et numérotation. On le redécoupe en séries homogènes.
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
                // « bullet » et « ordered » sont désormais portés par la balise
                if (['bullet', 'ordered'].indexOf(li.getAttribute('data-list')) !== -1) {
                    li.removeAttribute('data-list');
                }
                list.appendChild(li);
            });

            fragment.appendChild(list);
        });

        ol.parentNode.replaceChild(fragment, ol);
    });

    return doc.body.innerHTML;
}

/**
 * Indente le HTML pour le rendre lisible dans la prévisualisation.
 */
function prettyHtml(html) {

    const doc = new DOMParser().parseFromString(html || '', 'text/html');

    const walk = function (parent, depth) {

        let out = '';
        const pad = '    '.repeat(depth);

        Array.from(parent.childNodes).forEach(function (node) {

            if (node.nodeType === Node.TEXT_NODE) {
                if (node.textContent.trim()) {
                    out += node.textContent;
                }
                return;
            }

            if (node.nodeType !== Node.ELEMENT_NODE) {
                return;
            }

            const tag = node.tagName.toLowerCase();
            const attrs = Array.from(node.attributes).map(function (attr) {
                return ' ' + attr.name + '="' + attr.value + '"';
            }).join('');
            const open = '<' + tag + attrs + '>';

            if (VOID_TAGS.test(node.tagName)) {
                out += (BLOCK_TAGS.test(node.tagName) ? '\n' + pad : '') + open;
                return;
            }

            const inner = walk(node, depth + 1);

            if (BLOCK_TAGS.test(node.tagName)) {
                const multiline = inner.indexOf('\n') !== -1;
                out += '\n' + pad + open + inner + (multiline ? '\n' + pad : '') + '</' + tag + '>';
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
 * Construit (à la demande) le panneau de prévisualisation du HTML.
 */
function buildHtmlPreview(quill, container) {

    if (quill.__htmlPreview) {
        return quill.__htmlPreview;
    }

    const panel = document.createElement('div');
    panel.className = 'quill-html-preview d-none';
    panel.innerHTML = ''
        + '<div class="quill-html-preview-header">'
        +     '<span class="quill-html-preview-title">HTML généré</span>'
        +     '<button type="button" class="quill-html-preview-copy">Copier</button>'
        + '</div>'
        + '<pre class="quill-html-preview-code"><code></code></pre>';

    container.appendChild(panel);

    const code = panel.querySelector('code');
    const copy = panel.querySelector('.quill-html-preview-copy');

    copy.addEventListener('click', function () {
        const text = code.textContent;
        const done = function () {
            copy.textContent = 'Copié !';
            setTimeout(function () { copy.textContent = 'Copier'; }, 1500);
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

    const preview = {
        panel: panel,
        code: code,
        isOpen: function () { return !panel.classList.contains('d-none'); },
        refresh: function () {
            if (preview.isOpen()) {
                code.textContent = prettyHtml(toStorageHtml(quill.root.innerHTML));
            }
        },
        toggle: function () {
            panel.classList.toggle('d-none');
            preview.refresh();
        }
    };

    quill.__htmlPreview = preview;
    quill.on('text-change', preview.refresh);

    return preview;
}

/**
 * Résout la barre d'outils demandée (nom connu, JSON, ou tableau).
 */
function resolveToolbar(toolbar) {

    const toolbars = Object.assign({}, DEFAULT_TOOLBARS, window.aropixelQuillToolbars || {});

    // jQuery.data() désérialise déjà le JSON des data-attributes
    if (Array.isArray(toolbar)) {
        return toolbar;
    }

    if (typeof toolbar === 'string' && toolbar.startsWith('[') && toolbar.endsWith(']')) {
        try {
            return JSON.parse(toolbar);
        }
        catch (e) {
            console.error('Erreur lors du parsing de la barre d\'outils Quill custom :', e);
        }
    }

    return toolbars[toolbar] || toolbars['full'];
}

/**
 * Ajoute le bouton de prévisualisation HTML à la barre d'outils.
 */
function withHtmlButton(container) {

    if (window.aropixelQuillHtmlPreview === false || !Array.isArray(container)) {
        return container;
    }

    // Déjà présent dans la barre d'outils : on ne l'ajoute pas une seconde fois
    if (JSON.stringify(container).indexOf('"html"') !== -1) {
        return container;
    }

    return container.concat([['html']]);
}

/**
 * Construit la configuration du module « toolbar », que la barre d'outils
 * ait été déclarée comme un simple tableau ou comme un objet Quill complet.
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

        // Éviter la double initialisation
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
                        // Shift + Entrée : saut de ligne simple (<br>)
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
                        buildHtmlPreview(this.quill, $this.parent()[0]).toggle();
                    },
                    // « file » n'est pas un format Quill : le handler DOIT être fourni ici.
                    // Un addHandler('file') posé après `new Quill` arriverait trop tard, le
                    // bouton n'aurait aucun écouteur. « image », lui, est un format connu :
                    // son handler peut rester surchargé plus bas.
                    file: function () {
                        const quill = this.quill;
                        if ($.fn.FileManager) {
                            $($target[0]).FileManager({
                                editor: quill,
                                category: $target.attr('data-class')
                            });
                        } else {
                            // Repli : saisie manuelle d'une URL de fichier
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

        // Surcharge du handler image pour utiliser l'ImageManager si possible
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

        // Initialize content
        quill.root.innerHTML = toEditorHtml($target.val());
        quill.update();
        $target.val(toStorageHtml(quill.root.innerHTML));

        quill.on('text-change', function () {
            $target.val(toStorageHtml(quill.root.innerHTML));
        });
    });
}
