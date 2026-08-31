# Module 5 — Back-office contenu & layout

Assemble enfin de vraies pages, à partir des Sections et Templates publiés dans la bibliothèque.

## Décisions d'architecture prises pour ce module

**Personnalisation par placeholders.** Comme demandé : un composant peut contenir des marqueurs `{{champ}}` dans son HTML (ex. `<h1>{{titre}}</h1>`). Quand ce composant est utilisé dans un bloc de page, le formulaire détecte automatiquement ces marqueurs et génère un champ de saisie par marqueur trouvé — l'admin n'a jamais besoin de connaître ou taper la syntaxe `{{...}}` lui-même. Les valeurs sont stockées dans `page_blocks.contenu_json` et substituées au moment du rendu, après résolution des références vivantes (Module 4). Limite actuelle assumée : un composant référencé *à l'intérieur* d'une section (via 🧩) n'a pas accès au contenu de cette page — ses propres placeholders, s'il en a, resteraient tels quels. Cette limite pourra être levée plus tard si besoin.

**Sections et Templates uniquement sur une page**, comme demandé — le sélecteur de bloc ne propose que ces deux niveaux, en respectant la hiérarchie de l'Atomic Design.

**Nouvelle permission `pages.manage`**, cohérente avec le modèle posé au Module 1 : séparée de `components.write_code`/`components.assemble`, pour pouvoir donner à quelqu'un (ex. un profil marketing) le droit de construire des pages avec des sections déjà publiées, sans lui donner accès à la bibliothèque de composants elle-même.

**Glisser-déposer natif Filament.** Contrairement aux Modules 3-4, pas de JS externe ici : le réordonnancement des blocs utilise le `Repeater` de Filament (`->reorderable()`), qui gère ça nativement. Le seul JS custom de ce module est l'aperçu (même mécanisme éprouvé qu'avant : bouton + iframe sandboxée + `$wire.call`), donc un module structurellement plus simple et moins à risque que les précédents.

## Étapes

1. **Copier les fichiers de ce module** :
   - `database/migrations/2026_08_12_000001_create_pages_table.php`
   - `database/migrations/2026_08_12_000002_create_page_blocks_table.php`
   - `app/Models/Page.php`
   - `app/Models/PageBlock.php`
   - `app/Services/PageRenderer.php`
   - `app/Policies/PagePolicy.php`
   - `database/seeders/PermissionSeeder.php` *(remplace la version du Module 1 — ajoute `pages.manage`)*
   - `app/Filament/Resources/PageResource.php`
   - `app/Filament/Resources/PageResource/Pages/ListPages.php`
   - `app/Filament/Resources/PageResource/Pages/CreatePage.php`
   - `app/Filament/Resources/PageResource/Pages/EditPage.php`
   - `resources/views/filament/forms/components/page-preview.blade.php`

2. **Migrer et re-peupler les permissions**
   ```bash
   php artisan migrate
   php artisan db:seed --class=PermissionSeeder
   ```

3. **Vous donner (ou donner à un compte de test) la permission `pages.manage`** via l'écran Administrateurs — un Super Admin l'a déjà via le bypass.

4. **Tester** :
   - Créez un composant de type "Section" avec un placeholder, ex. HTML `<section><h1>{{titre}}</h1><p>{{texte}}</p></section>`, publiez-le.
   - Créez une page, ajoutez ce bloc : les champs "Titre" et "Texte" doivent apparaître automatiquement sous le bloc.
   - Remplissez-les, vérifiez que l'aperçu en bas affiche le texte saisi à la place des `{{...}}`.
   - Ajoutez un deuxième bloc, testez le glisser-déposer pour les réordonner, décochez "Actif" sur l'un des deux et vérifiez qu'il disparaît de l'aperçu.
   - Republiez un composant déjà utilisé sur cette page avec un changement visuel : vérifiez que l'aperçu de la page le reflète (référence vivante, comme au Module 4).

## Ce que fait ce module

- CRUD des pages (titre, slug auto-généré, statut, site)
- Constructeur de page par blocs, réordonnables par glisser-déposer, activables/désactivables
- Contenu personnalisable par page via placeholders détectés automatiquement
- Aperçu de la page assemblée, résolu (références vivantes + placeholders)
- Permission dédiée `pages.manage`, indépendante de la bibliothèque de composants

## Ce que ce module ne fait pas encore

- Pas de rendu **public** réel (Module 9) — pour l'instant, seul l'aperçu admin existe
- Pas de SEO (Module 6) : la page n'a ni meta title, ni meta description pour l'instant
- Les placeholders ne descendent pas dans les composants référencés en interne (limite notée plus haut)

## Prochaine étape

Une fois testé et validé : **Module 6 — SEO semi-automatisé**, avec les questions de clarification habituelles.
