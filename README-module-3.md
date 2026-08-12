# Module 3 — Dashboard créatif (mode code)

Remplace les champs texte bruts du Module 2 par un vrai éditeur Monaco (3 onglets HTML/CSS/JS) avec aperçu live sandboxé, dans le même écran d'édition.

## Choix techniques

- **Monaco chargé depuis un CDN** (`jsdelivr`, loader AMD), pas via npm/Vite — pour rester cohérent avec votre préférence de ne pas dépendre d'un `npm run dev` séparé. `php artisan serve` suffit toujours.
- **Aperçu isolé** : `<iframe sandbox="allow-scripts">` — volontairement **sans** `allow-same-origin`, pour que le JS collé s'exécute dans une origine opaque, sans accès aux cookies/stockage de l'application. Une CSP stricte est injectée dans le document de l'aperçu (`default-src 'none'`) : même un script malveillant collé par erreur ne peut pas déclencher de requête réseau sortante depuis l'aperçu.
- **Mise à jour différée** (~400ms après la dernière frappe) pour ne pas re-render l'aperçu à chaque caractère.

## Étapes

1. **Copier les fichiers de ce module** — les trois derniers **remplacent** les fichiers du Module 2 du même nom :
   - `app/Filament/Forms/Components/CodeEditorField.php` *(nouveau)*
   - `resources/views/filament/forms/components/code-editor-field.blade.php` *(nouveau)*
   - `app/Filament/Resources/ComponentDefinitionResource.php` *(remplace la version du Module 2)*
   - `app/Filament/Resources/ComponentDefinitionResource/Pages/CreateComponentDefinition.php` *(remplace la version du Module 2)*
   - `app/Filament/Resources/ComponentDefinitionResource/Pages/EditComponentDefinition.php` *(remplace la version du Module 2)*

2. **Rien à migrer** — ce module ne touche pas la base de données, uniquement l'interface.

3. **Tester** — ouvrez un composant existant (créé au Module 2) : les trois onglets HTML/CSS/JS doivent afficher Monaco avec le code déjà en place, et l'aperçu à droite doit se mettre à jour pendant que vous tapez. Sauvegardez : une nouvelle version doit apparaître dans l'historique, comme au Module 2.

## Point d'attention en testant

C'est le module le plus dépendant du JS (Alpine + Livewire + Monaco chargé en externe) — le plus susceptible d'avoir besoin d'un ajustement une fois en conditions réelles. Si l'éditeur ne s'affiche pas ou que l'aperçu ne se met pas à jour, ouvrez la console du navigateur et envoyez-moi le message d'erreur exact (comme d'habitude) — je corrige précisément plutôt que de deviner.

## Ce que fait ce module

- Édition du code en Monaco (coloration syntaxique HTML/CSS/JS)
- Aperçu instantané, isolé du reste de l'application
- Compatible avec le système de versioning existant (aucun changement de modèle de données)

## Ce que ce module ne fait pas encore

- Pas d'interface WYSIWYG / GrapesJS (Module 4)
- Pas de consommation des composants dans une page réelle (Module 5)

## Prochaine étape

Une fois testé et validé : **Module 4 — Dashboard créatif (mode WYSIWYG)**, intégration GrapesJS.
