# Module 0 — Fondations

Installation du socle Laravel 12 + Filament, avec PostgreSQL en local (XAMPP + PostgreSQL installé à part).

## Prérequis

- PHP 8.2.x (celui de XAMPP)
- PostgreSQL installé et lancé en local
- Composer
- Node.js (pas nécessaire pour faire tourner ce module, seulement pour plus tard)

## Étapes

1. **Créer le projet**
   ```bash
   composer create-project laravel/laravel:^12.0 atomic-design-builder
   cd atomic-design-builder
   ```

2. **Configurer l'environnement**
   Copier le contenu de `.env.example` (fourni dans ce module) dans votre `.env`, puis adapter `DB_USERNAME` / `DB_PASSWORD` à votre installation PostgreSQL locale. Créer la base au préalable (ex. via pgAdmin ou `createdb atomic_design_builder`).

3. **Installer Filament**
   ```bash
   composer require filament/filament:"^3.3" -W
   php artisan filament:install --panels
   ```

4. **Installer spatie/laravel-permission**
   (installation seulement — la configuration des rôles/permissions arrive au Module 1)
   ```bash
   composer require spatie/laravel-permission
   ```

5. **Ajouter les fichiers du projet**
   Copier dans votre projet :
   - `database/migrations/2026_08_10_000001_create_sites_table.php`
   - `app/Models/Site.php`
   - `database/seeders/SiteSeeder.php`

   Puis, dans `database/seeders/DatabaseSeeder.php`, ajouter dans la méthode `run()` :
   ```php
   $this->call(SiteSeeder::class);
   ```

6. **Migrer et peupler**
   ```bash
   php artisan migrate
   php artisan db:seed --class=SiteSeeder
   ```

7. **Créer votre compte admin Filament**
   ```bash
   php artisan make:filament-user
   ```

8. **Lancer le projet**
   ```bash
   php artisan serve
   ```
   Le panneau d'administration est accessible sur `/admin`. Aucun `npm run dev` n'est nécessaire à ce stade — Filament fonctionne sans build front tant qu'on ne touche pas au dashboard créatif (Monaco/GrapesJS arrivent aux modules 3 et 4).

## Ce que fait ce module

- Projet Laravel 12 + Filament installés et fonctionnels
- Connexion PostgreSQL opérationnelle
- Table `sites` en place (une ligne "Site principal" pré-remplie) — prépare l'ouverture multi-clients sans redéfinir le schéma plus tard
- Panneau d'administration Filament accessible avec un compte admin

## Ce que ce module ne fait pas encore

- Rôles/permissions (Module 1)
- Bibliothèque de composants (Module 2)
- Dashboard créatif (Modules 3-4)

## Prochaine étape

Une fois ce module testé et validé de votre côté : **Module 1 — Authentification & permissions**, avec les questions de clarification habituelles.
