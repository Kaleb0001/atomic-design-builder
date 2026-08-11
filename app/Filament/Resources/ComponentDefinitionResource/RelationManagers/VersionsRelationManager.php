<?php

namespace App\Filament\Resources\ComponentDefinitionResource\RelationManagers;

use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class VersionsRelationManager extends RelationManager
{
    protected static string $relationship = 'versions';

    protected static ?string $title = 'Historique des versions';

    public function form(Form $form): Form
    {
        // Lecture seule : la création d'une version passe par le formulaire
        // principal du composant (onglet "Code"), pas par cet écran.
        return $form->schema([]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('message')
            ->columns([
                Tables\Columns\TextColumn::make('message')->label('Message')->default('—'),
                Tables\Columns\TextColumn::make('auteur.name')->label('Auteur')->default('—'),
                Tables\Columns\TextColumn::make('created_at')->label('Créée le')->dateTime('d/m/Y H:i')->sortable(),
                Tables\Columns\IconColumn::make('is_current')
                    ->label('Active')
                    ->boolean()
                    ->getStateUsing(fn ($record) => $record->id === $record->componentDefinition->version_courante_id),
            ])
            ->defaultSort('created_at', 'desc')
            ->headerActions([])
            ->actions([
                Tables\Actions\Action::make('restaurer')
                    ->label('Restaurer')
                    ->icon('heroicon-o-arrow-uturn-left')
                    ->requiresConfirmation()
                    ->modalDescription('Crée une nouvelle version reprenant ce code, sans supprimer l\'historique existant.')
                    ->visible(fn ($record) => $record->id !== $record->componentDefinition->version_courante_id)
                    ->action(function ($record) {
                        $newVersion = $record->componentDefinition->versions()->create([
                            'code_html' => $record->code_html,
                            'code_css' => $record->code_css,
                            'code_js' => $record->code_js,
                            'auteur_id' => auth()->id(),
                            'message' => 'Restauration de la version du '.$record->created_at->format('d/m/Y H:i'),
                        ]);

                        $record->componentDefinition->update(['version_courante_id' => $newVersion->id]);
                    }),
            ]);
    }
}
