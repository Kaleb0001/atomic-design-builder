<?php

namespace App\Filament\Resources;

use App\Filament\Forms\Components\HybridEditorField;
use App\Filament\Resources\ComponentDefinitionResource\Pages;
use App\Filament\Resources\ComponentDefinitionResource\RelationManagers;
use App\Models\ComponentDefinition;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class ComponentDefinitionResource extends Resource
{
    protected static ?string $model = ComponentDefinition::class;

    protected static ?string $navigationIcon = 'heroicon-o-cube';

    protected static ?string $navigationLabel = 'Bibliothèque de composants';

    public const TYPES = [
        'atom' => 'Atom',
        'molecule' => 'Molecule',
        'organism' => 'Organism',
        'section' => 'Section',
        'template' => 'Template (page réutilisable)',
    ];

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Identité')
                ->columns(2)
                ->schema([
                    Forms\Components\Select::make('type')
                        ->label('Niveau')
                        ->options(self::TYPES)
                        ->required()
                        ->disabled(fn (Forms\Get $get) => filled($get('variant_of_id')))
                        ->dehydrated(),

                    Forms\Components\TextInput::make('nom')
                        ->required()
                        ->maxLength(255),

                    Forms\Components\Select::make('variant_of_id')
                        ->label('Variante de')
                        ->relationship('variantOf', 'nom', fn ($query) => $query->whereNull('variant_of_id'))
                        ->searchable()
                        ->preload()
                        ->live()
                        ->afterStateUpdated(function ($state, Forms\Set $set) {
                            if ($state) {
                                $set('type', ComponentDefinition::find($state)?->type);
                            }
                        })
                        ->helperText("Laisser vide s'il s'agit du composant de base."),

                    Forms\Components\TextInput::make('variant_nom')
                        ->label('Nom de la variante')
                        ->placeholder('ex. secondary')
                        ->visible(fn (Forms\Get $get) => filled($get('variant_of_id')))
                        ->required(fn (Forms\Get $get) => filled($get('variant_of_id'))),

                    Forms\Components\Select::make('statut')
                        ->options([
                            'brouillon' => 'Brouillon',
                            'publie' => 'Publié',
                        ])
                        ->default('brouillon')
                        ->required(),
                ]),

            Forms\Components\Section::make('Code')
                ->description("Mode Visuel par défaut ; le mode Code (si vous en avez le droit) est accessible via le bouton en haut. Chaque sauvegarde crée une nouvelle version dans l'historique (onglet visible après création).")
                ->schema([
                    HybridEditorField::make('code')
                        ->label('')
                        ->columnSpanFull(),

                    Forms\Components\TextInput::make('message')
                        ->label('Message de version (optionnel)')
                        ->placeholder('ex. Correction du padding mobile')
                        ->maxLength(255),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('nom')->searchable(),
                Tables\Columns\TextColumn::make('type')
                    ->badge()
                    ->formatStateUsing(fn (string $state) => self::TYPES[$state] ?? $state),
                Tables\Columns\TextColumn::make('variant_nom')->label('Variante')->placeholder('—'),
                Tables\Columns\TextColumn::make('statut')
                    ->badge()
                    ->color(fn (string $state) => $state === 'publie' ? 'success' : 'gray')
                    ->formatStateUsing(fn (string $state) => $state === 'publie' ? 'Publié' : 'Brouillon'),
                Tables\Columns\TextColumn::make('versions_count')
                    ->counts('versions')
                    ->label('Versions'),
                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Modifié le')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('type')->options(self::TYPES),
                Tables\Filters\SelectFilter::make('statut')->options([
                    'brouillon' => 'Brouillon',
                    'publie' => 'Publié',
                ]),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\VersionsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListComponentDefinitions::route('/'),
            'create' => Pages\CreateComponentDefinition::route('/create'),
            'edit' => Pages\EditComponentDefinition::route('/{record}/edit'),
        ];
    }
}
