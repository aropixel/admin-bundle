# Cahier de specs — Design System AdminBundle

> **Statut : chantier très avancé.** Ce document guide la refonte CSS de l'AdminBundle. Il
> n'est pas de la documentation utilisateur. Le détail par étape est annoté « ✅ Fait » dans
> le corps ; la **section §0 ci-dessous** donne l'image d'ensemble et surtout ce qui reste,
> pour reprendre le travail sans relire tout le document.

---

## 0. État d'avancement (juillet 2026)

> Section de reprise. Les étapes du §8, les critères du §9 et les listes des §11/§15/§16
> portent le détail ; ce résumé les agrège.

### Fait

- **Ablation Stisla complète** — `style.css` (3 035 l.) + `components.css` (2 083 l.)
  supprimés. Volume CSS **8 732 → ~3 900 lignes, toutes les nôtres**. §1, §8-4/5.
- **Fondations sur tokens `--aro-*`** — `_tokens.css`, pont Bootstrap, typographie, layout,
  reset, **cascade layers actives**. La densité (14px) est tranchée. §3, §4, §8-3/6.
- **Poppins auto-hébergé**, zéro requête Google. §4.
- **Un composant = un fichier**, tous les composants rendus par un template migrés sur
  tokens (P0/P1/P2, plus badge/avatar/table/navbar/menu/formulaire…). §6, §8-6.
- **Icônes → Lucide via ux-icons** — `require` du bundle à jour, `base.html.twig` nettoyé de
  FontAwesome/Ionicons/flag-icon, templates migrés, `ux:icons:lock` documenté. §11.
- **Catalogue de composants** — route dev `/admin/_catalog` **+ export statique autonome**
  `docs/catalog.html` (publiable en GitHub Pages), régénérable par `castor catalog:build`. §14.
- **Cliquet CI** `castor qa:design-system` câblé (dont un garde-fou d'accolades), plafonds
  resserrés à mesure. §9.
- **Interface EN par défaut** — 6 locales complètes et alignées (202 clés identiques),
  chaînes FR en dur externalisées, clés pivots normalisées. Vérifié au rendu. §13, §15.
- **Renommage `custom.css` → `style.css`** ; tokens legacy `--main-*`/`--second-*`
  supprimés et **promus en `--aro-*`** (`--aro-color-primary-subtle`, `--aro-accent-magenta`). §8-7.
- **Documentation** — `css_customization.md` réécrit, `theming.md` créé (API tokens),
  `form-theme-blocks.md` (table blocs↔types), liens morts de `doc/index.md` corrigés. §15, §16.
- **Thème de formulaire découpé et normalisé** (22 juillet 2026) —
  `Form/layout.html.twig` **805 l. → 28 l. d'assemblage** (`{% use %}` uniquement) + dix
  sous-thèmes par domaine (`_core`, `_collection` + `_collection_macros`, `_controls`,
  `_date`, `_editor`, `_file`, `_gallery`, `_image`, `_select2`, `_translatable`).
  L'ensemble vit désormais dans **`Form/layout/`**, le fichier d'assemblage étant
  `Form/layout/theme.html.twig` : le répertoire porte le thème, plus aucun `_*.html.twig`
  ne traîne à la racine de `Form/`. Le chemin public a changé — rupture **détectable**
  (Twig lève « unable to find template »), donc sans risque au sens du §10.
  **43 blocs conservés, aucun perdu.** Bloc `aropixel_editor_widget` **normalisé** en
  `aropixel_admin_editor_widget` (bloc + `EditorType::getBlockPrefix()`) : le nommage
  `aropixel_admin_*` est désormais uniforme et la **fenêtre BC est refermée** avant
  diffusion de v3. Preuve d'inertie : **54/54 captures identiques au bit près**, code
  d'origine vs découpé à état de cache égal. §16, §8.
- **Pages d'authentification réparées + lot E clos** (23 juillet 2026) — séquelles de
  l'ablation de Stisla : classes dont la CSS était partie, laissant le markup se défaire en
  silence. Les 5 règles d'auth restaurées dans `components/_auth.css` (nouveau) ; puis
  l'audit généralisé a trouvé **54 classes orphelines** et **3 contrôles Bootstrap 4 morts**.
  Traité en E1 (défauts à impact : template mort supprimé, 2 boutons de modale réparés,
  boutons d'auth repassés pleine largeur), E2 (**26 fossiles inertes retirés**, prouvés
  `54/54`, + cliquet `orphan_classes` câblé et testé), E3 (catalogue `mb-6`/`mb-8` sur
  tokens). Bilan : **54 → 12 orphelines** (les 12 restants sont des crochets JS et marqueurs
  sémantiques légitimes), attributs BS4 à zéro, cliquet à 12. Voir « Reste à faire », lot E.
- **Lot B — résidus CSS du §9 clos** (23 juillet 2026) — `style.css` réduit à ses `@import`
  (tail de ~120 l. évacué vers les fichiers légitimes), **hex hors tokens 5 → 0**, **inline
  `style=` 1 → 0**, **`!important` en `components/` 24 → 1** (seul reste `.bg-success`, qui bat
  le `.bg-success` layered de Bootstrap). Les 13 utilitaires d'espacement maison (`.m-r-15`, …)
  **migrés vers Bootstrap** (`.me-3`, …) puis supprimés, avec une dérive de quelques pixels
  assumée. `.cke_combo_button` supprimée (CKEditor entièrement retiré au profit de Quill). Deux
  fichiers nouveaux (`_tabs.css`, `_bg-utilities.css`). Cliquets abaissés (**1/0/0**). Prouvé :
  `after-E2` ↔ `after-B3` **52/54** (2 écarts = catalogue E3), `after-B3` ↔ `after-B4` **54/54**,
  `after-B4` ↔ `after-B5` 44 écarts **tous des dérives d'espacement bénignes** (barre de nav,
  vérifiées). Voir « Reste à faire », lot B.
- **Lot C — documentation clos** (23 juillet 2026) — exemples FontAwesome des docs →
  `ux_icon('lucide:…')`, **`doc/icons.md`** (système d'icônes + surcharge) et
  **`doc/upgrade-v3.md`** (guide de migration ancien major → v3) créés, `admin_menu.md` corrigé
  (les `['icon' => …]` des items **ne sont pas rendus** — menu plein écran = libellés seuls),
  et les 14 chaînes FontAwesome mortes retirées d'`AdminMenuBuilder`/`QuickMenuBuilder`. Voir
  « Reste à faire », lot C.
- **Correctif — pagination et texte des surfaces primary** (23 juillet 2026). Deux bugs
  distincts sur les surfaces teal :
  - *Fond de la page active* — Bootstrap fige `--bs-pagination-active-bg: #0d6efd` sur
    `.pagination` (littéral que le `--bs-primary` bridgé n'atteint pas), donc la page courante
    du pager restait **bleu Bootstrap**. Repointée sur `--aro-color-primary` dans
    `foundations/_bootstrap-bridge.css` (bloc component-scopé).
  - *Couleur de police* — la règle globale **`a { color: secondary }`** (lien de contenu,
    *unlayered*) écrasait la couleur de **tout composant à base de `<a>`** : elle bat les
    déclarations *layered* de Bootstrap quelle que soit la spécificité. Résultat : boutons
    `<a class="btn btn-primary/cta/secondary">` et page active du pager rendus en **slate
    foncé** au lieu du blanc de leur token de contraste (les `<button>` n'étaient pas touchés,
    d'où des variantes correctes dans le catalogue mais fausses en liste). Corrigé en
    **excluant `.btn` et `.page-link`** de la règle `a` (`a:not(.btn):not(.page-link)`), ce qui
    rend la main à `--bs-btn-color` / `--bs-pagination-active-color` (blanc). Prouvé au harnais :
    38 écrans, texte des boutons colorés et de la page active **slate → blanc**, liens de
    contenu et pages non-actives inchangés.
- **Correctif — bouton d'action des listes Blog + macro `actions()` étendue** (23 juillet 2026).
  Les listes news et catégories de **BlogBundle** rendaient encore leur déclencheur d'action
  avec `<i class="fas fa-ellipsis-h">` — un `<i>` **vide** depuis le retrait de FontAwesome (carré
  gris sans icône). Basculées sur la macro partagée `@AropixelAdmin/Macro/actions.html.twig`
  (ux-icons/Lucide). La macro, jusque-là **importée mais jamais appelée**, a été étendue pour
  couvrir ces cas réels : paramètre `status` (bascule en ligne/hors ligne, hook JS `a.status`)
  et `delete_token` (le token CSRF **diffère par contrôleur** — `delete__post<id>`,
  `delete__post_category<id>`, `delete__user<id>` — donc l'appelant le fournit ; le défaut
  `delete<id>` de la macro ne correspondait à aucun contrôleur livré). Première pièce concrète
  de la **propagation Blog/Page/Menu** (§10, second chantier). Prouvé au harnais (déclencheur
  vide → `lucide:ellipsis` sur `post-list` et `category-list`), CSRF et hooks JS inchangés.
- **Correctif — taille de police des champs de formulaire** (23 juillet 2026). Bootstrap fige
  `font-size: 1rem` sur `.form-control` (littéral), donc `input`, `textarea` et `select`
  rendaient à **16px** alors que tout le reste de l'admin est sur la base **14px**. Ramenés sur
  le token en ajoutant `font-size: var(--aro-text-base)` à la règle `.form-control, .form-select`
  de `_form.css` (unlayered, bat le layered de Bootstrap). Prouvé au harnais : 20 écrans, texte
  des champs peuplés **16 → 14px** (les champs vides ne diffèrent pas — pas de glyphe).
- **Propagation Blog/Page/Menu — passe 1 : FontAwesome** (23 juillet 2026, second chantier §10).
  Après le retrait de FontAwesome (chantier admin), les bundles compagnons rendaient encore ~68
  `<i class="fas fa-…">` — des `<i>` **vides** (icônes fantômes partout, surtout dans le page
  builder). Tous migrés vers `{{ ux_icon('lucide:…') }}` sur **9 fichiers** (blog `category/order`,
  menu `menu/item`, page `base`/`index`/`builder/*`). Au passage :
  - **Boutons d'action de liste → macro `actions()`** partagée (page `index` + `builder/list`,
    comme blog `post`/`category`) — corrige aussi le déclencheur `badge-light` **délavé** (BS4
    mort) en `text-bg-light`, et gère la suppression conditionnelle (`page.isDeletable`) via
    `delete_path` null. Token CSRF préservé par appel (spécifique au contrôleur).
  - **Icônes de blocs personnalisés** (`Configuration.php` : `custom_blocks[].icon`) — le défaut
    `fas fa-puzzle-piece` → **`lucide:puzzle`**, et `_library.html.twig` rend `{{ ux_icon(pb_block.icon) }}`
    au lieu de `<i class="{{ pb_block.icon }}">`. **Rupture d'API intégrateur** documentée dans
    `upgrade-v3.md`.
  Prouvé au harnais : page builder (cartes de blocs, aperçu device, Save…), `page-list`,
  `category-order` — toutes icônes **fantômes → Lucide**. Reste du second chantier : audit
  orphelines/BS4 + conformité twig-cs des 3 bundles, item G (éditeur CKEditor), item F.
- **Propagation Blog/Page/Menu — passe 2 : résidus BS3/4 & Stisla** (24 juillet 2026). Audit
  orphelines lancé sur les 3 bundles (mêmes heuristiques que le lot E, CSS partagé d'admin).
  **blog : 0 orpheline** après nettoyage. Traité :
  - **Fossiles décoratifs morts** — `breadcrumb-caret`/`position-right` (7 fils d'Ariane, 3
    bundles), `card-table`/`table-vcenter` (4 tables — `_table.css` fait déjà le `vertical-align`
    et la `.card` habille la table), `main-wrapper` (gardé `main-wrapper-1`), `panel-list`,
    `v-align-middle`, `heading-btn`. Retrait inerte (byte-identical au harnais).
  - **Vrais bugs BS4 → BS5** : `custom-control`/`custom-checkbox`/`custom-control-input`/
    `custom-control-label` (cases à cocher du panneau sources de menu) → **`.form-check*`** ;
    `dropdown-menu-right` → `dropdown-menu-end` ; `card-heading` → `card-title`.
  - **Badge « section » de menu réparé** : `.bg-dark-grey` (non défini → badge sans fond)
    **défini** dans `_bg-utilities.css` sur `--aro-color-secondary` (rôle structurel/neutre),
    à côté de `.bg-pink`/`.bg-teal`. Visible au harnais (menu-main : badge SECTION slate).
  - **Tokens legacy** dans un `<style>` inline de page `index` : `var(--main-bg-color, …)` /
    `var(--light-grey, …)` → `--aro-color-primary` / `--aro-color-bg`.
  Prouvé : ratchet admin inchangé (1/0/0/12), harnais menu OK (cases `form-check`, badge
  SECTION), reste inerte. **Non traités, hors BS3/4** : module slider **UIkit** (`uk-*`, autre
  framework), crochets JS (`iconUpload`, `pb-color-preset`, `add-input-ressource`).
- **Labels du page builder → classe dédiée `.pb-label`** (24 juillet 2026). Le `form-label-sm`
  **mort** (faux air de variante Bootstrap, jamais stylé) remplacé par une classe **spécifique
  au page builder** `.pb-label`, définie dans `page-builder/_inspector.css` : **`--aro-text-base`
  (14px) + `--aro-weight-semibold` (600)** — le poids s'aligne sur les en-têtes d'accordéon
  actifs de l'inspecteur (600) sans les surcharger ; un flip vers `--aro-weight-bold` suffit si
  l'on veut plus de contraste. `.form-label` (base BS5) conservé à côté. Couverture **complète** :
  les 27 labels de `builder/_inspector.html.twig` **et** les 18 des 8 `block_types/*.js` (les
  formulaires d'inspecteur générés côté JS par type de bloc — button, title, image, banner,
  slider, divider, spacer, iframe). ⚠ **Ces JS existent en double** — `page-bundle/assets/…`
  (AssetMapper) **et** `src/Resources/public/js/…` (classique/`assets:install`) — il faut éditer
  **les deux** ; ici les deux copies sont resynchronisées à l'identique (`diff -rq` vide).
  Tokens `--aro-*` disponibles dans le builder (son `{% block stylesheets %}` appelle
  `{{ parent() }}`).
- **CKEditor → Quill : éditeur translatable réparé + pont média→éditeur (items G & F)**
  (24 juillet 2026). Champs translatable blog/page (`description`, `htmlContent`) basculés de
  `TextareaType`+`class=ckeditor` (mort) vers `EditorType`/Quill ; `block.js` mort (2 copies)
  supprimé ; **plus aucune référence CKEditor vivante**. Pont **fichier→éditeur** reconstruit pour
  Quill (bouton toolbar + handler via `modules.toolbar.handlers`, insertion `<a href>`), branche
  CKEditor morte retirée de l'insertion **image** (ce qui la répare : le test `constructor.name`
  échouait sur le build minifié). `_editor.html.twig` rendu auto-suffisant (active les 2 modales)
  et `data-class` corrigé en contexte translatable. **Prérequis d'intégration** relevé : la route
  publique `file_download` doit être déclarée **hors du préfixe `/admin`** (cf. `installation.md`) —
  sinon la bibliothèque de fichiers lève 500 ; enregistrée côté **sandbox** (pas dans le bundle).
  Prouvé au harnais Browserless. Détail : « Reste à faire », lots F et G. §10.

### Invariants Twig du thème de formulaire — à lire avant tout re-découpage

Découverts en découpant le thème, documentés en tête des fichiers concernés. Les deux
premiers produisent des ruptures **silencieuses** s'ils sont enfreints.

1. **`_core` est le seul sous-thème autorisé à `{% use %}` la mise en page Bootstrap**, donc
   le seul où `parent()` est permis. `{% use %}` fusionne le jeu de blocs *entier* du
   template utilisé : un second sous-thème utilisant Bootstrap réinjecterait ses `form_row`,
   `form_label`… par-dessus nos surcharges, sans le moindre avertissement (Twig fusionne en
   silence, dernier `use` gagnant). C'est pourquoi `datetime_widget` vit dans `_core` et non
   dans `_date` — il appelle `parent()`.
2. **`block('x', 'tpl.twig')` n'est pas un substitut de `parent()`.** Twig compile la forme à
   deux arguments **sans transmettre `$blocks`** (`Node/Expression/BlockReferenceExpression`) :
   le bloc Bootstrap s'exécuterait sans voir aucune de nos surcharges.
3. **Un sous-thème doit être « traitable »** pour être utilisable via `{% use %}` : ni parent,
   ni macro, ni corps hors blocs (`Node/ModuleNode::compileIsTraitable`). D'où
   `_collection_macros.html.twig`, séparé et **importé** (depuis l'intérieur d'un bloc), non `use`.

### Pièges d'environnement relevés — non traités, hors périmètre du chantier

Constatés le 22 juillet 2026 en validant le découpage. Aucun n'est causé par la refonte ;
tous sont reproductibles sur le code d'origine.

- **`castor qa:all` reformate le code source.** Il lance PHP-CS-Fixer en mode *correction*,
  pas *vérification* : un seul passage a modifié 116 fichiers d'`admin-bundle` et 69 des trois
  autres bundles, **dont des règles *risky* à effet sémantique** (`==` → `===`). Révoqué.
  Utiliser `castor qa:cs --dry-run` sur un arbre que l'on veut garder propre.
- **`castor qa:twig-cs` aussi** — même piège, autre outil. Il rapporte « errors: 0 » *parce
  qu'il vient de corriger*, et il balaie les quatre bundles : un passage a reformaté 18
  templates de Blog/Page/Menu (`{% include %}` → `{{ include() }}`, espacement des
  commentaires, lignes vides). `admin-bundle` était déjà conforme et n'a pas bougé.
  Révoqué. Ne le lancer que sur un arbre dont on accepte le reformatage, ou juste avant de
  committer les bundles concernés.
- **Une dépréciation LiipImagine casse les lignes de DataTable en dev.**
  `Liip\ImagineBundle\Templating\FilterTrait` émet un « User Deprecated » qui **pollue le
  fragment AJAX** des lignes de liste : la cellule d'action affiche `{"exception": {}}` au lieu
  du menu `…`. Révélé par un `cache:clear`, stable ensuite. **Conséquence pour le chantier :
  il fausse le harnais de capture sur les 16 écrans de liste Blog/Page/Menu** — toute
  comparaison doit se faire à état de cache égal, sans quoi l'écart s'impute à tort au CSS.
- **`asset-map:compile` gèle le CSS servi en dev — ne jamais le lancer sur le sandbox.**
  Dès qu'un `public/assets/` compilé existe, Symfony (même en `APP_ENV=dev`) sert **ces
  fichiers figés** et ignore les sources, avec l'avertissement explicite « Symfony will not
  serve any changed assets until you delete the files in `public/assets` ». En B, un
  `asset-map:compile` lancé par erreur a figé le CSS : deux captures consécutives sont
  ressorties **identiques malgré une correction réelle** (`after-B1` == `after-B2`), ce qui
  fait croire à tort que le correctif n'a rien changé. Le bundle est servi en live via un
  **symlink** `vendor/aropixel/admin-bundle → admin-bundle/`, donc l'état sain du dev est
  **sans** `public/assets/`. Remède : `rm -rf application/public/assets` (+ `cache:clear`).
  Diagnostic rapide : comparer le hash du `href` de `style.css` dans la page avant/après une
  édition — s'il ne bouge pas, le CSS est figé.
- Sandbox : les tests fonctionnels échouent (36 erreurs) faute de fixtures enregistrées comme
  services en env `test` — identique avant/après le découpage.

> **Campagnes de captures laissées dans `var/visual/`** pour la reprise :
> `before-A1` (avant découpage, **cache chaud**), `after-A1` (après découpage) et `orig-full`
> (code d'origine restauré, **même cache** qu'`after-A1`). C'est la paire
> `orig-full` ↔ `after-A1` qui porte la preuve — 54/54 — et non `before-A1` ↔ `after-A1`,
> dont les 16 écarts ne sont que la dépréciation LiipImagine ci-dessus.
> `smoke-A1`, `after-A1b` et `orig-recheck` sont des captures de travail, supprimables.
> S'y ajoute `after-A2`, pris après le rangement du thème dans `Form/layout/` (**52/54**
> face à `after-A1`), puis `after-A3` après la réparation des pages d'authentification
> (**44/54** face à `after-A2`, les 10 écarts étant voulus), puis `after-E1` après la
> première passe du lot E (**50/54** face à `after-A3` : les 4 écarts sont les boutons
> d'envoi des pages d'authentification repassés pleine largeur, `btn-block` → `w-100`),
> puis `after-E2` après le retrait des 26 classes fossiles inertes (**54/54** face à
> `after-E1` — preuve que chacune ne produisait rien), puis `after-B3` (évacuation du tail,
> **52/54** face à `after-E2` — 2 écarts = catalogue E3), `after-B4` (4 `!important` retirés,
> **54/54** face à `after-B3`), et `after-B5` (migration des utilitaires d'espacement vers
> Bootstrap, **44 écarts** face à `after-B4`, **tous des dérives d'espacement bénignes** : la
> barre de nav sur chaque page + tuiles du menu plein écran + un bouton de modale). `after-B1`
> et `after-B2` sont des captures de travail — `after-B2` est **figée** (cf. le piège
> `asset-map:compile` en §0), à ne pas prendre pour référence. `login-now`/`login-fix` sont
> les avant/après du seul écran de connexion, supprimables.

> **Le harnais n'est pas totalement déterministe : `auth-login` dépend de l'heure.**
> `Security/login.html.twig:62` choisit la salutation sur la variable `hour`
> (`hello` de 5 h à 19 h, `good_evening` jusqu'à 23 h, `good_night` sinon). Deux campagnes
> prises de part et d'autre d'une de ces bornes divergent donc sur `auth-login@1440` et
> `auth-login@1024` **sans qu'aucun code n'ait changé** — c'est exactement ce qui explique
> les 2 écarts d'`after-A1` ↔ `after-A2`. Avant de conclure à une régression sur ces deux
> captures, comparer l'heure de prise de vue.

### Séquelles de l'ablation de Stisla sur les pages d'authentification — ✅ réparé

Relevé le 22 juillet 2026 en parcourant les rendus. **Les douze templates
`Security/` `Reset/` `Activation/` `First/` portaient des classes qui ne correspondaient
plus à aucune règle** : `bf1137e9 « Ablation de stisla »` a emporté les sélecteurs sans que
le markup soit repris. Défaut silencieux typique — rien ne casse, la mise en page se
défait simplement.

| Règle perdue | Vivait dans | Effet |
|---|---|---|
| `.form-group { margin-bottom: 25px }` | `style.css:432` | tout l'espacement vertical du formulaire de connexion. Bootstrap 5 a supprimé `.form-group`, donc rien ne l'a remplacé |
| `.form-group label { margin-bottom: .5rem }` | `_form.css:563` | libellé collé à son champ |
| `.login-brand { margin: 20px 0; text-align: center }` | `style.css:2227` | logo aligné à gauche, sans respiration — **sur les 12 pages** |
| `.simple-footer { text-align: center; margin: 40px 0 }` | `style.css:2932` | copyright aligné à gauche et collé — **sur les 12 pages** |
| `#togglePassword { position: absolute; … }` | `_form.css:639` | œil de révélation rejeté **sous** le champ mot de passe |

Restaurées sur tokens dans **`components/_auth.css`** (nouveau), importé par `style.css` —
le seul feuillet commun aux deux familles de pages : le layout autonome de connexion et les
onze pages qui étendent `base.html.twig` (lesquelles ne chargent **pas** `login.css`).

Deux choix de portée à ne pas défaire :

- **`.form-group` est scopé à `.login-content`.** Une règle nue toucherait aussi
  `.form-horizontal .form-group`, dont l'espacement vient déjà du `mb-3` posé par
  `form_row` — et décalerait tous les formulaires de l'application.
- **Le contexte de positionnement de l'œil est `.password-field`, pas `.form-group`.** Le
  groupe contient aussi la ligne de libellé et le message de validation : s'y ancrer
  replace l'icône sous le champ dès que l'un des deux est présent. C'était le bug d'origine.

Corrigé au passage, **deux défauts fonctionnels** que la seule relecture visuelle ne montre
pas :

- `security.js` permutait encore les classes FontAwesome `fa-eye` / `fa-eye-slash` sur un
  élément qui rend désormais un SVG Lucide inline. Le clic changeait bien le `type` du
  champ, mais **l'icône ne bougeait plus**. Deux SVG, un `hidden`, permutés par le script.
- L'œil était un `<svg>` nu porteur d'un `click` : **inatteignable au clavier**. C'est
  maintenant un `<button type="button">` avec `aria-pressed` et `aria-controls`.

> **Piège JS à retenir — `hidden` n'existe pas sur `SVGElement`.**
> `hidden` est défini sur `HTMLElement`. `svg.hidden = true` crée silencieusement un
> *expando* : la propriété se relit à `true` et **rien ne change à l'écran**. Ma première
> vérification automatisée lisait cette propriété et déclarait le basculement fonctionnel
> alors qu'il ne l'était pas. Utiliser `toggleAttribute('hidden', bool)`, et **mesurer la
> géométrie rendue** (`getBoundingClientRect().width > 0`), jamais une propriété JS.

Preuve de non-régression : `after-A2` ↔ `after-A3` = **44/54 identiques au bit près**, les
10 écarts étant exactement les 5 écrans d'authentification × 2 viewports.

### Reste à faire

> ## Point de reprise — 24 juillet 2026
>
> **Tout est commité** sur la branche **`feature/design-system`** des 4 dépôts (pas de push) :
> `admin-bundle` f7aaf40a (78 fichiers), `page-bundle` 704a8a0 (26), `blog-bundle` 4e9455f (4),
> `menu-bundle` b722fa5 (4). Arbres propres.
>
> **Fait.** Chantier AdminBundle **clos** — lots A (thème de formulaire), B (résidus CSS),
> C (doc), E (orphelines) + une série de correctifs de rendu (auth, pagination, texte blanc sur
> primary, taille des champs à 14px). Le cliquet `qa:design-system` tient au plancher :
> `orphan_classes` 12, `hex_components` 0, `inline_styles` 0, `important_components` **1**
> (`.bg-success`, nécessaire). Second chantier (§10) **démarré** : propagation Blog/Page/Menu
> — passe 1 (FontAwesome → ux-icons/Lucide, ~68 icônes, listes → macro `actions()`), passe 2
> (résidus BS3/4 → BS5), et labels du page builder (`.pb-label`, en semibold).
>
> **Items G + F — ✅ CLOS le 24 juillet 2026** (CKEditor → Quill, pont média→éditeur), commités
> (admin `e4e31ae`, blog `3521707`, page `cbdbc99`, pas de push). Détail en tête de « Fait » et
> dans les sections F/G plus bas.
>
> **Item D — ✅ CLOS le 24 juillet 2026** : indicateurs de statut basculés sur la famille
> sémantique (feu tricolore success/warning/danger). Détail §12 et section D.
>
> **Reste à faire, par priorité :**
> 1. **Conformité twig-cs des 3 bundles compagnons** — jamais mis aux normes ; le CI est rouge
>    dessus (préexistant à nos commits). `qa:twig-cs` reformate en masse (le lancer sur un arbre
>    dédié, pas mélangé).
> 2. **Divers** :
>    - ~~police **Typekit externe** de `page-builder.css`~~ **✅ FAIT le 24 juillet 2026** —
>      `@import url("https://use.typekit.net/tpb1kxh.css")` **supprimé** (fuite RGPD, même souci
>      que les Google Fonts retirées d'admin). La police `forevs` qu'il fournissait ne servait
>      qu'au **preview du bloc bannière** (`.pb-banner-preview`, `_blocks.css`) — vestige d'un
>      projet repris ; `font-family: forevs, sans-serif` → `sans-serif`. Plus **aucun** `@import`
>      externe ni référence Google/Typekit (hors commentaire historique de `_fonts.css`) dans les
>      4 bundles. (`forevs` étant une police commerciale Adobe, l'auto-hébergement à la Poppins
>      n'était pas une option légale — d'où la suppression pure.)
>    - **confirmer semibold vs bold** pour `.pb-label` (flip d'un token) ;
>    - **le cliquet `.castor/css.php` n'est versionné nulle part** (racine sandbox, hors dépôts
>      bundle) — à porter dans un repo si on veut le conserver.

**A. Thème de formulaire (§16) — ✅ clos**, cf. « Fait » ci-dessus et les invariants Twig.

**B. Résidus des critères §9 — ✅ CLOS le 23 juillet 2026.** `style.css` est **imports
uniquement** (le tail de ~120 l. évacué), hex hors `_tokens.css` à **0**, inline `style=` à
**0**, `!important` en composants **24 → 1** (le seul restant, `.bg-success`, est nécessaire).
Cliquet abaissé (**1/0/0**). Détail :

- **Tail de `style.css` évacué**, chaque règle vers son fichier légitime : globaux
  typographiques (`a`, `hr`, rythme `p/ul/ol`) → `foundations/_typography.css` ; utilitaires
  (`.cursor-pointer`, `.text-underlined-dashed`, `.missing-img`) → `_helpers.css` ; item de
  dropdown actif → `_dropdown.css` ; puce Select2 → `vendor/_select2.css` ; onglets soulignés
  (PageBundle) → **`_tabs.css`** (nouveau) ; icône utilisateur du menu plein écran →
  `_fullscreen-menu.css` ; `.section-header` legacy + `.main-footer` → `foundations/_layout.css`.
- **5 hex → tokens** : `#EC0868`/`#0F8B8D` (badges type MenuBundle) → `--aro-accent-pink`/
  `--aro-accent-teal` ; `#ebebeb` (puce Select2) → `--aro-color-surface-muted`. hex hors
  tokens : **0**.
- **`.heading-elements` supprimée** (morte depuis la suppression du panel BS3 en E1) — emporte
  1 `!important`.
- **`!important` : 24 → 1.** En quatre temps. (a) L'évacuation du tail en élimine 5 (ceux qui
  ne battaient que du Bootstrap *layered normal* : unlayered gagne sans) + `.heading-elements`
  morte. (b) 4 de plus, prouvés inertes (`after-B3` ↔ `after-B4` **54/54**) :
  `.editable-indicator` (2, classe **morte** — les datatablers utilisent `data-modal-xeditable`),
  le `.dropdown-item-desc` **inexistant** (0 usage), et le `margin` du séparateur de dropdown
  qui **ré-affirmait la valeur Bootstrap** (redondant). (c) **Les 13 utilitaires d'espacement
  `.m-*`/`.p-r-0` migrés vers Bootstrap** (`.me-3`, `.m-0`, …) dans tous les gabarits, puis
  supprimés : Bootstrap porte lui-même le `!important` de l'utilitaire, dans sa couche, donc il
  ne compte plus comme dette *first-party*. Mapping au pas Bootstrap le plus proche (15→16,
  20→24, 30→24, 35→24, 50→48), dérive de quelques pixels **assumée** (`after-B4` ↔ `after-B5` :
  44 écrans, tous une dérive d'espacement bénigne — bbox identique sur la barre de nav qui
  s'affiche partout, plus les tuiles du menu plein écran et un bouton de modale). (d)
  `.cke_combo_button` **supprimée** — CKEditor entièrement retiré au profit de Quill.
- **1 seul `!important` restant, nécessaire : `.bg-success`** — bat le `.bg-success`
  *layered `!important`* de Bootstrap (l'importance prime sur l'ordre de couche, seul un
  `!important` unlayered le bat). Le cliquet `important_components` est donc à **1**, plancher
  de fait.
- **Inline `style=` de `Form/base.html.twig:136`** → classe `.locale-dropdown` dans
  `_translation.css`.

> **Piège d'ordre de cascade à retenir** (rencontré en B, corrigé) : les utilitaires de
> couleur `.bg-pink`/`.bg-teal` posent un `border-color` que `_badge.css`
> (`.badge { border: 1px solid transparent }`) **réinitialise s'il charge après**. Le tail les
> chargeait en dernier ; les déplacer dans `_helpers.css` (importé avant `_badge.css`) a cassé
> la bordure des badges de menu (prouvé au harnais, 6 écrans). Corrigé en les isolant dans
> **`_bg-utilities.css` importé en dernier**, comme le fait Bootstrap pour ses propres
> utilitaires. Leçon : un utilitaire qui surcharge un composant doit charger **après** lui.

Preuve d'inertie : `after-E2` ↔ `after-B3` = **52/54 au bit près**, les 2 écarts étant le
sous-incrément catalogue E3 (déjà validé), pas B.

**C. Documentation (§15) — ✅ CLOS le 23 juillet 2026.**
- ✅ `forms.md` / `form_templates.md` : les `<i class="fa* fa-…">` des exemples de gabarits
  intégrateur → `{{ ux_icon('lucide:…') }}` (trash-2, import, info, plus).
- ✅ **`doc/icons.md` créé** — le système d'icônes (ux-icons + Lucide), `ux:icons:lock`, et la
  **surcharge** (déposer un SVG dans `assets/icons/<préfixe>/<nom>.svg`, précédence locale ;
  icône maison via `app:…`). Lié depuis `index.md`, `CLAUDE.md`, `css_customization.md`.
- ✅ **`doc/upgrade-v3.md` créé** — guide de migration ancien major → v3, structuré par
  ruptures *visibles* vs *silencieuses* (§10) : icônes, Bootstrap 5, tokens `--aro-*`,
  utilitaires d'espacement, bloc `aropixel_admin_editor_widget`, chemin du thème de formulaire,
  CKEditor → Quill, checklist.
- ✅ **`admin_menu.md` : icônes de menu clarifiées.** Découverte en cours de route : les
  `['icon' => …]` des items de menu **ne sont pas rendus** — le menu plein écran actuel
  n'affiche que les libellés (`Menu/link.html.twig` = `<span>` seul ; `_quick-menu.html.twig`
  = tuiles sans icône). Seuls `submenu.html.twig` et `Shortcut/link.html.twig` rendent encore
  un `<i class="{{ icon }}">` (mort depuis le retrait de FontAwesome). Doc corrigée pour dire
  la vérité (libellés seuls ; icônes possibles en surchargeant les gabarits avec `ux_icon`).
  **Nettoyage code au passage :** les `['icon' => 'fas fa-…']` **morts** d'`AdminMenuBuilder.php`
  (9) et `QuickMenuBuilder.php` (5) supprimés — dernières chaînes FontAwesome du code livré.

**D. Arbitrage §12 — ✅ CLOS le 24 juillet 2026.** Les indicateurs de statut basculent sur la
famille sémantique en **feu tricolore** : `--aro-status-online` → `var(--aro-color-success)`
(vert), `--aro-status-scheduled` → `var(--aro-color-warning)` (orange), `--aro-status-offline` →
`var(--aro-color-danger)` (rouge). « En ligne » cesse d'être le teal de marque (qui doublait
`--aro-color-primary`). Catalogue + preview re-générés. Détail au §12 (« Tranchés »).

**F. Pont média→éditeur re-ciblé sur Quill — ✅ CLOS le 24 juillet 2026.** Traité avec G (même
chantier). État réel trouvé, plus avancé que noté : **image→éditeur marchait déjà** (branche
Quill dans `image-manager/editor.js`), seul **fichier→éditeur** (`FL_Editor` de `files.js`)
restait 100 % CKEditor (écriture dans `.cke_dialog_ui_input_text`) et — surtout — **inatteignable**
(aucun handler de toolbar Quill ne le déclenchait, contrairement à l'image). Fait :
- **Bouton « fichier » ajouté à la toolbar Quill** (`app.js`, toolbar `full`) + icône SVG enregistrée
  via `Quill.import('ui/icons')`. **Piège Quill 2** : `Toolbar.attach()` n'attache un écouteur à un
  bouton `ql-*` que si un handler existe **à la construction** ou si le format est connu ; « file »
  n'étant pas un format, le handler doit être passé dans `modules.toolbar.handlers`, pas via un
  `addHandler` tardif (qui marche pour « image », format connu).
- **`FL_Editor.insert_file()` réécrit** : au lieu des `.cke_dialog*`, il insère un lien
  `<a href>` dans Quill (`insertText(i, nom, 'link', url)`), libellé = nom de fichier (dernier
  segment de l'URL de téléchargement `file_download`). `open_modal()` débarrassé des hacks
  z-index CKEditor.
- **Branche CKEditor morte retirée d'`image-manager/editor.js`** — l'insertion est désormais le
  chemin Quill **inconditionnel**. Corollaire : ce test `constructor.name === 'Quill'` était en
  fait **cassé** (le build Quill minifié renomme la classe en « I »), donc l'insertion d'image
  était silencieusement inopérante ; le nettoyage la répare. `FL_Editor` utilise le même
  duck-typing (`insertText`/`getSelection`) plutôt que le nom de classe.
- **`_editor.html.twig` rendu auto-suffisant** : il appelle `enable_image_library_modal()` **et**
  `enable_file_library_modal()` (inclusion idempotente par `form_end`), pour que ses boutons image
  ET fichier fonctionnent même sans widget Image/File frère sur le formulaire.
- **`data-class` (catégorie média) corrigé en contexte translatable** : la valeur du parent
  immédiat y est la **collection** de traductions, pas l'entité — remontée d'un cran quand elle
  est itérable. Vérifié au harnais : blog translatable (2 locales) → `data-class` =
  `Aropixel\BlogBundle\Entity\Post` sur chaque panneau, pas `ArrayCollection`.
- **Prérequis d'intégration (pas un bug du bundle)** : le rendu des lignes de la bibliothèque de
  fichiers appelle `aropixel_file_url()` → route **`file_download`**. Cette route est **publique par
  conception** et doit être déclarée par le projet intégrateur **hors** du préfixe protégé `/admin`
  (`download.yaml`, cf. `installation.md` — « Public download route (must be outside of protected
  prefix) »). L'importer dans le `routes.yaml` du bundle (monté sous `/admin`, derrière le
  pare-feu) serait une **erreur** : le téléchargement doit rester joignable sans authentification.
  Le sandbox ne la déclarait pas → 500 (widget compris, pas que l'éditeur) ; enregistrée au niveau
  **`application/`** du sandbox, hors dépôts bundle.

Prouvé au harnais Browserless (login réel) : bouton fichier rendu + icône, modale ouverte,
file-ajax **200**, sélection d'un fichier → **lien inséré dans Quill**
(`<a href="…/download/1/Terms and conditions.pdf" target="_blank">Terms and conditions.pdf</a>`).
Cliquet `qa:design-system` inchangé (orphan_classes 12, important_components 1).

**G. Éditeur translatable Blog/Page → Quill — ✅ CLOS le 24 juillet 2026.** Les champs translatable
`description` (blog `PostTranslatableType`) et `htmlContent` (page `DefaultTranslatablePageType`)
rendaient un `<textarea class="ckeditor">` nu (CKEditor absent → pas de WYSIWYG). Basculés sur
**`widget => EditorType::class`** (Quill), cohérents avec leurs variantes monolingues (`PostType`,
`DefaultPageType`) qui utilisaient déjà `EditorType`. Le `TranslatableSubscriber` propage `attr`
(pas les options custom), donc toolbar `full` par défaut ; un éditeur Quill par locale, initialisé
au `DOMReady`. Le `block.js` de page-bundle (les **2 copies** `assets/` + `Resources/public/js/`)
qui appelait `CKEDITOR.replace(...)` était en fait **code mort** (markup `.js-block-admin-tabs`
inexistant, jamais importé) → **supprimé**. Vérifié au harnais en mode multilingue : blog `post/new`
→ 2 éditeurs Quill (en/fr) initialisés, bouton fichier + modales présents. Plus **aucune** référence
CKEditor vivante dans les 4 bundles.

**E. Classes orphelines et attributs Bootstrap 4 — ✅ CLOS le 23 juillet 2026.** Relevé le
22 juillet ; traité en trois passes (E1 défauts à impact, E2 fossiles inertes + cliquet, E3
catalogue) : **54 → 12 orphelines**, les 3 attributs BS4 à zéro, cliquet `orphan_classes`
câblé et abaissé à 12. Statut détaillé plus bas.

Généralisation du défaut réparé le même jour sur les pages d'authentification. Celui-là
avait été trouvé à l'œil, en naviguant ; l'audit ci-dessous montre que ce n'était pas un
cas isolé mais **une famille**, et qu'elle se mesure. C'est exactement la catégorie que le
§10 désigne comme la seule où il vaut la peine de se contraindre : **rien ne casse, rien
n'alerte, la mise en page se défait simplement**.

*Famille 1 — 54 classes citées par les templates et stylées nulle part* (sur 356 classes
distinctes ; les 25 crochets JS légitimes ont été écartés en les confrontant au JS du
bundle). Par groupe :

- **Panneaux Bootstrap 3** — `panel`, `panel-default`, `panel-heading`, `panel-title`,
  `panel-footer`. `File/Widget/files.html.twig:2` est un panneau BS3 intégral : il rend un
  `<div>` nu, sans cadre ni fond.
- **Utilitaires supprimés en Bootstrap 5** — `pull-right`, `float-left`, `btn-block`,
  `dropdown-menu-right`, `text-default`.
- **Résidus Stisla** — `main-wrapper`, `section-body`, `footer-right`, `sidebar-menu`,
  `nav-link-lg`, `user-body`, `alert-styled-left`, `alert-bordered`, `alert-arrow-left`,
  `border-left-info`, `border-left-xlg`, `control-label`, `text-small`, `list-condensed`,
  `has-icon`, `heading-btn`, `breadcrumb-caret`, `position-right`…
- **`mb-6` et `mb-8`**, dans `catalog/index.html.twig` — Bootstrap 5 s'arrête à `mb-5`.
  Le catalogue lui-même porte des espacements qui n'existent pas.
- **`full-with`**, dans `Image/Modals/crop.html.twig` — faute de frappe pour `full-width` :
  cette classe n'a jamais rien fait.

*Famille 2 — 3 contrôles Bootstrap 4 morts.* Le bundle embarque **Bootstrap 5.3.0-alpha1**,
où `data-toggle` est devenu `data-bs-toggle`. 24 emplacements sont corrects, trois sont
restés en BS4 :

| Fichier | Attribut | Conséquence | Statut E1 |
|---|---|---|---|
| `File/Widget/files.html.twig:19` | `data-toggle="modal"` | le bouton **n'ouvre pas** la bibliothèque de fichiers | ✅ template supprimé (mort) |
| `File/Modals/library.html.twig:42` | `data-dismiss="modal"` | « Fermer » **ne ferme pas** | ✅ `data-bs-dismiss` |
| `Image/Modals/attributes.html.twig:65` | `data-dismiss="modal"` | idem | ✅ `data-bs-dismiss` |

Le widget « fichiers » cumulait les deux familles — mais c'était le **template mort** : le
widget FileType réellement rendu est la `.card` de `Form/layout/_file.html.twig`, intacte.

> **Faux positif vérifié, à ne pas recorriger** : le `data-target` de
> `Form/layout/_editor.html.twig:29` n'est pas du Bootstrap. `app.js:29` le lit via
> `$this.data('target')` pour brancher Quill sur son `<textarea>`. Le renommer en
> `data-bs-target` casserait l'éditeur.

*Méthode, pour rejouer l'audit.* Extraire les classes littérales des `class="…"` des
templates (ignorer les attributs contenant du Twig), et les confronter à l'union de : tout
`.css` sous `public/css`, le `bootstrap.min.css` de `public/modules/bootstrap/css`, et les
blocs `<style>` inline des templates — le catalogue en a un, sans quoi ses classes `cat-*`
ressortent à tort. Classer ensuite le reliquat en deux tas selon que la classe apparaît ou
non dans le JS du bundle : un crochet JS non stylé est légitime, une classe inconnue des
deux est orpheline. Le tri final reste manuel — quelques crochets vivent dans des scripts
inline ou des contrôleurs Stimulus que l'heuristique ne voit pas.

#### E1 — passe des défauts à impact réel — ✅ 23 juillet 2026

Ce qui **change le rendu ou le comportement** a été traité et prouvé ; le reste (fossiles
décoratifs inertes + cliquet) suit en E2. Fait :

- **`File/Widget/files.html.twig` supprimé.** Template **mort** — aucun include ni contrôleur
  ne le rend (le vrai widget FileType est la `.card` de `Form/layout/_file.html.twig:36`).
  Vérifié sur les quatre bundles + PHP ; les traces en `application/var/cache` ne sont que la
  précompilation Twig de tout le répertoire, pas une référence. Sa suppression emporte d'un
  coup **8 orphelines** (`panel*`, `border-left-*`, `heading-btn`, `pull-right`), le chemin
  d'include legacy `AropixelAdminBundle:Themes/Limitless:` (cassé en Symfony moderne) **et**
  un des trois attributs BS4.
- **Les 2 boutons « Fermer » BS4 encore vivants corrigés** — `data-dismiss` → `data-bs-dismiss`
  dans `File/Modals/library.html.twig` et `Image/Modals/attributes.html.twig`. Rename 1:1
  vers la forme qu'exige Bootstrap 5, déjà en place et fonctionnelle sur 24 emplacements
  frères du même code (p. ex. `Image/Widget/image.html.twig`). Ces deux modales ne sont pas
  atteignables par URL — pas de preuve au harnais, validité par construction et cohérence.
- **`btn-block` → `w-100`** sur les 4 pages d'auth (`Reset/request`, `Reset/reset`,
  `Activation/create_password`, `First/request`) : le bouton d'envoi **repasse pleine
  largeur**, aligné sur la connexion qui utilise déjà `w-100`. Visible au harnais (`after-E1`,
  les 4 seuls écarts voulus).
- **Renommages BS5 mécaniques** : `dropdown-menu-right` → `dropdown-menu-end` et
  `flex-1` → `flex-grow-1` (menu plein écran) ; `float-left` → `float-start` (×2, modale de
  recadrage) ; `full-with` (faute de frappe, inerte) supprimée.

Bilan : **54 → 40 orphelines**, les 3 attributs BS4 à zéro (le `data-target` de `_editor`
reste, faux positif documenté ci-dessus). `lint:twig` OK (80 fichiers), aucune collatérale
sur l'admin.

#### E2 — cliquet câblé + fossiles inertes retirés — ✅ 23 juillet 2026

**Cliquet d'abord, puis nettoyage** (dans cet ordre, pour geler le plancher avant d'y
descendre). Résultat : **40 → 14 orphelines**, `qa:design-system` vert.

- **`orphan_classes` ajouté à `.castor/css.php`** — `countOrphanClasses()` porte l'heuristique
  de l'audit dans le cliquet : classes des `class="…"` littéraux (sans accolade Twig, donc
  conservateur — zéro faux positif) croisées avec l'union CSS + Bootstrap + `<style>` inline,
  et exonérées si un script (`.js` ou `<script>` inline) les nomme. Plafond posé à 40, **testé
  qu'il mord** (une classe bidon ajoutée → `41 OVER`, retirée → `40 OK`), puis abaissé à **14**
  une fois le nettoyage fait. Même mécanique de descente que `important_components: 24`.
- **26 fossiles Stisla/BS3 retirés**, purement inertes — `after-E1` ↔ `after-E2` =
  **54/54 identiques au bit près**, la preuve que chacune ne produisait rien. Fait au cas par
  cas comme prévu : `main-wrapper` retirée en **gardant** `main-wrapper-1` (qui porte
  `style.css:148`) ; `text-size-mini`/`text-default`/`control-label`/`text-small` retirées
  sans les remapper vers `.small` — les remapper serait un *choix de style*, pas du nettoyage
  de fossile, donc hors périmètre E2. Vérifié au préalable qu'aucune des 26 n'est un hook JS
  (4141 l. de JS + `<script>` inline scannées, zéro référence).

**Plancher atteint : 14** = les **12 gardiens légitimes** (crochets JS + marqueurs
sémantiques, cf. tas ci-dessous) **+ 2** : `mb-6`/`mb-8` du catalogue, laissés à leur propre
incrément (ci-dessous). Quand ces deux tomberont, abaisser le plafond à 12.

Les 12 gardiens — à **conserver**, ce ne sont pas des bugs : crochets JS dont le vrai levier
est un `data-*` (`deleteImg`, `iconUpload`, `save-attributes`, `size-filter-option`,
`modalAttributes`), marqueurs sémantiques dont la mise en page vient des utilitaires BS
voisins (`collection-form-content`, `collection-form-actions`, `quill-editor-container`,
`form-error-message` — ce dernier est **dans** un `.invalid-feedback` BS qui porte le rouge,
`form-widget`, `clickable`), et le hook de validation Bootstrap `needs-validation`.

#### E3 — sous-incrément catalogue — ✅ 23 juillet 2026

`mb-6` / `mb-8` (les 2 dernières des 14) n'existent pas en BS5 (qui s'arrête à `mb-5`) :
dans `catalog/index.html.twig` le TOC et les nuanciers perdaient leur marge basse. Corrigé
en définissant `.mb-6`/`.mb-8` sur tokens (`--aro-space-6` = 24px, `--aro-space-8` = 32px)
dans le `<style>` **page-scopé** du catalogue — pas dans le CSS du bundle, dont l'échelle
reste celle de BS5. Les deux blocs `<style>` étant tenus identiques (live + export statique),
la règle a été ajoutée aux deux : `catalog/index.html.twig` **et** le second `<style>` de
`docs/catalog.html` (hand-authored ; `catalog:build` ne régénère que le premier, la CSS du
bundle embarquée). Puis `castor catalog:build` — export + `docs/index.html` + aperçu
re-tirés (invariant CLAUDE.md).

**Lot E clos. `orphan_classes` : 54 → 12**, plafond abaissé à **12** = les 12 gardiens
légitimes, plus aucun fossile. Toute nouvelle classe orpheline fait désormais échouer le
build.

*Non mesuré.* Le scan n'a tourné que sur `admin-bundle`. La liste des news de BlogBundle
présente des symptômes de la même famille (constaté le 22 juillet 2026 en parcourant les
rendus). Le §10 ayant scopé la propagation à un second chantier, elle n'est pas ouverte —
mais l'audit tourne tel quel sur les trois autres bundles et le chiffrerait sans rien
modifier.

### Hors périmètre (décidé — ce n'est pas un reste)

- **~9 composants du design system non implémentés** (`Stat`, `GridList`, `Accordion`,
  `SplitButton`, `ButtonGroup`, `RadioGroup`, `Dialog`, `List`, `FileUpload`) — « à la
  demande, pas en bloc » (§6c).
- **Disposition verticale des formulaires** (§16) — décidée NON, réversible.
- **Mode sombre** (§10).

---

## 1. Objectifs

1. **Casser toute dépendance au thème Stisla.** Supprimer `style.css` (3035 l.) et
   `components.css` (2083 l.) — 5118 lignes de thème tiers que nous ne maîtrisons pas.
2. **Un `style.css` de base minimal**, reprenant le rôle de l'actuel `custom.css` :
   tokens, reset applicatif, éléments HTML nus. Rien d'autre.
3. **Un composant = un fichier** dans `css/components/`, autonome et lisible seul.
4. **Thémable par variables CSS** : un développeur intégrateur change ses couleurs,
   rayons et typographie sans surcharger une seule règle.
5. **Qualité visuelle de niveau Tailwind Plus** : hiérarchie typographique nette,
   élévations discrètes, densité maîtrisée, états (hover/focus/disabled) systématiques.

### Non-objectifs

- **Ne pas migrer vers Tailwind.** Décision actée : on reste sur Bootstrap 5.3 comme
  socle (grille, utilitaires, composants JS). Le design system se construit *sur*
  Bootstrap, pas contre lui.
- Ne pas toucher aux plugins jQuery tiers (select2, DataTables, Quill, Cropper…) :
  on les **habille**, on ne les remplace pas dans ce chantier.
- Ne pas introduire d'étape de build. Le CSS reste servi tel quel via `assets:install`.

---

## 2. État des lieux

### Empilement actuel (à inverser)

```
style.css             —   ← thème Stisla                 [SUPPRIMÉ ✅]
components.css        —   ← composants Stisla            [SUPPRIMÉ ✅]
custom.css        438 l.  ← nos patchs par-dessus        [DEVIENT style.css]
components/*      3059 l.  ← nos composants (18 fichiers) [À REFONDRE]
foundations/      117 l.  ← _fonts.css (Poppins local)   [NOUVEAU]
login.css          43 l.  ← écrans d'authentification    [DEVIENT un composant]
skins/reverse.css     —   ← orphelin                     [SUPPRIMÉ ✅]
```

> **Incrément 1 fait.** `skins/reverse.css` (orphelin) et `Menu/section.html.twig` (0 octet)
> supprimés ; 127 des 141 classes d'espacement de `_helpers.css` retirées — 14 étaient
> utilisées, sur 39 occurrences. Le fichier passe de 541 à 172 lignes et de 144 à 18
> `!important`, faisant chuter le compteur global de **301 à 174**.
>
> Diff de captures : **48/48 identiques**. L'incrément est prouvé inerte.

Aujourd'hui `custom.css` et `components/` sont une **couche de rustines** appliquée
au-dessus de Stisla. D'où la dette mesurable :

- `!important` omniprésent — conséquence directe de la lutte contre la spécificité Stisla ;
- couleurs en dur (`#0CABA8`, `#585c72`, `#e7e7e7`…) mêlées aux variables ;
- variables mal nommées : `--main-bg-color` désigne en réalité la **couleur primaire**
  de marque, pas un fond ;
- sélecteurs de forte spécificité (`.card .card-body > .table-responsive:first-child > .table thead th`) ;
- `_override.css` réduit à 2 lignes, `_dropdown.css` à 7 : découpage non tenu.

Une fois Stisla supprimé, **rien de tout ça n'est nécessaire**. C'est le gain principal.

### Ce qui est déjà bon et doit être conservé

- Le découpage par composant existe déjà et sa granularité est globalement juste.
- Le principe `custom.css` comme point d'entrée unique avec `@import` fonctionne.
- Une amorce de tokens existe dans `:root`.

---

## 3. Architecture cible

```
css/
├── style.css                  # point d'entrée UNIQUE — imports + rien d'autre
├── foundations/
│   ├── _tokens.css            # variables primitives + sémantiques
│   ├── _bootstrap-bridge.css  # mapping tokens → variables --bs-*
│   ├── _reset.css             # corrections applicatives sur le reset Bootstrap
│   ├── _typography.css        # échelle typographique, titres, texte courant
│   └── _layout.css            # app shell, main-content, footer
├── components/
│   ├── _button.css
│   ├── _card.css
│   └── …                      # un fichier par composant, cf. §6
└── vendor/
    ├── _select2.css           # habillage des plugins tiers
    ├── _datatables.css
    ├── _quill.css
    └── …
```

Trois couches, dans cet ordre d'import strict :

| Couche | Rôle | Peut dépendre de |
|---|---|---|
| `foundations/` | Tokens, reset, typo, layout | Bootstrap |
| `components/` | Composants du bundle | `foundations/` |
| `vendor/` | Habillage des plugins tiers | `foundations/`, `components/` |

`style.css` ne contient **que** des `@import`. Aucune règle. C'est vérifiable
automatiquement et c'est le garde-fou qui empêche le fichier de redevenir un fourre-tout.

### Le pont Bootstrap (`_bootstrap-bridge.css`)

Bootstrap 5.3 expose nativement ses propres variables CSS (`--bs-primary`,
`--bs-body-bg`, `--bs-border-radius`…). En mappant nos tokens dessus :

```css
:root {
  --bs-primary: var(--aro-color-primary);
  --bs-body-bg: var(--aro-color-bg);
  --bs-border-radius: var(--aro-radius-md);
}
```

…tous les composants Bootstrap que nous ne réécrivons pas sont thémés **gratuitement**,
sans une seule surcharge de règle. C'est le levier qui rend le chantier réaliste : on ne
redessine que ce qui a une valeur ajoutée, le reste suit automatiquement.

---

## 4. Système de tokens

Deux niveaux. Les développeurs intégrateurs ne surchargent **que** le niveau sémantique.

### Niveau 1 — primitives (palette brute, non surchargeable en pratique)

```css
--aro-teal-50 … --aro-teal-900
--aro-gray-50 … --aro-gray-900
--aro-red-*, --aro-amber-*, --aro-green-*, --aro-blue-*
```

Échelles à 10 crans, contraste vérifié (cf. §7).

### Niveau 2 — sémantique (l'API publique de thème)

| Famille | Tokens |
|---|---|
| Marque | `--aro-color-primary`, `-hover`, `-active`, `-subtle`, `-contrast` |
| Marque 2 | `--aro-color-secondary` + mêmes déclinaisons |
| États | `--aro-color-success`, `-danger`, `-warning`, `-info` (+ `-subtle`, `-contrast`) |
| Surfaces | `--aro-color-bg`, `--aro-color-surface`, `--aro-color-surface-raised` |
| Texte | `--aro-color-text`, `-muted`, `-subtle`, `-inverted` |
| Bordures | `--aro-color-border`, `-strong`, `-focus` |
| Rayons | `--aro-radius-sm/md/lg/full` |
| Élévation | `--aro-shadow-sm/md/lg` |
| Espacement | `--aro-space-1` … `--aro-space-12` |
| Typo | `--aro-font-sans`, `--aro-text-xs` … `--aro-text-2xl`, `--aro-leading-*`, `--aro-weight-*` |
| Mouvement | `--aro-transition-fast/base` |

### Valeurs

Issues du projet **Claude Design « Aropixel Admin Design System »**
(`claude.ai/design/p/59d90acd-ec36-4288-a16b-ea00108fe7d9`), qui fait référence pour
tout ce qui est visuel.

#### Marque et accents

| Token | Valeur | Rôle |
|---|---|---|
| `--aro-color-primary` | `#06BAB4` | Teal de marque, éclairci vers le mint du logo |
| `--aro-color-primary-hover` | `#05A29D` | |
| `--aro-color-secondary` | `#2E4F5E` | Encre ardoise — actions structurelles / neutres foncées |
| `--aro-color-secondary-hover` | `#223C48` | |
| `--aro-color-cta` | `#FF6B5B` | Corail — **une seule action à forte emphase par écran** |
| `--aro-color-cta-hover` | `#F0503F` | |

> Le corail est une **addition** du design system, absente de l'existant. C'est la
> décision visuelle la plus structurante : elle introduit une hiérarchie d'action à trois
> niveaux (corail = l'action clé, teal = action normale, ardoise = action structurelle).
> À faire respecter dans les templates, sinon l'accent perd tout son sens.

#### Sémantique

`--aro-color-success` `#63CEB3` · `--aro-color-danger` `#E52321` ·
`--aro-color-warning` `#F25C05` · `--aro-color-info` `#E39B02`

#### Surfaces, texte, lignes

| Token | Valeur |
|---|---|
| `--aro-color-bg` | `#F5F8FA` (fond applicatif, blanc cassé froid) |
| `--aro-color-surface` | `#FFFFFF` (cartes) |
| `--aro-color-border` | `#DDE1E5` (filets 1px) |
| `--aro-color-border-card` | `#F1F1F2` |
| `--aro-color-border-line` | `#ECECEC` (séparateur d'en-tête de carte) |
| `--aro-color-text` | `#333333` |
| `--aro-color-text-strong` | `#181C32` |
| `--aro-color-text-label` | `#3F4254` |
| `--aro-color-text-muted` | `#99A1B7` |
| `--aro-color-text-subtle` | `#A1A5B7` (fil d'Ariane, méta) |

#### Champs de formulaire

| Token | Valeur |
|---|---|
| `--aro-field-bg` | `#FFFFFF` |
| `--aro-field-border` | `#D7DCE2` |
| `--aro-field-bg-disabled` | `#F2F4F7` |
| `--aro-field-border-disabled` | `#E4E8EC` |

> **Incohérence à lever dans le design system.** Le `readme.md` décrit les champs comme
> « fond gris doux, sans bordure au repos », alors que `tokens/colors.css` définit un fond
> **blanc avec bordure visible** (`#D7DCE2`), commenté « resting fill — white, sits above
> the app bg ». Les tokens semblent avoir évolué après la rédaction du readme. **Les
> tokens font foi** — c'est aussi le comportement attendu sur un fond applicatif gris.

#### Indicateurs de statut

`--aro-status-online` `var(--aro-color-success)` (vert `#63CEB3`) · `--aro-status-scheduled`
`var(--aro-color-warning)` (orange `#F25C05`) · `--aro-status-offline` `var(--aro-color-danger)`
(rouge `#E52321`)

> **Feu tricolore sémantique** (arbitrage §12 tranché le 24 juillet 2026). Les trois états de
> publication *référencent* la famille success/warning/danger — vert=live, orange=en attente,
> rouge=hors ligne. On évite ainsi que « en ligne » reprenne le teal de `--aro-color-primary`,
> et un re-thème des couleurs sémantiques suit automatiquement.

#### Rayons

| Token | Valeur | Usage |
|---|---|---|
| `--aro-radius-xs` | `2px` | Badges, tags, pastilles de couleur |
| `--aro-radius-sm` | `3px` | Dropdowns, petits contrôles |
| `--aro-radius-input` | `5px` | Champs de saisie |
| `--aro-radius-card` | `6px` | Cartes |
| `--aro-radius-pill` | `100px` | Boutons et badges arrondis |

#### Élévation

| Token | Valeur |
|---|---|
| `--aro-shadow-card` | `0 4px 12px rgba(0,0,0,.03)` |
| `--aro-shadow-soft` | `0 1px 3px #E6ECF1` (boutons default/outline) |
| `--aro-shadow-dropdown` | `0 1px 3px rgba(0,0,0,.1)` |
| `--aro-shadow-modal` | `0 4px 15px rgba(0,0,0,.2)` |

Ombres volontairement très douces — rien de lourd ni de flottant.

#### Mouvement

`--aro-ease` `cubic-bezier(.4,0,.2,1)` · `--aro-duration-fast` `.2s` ·
`--aro-duration-base` `.5s`

Fondus de couleur et d'opacité uniquement. Pas de rebond, pas de glissement, pas de
rétrécissement au clic.

#### Typographie

Famille unique : **Poppins** (sans-serif géométrique), graisses 100–900.

```css
--aro-font-sans: 'Poppins', system-ui, -apple-system, 'Segoe UI', sans-serif;
```

Graisses : 300 light · 400 regular · 500 medium · 600 semibold · 700 bold.
Titres en 600–700.

> **Poppins est auto-hébergé. ✅ Fait.**
>
> Ce n'était pas le cas : `style.css` contenait **27 `@font-face` pointant tous vers
> `fonts.gstatic.com`**, et `Security/login.html.twig:10` ajoutait un `<link>` Google Fonts
> explicite. Seul Roboto (police de Stisla, inutilisée) était présent localement. Le
> problème RGPD existait donc déjà, et supprimer `style.css` aurait fait disparaître la
> police en silence, invalidant toute mesure typographique.
>
> Désormais : `css/foundations/_fonts.css` + `fonts/poppins/`, **10 fichiers, ~80 Ko**.
>
> - Graisses **300 à 700** seulement. Les 100/200/800/900 n'apparaissaient que dans des
>   utilitaires morts (`.text-thin`, `.text-bolder`) ou concernaient FontAwesome.
> - Sous-ensembles **latin + latin-ext**, `devanagari` écarté. Le latin-ext est nécessaire
>   au tchèque.
> - `font-display: swap`.
>
> Vérifié au navigateur : **aucune requête vers `googleapis`/`gstatic`**, `body` rendu en
> Poppins, et seules les graisses réellement utilisées sont téléchargées grâce aux
> `unicode-range`.
>
> **Reliquat :** les 27 `@font-face` de `style.css` pointent toujours vers Google. Ils sont
> supplantés par les nôtres (chargés après) pour les graisses 300-700, et disparaîtront
> avec Stisla à l'Étape 5. Tant que Stisla est là, une graisse hors 300-700 rappellerait
> Google — aucune n'est utilisée.

#### Layout

`--aro-sidebar-width` `250px` · `--aro-navbar-height` `70px` ·
`--aro-content-max` `1200px`

### Échelle typographique (actée)

Base **14px / 1.5** — densité modérée. Plus lisible que les 13px hérités de Stisla,
tout en restant dense pour des écrans de gestion chargés.

> **Tranchée à l'Étape 6**, sur la DataTable, une fois `_table.css` sur tokens — le seul
> état où la question est répondable. Tant que `vendor/_datatables.css` épinglait les
> listes à `13px !important`, basculer le token ne déplaçait que 1 à 9 px : on aurait
> arbitré à l'aveugle.
>
> **La mesure a reformulé la question.** Passer de 13 à 14px coûte **5 px par page de dix
> lignes**, parce que la hauteur de ligne vient du padding de cellule et du line-height,
> pas de la taille de police. Le compromis n'a donc jamais été lisibilité contre
> compacité : la compacité n'était presque pas en jeu.
>
> Le mécanisme est vérifié au passage : la densité se change bien par **une seule
> variable**, à condition qu'aucun composant ne code de taille en dur (§7-2).

| Token | Valeur | = px | Usage |
|---|---|---|---|
| `--aro-text-xs` | `0.75rem` | 12px | Labels, métadonnées |
| `--aro-text-sm` | `0.8125rem` | 13px | Texte secondaire |
| `--aro-text-base` | `0.875rem` | 14px | Texte courant |
| `--aro-text-lg` | `1rem` | 16px | Sous-titres |
| `--aro-text-xl` | `1.125rem` | 18px | |
| `--aro-text-2xl` | `1.375rem` | 22px | Titres de page |

**Unités en `rem`, pas en `px`.** Typographie et espacements sont exprimés en rem (÷16 de
la valeur pixel) pour **suivre le réglage de taille de police du navigateur et le zoom** —
une propriété d'accessibilité que les px n'ont pas, et la raison pour laquelle Tailwind
procède ainsi. À racine 16px, le rendu est identique aux anciens px (vérifié : diff nul sur
les écrans sans bouton). Rayons, bordures, ombres et dimensions structurelles restent en
px : le détail fin et le chrome fixe ne doivent pas enfler au zoom.

Échelle d'espacement sur une base de **4px** (`--aro-space-1` = 4px, `-2` = 8px, etc.).

Tailles par rôle : titre de page **20px/600** · en-tête de carte **16px/600** ·
label de formulaire **14px/500** · badge **10px capitales**.

> Ce changement de 13 → 14px modifie la hauteur de toutes les lignes de liste. Les écrans
> les plus denses (DataTable, médiathèque) sont à contrôler en priorité lors de la
> validation visuelle.

### Réconciliation avec le design system

Le design system **documente l'existant** sur les fondations (13px, grille 5px, noms de
variables historiques) et **prescrit** sur la palette et les composants. Là où les deux
divergent, les décisions du §10 priment — le design system n'argumentait pas contre elles,
il transcrivait le code actuel.

| Sujet | Design system | Cahier de specs | Retenu |
|---|---|---|---|
| Base typographique | 13px / 1.54 | 14px / 1.5 | **14px** (§10) |
| Grille d'espacement | 5px | 4px | **4px** (§10) |
| Nommage des tokens | `--main-bg-color` verbatim | `--aro-*`, sans alias | **`--aro-*`** (§10) |
| Icônes | FontAwesome 5 + Ionicons | ux-icons / Tabler | **ux-icons** (§11) |

**Conséquence à traiter :** les cotes des composants du design system sont calées sur la
grille de 5 (champs 45px, padding de carte 25px, boutons `11px 22px`). Elles doivent être
re-dérivées sur la base 4 — champs 44px, padding de carte 24px, etc. Les écarts sont
d'un pixel et visuellement imperceptibles, mais ils doivent être faits **une fois, dans les
tokens**, et non improvisés composant par composant.

**Règle absolue : aucune valeur brute hors de `_tokens.css`.** Pas un hex, pas un `px`
d'espacement, pas un `box-shadow` littéral dans un fichier de composant. Contrôlable par grep.

### Pas de couche d'alias — décision

Une version antérieure de ce document prévoyait une couche d'alias dépréciés
(`--main-bg-color: var(--aro-color-primary)`) pour ne pas casser les thèmes clients.

**Elle est abandonnée.** v3 n'est pas diffusée (seul tag du dépôt : `v3.0.0-alpha1`), donc
aucun projet n'est installé sur le nommage actuel. Conserver des alias reviendrait à
traîner une dette de compatibilité envers une base d'utilisateurs qui n'existe pas.

Le nommage historique disparaît intégralement. Nettoyage franc.

> **Ce qui reste vrai :** des projets clients existent sur le **major précédent**. Le guide
> de migration ancien major → v3 (§15) reste un livrable réel. Ce qui disparaît, c'est la
> contrainte de préserver les noms *internes à v3*.

### L'API de theming PHP concurrente

`src/DependencyInjection/Configuration.php:103-110` définit une seconde API de thème, non
documentée jusqu'ici :

```yaml
aropixel_admin:
    theme:
        colors:
            background_color: '#0CABA8'
            btn_background_color: '#0CABA8'
            btn_color: '#fff'
```

Elle est consommée en **styles inline** dans `Security/login.html.twig:51` et `:112`, ainsi
que dans `Email/activation.html.twig:45` et `Email/reset.html.twig:46`.

**Un style inline bat les tokens, le pont Bootstrap, les cascade layers et `!important`.**
Le §5 déclare `--aro-*` « l'API publique de thème » alors qu'une API PHP la surclasse
silencieusement sur l'écran de login. Ses défauts (`#0CABA8`) diffèrent en plus du nouveau
`--aro-color-primary` (`#06BAB4`) : une installation neuve afficherait deux teals côte à
côte.

**Résolution :** conserver les clés de configuration (elles sont utiles), cesser d'émettre
des styles inline, et faire émettre par `base.html.twig` un bloc
`<style>:root{ --aro-color-primary: … }</style>` **après** les liens CSS. L'API PHP est
ainsi **absorbée** par l'API tokens au lieu de la concurrencer. Aligner ses défauts sur la
nouvelle palette.

Les emails conservent leurs styles inline — c'est le comportement correct en HTML d'email.

---

## 5. Nommage et contrat de surcharge

### Règle de préfixe

| Cas | Convention | Exemple |
|---|---|---|
| Bootstrap possède le composant | on étend sa classe, sans préfixe | `.btn`, `.card`, `.modal` |
| Le bundle invente le composant | préfixe `aro-` | `.aro-section-header`, `.aro-file-widget` |
| Variante | suffixe modificateur BEM-like | `.aro-section-header--compact` |
| État | classe `is-` / attribut ARIA | `.is-active`, `[aria-selected]` |

Le préfixe `aro-` garantit zéro collision avec le CSS applicatif du projet hôte, et rend
immédiatement lisible dans un template ce qui vient du bundle.

### Contrat public / interne

Le design system publie un **contrat** : ce qui est stable et surchargeable sans risque
lors d'une montée de version.

- **Public et stable** : tokens sémantiques `--aro-*`, classes racines de composants.
- **Interne, peut changer sans préavis** : primitives, sélecteurs descendants, structure DOM interne.

Chaque fichier de composant documente en en-tête ses points d'extension :

```css
/**
 * Section header
 * Public : .aro-section-header, --aro-section-header-padding
 * Interne : .aro-section-header__actions (structure susceptible d'évoluer)
 */
```

---

## 6. Inventaire des composants

Dérivé des 69 templates Twig existants. Priorisation par fréquence d'usage et par
dépendance (un composant P1 ne peut pas commencer avant ses fondations P0).

### P0 — Fondations et socle (bloquant pour tout le reste)

| Composant | Source actuelle | Note |
|---|---|---|
| Tokens | `custom.css` `:root` | Renommage complet |
| Reset + typographie | `style.css` | Repartir de zéro |
| Layout / app shell | `_fullscreen.css` (390 l.) | Le plus gros morceau |
| Bouton | `_btn.css` (158 l.) | Variantes à rationaliser |
| Carte | `_card.css` (101 l.) | Supprimer les sélecteurs table imbriqués |
| Formulaire | `_form.css` (645 l.) | Le plus lourd — à découper. **Voir aussi §16** : le thème Twig est un chantier distinct et plus lourd encore |
| Table | `_table.css` (131 l.) | |
| Section header | `_section-header.css` (152 l.) | |

### P1 — Navigation et structure

Menu fullscreen · quick-menu · sous-menus · raccourcis · breadcrumb · onglets ·
search-nav (`_search-nav.css`, 214 l.) · pagination · footer

### P2 — Widgets métier

Widget image (+ galerie, légende, preview) · widget fichier (`_file.css`, 303 l.) ·
widgets de publication (date, statut, slug, publish) · indicateur de statut ·
DataTable et ses cellules · liste ordonnable (drag & drop) · badge · macros d'actions

### P3 — Feedback et écrans dédiés

Modale · offcanvas · dropdown · toast · alerte · tooltip · **état vide** (à créer,
absent aujourd'hui) · écrans d'authentification (`login.css` → `_auth.css`)

### P4 — Habillage des plugins tiers

Select2 · DataTables · Quill (`_wysiwyg.css`) · Cropper · pickers date/couleur/heure
(`_picker.css`) · iziToast · x-editable

> Note : `_helpers.css` (541 l.) n'est pas un composant. Son contenu doit être **audité
> et réparti** : ce qui double un utilitaire Bootstrap est supprimé, le reste rejoint
> `foundations/` ou le composant concerné. Aucun fichier « helpers » dans la cible.

### Apports du design system

Le design system définit ~40 composants, contre 18 fichiers CSS aujourd'hui. Trois
catégories, à ne pas traiter de la même façon.

**a) Formalisation de motifs existants** — à intégrer au périmètre, sans surcoût réel :
`Avatar` et `StatusDot` (aujourd'hui `.header-image`, `.img-state-icon`,
`.status-indicator` éparpillés), `IconButton`, `Tooltip`, `Toast`, `Switch`, `Tag`
(distinct du `Badge` statique).

**b) Reprises enrichies** — même rôle qu'aujourd'hui, mais systématisées :
`Badge` (solid/soft/outline × 7 couleurs, contre un `.badge-default` unique),
`Button` (échelle xs→lg, 7 variantes), `PageHeader` (variante riche de `SectionHeader`),
`NavTabs`, `EmptyState` (identifié comme manquant en P3).

**c) Nouveautés sans usage actuel** — `Stat` (5 formes), `GridList` (4 formes),
`Accordion`, `SplitButton`, `ButtonGroup`, `RadioGroup` (7 dispositions), `Dialog`,
`List`, `FileUpload`.

> **Recommandation de périmètre.** Ne construire en première passe que ce que les 69
> templates utilisent réellement — soit (a), (b) et la portion de (c) effectivement
> appelée. Les autres restent **spécifiés dans le design system mais non implémentés**,
> et seront ajoutés à la demande.
>
> Construire les ~40 composants d'emblée doublerait le chantier pour du CSS non exercé,
> donc non validé et destiné à diverger. `RadioGroup` en 7 dispositions ou `Stat` en 5
> formes sont des catalogues de possibilités, pas un cahier des charges.

---

## 7. Règles de rédaction

Contraintes vérifiables, à faire respecter en revue :

1. **`!important` selon la couche** — la règle n'est pas la même partout :

   | Couche | Règle |
   |---|---|
   | `foundations/` | **Zéro.** Aucune exception. |
   | `components/` | **Zéro.** Toute exception doit être justifiée en revue. |
   | `vendor/` | **Toléré**, à condition d'être commenté avec sa raison. |

   La tolérance en `vendor/` n'est pas un relâchement : les plugins jQuery (Select2,
   DataTables, Cropper) posent des styles **inline** via JavaScript, et un style inline
   ne se bat qu'avec `!important`. Aucune architecture CSS n'y change quoi que ce soit.

   Partout ailleurs, `!important` signale un problème d'architecture, pas un besoin
   réel — voir ci-dessous.
2. **Zéro valeur brute** hors `_tokens.css` (couleurs, espacements, ombres, rayons).
3. **Spécificité plafonnée à 2 niveaux.** Pas de `.a .b > .c .d`.
4. **Pas de sélecteur d'élément nu** dans `components/` (`div`, `span`, `table`) —
   ils appartiennent à `foundations/_reset.css`.
5. **États systématiques** : tout élément interactif définit `:hover`, `:focus-visible`,
   `:disabled` et, le cas échéant, `[aria-expanded]` / `.is-active`.
6. **Accessibilité** : contraste AA minimum (4.5:1 texte courant, 3:1 texte large et
   bordures d'input). Focus visible jamais supprimé sans remplacement.
7. **Un fichier de composant ≤ 200 lignes.** Au-delà, c'est deux composants.
8. **Ordre interne d'un fichier** : variables locales → bloc racine → éléments →
   modificateurs → états → responsive.

### Surcharger Bootstrap sans `!important`

Nous produisons bien une surcouche de Bootstrap — mais la surcharge par force brute n'est
que le dernier recours. Trois leviers, par ordre de préférence :

**1. Le pont de variables (§3).** La plupart du temps il n'y a rien à surcharger :
on redéfinit `--bs-primary`, `--bs-border-radius`, et Bootstrap se réaccorde seul.

**2. Les variables de composant Bootstrap.** Bootstrap 5.3 expose des variables *à
l'intérieur* de ses composants (`--bs-btn-bg`, `--bs-btn-hover-bg`, `--bs-btn-padding-y`,
`--bs-card-spacer-y`…). Les redéfinir ne demande aucune surenchère de spécificité :

```css
.btn-primary {
  --bs-btn-bg: var(--aro-color-primary);
  --bs-btn-hover-bg: var(--aro-color-primary-hover);
  --bs-btn-box-shadow: none;
}
```

C'est le levier le plus sous-utilisé, et il couvre l'essentiel des composants Bootstrap.
**À privilégier systématiquement avant d'écrire une règle de surcharge.**

**3. Les cascade layers**, si les deux premiers ne suffisent pas. Bootstrap chargé dans
un layer, notre CSS après : le nôtre gagne **quelle que soit la spécificité**, ce qui rend
`!important` structurellement inutile.

> **Piège à connaître.** Bootstrap est aujourd'hui chargé par un `<link>` dans
> `base.html.twig`, donc **non layé** — et le CSS non layé bat le CSS layé. Ne mettre que
> *notre* CSS dans un layer le ferait donc **perdre**. Il faudrait charger Bootstrap via
> `@import url(...) layer(bootstrap)` depuis `style.css`, au prix d'une requête en
> cascade plutôt qu'un chargement parallèle.
>
> Bénéfice secondaire : le CSS applicatif du projet hôte, non layé, gagnerait à son tour
> sur le nôtre sans avoir à surenchérir. Ça règle proprement le contrat de surcharge (§5).

→ Adoption des cascade layers : arbitrage §12.

---

## 8. Stratégie de migration

L'objectif est de ne jamais avoir un dépôt cassé. Le chantier est séquentiel, pas parallèle.

**Étape 0 — Galerie de référence visuelle. ✅ Faite.** Le projet Claude Design
« Aropixel Admin Design System » tient ce rôle : il fixe la palette, les élévations, les
rayons, le mouvement et le comportement de ~40 composants dans leurs différents états.

C'est le **contrat visuel** du chantier. `_tokens.css` n'en est que la transcription, et
toute question d'apparence se tranche en s'y référant plutôt qu'en arbitrant dans le code.

**Étape 1 — Prérequis, sans une ligne de CSS.** Le blocage n'est pas le CSS : on ne peut
pas voir la plupart des écrans à redessiner, ni prouver qu'on n'a rien cassé. Fixtures
(aucun utilisateur aujourd'hui, `castor fixtures` a son corps commenté), inventaire
d'écrans, harnais de capture, référence archivée, auto-hébergement de Poppins, garde-fous
du §9 en CI. Environ une semaine.

**Étape 2 — Suppressions sans risque.** `skins/reverse.css` (orphelin), les ~135 classes
d'espacement mortes de `_helpers.css`, `Menu/section.html.twig` (0 octet). Sa vraie
fonction est de **prouver que le harnais de capture marche**, en produisant un diff nul.

**Étape 3 — Fondations sous Stisla. ✅ Faite.** `foundations/` (fonts, tokens, pont
Bootstrap) importé *avant* Stisla, et `admin_theme.colors` absorbé dans les tokens.

Menée en trois sous-étapes pour que chaque diff soit attribuable à une cause unique :

| | Effet mesuré |
|---|---|
| `_tokens.css` seul | **48/48 identiques** — déclarer des variables que personne ne lit ne change rien |
| `_bootstrap-bridge.css` | 36 écrans, **tous les écarts ≤ 30/255, aucun au-delà** : la couleur de texte par défaut de Bootstrap (`#212529`) s'aligne sur le token (`#333333`). Aucun aplat, aucun décalage de mise en page |
| Absorption `admin_theme.colors` | 2 écrans — le login seul, `#0CABA8` → `#06BAB4`. Exactement le périmètre où l'API PHP s'appliquait |

Les cascade layers étaient prévues ici : elles sont reportées à l'Étape 5 (§12).

> **Ce n'est pas neutre visuellement.** Bootstrap livre `--bs-primary: #0d6efd` et Stisla
> le repeint par-dessus. Dès que le pont pose `--bs-primary: var(--aro-color-primary)`,
> tout composant Bootstrap que Stisla ne repeignait pas passe du bleu au teal. Changement
> souhaitable, mais changement : prévoir une revue de captures, pas un merge « sans effet ».

**Étape 4 — Re-provisionner les skins de plugins. ✅ Faite.** `components.css` (2 083 l.)
est **supprimé**.

La méthode a compté plus que la lecture : plutôt que d'auditer 2 083 lignes de sélecteurs,
le fichier a été délié et le harnais a désigné les dépendances réelles — **5 écrans, tous
des listes**. La partie porteuse tenait en une poignée de règles DataTables, dont le
`border-collapse: collapse` qui masque les séparateurs verticaux natifs.

Extraits vers `vendor/` : `_datatables.css`, `_select2.css`, `_izitoast.css`,
`_tagsinput.css`, `_pwstrength.css` — les plugins effectivement chargés par
`base.html.twig`. Les skins de daterangepicker, Dropzone, Selectric et FullCalendar ont
été écartés : leurs assets sont vendorés mais jamais liés.

> **Une transcription doit conserver sa position dans la cascade.** Importés en dernier
> comme le §3 le prescrit pour l'architecture cible, ces fichiers réappliquaient la
> bordure d'en-tête DataTables de Stisla par-dessus notre `_table.css` — un trait d'un
> pixel sur chaque liste. Ils sont donc importés **avant** `components/`, là où
> `components.css` se trouvait, et ne rejoindront la position du §3 qu'une fois restylés
> sur les tokens plutôt que transcrits.

Preuve : **48/48 captures identiques**, `components.css` délié puis supprimé.

**Étape 5 — Ablation de Stisla, puis cascade layers. ✅ Faite.** `style.css` (3 035 l.)
supprimé, cascade layers activées.

**Le shell n'a eu besoin d'aucune correction.** Les 6 lignes de `_fullscreen.css` prévues
étaient déjà suffisantes : le dashboard n'a bougé que de **0,6 %**, et aucun écran n'a
présenté de mise en page cassée — rien de superposé, rien d'inatteignable. La prédiction
tient : Bootstrap fournit la structure, Stisla ne faisait que repeindre.

**Une lacune du pont, révélée par l'ablation.** Bootstrap 5.3 dérive les fonds de surface
de `--bs-body-bg` (`--bs-card-bg`, `--bs-dropdown-bg`, `--bs-modal-bg`, `--bs-offcanvas-bg`,
`--bs-accordion-bg`, `--bs-popover-bg`, `--bs-list-group-bg`). Notre `--bs-body-bg` étant
le gris applicatif, toutes ces surfaces devenaient grises — Stisla le masquait en
repeignant `.card` en blanc. Le pont comble désormais l'écart, mais **pas depuis `:root`** :
ce sont des variables déclarées au niveau du composant, et une valeur posée sur l'élément
bat toujours celle héritée de la racine. Il a fallu une règle par composant, ce qui est
précisément la technique du §7-2.

Correction faite : le pire écran passe de **41,5 % à 9,0 %**.

**Les cascade layers confirment le report du §12.** Activées après l'ablation, elles
produisent **0,04 à 0,19 % d'écart** et aucun changement de hauteur — contre 40 écrans sur
48 lorsqu'elles avaient été tentées avec Stisla encore chargé.

Volume CSS : **8 732 → 4 182 lignes**. Reprise d'apparence consignée au §17.

> **Règle de l'étape : on corrige la mise en page, on consigne l'apparence.** Tout ce qui
> est mal placé, superposé ou inatteignable est corrigé ici. Tout ce qui est seulement laid
> — couleur, graisse, espacement — part sur une liste de reprise et n'est pas touché,
> puisque les composants vont le réécrire. Sans cette règle, l'étape avale le chantier.

**Étape 6 — Migration composant par composant.** Réécrire sur les tokens, retirer les
`!important`, valider par capture, adapter les Twig. Un composant = une PR relisable.

*Typographie ✅ faite.* `components/_text.css` → `foundations/_typography.css` : 26 classes
utilitaires ramenées à 5, les 19 autres n'étant employées nulle part. `.text-primary` et
`.text-muted` ont été **supprimées plutôt que portées** — ce sont des classes Bootstrap, et
le pont alimente déjà `--bs-primary` et `--bs-secondary-color` avec nos tokens. Le meilleur
code de composant reste celui qu'on n'a plus à écrire.

> **Un défaut de l'Étape 5 rattrapé ici.** Stisla portait l'unique règle
> `body { font-family: 'Poppins' }`. En le supprimant, l'admin est repassé à la pile de
> polices système — et la revue du diff d'ablation, déjà chargée, ne l'a pas relevé.
> `_typography.css` est désormais la seule règle qui applique la police.
>
> C'est le contrôle de fidélité à densité constante qui l'a révélé, en isolant un écart
> qui n'aurait pas dû exister. Sans ce contrôle en deux temps, le défaut serait passé.

> **L'arbitrage de densité ne peut pas encore se faire.** Basculer `--aro-text-base` de 13
> à 14px ne déplace que **+1 à +9 px** : les composants codent encore leurs tailles en dur,
> et `vendor/_datatables.css` épingle les listes à `13px !important`. La bascule sera
> visible — donc jugeable — quand `_table.css` consommera les tokens. Le token est laissé à
> **14px**, valeur provisoire du §4.

*Tableaux ✅ faits.* `components/_table.css` et `vendor/_datatables.css` reconstruits sur
les tokens, avec une séparation nette : le composant ne contient **aucun `!important`** —
Bootstrap étant layé, il perd par construction — et seul le fichier `vendor/` en garde,
pour ce qui doit battre la feuille de style du plugin.

Deux règles vivaient en double, solides dans `vendor/` et pointillées dans `components/`,
toutes deux en `!important`, l'ordre des fichiers départageant. Elles sont désormais
déclarées **une fois**.

L'arbitrage de densité a été tranché ici : **14px confirmé** (§4).

*Boutons ✅ faits.* `_btn.css` (mécanique) et `_btn-variants.css` (la hiérarchie d'action),
tous deux sur tokens, sans un seul `!important`.

**La hiérarchie corail / teal / ardoise est rendue pour la première fois.** L'ancien
`.btn-secondary` était magenta et servait exactement au bouton de soumission — il portait
donc le rôle de CTA sous un nom qui dit l'inverse, puisque `.btn-secondary` signifie
« action discrète » chez Bootstrap. Renommé en `.btn-cta`, 4 templates migrés.
`.btn-secondary` retrouve son sens Bootstrap et prend l'ardoise du design system.

> **Une limite du pont, à connaître avant tout autre composant.** Les variantes de bouton
> de Bootstrap **figent leurs couleurs** : `.btn-primary` embarque `--bs-btn-bg: #0d6efd`
> en littéral, il ne lit pas `--bs-primary`. Ponter la palette thème donc les bordures,
> les liens et les `.text-*`, mais laisse tous les boutons bleus. Chaque variante utilisée
> doit déclarer son jeu complet — ne poser que les états de survol, comme le premier jet
> l'a fait, produit un bouton bleu au survol teal.

> **Le cliquet a attrapé ma propre violation.** Le fichier atteignait 206 lignes, six
> au-dessus du plafond du §7-7. Plutôt que de relever le plafond à la première gêne, le
> fichier a été découpé — et la couture est réelle : la hiérarchie d'action est une
> décision de design, le reste est de la mécanique.

*Cartes ✅ faites.* `_card.css` piloté intégralement par les variables `--bs-card-*`, sans
règle de surcharge ni `!important`. Contrairement aux boutons, les cartes **lisent** bien
les valeurs pontées : seul ce qui diffère des défauts Bootstrap est déclaré.

Ferme les entrées 1 et 5 du §17 : ombre, rayon, et en-tête séparé par un filet plutôt que
par un aplat gris — le défaut de Bootstrap fait lire l'en-tête comme une seconde surface.

*En-tête de section ✅ fait.* Premier composant à prendre le préfixe `aro-` (§5) :
`.section-header` → `.aro-section-header`, quatre occurrences migrées. Bootstrap possède
`.card`, `.btn` et `.table` ; il ne possède pas celui-ci, et le préfixe dit désormais de
quelle feuille de style une classe provient.

Six classes supprimées faute d'usage : `header-left-content`, `header-right`,
`section-entry-properties`, `section-entry-property`, `section-entry-status`,
`btn-animate-collapse`.

Ferme l'entrée 3 du §17. Le séparateur du fil d'Ariane est posé via
`--bs-breadcrumb-divider` plutôt que par une surcharge de `::before` — la règle survit
ainsi à une montée de version de Bootstrap. À noter : `.section-entry-property` portait
bien un séparateur ` - `, mais n'était employée nulle part ; le fil d'Ariane réel est celui
de Bootstrap.

*Badges et recherche ✅ faits.* `_badge.css` reconstruit sans **aucune variante de
couleur** : le fichier livrait `.badge-default` et les templates utilisaient `.badge-light`
et `.badge-danger` — des classes **Bootstrap 4, supprimées en 5**, qui ne faisaient donc
plus rien depuis la montée de version. Plutôt que réimplémenter un vocabulaire mort, les
templates passent à `.text-bg-*` de Bootstrap, que le pont thème déjà. Ferme l'entrée 7.

Les indicateurs de statut étaient déclarés **deux fois**, ici et dans `custom.css`, avec
des valeurs différentes pour les mêmes états. Ils sont regroupés dans `_badge.css` ;
`custom.css` perd 62 lignes.

`_search-nav.css` dépassait le plafond de 200 lignes parce qu'il hébergeait **deux
fonctionnalités** sans rapport. Séparé en `_search-nav.css` (159 l.) et
`_site-switcher.css` (67 l.).

> **Deux composants sans filet.** Le panneau de recherche ne s'ouvre qu'à la saisie et la
> modale de bascule qu'au clic : aucun des deux n'apparaît dans la référence visuelle. Ils
> ont été refaits par lecture, pas par mesure — les seuls dans ce cas jusqu'ici, et leurs
> en-têtes le signalent.

*Shell applicatif ✅ fait.* `_fullscreen.css` (390 l.) hébergeait **quatre préoccupations**.
Éclaté en `foundations/_layout.css` (46 l.), `components/_navbar.css` (137 l.),
`_fullscreen-menu.css` (180 l.) et `_quick-links.css` (95 l.).

Les 28 valeurs hexadécimales étaient concentrées dans deux systèmes de teintes de tuile,
chacune déclarée **deux fois** — un hex avec alpha au repos, un autre au survol, sans
relation énoncée entre les deux. Elles deviennent sept tokens `--aro-accent-*`, les deux
états étant dérivés par `color-mix`.

> Ces tokens sont **nommés par apparence** (`mint`, `blue`, `orange`…), ce qui contredit la
> convention du §4. C'est assumé : ces teintes ne portent aucun sens, elles distinguent
> seulement des tuiles voisines. Le design system ne définit pas de gamme d'accents ; à
> réconcilier quand ce sera le cas.

Ferme l'entrée 2 du §17 — mais pas pour la raison supposée. Le pied de page ne s'empilait
pas faute de mise en page du conteneur : `.footer-left` contient un `<div class="bullet">`,
un élément **bloc** posé au milieu d'une phrase. Stisla le stylait ; sans lui, il coupait
la ligne.

*Thème de formulaire, partie B ✅ faite* (§16). Fossiles Bootstrap 3/4 retirés
(`panel panel-default`, `input-group-prepend/append`, `has-error`, `control-label`),
3 styles inline remplacés par des classes, 4 chaînes françaises en dur externalisées et
2 clés pivots françaises normalisées. Cinq clés ajoutées aux six fichiers de traduction.

> **Le nettoyage est inerte : 48/48 captures identiques.** C'est la confirmation directe de
> l'analyse du §16 — ces fossiles ne faisaient réellement plus rien depuis les migrations
> Bootstrap 3 → 4 → 5. Le markup mort n'était pas un risque, seulement du bruit.

> **Une traduction fausse trouvée au passage.** La clé `files.widget.title` existait déjà,
> mais sa version anglaise disait « Title » au lieu de « Files ». Corrigée. Relevé aussi :
> `fr.xlf` compte 400 unités contre 394 en anglais — l'écart du §15, à combler avant la
> bascule de locale.

*`_form.css` ✅ fait* (§16 partie C). 645 lignes réécrites sur les tokens et éclatées en
`_form.css` (85 l.), `_form-controls.css` (168 l.) et `_form-collection.css` (119 l.), le
skin Select2 rejoignant `vendor/_select2.css`.

Ferme l'entrée 4 du §17 : les champs sont blancs avec une bordure `--aro-field-border`, au
lieu du gris hérité du fond applicatif faute de style au repos.

> **130 lignes mortes supprimées.** Le bloc `.navbar .form-inline .search-result` était un
> ancien composant de recherche, supplanté par `#search-nav` et employé par aucun
> template. C'était près d'un cinquième du fichier.
>
> Un violet Stisla (`#6777ef`) subsistait dans les règles Select2 — repéré et tokenisé au
> passage.

**Étape 7 — Renommage. ✅ Faite (avec réserve).** `custom.css` est renommé `style.css` et le
bloc de tokens legacy `:root` (`--main-*`/`--second-*`) supprimé, ses valeurs promues en
`--aro-*`. Le fichier n'est **pas encore strictement « imports uniquement »** : il garde un
tail de ~120 lignes de règles réellement globales (couleur de lien, `<hr>`, quelques
utilitaires MenuBundle). Résidu consigné au §0.B.

### Pourquoi l'ablation en Étape 5 et non en dernier

Une version antérieure de ce document plaçait l'ablation à la fin, en prévoyant « une passe
de rattrapage conséquente ». Cette prédiction est l'argument contre ce plan.

1. **Tout composant migré sous Stisla est validé contre une base fausse.** Réécrire
   `_card.css` pendant que `style.css` est chargé produit une carte qui est la somme de nos
   règles *et* de celles de Stisla — indiscernables à l'œil. À l'ablation, elle change, et
   il faut la rerelire. On valide chaque composant deux fois.
2. **Ça reporte le plus gros inconnu au point de coût irrécupérable maximal.** Une tâche de
   découverte se place en début de projet, pas après avoir dépensé le budget.
3. **Le risque est déjà désamorcé.** Bootstrap 5 fournit la *structure* des composants,
   Stisla ne fait que *repeindre*. `_fullscreen.css` est déjà un shell de remplacement
   complet. `components.css` est à ~90 % des widgets de démo inutilisés. L'ablation dégrade
   **l'apparence, pas la mise en page** — et l'apparence est ce qu'on réécrit de toute façon.

Coût assumé : la sandbox paraît inachevée pendant quelques semaines.

### Validation visuelle

La sandbox `application/` sert de banc de test. Un inventaire des écrans à contrôler
après chaque lot (liste CRUD, formulaire, médiathèque, menu, login, dashboard) est à
constituer — il n'y a pas de test automatisé sur le CSS.

Le **catalogue de composants** (§14) constitue le second filet, plus fin : il expose
chaque composant dans tous ses états sur une seule page, ce que les écrans réels ne font
jamais. Les deux sont complémentaires — le catalogue attrape les régressions d'état, la
sandbox attrape les régressions d'intégration.

---

## 9. Critères d'acceptation

Le chantier est terminé quand, sur `admin-bundle` :

- [x] `style.css` (Stisla) et `components.css` (Stisla) sont supprimés du dépôt
- [ ] `grep -r '!important' css/foundations css/components` ne remonte **rien** —
      foundations ✅ ; **components : 24 restants** (cliquet, plafond abaissé au fil)
- [x] Les `!important` de `css/vendor/` sont tous commentés avec leur raison
- [ ] `grep -rE '#[0-9a-fA-F]{3,6}' css/ --exclude=_tokens.css` ne remonte rien —
      foundations/components ✅ ; **5 restants dans le tail de `style.css`** (couleurs
      MenuBundle + chip Select2)
- [ ] Le nouveau `style.css` ne contient que des `@import` — **renommé ✅, mais garde un
      tail de ~120 l.** de règles globales (couleur de lien, `<hr>`, utilitaires)
- [x] Aucun fichier de `components/` ne dépasse 200 lignes
- [~] Changer `--aro-color-primary` suffit à re-thémer l'admin entièrement — vrai partout
      **sauf** les `.bg-teal`/`.bg-pink` MenuBundle (hex littéraux, sans token)
- [ ] Aucun attribut `style=` inline dans `Security/` et `Form/` — **1 restant**
      (`Form/base.html.twig:136`, largeur du dropdown de langue)
- [x] Poppins auto-hébergé : `grep -r 'fonts.gstatic.com\|fonts.googleapis.com'` ne remonte rien
- [x] Volume CSS total en baisse significative (8 732 → ~3 900 lignes)
- [x] Tous les écrans de l'inventaire de validation sont contrôlés (harnais `castor visual:capture`)

> **Ces critères sont en CI. ✅ Fait** — `castor qa:design-system` (`.castor/css.php`),
> câblé dans `qa:all` et dans `.github/workflows/ci.yml`.
>
> **C'est un cliquet, pas une barrière.** La plupart des compteurs ne *peuvent pas* être à
> zéro aujourd'hui : `components/` porte 301 `!important` et 232 valeurs hexadécimales
> brutes. Chaque métrique a donc un plafond égal à sa valeur actuelle ; **la CI échoue si
> un chiffre monte**, et le plafond se resserre à mesure que les composants sont refaits.
> La tâche signale d'elle-même quand un compteur est passé sous son plafond, pour inviter à
> le verrouiller.
>
> Les métriques sur `foundations/` et `vendor/` sont des **zéros stricts** : ces répertoires
> sont neufs, il n'y a rien à amnistier.
>
> État initial : `important_foundations` 0 · `hex_foundations` 0 · `google_fonts` 0 ·
> `important_components` 301 · `hex_components` 232 · `oversized_components` 6 ·
> `inline_styles` 6.
>
> Deux précisions d'implémentation : les fichiers Stisla sont exclus partout (les compter
> noierait le signal), et les commentaires sont retirés avant comptage — sans quoi cette
> documentation même ferait échouer la règle qu'elle décrit.
>
> **Un test que le grep ne remplace pas :** basculer `--aro-color-primary` en magenta,
> capturer l'inventaire, et vérifier qu'aucun teal ne survit. Ça attrape ce que le grep hex
> rate — les couleurs cachées dans `rgba()`, les dégradés, les `fill` SVG et les styles
> inline.

---

## 10. Décisions actées

| Sujet | Décision | Conséquence |
|---|---|---|
| **Densité** | Base **14px / 1.5** — confirmée | Jugée sur la DataTable une fois `_table.css` sur tokens. Le 14px coûte **5 px par page de 10 lignes** : la hauteur de ligne vient du padding et du line-height, pas de la police. Cf. §4. |
| **Périmètre** | AdminBundle seul dans un premier temps | Blog/Page/Menu continuent de fonctionner via le pont Bootstrap. Propagation dans un second chantier. |
| **Préfixe** | `aro-` (variables `--aro-*` et classes `.aro-*`) | Un préfixe unique pour les deux, cohérent avec Aropixel. |
| **Ruptures de compatibilité** | **Autorisées** — v3 est un major assumé | Pas de contorsion pour préserver l'existant. Critère de décision : le gain justifie-t-il le coût, et la rupture est-elle **détectable** ? Voir ci-dessous. |
| **Disposition des formulaires** | Horizontal uniquement | Le vertical prévu par le design system n'est pas implémenté. Décision de périmètre, réversible. Cf. §16. |
| **Mode sombre** | Hors périmètre — thème clair uniquement | Les tokens de surface et de texte sont nommés par rôle malgré tout (`--aro-color-surface` et non `--aro-color-white`), ce qui laisse la porte ouverte sans surcoût. |

> Sur le mode sombre : la décision est de ne pas l'implémenter, mais le nommage
> sémantique des tokens reste sémantique. Ça ne coûte rien aujourd'hui et évite
> d'avoir à renommer l'intégralité des surfaces si le sujet revient.

### Ruptures : autorisées, mais pas gratuites

v3 assume les ruptures nécessaires. Cela lève la contrainte, pas le raisonnement : chaque
rupture reste payée par les intégrateurs, et par nous en support et en rédaction du guide
de migration. La question n'est plus « a-t-on le droit ? » mais « le gain le justifie-t-il ? »

Un second critère, plus important, s'ajoute — **la rupture est-elle détectable ?**

| Type de rupture | Détectabilité | Verdict |
|---|---|---|
| Classe CSS renommée | Immédiate — le style est visiblement faux | Sans risque |
| Token renommé | Immédiate — même effet | Sans risque |
| Markup Twig modifié | Immédiate — la mise en page casse | Sans risque |
| **Bloc de thème de formulaire renommé** | **Aucune** — Twig ignore la surcharge en silence | **À éviter** |
| Nom de bloc PHP (`getBlockPrefix`) | Aucune — même mécanisme | À éviter |

Une rupture visible est un désagrément ponctuel : le développeur la constate et la corrige.
Une rupture silencieuse est un défaut latent, qui peut survivre des mois en production.

C'est la seule catégorie où il vaut la peine de se contraindre, même en major.

## 11. Icônes

### Décision

FontAwesome, Ionicons et flag-icon-css sont **tous les trois supprimés**, au profit de
**[symfony/ux-icons](https://symfony.com/bundles/ux-icons/current/index.html)** avec le
set **Tabler** (MIT, ~5 900 icônes, orienté interfaces denses).

```twig
{{ ux_icon('tabler:pencil', {class: 'aro-icon'}) }}
```

Motivations :

- FontAwesome évolue vers un modèle de plus en plus fermé et payant — incompatible avec
  un bundle MIT.
- Le SVG inline remplace la police d'icônes : coloration par `currentColor` (donc pilotée
  par les tokens `--aro-*`), rendu net à toute taille, pas de FOUT, pas de fichier de
  police à charger.
- Trois dépendances d'assets supprimées.
- Le set reste interchangeable : Iconify expose 200 000+ icônes derrière la même API.

### Fonctionnement vérifié

- `IconFinder` scanne **les chemins du loader Twig**, y compris les templates de bundles
  situés dans `vendor/`. `ux:icons:lock` côté application découvre donc les icônes
  utilisées par l'AdminBundle et les rapatrie dans son `assets/icons/`.
- **Aucune dépendance réseau en production** une fois le lock effectué.
- Les icônes locales ont **précédence** sur les icônes à la demande : un intégrateur
  surcharge n'importe quelle icône de l'admin en déposant son SVG au chemin correspondant
  dans `assets/icons/`. Aucune configuration nécessaire.

### À prévoir ✅ Fait (jeu **Lucide**, cf. §12)

- [x] Ajouter `symfony/ux-icons` et `symfony/http-client` au `require` du bundle
- [x] Documenter `ux:icons:lock` dans `doc/installation.md` et le recommander dans le
      process de déploiement — seule action requise de l'intégrateur
- [x] Inventorier les icônes utilisées (grep des `fa-*` / `ion-*` sur les 69 Twig) — ~70,
      zéro Ionicons en réalité (les `ion-*` estimés étaient des faux positifs)
- [x] Établir la table de correspondance ancien nom → nom Lucide
- [x] Remplacer les `<i class="fas fa-…">` par `{{ ux_icon('lucide:…') }}`
- [x] Retirer FontAwesome, Ionicons et flag-icon-css de `base.html.twig` et des assets
      (~26 Mo d'assets supprimés)

> **Sélecteur de langue.** `flag-icon-css` servait à représenter les langues par des
> drapeaux. C'est incorrect (une langue n'est pas un pays) et problématique en
> accessibilité. L'i18n étant une fonctionnalité de premier plan, prévoir un libellé
> texte ou un code locale à la place. — Le libellé est le **code locale en capitales**
> (`FR`/`EN`…) dans le sélecteur, cf. `Form/base.html.twig`.

## 12. Arbitrages restants

### Tranchés

- **Jeu d'icônes → Lucide.** Décidé en rendant Tabler et Lucide côte à côte sur les icônes
  réelles de l'admin (mécanisme ux-icons identique, `currentColor` piloté par les tokens).
  Les ~1500 icônes de Lucide couvrent largement les ~70 de l'admin ; esthétique un peu plus
  douce. Préfixe `lucide:` dans les templates.

- **`skins/reverse.css` → supprimé.** Le fichier est **orphelin** : référencé par aucun
  template, aucune classe `skin-reverse` n'est émise nulle part, et il cible la sidebar
  Stisla que le bundle a abandonnée. Ce n'était pas un arbitrage, juste du code mort.
- **Cascade layers → adoptées, mais reportées à l'Étape 5.** Bootstrap passera d'un
  `<link>` à un `@import url(...) layer(bootstrap)`. `!important` devient alors
  structurellement inutile en `components/` au lieu de reposer sur la discipline, et le
  contrat de surcharge par l'application hôte (§5) se règle au passage.

  > **Report mesuré, pas théorique.** L'activation a été tentée à l'Étape 3 puis annulée.
  > Layer Bootstrap ne fait pas gagner que *notre* CSS : ça fait gagner **tout ce qui n'est
  > pas layé**, ce qui inclut aujourd'hui les 5 118 lignes de Stisla. Effet constaté sur
  > **40 écrans sur 48** — champ de recherche, contrôles de tri et pagination déplacés,
  > parce que des règles Stisla que Bootstrap battait par spécificité l'emportaient
  > soudain.
  >
  > Ces règles vont disparaître. Activer maintenant ferait donc valider chaque composant
  > contre un arbitrage de précédence qui changerait à nouveau à l'ablation. Le fichier
  > `foundations/_bootstrap-layer.css` est écrit et documenté, mais **non lié** : il
  > s'active juste après la suppression de Stisla, où le diff sera attribuable à une seule
  > cause.

- **Indicateurs de statut → famille sémantique en feu tricolore (RAG), tranché le
  24 juillet 2026.** Les trois pastilles de publication *référencent* désormais les tokens
  sémantiques au lieu de littéraux :
  - `--aro-status-online` → `var(--aro-color-success)` (vert `#63CEB3`) — était le teal
    `#06BAB4`, qui est **exactement** `--aro-color-primary` : un marqueur de statut reprenait
    la teinte des actions primary. Le vert est la convention universelle « live ».
  - `--aro-status-scheduled` → `var(--aro-color-warning)` (orange `#F25C05`) — était `#FFC700`.
  - `--aro-status-offline` → `var(--aro-color-danger)` (rouge `#E52321`) — était le gris
    `#E7E7E7`.

  Motif : online/scheduled/offline = **success/warning/danger** (vert=live, orange=en attente,
  rouge=hors ligne), et la *référence* aux tokens fait suivre automatiquement tout re-thème des
  couleurs sémantiques. **Compromis assumé** : « hors ligne » en rouge rapproche un contenu
  simplement dépublié d'un état d'erreur — accepté au titre de la lisibilité RAG. Catalogue +
  preview re-générés (invariant CLAUDE.md).

### Restants

Aucun — tous les arbitrages du §12 sont tranchés.

---

## 13. Fondamentaux éditoriaux

Repris du design system. Absents du cahier jusqu'ici, alors qu'ils conditionnent la
cohérence perçue autant que le CSS.

> **Portée : les libellés de l'interface uniquement.** La documentation du bundle est en
> anglais (§15). Les deux règles ne se recouvrent pas.

- **Langue.** L'**anglais** devient la langue par défaut et la voix de référence — le
  bundle vise un public de développeurs internationaux (§15). Il reste traduit en
  FR/EN/DE/ES/IT/CZ. Les libellés sont désormais pensés en anglais, puis traduits.
- **Registre.** Professionnel, concis, fonctionnel. C'est un outil de back-office, pas une
  surface marketing : libellés courts (noms ou verbes), aucun adjectif promotionnel,
  aucun point d'exclamation.
- **Casse.** Casse de phrase pour les libellés et les boutons (`Ajouter un article`, pas
  `Ajouter Un Article`). Les **CAPITALES** sont réservées aux micro-libellés : badges,
  sur-titres de section, séparateurs de fil d'Ariane.
- **Adresse à l'utilisateur.** Ton neutre et factuel
  (`Are you sure you want to delete…`). En français, vouvoiement systématique
  (`Êtes-vous sûr de vouloir supprimer…`). Confirmations et erreurs sans dramatisation.
- **Pas d'emoji.** L'iconographie passe exclusivement par le jeu d'icônes (§11).
- **Vocabulaire de statut.** Modèle récurrent de tout le contenu :
  *Publié / En ligne* · *Programmé* · *Hors ligne / Brouillon*. Ce vocabulaire doit être
  identique partout, y compris dans les fichiers de traduction.

---

## 14. Catalogue de composants HTML

> **✅ Fait.** Route dev `/admin/_catalog` (`CatalogAction`), template
> `catalog/index.html.twig` qui **étend le base admin** — donc consomme le CSS réel et ne
> peut pas mentir. Tous les composants dans leurs états, avec snippets et la hiérarchie
> d'action. Capturé dans la référence visuelle (`catalog@…`), il est son propre test de
> régression. Arbitrages du bas de §14 tranchés : **route Twig dev** (pas de page statique),
> **construit en fin de migration** (rattrapage du lot C, cf. roadmap).

Livrer **dans le bundle** une documentation HTML présentant tous les composants
disponibles, dans tous leurs états.

### Objectifs

1. **Documenter** — un développeur intégrateur voit ce qui existe et le markup à écrire,
   sans avoir à lire le CSS ni à fouiller les templates.
2. **Valider** — c'est le banc de contrôle visuel du chantier (§8) : chaque composant
   dans tous ses états sur une page unique, ce que les écrans réels ne montrent jamais.
3. **Contraindre** — ce qui n'est pas au catalogue n'existe pas. C'est le garde-fou
   contre la réapparition de variantes ad hoc dans les projets.

### Contrainte non négociable

Le catalogue **doit consommer le CSS réel du bundle**, pas une copie. Une page qui
duplique les styles diverge en quelques semaines et devient un mensonge — pire que pas de
documentation du tout, parce qu'on lui fait confiance.

De même, le markup affiché doit être **le markup réellement produit** par les macros et
form themes du bundle, pas une réécriture à la main.

### Contenu attendu

- Les **fondations** : palette avec valeurs et contrastes, échelle typographique,
  espacements, rayons, élévations.
- Chaque **composant** : rendu, tous ses états (hover, focus, disabled, erreur, actif),
  ses variantes, et le snippet Twig correspondant à copier.
- Les **règles d'usage** qui ne se déduisent pas du rendu — au premier chef la hiérarchie
  d'action à trois niveaux (corail / teal / ardoise, §4), qu'un catalogue purement visuel
  ne transmet pas.
- Les **fondamentaux éditoriaux** (§13).

### À trancher

1. **Génération.** Page statique versionnée, ou route Twig servie par le bundle en
   environnement `dev` uniquement ? La route garantit que le rendu ne peut pas diverger
   du code, mais n'est pas consultable hors installation. Une piste : route en dev, et
   export statique pour publication.
2. **Publication.** Consultable uniquement en local, ou publiée (GitHub Pages) comme
   vitrine du projet open source ? Le second a une valeur d'adoption réelle mais impose
   de tenir la publication à jour.
3. **Moment.** Construit en parallèle de la migration (chaque composant migré alimente le
   catalogue, l'effort est étalé et le contrôle est immédiat), ou en fin de chantier
   (moins de va-et-vient, mais on perd le bénéfice de validation pendant toute la durée
   des travaux) ?

> Recommandation sur le point 3 : **en parallèle**. Le catalogue sert précisément à
> valider pendant le chantier ; le construire après, c'est renoncer à son intérêt
> principal et se condamner à une passe de rattrapage.

---

## 15. Impact sur la documentation existante

Le bundle embarque déjà une documentation dans `doc/` — 16 fichiers, ~3 900 lignes,
indexée par `doc/index.md`. **Elle doit être tenue à jour au fil du chantier, pas à la
fin.** Une documentation qui décrit des classes disparues est plus nuisible qu'absente :
elle envoie les intégrateurs dans le mur avec l'autorité de l'officiel.

### Règle

Un composant migré n'est terminé que quand la documentation qui le mentionne est à jour.
Cela fait partie de la définition de « fini », au même titre que le CSS et les Twig.

### Fichiers impactés

Relevé par recherche des références aux icônes et aux classes du bundle :

| Fichier | Réf. icônes | Réf. classes | Impact |
|---|---|---|---|
| `css_customization.md` | 1 | 30 | **Réécriture quasi complète** |
| `form_templates.md` | 4 | 9 | Fort |
| `forms.md` | 8 | 3 | Fort |
| `admin_menu.md` | 8 | — | Icônes uniquement |
| `installation.md` | — | — | Ajout ux-icons (§11) |

**`css_customization.md` est le principal chantier documentaire.** Il catalogue
aujourd'hui l'échelle `.ts-xs` (8px) → `.ts-5` (26px), les graisses `.tw-300` → `.tw-700`
et les variantes de carte — c'est-à-dire exactement ce que la refonte remplace. Deux
options : le réécrire, ou le remplacer par un renvoi vers le catalogue de composants
(§14), qui a vocation à devenir la source unique.

**`installation.md`** doit documenter `ux:icons:lock` et le recommander dans le process de
déploiement — c'est la seule action requise de l'intégrateur (§11).

### À prévoir également

- [x] Documenter les tokens `--aro-*` surchargeables — **`doc/theming.md` créé** (API
      publique de thème, §5)
- [ ] Documenter le mécanisme de surcharge d'icône (dépôt d'un SVG dans `assets/icons/`)
- [ ] Guide de migration ancien major → v3 pour les projets existants : tokens renommés, classes
      disparues, surcharges à reprendre — **non écrit** (différé après le lot D)
- [~] Référencer `design-system.md` dans `doc/index.md` — **sans objet** : c'est un document
      de travail interne en français, hors de l'index public anglais

> **Encore à migrer :** `forms.md`, `form_templates.md` et `admin_menu.md` enseignent encore
> FontAwesome (`<i class="fas fa-…">`, `'icon' => 'fas fa-…'`) — à passer sur
> `{{ ux_icon('lucide:…') }}`. `css_customization.md` (réécrit, renvoie au catalogue) et
> `theming.md` (nouveau) sont faits.

> **Défaut relevé au passage. ✅ Corrigé.** `doc/index.md` pointait vers `adminbundle.md`,
> `blogbundle.md`, `pagebundle.md` et `menubundle.md` — quatre fichiers inexistants. Liens
> morts supprimés, remplacés par une section « Design & Theming ».

### Langue — décision

**Toute la documentation est en anglais.** C'est un produit open source destiné à un
public de développeurs internationaux ; la documentation est sa première surface de
contact.

Portée de la règle :

| Surface | Langue |
|---|---|
| `doc/*.md` | **Anglais** |
| Catalogue de composants (§14) | **Anglais** — c'est de la documentation |
| Commentaires d'en-tête des fichiers CSS (§5) | **Anglais** |
| Guide de migration ancien major → v3 | **Anglais** |
| Ce cahier de specs | Français — document de travail interne, non publié |
| Libellés de l'interface (§13) | **Anglais** par défaut, traduits |

### Bascule de l'interface en anglais ✅ Fait

**Décision : l'anglais devient la locale par défaut.** Le français reste une traduction
parmi les six. Cohérent avec la cible développeurs internationaux.

> **Réalisé (lot D).** Le défaut framework était déjà `en`, le bundle ne force aucune
> locale. `text.error` (absent des 6 locales) ajouté partout ; clé pivot
> `text_choose_image` → `text.choose_image` ; chaînes FR en dur de la modale de publication
> et du widget d'attachement externalisées ; 6 fichiers alignés à **202 clés identiques**.
> Vérifié au rendu sur `/admin/blog/post/1/edit` : 100 % anglais, zéro clé brute.
> `doc/i18n.md` corrigé (annonçait 2 locales au lieu de 6). Les chiffres de l'« état relevé »
> ci-dessous sont ceux du **diagnostic initial**, conservés comme trace.

État relevé — l'ampleur est modeste :

- **Les six locales existent et sont complètes** (`fr`, `en`, `de`, `es`, `it`, `cs`),
  ~778 lignes chacune. L'anglais n'est pas à écrire, il est déjà là.
- **`fr.xlf` compte 12 lignes de plus que les autres.** Quelques clés n'existent qu'en
  français. À combler avant la bascule : une clé absente de `en.xlf` s'affichera comme
  clé brute dans l'interface.
- **4 chaînes françaises en dur, jamais traduites** — elles apparaîtraient telles quelles
  dans une interface anglaise :

  | Fichier | Ligne | Chaîne |
  |---|---|---|
  | `Form/base.html.twig` | 335 | `<button>Enregistrer</button>` |
  | `Form/layout.html.twig` | 367 | `Aperçu de votre vidéo` |
  | `Form/layout.html.twig` | 727 | `placeholder="Entrer une date"` |
  | `Form/layout.html.twig` | 750 | `placeholder="Entrer une heure…"` |

- **2 littéraux français employés comme clés de traduction** — `'Ajouter un fichier'|trans`
  et `'Nouveau'|trans`, plus quelques valeurs de repli (`'Fichiers'`,
  `'Ajouter des fichiers'`). Ils fonctionnent, mais imposent le français comme langue
  pivot. À normaliser vers des clés structurées, sur le modèle de celles déjà en place
  (`text.delete`, `text.close`, `files.widget.no_selected_file`).

> **Bonne nouvelle.** Ces défauts sont tous dans le thème de formulaire — déjà au
> programme du §16. La bascule s'y greffe sans ouvrir de chantier supplémentaire.

**Ordre des opérations :** compléter `en.xlf` → externaliser les 4 chaînes en dur →
normaliser les 2 clés françaises → basculer la locale par défaut. Basculer avant aurait
pour effet de mélanger français et anglais dans la même interface.

---

## 16. Thème de formulaire (`Form/layout/`)

**867 lignes, 43 blocs** — le plus gros fichier du bundle, plus lourd que n'importe quel
fichier CSS. Refondre le CSS des formulaires sans traiter ce fichier n'a aucun sens :
c'est lui qui produit le markup que le CSS habille.

C'est aussi la future **API publique** : les intégrateurs surchargeront ces blocs par nom.
v3 n'étant pas encore diffusée, c'est la **dernière occasion de fixer ces noms librement** —
après, les renommer devient une rupture silencieuse (§10).

### Dette relevée

Le fichier contient du markup mort, indépendamment de ce chantier :

| Ligne | Problème |
|---|---|
| 500 | `panel panel-default border-left-xlg border-left-info` — markup **Bootstrap 3**, inerte depuis longtemps |
| 811, 816 | `input-group-prepend` / `input-group-append` — markup **Bootstrap 4**, supprimé en BS5. Le widget `money` est probablement déjà cassé visuellement |
| 767 | `has-error` — **Bootstrap 3**. BS5 utilise `is-invalid`, déjà employé ailleurs dans le même fichier |
| 367 | `control-label` — **Bootstrap 3** |

S'ajoutent :

- **13 icônes en dur**, dont `icon-files-empty` et `icon-files-empty2` (l. 502, 588) qui
  n'appartiennent ni à FontAwesome ni à Ionicons — vestiges d'une police d'icônes encore
  antérieure. Ces deux-là ne s'affichent probablement plus.
- **3 styles inline** (`width: 100%`, `height: 36px`), en contradiction directe avec la
  règle §7-2.
- Des utilitaires d'espacement Stisla (`m-b-30`, `m-r-20`, `m-r-10`) qui disparaîtront
  avec `style.css`.

> Ces défauts sont **antérieurs au chantier** et méritent d'être corrigés indépendamment.
> Ils indiquent surtout que ce fichier n'a jamais été revu dans son ensemble depuis la
> migration Bootstrap 3 → 4 → 5.

### Nettoyage : méthode

Le fichier est ancien, avec du code repris et vraisemblablement des parties inutilisées —
mais **il fonctionne**. C'est précisément ce qui rend le nettoyage risqué : dans un thème
de formulaire, « inutilisé dans ce dépôt » ne veut pas dire « inutilisé ». Les projets
clients surchargent ces blocs, et on ne peut pas les inspecter.

Trois catégories, à traiter avec des niveaux de prudence différents :

**a) Markup mort — suppression sûre.** Les classes Bootstrap 3/4 du tableau ci-dessus
n'ont aucun effet. Les retirer ne peut rien casser, par définition.

**b) Blocs surchargeant Symfony — rayon d'action maximal.** `form_row`, `form_label`,
`form_errors`, `widget_attributes`, `collection_widget`, `money_widget`, `date_widget`,
`time_widget`, `textarea_widget`, `choice_widget_*`. Ces blocs s'appliquent à **tous** les
formulaires, y compris ceux des projets clients. À réviser, jamais à supprimer sans
analyse.

> **Le piège à connaître.** `collection_widget` (l. 163) et la macro `collection_item`
> (l. 305) ressemblent à une implémentation héritée, supplantée par
> `aropixel_admin_collection_widget` et son offcanvas. **C'est faux.** `collection_widget`
> est le bloc standard de Symfony pour `CollectionType` : il gère toute collection Symfony
> classique employée par un intégrateur. Le supprimer casserait silencieusement ces
> formulaires-là.
>
> C'est le modèle du raisonnement à tenir sur chaque bloc avant de toucher à quoi que ce
> soit.

**c) Blocs de types maison** — les `aropixel_admin_*`. Le bundle enregistre **24
`getBlockPrefix`**. Un bloc sans préfixe correspondant est un candidat sérieux ; un
préfixe sans bloc signale un type qui s'affiche avec le rendu Symfony par défaut, ce qui
peut être voulu ou être un oubli.

### Première étape ✅ Faite

La **table de correspondance blocs ↔ types enregistrés** est établie :
[`doc/form-theme-blocks.md`](form-theme-blocks.md). 43 blocs croisés avec les 24
`getBlockPrefix` des quatre bundles.

**Le résultat contredit l'hypothèse de départ.**

- **Aucun bloc orphelin.** Chaque bloc `aropixel_*` est adossé à un type enregistré. Il n'y
  a rien à supprimer : la catégorie (c) est vide.
- **7 types sans bloc dédié, tous intentionnels.** `date`, `datetime` et `time` héritent
  des types Symfony dont le thème surcharge déjà les blocs génériques ; `entity_hidden`
  étend `HiddenType` ; `slideshow` étend `CollectionType` ; `library_file` et
  `library_image` sont des types composés dont les enfants sont stylés individuellement.

Autrement dit : **la dette de ce fichier est dans le markup à l'intérieur des blocs, pas
dans les blocs eux-mêmes.** Le nettoyage se réduit donc à la catégorie (a) — les fossiles
Bootstrap 3/4 — et à la révision des 18 surcharges Symfony, qu'il ne s'agit pas de
supprimer mais de réécrire.

Cela réduit sensiblement le risque du §16 : il n'y a pas de champ de mines de blocs morts à
désamorcer, seulement du markup à moderniser.

### Règle de suppression

**Suppression franche.** v3 n'étant pas diffusée (§4), aucun projet n'est installé sur ces
blocs : il n'y a personne à prévenir, donc pas de couche de dépréciation à traîner.

Une version antérieure de ce document prévoyait de conserver les blocs marqués
`@deprecated` pendant un cycle de version. C'est sans objet.

> **La table de correspondance reste indispensable** malgré tout. Elle ne sert plus à
> protéger les intégrateurs, mais à ne pas supprimer un bloc qui rend un formulaire *dans
> ce dépôt* — le piège `collection_widget` décrit plus haut reste entier.

### Contrat de mise en page

Le thème impose un layout **horizontal** (`{% use "bootstrap_5_horizontal_layout" %}` +
`form-horizontal` par défaut), avec un contrat maison `.form-group` / `.col-form-label` /
`.col-form-content`.

Le design system, lui, prévoit un `FormField` **vertical ou horizontal**.

**Décision : horizontal uniquement.** Le chantier est déjà vaste ; ajouter une seconde
disposition doublerait la surface à écrire, à documenter et à valider sur le composant le
plus lourd du bundle, pour un besoin qui ne s'est pas manifesté.

Le vertical reste possible plus tard. Seule consigne pour ne pas se le fermer : ne pas
coder en dur la structure à deux colonnes dans les blocs qui n'en ont pas besoin —
notamment les widgets métier (galerie, fichiers, image, éditeur), qui occupent déjà toute
la largeur. Aucune abstraction à construire aujourd'hui, juste ne pas ajouter de
dépendance gratuite au layout horizontal.

### À trancher — statut

> **Points 1, 2 et 3 tous actés. ✅ Fait** (22 juillet 2026). Le thème est réduit à 28 l.
> d'assemblage (`Form/layout/theme.html.twig`), découpé en dix sous-thèmes rangés dans
> `Form/layout/`, et `aropixel_editor_widget` est
> normalisé en `aropixel_admin_editor_widget`. Prouvé inerte : **54/54 captures identiques
> au bit près**. Les trois invariants Twig qui encadrent tout re-découpage sont énoncés au
> **§0, « Invariants Twig du thème de formulaire »**.

1. **Conserver `{% use "bootstrap_5_horizontal_layout.html.twig" %}` ?** ✅ **Oui, conservé.**
   S'en affranchir signifie reprendre à son compte toute la
   complexité du thème de formulaire Symfony (attributs, accessibilité, cas limites) et la
   maintenir à chaque version. C'est la même logique que le pont Bootstrap du §3 : on
   hérite et on ajuste, on ne réécrit pas.

2. **Découper le fichier.** ✅ **Fait.** `Form/layout/theme.html.twig` est réduit à l'assemblage
   (`{% use %}` uniquement), les 43 blocs répartis en dix sous-thèmes par domaine
   (`_core`, `_collection`, `_controls`, `_date`, `_editor`, `_file`, `_gallery`, `_image`,
   `_select2`, `_translatable`), plus `_collection_macros` pour la macro. L'ordre des `use`
   déterminant la résolution, `_core` — qui porte la mise en page Bootstrap et donc tous les
   blocs à `parent()` — est importé en premier. Trois invariants Twig découverts et
   documentés en tête des fichiers ; cf. §0, « Invariants Twig du thème de formulaire ».

3. **Noms de blocs — renommage libre.** ✅ **Fait.** `aropixel_editor_widget` →
   `aropixel_admin_editor_widget` (le bloc dans `_editor.html.twig` et
   `EditorType::getBlockPrefix()`, désormais `aropixel_admin_editor`). Le nommage est
   uniforme : tous les blocs maison portent le préfixe `aropixel_admin_*`.

   > Le raisonnement du §10 sur les ruptures indétectables **reste valable pour l'avenir** :
   > une fois v3 diffusée, renommer un bloc de thème redevient une rupture invisible. La
   > fenêtre était ouverte, elle est maintenant utilisée et refermée.

### Séquencement

Le thème de formulaire doit être migré **en même temps que le composant Formulaire (P0)**,
pas après. CSS et markup forment un tout : les traiter séparément revient à valider deux
fois la même chose, et à découvrir tard des incompatibilités.

---

## 17. Liste de reprise d'apparence

Ouverte à l'Étape 5. La règle de l'ablation était **« on corrige la mise en page, on
consigne l'apparence »** : tout ce qui était mal placé a été corrigé sur-le-champ, tout ce
qui n'était que laid est listé ici plutôt que rafistolé, puisque la refonte des composants
va le réécrire.

Sans cette liste, les semaines ingrates n'ont aucune preuve de progression et l'intuition
« ça régresse » finit par l'emporter sur les faits.

### À reprendre

| # | Constat | Composant qui le traitera |
|---|---|---|
| ~~1~~ | ~~Cartes sans ombre ni rayon~~ | ✅ clos — incrément 8 |
| ~~2~~ | ~~Pied de page empilé sur deux lignes~~ | ✅ clos — incrément 11 |
| ~~3~~ | ~~Séparateur de fil d'Ariane en `/`~~ | ✅ clos — incrément 9 |
| ~~4~~ | ~~Champs de formulaire au fond gris~~ | ✅ clos — incrément 12 |
| ~~5~~ | ~~En-têtes de carte sans fond distinct ni séparateur~~ | ✅ clos — incrément 8 |
| ~~6~~ | ~~Pastille d'avatar réduite à un cercle gris uni~~ | ✅ clos — incrément A7 |
| ~~7~~ | ~~Boutons d'action des listes délavés~~ | ✅ clos — incrément 10 |

> Cette liste est **initiale**, pas exhaustive. Elle se complète à mesure que chaque écran
> est relu, et se vide à mesure que les composants sont refaits. Un composant n'est terminé
> que lorsque les lignes qui le désignent ont disparu.

### Ce qui n'est PAS à reprendre

La densité générale a diminué — la plupart des écrans ont perdu 30 à 80 pixels de hauteur,
les écrans de menu près de 560. C'est l'effet attendu du retrait des marges de Stisla, et
c'est **conforme à la direction** : le design system vise une densité maîtrisée. À
réévaluer au moment de l'arbitrage 14px/13px (§4), pas avant.
