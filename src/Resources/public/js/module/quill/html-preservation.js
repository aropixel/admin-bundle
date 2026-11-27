/* ------------------------------------------------------------------------------
*
*  # Préservation du HTML que Quill ne sait pas représenter
*
*  Quill 2 ne connaît qu'un petit jeu d'éléments (paragraphes, titres, listes
*  plates, gras, liens…). Tout le reste — <div>, <section>, <button>, <small>,
*  <hr>, <pre>, une liste imbriquée, un paragraphe dans une cellule… — est
*  supprimé au chargement, texte compris, et l'enregistrement suivant le perd.
*
*  Ce module rend ces éléments représentables, sans rien perdre à l'aller-retour
*  ni après une modification :
*
*   - conteneur (`aro-html-block`) : un élément de niveau bloc, ou qui contient
*     des blocs. Il garde sa balise et ses attributs, et ses lignes restent
*     éditables. Le texte posé directement dedans est porté par une ligne
*     implicite (<aro-line>), retirée à l'enregistrement ;
*   - en ligne (`aro-html-inline`) : un élément en ligne inconnu (<button>,
*     <small>, <span class="…">…). Il garde sa balise et ses attributs, et son
*     texte reste éditable ;
*   - atome (<aro-embed>) : ce qui ne s'édite pas comme du texte (<hr>, <pre>,
*     <svg>, <script>, un commentaire, une icône vide <i class="fa …">…). Il est
*     conservé tel quel, octet pour octet, et ne se modifie que dans le mode
*     source.
*
*  Le marquage est fait par prepareHtml() (HTML stocké -> HTML de l'éditeur) et
*  retiré par restoreHtml() (l'inverse). Le registre de Parchment cherche un
*  blot par classe avant de le chercher par balise : la classe-marqueur suffit
*  à ce que le bon blot prenne l'élément, quelle que soit sa balise.
*
* ---------------------------------------------------------------------------- */

export const BLOCK_CLASS = 'aro-html-block';
export const INLINE_CLASS = 'aro-html-inline';
const LINE_TAG = 'ARO-LINE';
const EMBED_TAG = 'ARO-EMBED';
const EMBED_ATTR = 'data-aro-html';
const BLOCK_EMBED_CLASS = 'aro-html-block-embed';

/**
 * Lignes que Quill représente nativement — tant qu'elles ne contiennent que du
 * texte et des éléments en ligne.
 */
const NATIVE_LINE_TAGS = /^(P|H[1-6]|BLOCKQUOTE|LI)$/;

/**
 * Éléments en ligne que Quill représente nativement (gras, italique, lien…).
 */
const NATIVE_INLINE_TAGS = /^(A|STRONG|B|EM|I|U|S|STRIKE|SUB|SUP|CODE|SPAN)$/;

/**
 * Éléments vides par nature, que Quill représente nativement.
 */
const NATIVE_VOID_TAGS = /^(BR|IMG)$/;

/**
 * Attributs de <span> que Quill comprend : au-delà, le <span> serait déballé
 * et ses attributs perdus. Couleurs (style) et classes ql-*.
 */
const NATIVE_SPAN_STYLES = /^(color|background-color)$/;
const NATIVE_SPAN_CLASSES = /^ql-(size|font|align|direction|indent)-/;

/**
 * Éléments de niveau bloc au sens HTML : toujours un conteneur, même s'ils ne
 * contiennent que du texte.
 */
const BLOCK_LEVEL_TAGS = /^(DIV|SECTION|ARTICLE|ASIDE|HEADER|FOOTER|NAV|MAIN|ADDRESS|FIGURE|FIGCAPTION|DETAILS|SUMMARY|CENTER|HGROUP|FIELDSET|LEGEND|DL|DT|DD|MENU|TABLE|CAPTION|THEAD|TBODY|TFOOT|TR|TD|TH|UL|OL|LI|BLOCKQUOTE|P|H[1-6])$/;

/**
 * Structure de tableau : Quill n'en gère qu'une version réduite (ni <thead>, ni
 * <th>, une seule ligne par cellule). Un tableau est donc toujours conservé
 * comme une imbrication de conteneurs.
 */
const TABLE_TAGS = /^(TABLE|CAPTION|THEAD|TBODY|TFOOT|TR|TD|TH)$/;

/**
 * Éléments conservés tels quels : leur contenu ne s'édite pas comme du texte.
 */
const ATOMIC_TAGS = /^(HR|PRE|SVG|MATH|SCRIPT|STYLE|NOSCRIPT|TEMPLATE|VIDEO|AUDIO|OBJECT|EMBED|CANVAS|PICTURE|FORM|INPUT|SELECT|TEXTAREA|COLGROUP|COL|WBR|MAP|AREA|SOURCE|TRACK|LINK|META)$/;

/**
 * Atomes de niveau bloc : posés directement dans leur conteneur, pas dans une
 * ligne.
 */
const BLOCK_ATOMIC_TAGS = /^(HR|PRE|FORM|COLGROUP|VIDEO|AUDIO|OBJECT|EMBED|CANVAS|PICTURE|MATH)$/;

/**
 * Atomes sans rendu dans l'éditeur : on n'en montre qu'une étiquette (un
 * <style> appliquerait ses règles à toute l'administration).
 */
const LABELLED_ATOMIC_TAGS = /^(SCRIPT|STYLE|NOSCRIPT|TEMPLATE|LINK|META)$/;

const isElement = function (node) {
    return node.nodeType === Node.ELEMENT_NODE;
};

const isBlankText = function (node) {
    return node.nodeType === Node.TEXT_NODE && !node.textContent.replace(/[\s]/g, '');
};

/**
 * Un <span> dont Quill comprend tous les attributs reste un <span> Quill.
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
 * Rôle d'un nœud pour son parent : 'block' exige un contexte de blocs,
 * 'inline' tient dans une ligne, 'blank' est un espace sans signification.
 *
 * Le marquage se fait en remontant : un élément est classé après ses enfants,
 * puisqu'un élément en ligne qui contient un bloc devient lui-même un
 * conteneur.
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

    // Les nœuds d'interface de Quill sont régénérés par Quill lui-même
    if (node.classList.contains('ql-ui')) {
        return 'blank';
    }

    // Vidéo : le blot natif (iframe.ql-video) est un bloc, valable dans
    // n'importe quel conteneur.
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

    // En ligne, vide : rien à éditer (icône <i class="fa …">, ancre <a name>)
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
 * Remplace un atome par son représentant dans l'éditeur : le HTML d'origine,
 * intact, est porté par un attribut, et l'élément n'est montré qu'à titre
 * d'aperçu.
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
 * L'aperçu vit dans le formulaire d'administration : un <input name> y serait
 * posté avec la fiche, un bouton la soumettrait. Il est donc inerte, et ses
 * contrôles désactivés (un contrôle désactivé n'est jamais posté). Seul
 * l'aperçu est touché : le HTML enregistré est celui de l'attribut.
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
 * Un <button> conservé reste éditable (son texte est du texte), mais un clic
 * dessus soumettrait le formulaire d'administration.
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
 * Dans un conteneur, Quill n'accepte que des lignes et des blocs : chaque suite
 * de texte et d'éléments en ligne posée directement dedans est donc portée par
 * une ligne implicite. Une suite faite d'espaces seuls, entre deux blocs, n'a
 * pas de sens et disparaît.
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

    // Un conteneur vide (<div class="spacer"></div>) doit le rester : sans
    // ligne, Parchment le supprimerait.
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
 * HTML stocké (déjà analysé) -> HTML de l'éditeur, en place.
 */
export function prepareHtml(doc) {

    const embeds = [];

    Array.from(doc.body.childNodes).forEach(function (node) {
        classify(node, doc, embeds);
    });

    // Les atomes d'abord : ils sortent de l'arbre avant le découpage en lignes
    embeds.forEach(function (item) {
        toEmbed(doc, item.node, item.block);
    });

    doc.body.querySelectorAll('.' + BLOCK_CLASS).forEach(function (container) {
        wrapInlineRuns(doc, container);
    });

    // Le premier niveau aussi : sans ligne implicite, Quill envelopperait le
    // texte nu dans un <p>, que l'enregistrement ajouterait au contenu.
    if (Array.from(doc.body.childNodes).some(function (node) { return !isBlockChild(node) && !isBlankText(node); })) {
        wrapInlineRuns(doc, doc.body);
    }
}

/**
 * HTML de l'éditeur (déjà analysé) -> HTML stocké, en place.
 */
export function restoreHtml(doc) {

    // Atomes : le HTML d'origine, octet pour octet
    Array.from(doc.body.querySelectorAll(EMBED_TAG.toLowerCase())).forEach(function (embed) {

        const html = embed.getAttribute(EMBED_ATTR) || '';
        const template = doc.createElement('template');
        template.innerHTML = html;

        embed.parentNode.replaceChild(template.content, embed);
    });

    // Lignes implicites. Seule entre deux blocs, elle redevient le texte nu
    // d'origine ; plusieurs à la suite, c'est que l'utilisateur a coupé la
    // ligne (Entrée) : ce sont alors de vrais paragraphes.
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
 * Le contenu d'un atome compte : un champ fait d'un seul <hr> n'est pas vide.
 */
export const EMBED_SELECTOR = EMBED_TAG.toLowerCase();

let registered = false;

/**
 * Blots des trois familles. À n'appeler qu'une fois, après Quill.
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
     * Conteneur : balise et attributs d'origine, jamais fusionné avec son
     * voisin (deux <div> côte à côte restent deux <div>).
     */
    class HtmlBlock extends Container {
        checkMerge() {
            return false;
        }
    }
    HtmlBlock.blotName = 'aro-html-block';
    HtmlBlock.className = BLOCK_CLASS;
    // Balise propre : une balise réelle (DIV) ferait de ce blot celui de tous
    // les <div>, marqués ou non. Un conteneur n'est jamais créé par son nom :
    // il vient du HTML chargé, ou d'un clone (cloneNode, balise conservée).
    HtmlBlock.tagName = 'ARO-BLOCK';

    /**
     * Ligne implicite : du texte posé directement dans un conteneur. Une balise
     * à elle, pour que le delta la distingue d'un paragraphe (annuler une
     * suppression la recrée à l'identique).
     */
    class HtmlLine extends Block {}
    HtmlLine.blotName = 'aro-line';
    HtmlLine.tagName = LINE_TAG;

    /**
     * En ligne : balise et attributs d'origine. Pas de fusion avec un voisin
     * identique (deux boutons restent deux boutons), pas de déballage, pas de
     * réordonnancement avec le gras ou l'italique qu'il contient.
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

    // Inline.compare range gras, italique, lien… dans un ordre d'imbrication
    // fixe, et Quill réordonne les éléments pour le respecter. Un élément
    // conservé garde sa place : il n'est comparé à rien.
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
     * Atome en ligne : <i class="fa …"></i>, un commentaire, un <script>…
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
     * Atome de niveau bloc : <hr>, <pre>, <form>…
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
 * Fusion de deux lignes (Retour arrière en début de ligne, Suppr en fin de
 * ligne, suppression d'une sélection à cheval).
 *
 * Quill déplace toujours la première ligne dans la seconde, puis supprime la
 * première. Quand la première est la dernière ligne d'un conteneur conservé,
 * le conteneur se retrouve vide et disparaît — Retour arrière au début du
 * paragraphe qui suit un <div class="boutons"> supprimerait le <div>. Dans ce
 * cas seulement, on fait l'inverse : la seconde ligne rejoint la première, dans
 * son conteneur.
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
 * Entrée sur une ligne vide, en fin de conteneur conservé : la ligne sort du
 * conteneur, comme on sort d'une liste. Sans cela, rien ne permettrait d'écrire
 * après un <div> placé en fin de contenu.
 *
 * Renvoie true quand la touche doit garder son comportement ordinaire.
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
