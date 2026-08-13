# Module 4 — Dashboard créatif (mode WYSIWYG)

Ajoute un mode d'assemblage visuel (GrapesJS) pour les admins sans la permission `components.write_code`. La bascule entre Monaco et GrapesJS est automatique, selon la permission de l'utilisateur connecté.

## Décisions d'architecture prises pour ce module

**Références vivantes.** Comme demandé, un bloc "composant" déposé dans le canevas n'est pas une copie figée : il devient `<div data-atomic-ref="ID"></div>`, résolu à l'affichage — jamais au moment de la sauvegarde. Concrètement : si vous modifiez et republiez l'atome "Button" demain, toutes les compositions qui le référencent (organismes, sections, pages) afficheront automatiquement la nouvelle version à leur prochain rendu, sans rien retoucher ailleurs. C'est la logique DRY que vous avez demandée, avec deux garde-fous : une référence vers un composant non publié (ou supprimé) est simplement ignorée (remplacée par un commentaire HTML explicite), et les références circulaires sont détectées et coupées (profondeur maximale de 8 niveaux).

**Résolution côté serveur, pas côté navigateur.** Résoudre une référence suppose d'aller chercher en base la version publiée actuelle d'un autre composant — impossible à faire uniquement en JS. J'ai ajouté `ComponentResolver` (service PHP, basé sur `DOMDocument` plutôt que sur des regex — plus robuste face à du HTML imbriqué) et une méthode `resolvePreviewHtml()` appelée depuis le JS via `$wire.call(...)`.

**Cohérence avec le Module 3.** Comme cette mécanique de référence existe désormais, l'aperçu du mode code (Monaco) a été mis à jour pour passer par la même résolution — un développeur qui taperait `data-atomic-ref="12"` à la main dans son HTML aura un aperçu exact, identique à ce que verrait un utilisateur du mode WYSIWYG.

**Palette mixte.** Comme demandé : vos composants publiés (préfixés 🧩, groupés par niveau atomique) et des blocs génériques de base (texte, image, lien, bloc div) cohabitent dans le même panneau de blocs.

## Étapes

1. **Copier les fichiers de ce module** :
   - `app/Services/ComponentResolver.php` *(nouveau)*
   - `app/Filament/Resources/ComponentDefinitionResource/Concerns/ResolvesPreview.php` *(nouveau)*
   - `app/Filament/Forms/Components/WysiwygEditorField.php` *(nouveau)*
   - `resources/views/filament/forms/components/wysiwyg-editor-field.blade.php` *(nouveau)*
   - `app/Filament/Resources/ComponentDefinitionResource.php` *(remplace la version du Module 3)*
   - `app/Filament/Resources/ComponentDefinitionResource/Pages/CreateComponentDefinition.php` *(remplace la version du Module 3)*
   - `app/Filament/Resources/ComponentDefinitionResource/Pages/EditComponentDefinition.php` *(remplace la version du Module 3)*
   - `resources/views/filament/forms/components/code-editor-field.blade.php` *(remplace la version du Module 3 — mise à jour de cohérence de l'aperçu)*

2. **Rien à migrer** — aucun changement de schéma.

3. **Tester le mode WYSIWYG** : connectez-vous avec un compte qui n'a **pas** la permission `components.write_code` mais qui a `components.assemble` (créez-en un via l'écran Administrateurs si besoin). Sur la fiche d'un composant, la section Code doit afficher GrapesJS (panneau de blocs à gauche, canevas à droite) au lieu de Monaco.
   - Glissez un bloc générique (ex. "Bloc (div)") : ça doit apparaître dans le canevas.
   - Glissez un bloc 🧩 (un de vos composants publiés) : un encadré en pointillés avec son nom doit apparaître.
   - Attendez ~1s : l'aperçu résolu en bas doit afficher le **contenu réel** du composant référencé, pas le placeholder en pointillés.
   - Sauvegardez, puis republiez le composant référencé avec un changement visible (ex. couleur de fond) : revenez sur la première fiche, l'aperçu doit refléter le changement sans que vous ayez touché à rien ici.

4. **Revérifier le mode code (Monaco)** avec votre compte Super Admin : toujours fonctionnel comme au Module 3, aperçu désormais résolu côté serveur.

## Ce que fait ce module

- Assemblage visuel par glisser-déposer, avec blocs personnalisés + génériques
- Références vivantes entre composants, résolues à l'affichage
- Aperçu cohérent entre les deux modes (code et WYSIWYG)
- Bascule automatique selon les permissions, sans configuration supplémentaire

## Ce que ce module ne fait pas encore

- Le rendu **public** final (Module 9) devra utiliser le même `ComponentResolver` pour que les pages publiées bénéficient aussi des références vivantes
- Pas de détection visuelle du niveau atomique attendu (rien n'empêche aujourd'hui de glisser un organisme entier dans un atome — à encadrer plus tard si besoin)
- Le champ JS n'est pas éditable en mode WYSIWYG (cohérent avec l'esprit "assemblage visuel", mais à confirmer si un besoin apparaît)

## Prochaine étape

Une fois testé et validé : **Module 5 — Back-office contenu & layout**, avec les questions de clarification habituelles.
