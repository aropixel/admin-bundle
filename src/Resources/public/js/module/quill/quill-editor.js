/* ------------------------------------------------------------------------------
*
*  # Éditeur Quill de l'administration
*
*  Regroupe toute la configuration de l'éditeur WYSIWYG :
*   - barres d'outils (par défaut + personnalisées via window.aropixelQuillToolbars)
*   - boutons « image » et « fichier » branchés sur les médiathèques
*   - saut de ligne simple (<br>) avec Shift + Entrée
*   - collage nettoyé (on ne garde que les formats structurants)
*   - édition du code source HTML (le bouton <> remplace le rendu par le code)
*   - choix de la cible du lien (case « Nouvel onglet » dans la bulle de lien)
*   - infobulles françaises sur les outils de la barre
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
 * Libellés anglais de secours des infobulles, indexés par sélecteur CSS.
 * Les libellés dans la langue de l'admin arrivent via window.aroQuillI18n
 * (rendu par base.html.twig avec |trans, comme aroDialogI18n) ; la surcharge
 * intégrateur passe par window.aropixelQuillTooltips et gagne sur tout.
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
 * Chaînes traduites de l'éditeur, rendues par base.html.twig.
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

    // Quill 2 force target="_blank" (et rel) sur tous les liens. On étend le
    // format pour que la cible devienne un choix — la case « Nouvel onglet »
    // de la bulle de lien (cf. enhanceLinkTooltip) — conservé dans le HTML.
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
 * Inverse de prettyHtml : retire l'indentation et les sauts de ligne ajoutés
 * pour la lisibilité (ou tapés à la main dans le mode source), afin qu'ils ne
 * polluent ni le HTML stocké ni le rendu de l'éditeur (white-space: pre-wrap).
 * Les espaces significatifs — entre éléments en ligne — sont préservés.
 */
const CONTAINER_TAGS = /^(BODY|UL|OL|TABLE|THEAD|TBODY|TFOOT|TR)$/;

function compactHtml(html) {

    const doc = new DOMParser().parseFromString(html || '', 'text/html');

    const strip = function (parent) {

        Array.from(parent.childNodes).forEach(function (node) {

            if (node.nodeType === Node.ELEMENT_NODE) {
                strip(node);
                return;
            }

            if (node.nodeType !== Node.TEXT_NODE || node.textContent.trim()) {
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
        });
    };

    strip(doc.body);

    return doc.body.innerHTML;
}

/**
 * Construit (à la demande) le panneau d'édition du code source HTML.
 * Ouvert, il remplace le texte rendu ; le code y est modifiable, tenu en
 * phase avec le champ soumis, et réappliqué à l'éditeur à la fermeture.
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

    // Tant que le mode source est ouvert, le textarea est la source de vérité :
    // soumettre le formulaire sans refermer le panneau ne perd rien.
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
 * Pose les infobulles (attributs title) sur les contrôles de la barre d'outils.
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
 * Ajoute à la bulle de lien du thème Snow une case « Nouvel onglet » qui
 * pilote l'attribut target du lien (cf. AroLink dans setupQuill). Un nouveau
 * lien s'ouvre dans un nouvel onglet par défaut — le comportement historique —
 * mais le choix est désormais conservé et rechargé à l'édition du lien.
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

    // target du lien sous la sélection : '' si lien sans target, null si pas de lien
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

    // Réécriture du save de BaseTooltip pour le mode lien : même logique,
    // mais la valeur du format devient { href, target }.
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
 * Ajoute le bouton d'édition du code source HTML à la barre d'outils.
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
                        buildHtmlSource(this.quill, $this.parent()[0], $target).toggle();
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

        applyTooltips(quill);
        enhanceLinkTooltip(quill);

        // Initialize content
        quill.root.innerHTML = toEditorHtml($target.val());
        quill.update();
        $target.val(toStorageHtml(quill.root.innerHTML));

        quill.on('text-change', function () {
            $target.val(toStorageHtml(quill.root.innerHTML));
        });
    });
}
