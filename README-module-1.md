# Module 1 — Authentification & permissions

Rôle Super Admin (accès total) + permissions assignables librement à n'importe quel autre admin. Pas de scoping par site pour l'instant (un seul site). Création de comptes 100% manuelle via Filament.

## Étapes

1. **Publier et migrer la configuration spatie/laravel-permission**
   ```bash
   php artisan vendor:publish --provider="Spatie\Permission\PermissionServiceProvider"
   php artisan migrate
   ```

2. **Copier les fichiers de ce module** dans votre projet (ils remplacent les fichiers par défaut du même nom) :
   - `app/Models/User.php`
   - `app/Providers/AppServiceProvider.php`
   - `app/Policies/UserPolicy.php`
   - `app/Console/Commands/MakeSuperAdmin.php`
   - `app/Filament/Resources/UserResource.php`
   - `app/Filament/Resources/UserResource/Pages/ListUsers.php`
   - `app/Filament/Resources/UserResource/Pages/CreateUser.php`
   - `app/Filament/Resources/UserResource/Pages/EditUser.php`
   - `database/seeders/PermissionSeeder.php`

3. **Enregistrer le seeder** — dans `database/seeders/DatabaseSeeder.php`, ajouter dans `run()` :
   ```php
   $this->call(PermissionSeeder::class);
   ```

4. **Peupler la base**
   ```bash
   php artisan db:seed --class=PermissionSeeder
   ```

5. **Vous promouvoir Super Admin** (utilisez l'email du compte créé au Module 0 avec `make:filament-user`)
   ```bash
   php artisan app:make-super-admin votre@email.com
   ```

6. **Vérifier** — reconnectez-vous sur `/admin` : un menu **Administrateurs** doit apparaître, permettant de créer d'autres comptes, de leur donner le statut Super Admin ou de cocher des permissions individuelles (`components.write_code`, `components.assemble` pour l'instant — la liste s'enrichira aux modules suivants sans qu'il faille retoucher ce fichier).

## Ce que fait ce module

- Rôle `super_admin` : accès total, sans avoir à cocher de permissions une à une (bypass via `Gate::before`)
- Permissions nommées, assignables individuellement à n'importe quel compte (pas de rôles intermédiaires rigides)
- Gestion des comptes admin réservée au Super Admin (`UserPolicy`)
- Écran Filament dédié pour créer/éditer les administrateurs

## Ce que ce module ne fait pas encore

- Scoping des permissions par site (à ajouter si vous ouvrez à plusieurs clients)
- Invitation par email (création manuelle uniquement pour l'instant)
- Les permissions elles-mêmes ne bloquent encore rien dans l'interface, puisque les écrans qu'elles protègent (dashboard créatif, etc.) arrivent aux modules suivants

## Prochaine étape

Une fois testé et validé : **Module 2 — Bibliothèque de composants**, avec les questions de clarification habituelles.
