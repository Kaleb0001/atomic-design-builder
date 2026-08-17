<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PageResource\Pages;
use App\Models\ComponentDefinition;
use App\Models\Page;
use App\Models\Site;
use App\Services\PageRenderer;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Str;

class PageResource extends Resource
{
    protected static ?string $model = Page::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static ?string $navigationLabel = 'Pages';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Page')
                ->columns(2)
                ->schema([
                    Forms\Components\Select::make('site_id')
                        ->label('Site')
                        ->relationship('site', 'nom')
                        ->default(fn () => Site::query()->value('id'))
                        ->required(),

                    Forms\Components\Select::make('statut')
                        ->options([
                            'brouillon' => 'Brouillon',
                            'publie' => 'Publié',
                        ])
                        ->default('brouillon')
                        ->required(),

                    Forms\Components\TextInput::make('titre')
                        ->required()
                        ->live(onBlur: true)
                        ->afterStateUpdated(function (?string $state, Forms\Set $set, Forms\Get $get) {
                            if (blank($get('slug'))) {
                                $set('slug', Str::slug($state));
                            }
                        })
                        ->maxLength(255),

                    Forms\Components\TextInput::make('slug')
                        ->required()
                        ->unique(ignoreRecord: true)
                        ->maxLength(255),
                ]),

            Forms\Components\Section::make('Sections de la page')
                ->description('Glissez pour réordonner. Seules les Sections et Templates publiés sont proposés.')
                ->schema([
                    Forms\Components\Repeater::make('pageBlocks')
                        ->relationship()
                        ->reorderable()
                        ->orderColumn('ordre')
                        ->collapsible()
                        ->itemLabel(fn (array $state) => ComponentDefinition::find($state['component_definition_id'] ?? null)?->nom ?? 'Nouveau bloc')
                        ->schema([
                            Forms\Components\Select::make('component_definition_id')
                                ->label('Section / Template')
                                ->options(fn () => ComponentDefinition::query()
                                    ->where('statut', 'publie')
                                    ->whereIn('type', ['section', 'template'])
                                    ->get()
                                    ->mapWithKeys(fn (ComponentDefinition $c) => [
                                        $c->id => $c->nom.($c->variant_nom ? " ({$c->variant_nom})" : ''),
                                    ]))
                                ->live()
                                ->required()
                                ->columnSpan(2),

                            Forms\Components\Toggle::make('actif')
                                ->label('Actif')
                                ->default(true),

                            Forms\Components\Fieldset::make('Contenu de ce bloc')
                                ->columns(1)
                                ->visible(fn (Forms\Get $get) => filled($get('component_definition_id')))
                                ->schema(function (Forms\Get $get) {
                                    $component = ComponentDefinition::with('currentVersion')->find($get('component_definition_id'));
                                    $html = $component?->currentVersion?->code_html ?? '';
                                    $fields = app(PageRenderer::class)->detectPlaceholders($html);

                                    if (empty($fields)) {
                                        return [
                                            Forms\Components\Placeholder::make('no_fields')
                                                ->label('')
                                                ->content('Ce composant ne définit aucun champ éditable ({{champ}}).'),
                                        ];
                                    }

                                    return collect($fields)->map(
                                        fn (string $field) => Forms\Components\TextInput::make("contenu_json.{$field}")
                                            ->label(Str::headline($field))
                                    )->all();
                                }),
                        ])
                        ->columns(3)
                        ->columnSpanFull(),
                ]),

            Forms\Components\Section::make('Aperçu')
                ->schema([
                    Forms\Components\View::make('filament.forms.components.page-preview'),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('titre')->searchable(),
                Tables\Columns\TextColumn::make('slug')->searchable(),
                Tables\Columns\TextColumn::make('site.nom')->label('Site'),
                Tables\Columns\TextColumn::make('statut')
                    ->badge()
                    ->color(fn (string $state) => $state === 'publie' ? 'success' : 'gray')
                    ->formatStateUsing(fn (string $state) => $state === 'publie' ? 'Publié' : 'Brouillon'),
                Tables\Columns\TextColumn::make('pageBlocks_count')
                    ->counts('pageBlocks')
                    ->label('Blocs'),
                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Modifiée le')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
            ])
            ->filters([
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

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPages::route('/'),
            'create' => Pages\CreatePage::route('/create'),
            'edit' => Pages\EditPage::route('/{record}/edit'),
        ];
    }
}
