# Feuille de route — suite de la refonte

> Prend la suite des 12 incréments faits (voir `design-system.md` §8). L'état de départ :
> Stisla supprimé, fondations + 12 composants sur tokens, CSS passé de 8 732 à ~3 900
> lignes, garde-fous CI au vert, référence visuelle à 48 captures.
>
> **Règle de travail inchangée** (plan initial, R1) : un incrément = une unité mergeable en
> moins d'une journée, adossée à une comparaison de captures. Un incrément non mergé en 3
> jours est découpé.

---

## Vue d'ensemble

Quatre lots. Ils sont **largement indépendants** — l'ordre ci-dessous est une recommandation,
pas une contrainte forte, sauf les dépendances signalées.

| Lot | Nature | Risque | Effort indicatif |
|---|---|---|---|
| A — Finir la migration CSS | Mécanique, méthode éprouvée | Faible | 5–7 incréments |
| B — Icônes (ux-icons / Tabler) | Atomique, transverse | **Élevé** (collision) | 1 gros incrément + préparation |
| C — Catalogue + documentation | Finition, valorisation | Faible | 3–4 incréments |
| D — Bascule locale + renommage final | Séquencé, sensible | Moyen | 3 incréments |

---

## Lot A — Finir la migration CSS

Même méthode qu'aux incréments 5–12 : mesurer l'usage, tokeniser, découper sous 200 lignes,
diff de captures. Huit fichiers restent à `0 token`.

**A1 — `_file.css` ✅ fait.** Découpé en `_media-library.css` (123 l.) et `_media-widget.css`
(199 l.), sur tokens. `.icons-list-extended` et `.thumb-rounded` supprimées (mortes).
`!important` du composant : 68 → 58 · hex : 99 → 81.

> La bibliothèque étant rendue **en modale** (hors référence visuelle), elle a été validée
> en pilotant réellement le navigateur : ouverture de `#modalLibrary` puis capture,
> archivée dans `var/visual/increment-A1/_manual-image-library-modal.png`. Chrome, table et
> boutons corrects sur les tokens. À noter, sans rapport avec le CSS : la table affiche
> « No data available » alors que 8 images existent — un sujet de requête AJAX à traiter
> ailleurs.

**A2 — `_wysiwyg.css` ✅ fait.** Le fichier était à ~95 % du **wysihtml5**, un ancien
éditeur **mort** : `app.js` l'initialise sur `.html5-editor`, sélecteur qu'aucun template
n'émet, donc son toolbar n'est jamais rendu. Réduit à `_editor.css` (une règle pour Quill,
le seul éditeur vivant). Les 4 chargements JS de wysihtml5 dans `Form/base.html.twig` —
inertes eux aussi — supprimés au passage. **48/48 captures identiques** : preuve que tout
était mort. `!important` 58 → 45 · hex 81 → 76.

**A3 — `_picker.css` ✅ fait.** Skin de pickadate + clockpicker, tous deux vivants
(initialisés sur `.pickadate` / `.pickatime`). Déplacé en `vendor/_pickers.css`, couleurs
tokenisées — dont 7× le token legacy `--main-bg-color`, converti avant sa disparition en D3.
`oversized_components` = **0** : plus aucun fichier hors plafond (critère §9 atteint).
`!important` 45 → 42 · hex 76 → 58.

> Rendu en popup au focus, hors référence visuelle. Le calendrier s'ouvre bien
> (`display:block` confirmé), mais pickadate masque l'input source et positionne le popup
> sur un champ d'affichage difficile à cadrer — je n'ai **pas** capturé le popup stylé.
> Vérification par le raisonnement : le seul changement de couleur visible est le jour
> sélectionné, `#0CABA8` → `#06BAB4`, le même décalage de teal que tout l'admin a reçu à
> l'incrément 2. Le reste est déplacement de fichier. Risque faible, mais capture non faite.

**A4 — `_menu.css`** (71 l.) — sidebar Stisla. **Vérifier d'abord s'il est mort** : la
sidebar a été abandonnée au profit du menu plein écran. Probable suppression pure.

**A5 — `_modal.css` + `_dropdown.css` + `_override.css`** (70 l. cumulées) — un incrément
groupé. Petits, pilotables par `--bs-modal-*` / `--bs-dropdown-*`.

**A6 — `_helpers.css` ✅ fait.** 172 → 43 lignes. Supprimé : le bloc `w-/h-/mw-*px`
entier (~40 l., **mort** — aucun usage), les variantes de bordure `-lg`/`-xl`/`-dashed` et
`.flex-center` (mortes). Gardés : 14 utilitaires d'espacement (39 usages) et 4 de bordure.
**48/48 identiques.** `!important` 37 → 33.

> Deux points d'audit tranchés, pas contournés :
> - Les espacements restent en **px, pas en tokens** : ce sont des utilitaires legacy
>   marqués pour remplacement par les équivalents Bootstrap (`.mt-3`…) au fil de la
>   migration des templates. Les tokeniser décalerait 15px→16px en silence. Leur
>   `!important` est légitime — c'est ainsi que les utilitaires battent les composants,
>   exactement comme les `.mt-*` de Bootstrap.
> - `.border-top`/`.border-bottom` **masquent** celles de Bootstrap 5 (les nôtres en
>   `currentColor`, gagnantes car non layées). `.border-left`/`.border-right` sont
>   légitimement à nous (BS5 les a renommées `start`/`end`). Gardées à l'identique ; une
>   passe ultérieure pourra réconcilier la paire top/bottom.
>
> Le fichier ne disparaît pas encore — il disparaîtra quand les templates passeront aux
> utilitaires Bootstrap. Mais il n'est plus un dépotoir.

**A7 — `Avatar` ✅ fait.** Composant `_avatar.css` (68 l.) : `.aro-avatar` circulaire (image
en `object-fit: cover`), état vide `.aro-avatar__placeholder` (surface légère + icône
centrée) au lieu du cercle gris uni, pastille de statut reprenant les tokens
`--aro-status-*`, tailles sm/lg. Appliqué au header via `Form/base.html.twig` avec repli
sur l'état vide. **Ferme la dernière entrée du §17.** 14 écrans de formulaire changent
(l'avatar), comme voulu.

> Le seul du lot A qui **crée** au lieu de nettoyer. Détail relevé : `cmp` signalait un 15e
> écart (`project-list-empty@1024`), mais la comparaison pixel donne **0 px** — les octets
> du PNG diffèrent, pas l'image. `cmp` compare les octets ; l'encodage PNG est parfois non
> déterministe. Faux positif sans conséquence, mais la comparaison gagnerait à être
> pixel-based (piste pour le harnais).

> À l'issue du lot A : `oversized_components` = 0, `hex_components` proche de 0 (hors
> `vendor/`), la plupart des `!important` restants confinés à `vendor/`.

---

## Lot B — Icônes (§11)

**Le chantier le plus risqué, à isoler.** ~220 références d'icônes (FontAwesome + Ionicons)
dans **44 templates**. Le plan initial est clair : un balayage **atomique**, un jour calme,
**jamais entrelacé** avec des éditions manuelles — sinon collision avec toute branche en cours.

**B0 — Prérequis, hors du balayage :**
1. ✅ **Lucide** choisi (§12), après rendu comparatif Tabler/Lucide sur les icônes réelles.
2. Ajouter `symfony/ux-icons` + `symfony/http-client` au `require` du bundle.
3. Câbler le `prepend()` — inutile finalement (§11), mais vérifier le fonctionnement de
   `ux:icons:lock` sur les templates du bundle depuis la sandbox.
4. ✅ Table de correspondance `fa-*` → `lucide:*` construite et validée (68/68 noms
   existent). **Correction du périmètre :** ~70 icônes FA uniques, **aucune Ionicon** — les
   ~220 estimées incluaient des faux positifs (`position-*`, `accordion-*`).

**B1 — Le balayage ✅ fait.** 123 icônes de template → `{{ ux_icon('lucide:…') }}`
(transformation scriptée, prévisualisée à blanc, 1 cas manuel : le titre `trans` du login),
+ 4 icônes générées en JS → SVG Lucide inline (spinner, toggle, croix d'upload), + 3
vestiges Icomoon (`icon-upload`, `icon-paperplane`, `icon-files-empty2`). **Zéro icône
FontAwesome restante** dans les templates comme dans le JS. `<link>` FontAwesome retiré de
`base.html.twig` et `login.html.twig`. Toutes les icônes importées en local via
`ux:icons:lock` (70 SVG). Rendu vérifié sur login, listes, formulaires.

**B2 — Nettoyage ✅ (essentiel).** `symfony/ux-icons` + `symfony/http-client` ajoutés au
`require` du bundle. `ux:icons:lock` documenté dans `installation.md`. `flag-icon-css` :
**rien à faire** — n'était utilisé dans aucun template ni chargé (le sélecteur de langue ne
l'a jamais câblé). Reste, sans urgence : retirer les fichiers modules FontAwesome/Ionicons
des assets (poids mort désormais), et les sélecteurs `.fa-laptop`/`.fa-network-wired` morts
de `_site-switcher.css` (ciblent un markup d'impersonation absent des templates).

> Gain : trois dépendances d'assets supprimées, coloration des icônes pilotée par les tokens.
> ⚠ Ce lot invalide une partie de la référence visuelle (les icônes changent partout) —
> prévoir une **nouvelle capture de référence** juste après.

---

## Lot C — Catalogue + documentation (§14, §15)

**En retard sur le plan**, qui voulait le catalogue construit au fil de l'eau. À rattraper.

**C1 — Catalogue de composants ✅ fait** (§14). Route `dev` uniquement `/_catalog`
(`CatalogAction`, gardé sur l'env + condition de route), template
`@AropixelAdmin/catalog/index.html.twig` **étendant le base admin** — donc rendu sur le CSS
réel, il ne peut pas diverger de l'admin. Sections : couleurs (avec la **hiérarchie
corail/teal/ardoise énoncée**), typographie, espacements/rayons, boutons (6 variantes ×
tailles × disabled), badges & statuts, avatar, cartes, contrôles de formulaire (dont les
interrupteurs à 3 états), icônes Lucide — chacune avec son snippet. **Capturé dans la
référence** (`catalog@1440/1024`) : le catalogue est son propre test de régression. 54/54
déterministes.

> Les nombreux `style=` inline du template sont du **chrome de catalogue** (swatches, mise
> en page), clairement commentés « NOT part of the design system », hors du grep `inline_styles`
> (limité à `Security/` et `Form/`). Les composants, eux, utilisent les vraies classes.

**C2 — Dette de test ✅ (partiel, honnête).** Le harnais pilote désormais les écrans à
interaction via un champ `open` (snippet JS exécuté après chargement, transition attendue,
puis capture). **2 des 5 automatisés et dans la référence** : menu plein écran et modale
bibliothèque d'images. Déterminisme re-vérifié : **52/52**.

> **Les 3 autres ne sont pas un problème de harnais mais de fixtures :** aucune entité de la
> sandbox n'exerce le widget fichier (modale fichier inatteignable) ni une CollectionType en
> mode offcanvas (offcanvas sans déclencheur) ; la modale de recadrage est multi-étapes et
> dépend de la table AJAX de la bibliothèque, actuellement vide. Chacune est documentée dans
> `screens.php` avec sa raison. La modale fichier est couverte **par proxy** — même
> `_media-library.css` que la modale image, elle capturée.

> **Deux découvertes.** (1) `#burgerMenu`/`#togglePassword` sont devenus des `<svg>` au
> balayage d'icônes ; `SVGElement` n'a pas de `.click()`, d'où le pilotage par
> `dispatchEvent(new MouseEvent('click'))` — qui confirme au passage que les handlers jQuery
> réels ne sont pas cassés. (2) Vrai **trou de couverture** de la sandbox : 3 composants du
> bundle ne sont rendus par aucune donnée de test.

**C3 — Documentation utilisateur ✅ fait** (§15). Créé `theming.md` — l'API publique de
thème par tokens `--aro-*` (surcharge une couleur, config `theme.colors`, liste des tokens,
renvoi au catalogue), qui n'existait pas. `css_customization.md` réécrit : renvoie au
catalogue et à `theming.md`, et liste les classes **supprimées** au rebuild (`.ts-*`,
`.tw-*`, tailles `w-px`, FontAwesome → `ux_icon`). Les **4 liens morts** de `doc/index.md`
supprimés (`adminbundle.md`…, jamais écrits ; les bundles compagnons ont leur propre dépôt),
remplacés par une section « Design & Theming ». Index de doc du `CLAUDE.md` du bundle mis à
jour (theming, blocs de formulaire, catalogue).

---

## Lot D — Bascule locale + renommage final (§15, §8) ✅ FAIT

**D1 — Anglais complété.** `text.error` (absent des **6** locales, rendu en clé brute)
ajouté partout. Clé pivot FR normalisée : `text_choose_image` → `text.choose_image` (le
français n'affichait rien). Chaînes françaises en dur externalisées — la modale d'options de
publication (`Form/base.html.twig`) et le widget d'attachement de fichier passent par
`{% trans %}` (clés existantes réutilisées, 5 nouvelles clés × 6 locales). 2 clés mortes
supprimées, 1 doublon FR retiré. Les 6 fichiers : XML valide, jeux de 202 clés identiques.

**D2 — EN par défaut.** Le défaut framework était déjà `en` ; le bundle ne force aucune
locale. `doc/i18n.md` corrigé (il annonçait 2 locales au lieu de 6 et donnait `fr` en
exemple). Vérifié à l'exécution sur `/admin/blog/post/1/edit` : rendu 100 % anglais, zéro
clé brute, zéro reliquat français.

**D3 — Renommage + tokens legacy.** `custom.css` → `style.css`. Le bloc `:root` legacy
(`--main-bg-color` & consorts) supprimé ; ses valeurs promues en `--aro-*`
(`--aro-color-primary-subtle`, `--aro-accent-magenta`) et tous les consommateurs migrés
(`_navbar.css`, page-bundle `_ui.css`, tail de `style.css`). Code Stisla mort supprimé
(skins spectrum & x-editable, pagination/nav/section-entry inertes) : `style.css` 388 → 172
lignes, `hex_components` 52 → 5, `important_components` 33 → 24 (plafonds du ratchet
abaissés). Références mises à jour (`base.html.twig`, `login.html.twig`, `.castor/catalog.php`,
`.castor/css.php`). Vérifié au harnais visuel : dashboard, menu-fullscreen, menu-main/footer,
auth-login à 0 diff ; page-builder identique. Les quelques règles réellement globales
(couleur de lien, `<hr>`, utilitaires MenuBundle) restent dans le tail de `style.css` plutôt
que dispersées ; le §1 « imports uniquement » est approché, pas atteint au pixel.

---

## Ce qui n'est PAS dans cette feuille de route

- **Le guide de migration ancien major → v3.** Livrable réel (§15) mais qui se rédige une
  fois la cible stable — après le lot D.
- **Le mode sombre.** Hors périmètre (§10). Le nommage des tokens le laisse possible.
- **Les composants du design system sans usage actuel** (`Stat`, `GridList`, `Accordion`…,
  §6). Spécifiés, non implémentés : à ajouter à la demande, pas en bloc.

---

## Séquence recommandée

```
Lot A (A1–A7)  ──►  Lot B (B0–B2)  ──►  nouvelle référence visuelle
                         │
        Lot C (C1–C3) ───┘  (C1/C2 peuvent démarrer en parallèle du lot A)
                                              │
                                     Lot D (D1–D3)  ──►  fin
```

**Pourquoi A avant B :** finir la migration CSS d'abord fait que le balayage d'icônes opère
sur des templates au CSS déjà stabilisé — on ne repasse pas dessus. Et B invalide la
référence visuelle, donc autant l'avoir enrichie par C2 avant.

**Pourquoi C2 tôt :** scripter les écrans à interaction lève l'angle mort qui grandit à
chaque composant refait sans filet (`_file` au lot A en fait partie).

**Pourquoi D en dernier :** le renommage final et la bascule de locale supposent que tout le
reste est stable.
