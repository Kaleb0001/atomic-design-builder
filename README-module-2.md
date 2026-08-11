# Module 2 — Bibliothèque de composants

## Décisions d'architecture prises pour ce module

**Niveau « page » renommé en « template ».** Vous m'avez laissé trancher ce point. J'ai renommé le niveau en `template` plutôt que `page`, pour une raison précise : le Module 5 aura sa propre table `pages` (site réel, slug, SEO, statut publié) qui, elle, représente une page **publiée avec du contenu réel**. Si les deux s'appelaient « page », on aurait deux choses différentes portant le même nom — source de confusion garantie en cours de projet. Un `template` est donc un patron de mise en page réutilisable (assemblage de sections, sans contenu figé) ; une `page` (Module 5) sera une instance concrète, avec ou sans template de départ. C'est exactement la distinction Templates/Pages du Atomic Design original de Brad Frost — votre brief avait fusionné les deux, ça les sépare proprement.

**Variantes indépendamment versionnées.** Comme demandé, une variante (ex. bouton "secondary") est un composant à part entière, avec son propre historique de versions — pas une simple option de configuration. Techniquement, elle vit dans la même table `component_definitions`, avec un champ `variant_of_id` qui pointe vers le composant de base. Ça évite de dupliquer toute la mécanique de versioning dans une table séparée.

**Bibliothèque globale, non cloisonnée par site.** Point que je n'avais pas soumis en question mais qui découle directement de votre réponse "usage interne pour accélérer mes futurs projets" : les composants ne sont **pas** rattachés à un `site_id`. Un bouton ou une navbar créés ici seront réutilisables sur n'importe quel futur site généré avec l'outil — c'est tout l'intérêt d'une bibliothèque de design system. Seules les pages réelles (Module 5) seront scopées par site. Si un jour vous voulez isoler certains composants par client, c'est une migration additive, pas une réécriture.

## Étapes

1. **Copier les fichiers de ce module** dans votre projet :
   - `database/migrations/2026_08_10_000002_create_component_definitions_table.php`
   - `database/migrations/2026_08_10_000003_create_component_versions_table.php`
   - `app/Models/ComponentDefinition.php`
   - `app/Models/ComponentVersion.php`
   - `app/Policies/ComponentDefinitionPolicy.php`
   - `app/Filament/Resources/ComponentDefinitionResource.php`
   - `app/Filament/Resources/ComponentDefinitionResource/Pages/ListComponentDefinitions.php`
   - `app/Filament/Resources/ComponentDefinitionResource/Pages/CreateComponentDefinition.php`
   - `app/Filament/Resources/ComponentDefinitionResource/Pages/EditComponentDefinition.php`
   - `app/Filament/Resources/ComponentDefinitionResource/RelationManagers/VersionsRelationManager.php`

2. **Migrer**
   ```bash
   php artisan migrate
   ```

3. **Tester** — reconnectez-vous sur `/admin` (avec un compte ayant la permission `components.write_code`, ou votre compte Super Admin) : un menu **Bibliothèque de composants** doit apparaître.
   - Créez un composant de base (ex. type "Atom", nom "Button"), collez un peu de HTML/CSS dans les champs, sauvegardez.
   - Ouvrez sa fiche : un onglet "Historique des versions" doit lister la version initiale.
   - Modifiez le code et sauvegardez à nouveau : une deuxième version doit apparaître dans l'historique, avec un bouton "Restaurer" sur les versions non actives.
   - Créez un second composant avec "Variante de" = Button, "Nom de la variante" = secondary : le niveau doit se verrouiller automatiquement sur "Atom" (hérité du parent).

## Ce que fait ce module

- Modèle de données complet : définitions, versions immuables, variantes
- CRUD fonctionnel via Filament (champs texte bruts pour le code)
- Historique consultable et restaurable, sans jamais perdre de version
- Accès protégé par la permission `components.write_code` posée au Module 1

## Ce que ce module ne fait pas encore

- Pas d'éditeur Monaco ni d'aperçu live sandboxé (Module 3)
- Pas d'interface WYSIWYG / GrapesJS (Module 4)
- Les composants créés ici ne sont pas encore consommables dans une page réelle (Module 5)

## Prochaine étape

Une fois testé et validé : **Module 3 — Dashboard créatif (mode code)**, avec Monaco Editor et l'aperçu sandboxé.
