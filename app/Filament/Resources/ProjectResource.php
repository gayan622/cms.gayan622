<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProjectResource\Pages;
use App\Models\Project;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class ProjectResource extends Resource
{
    protected static ?string $model            = Project::class;
    protected static ?string $navigationIcon   = 'heroicon-o-briefcase';
    protected static ?string $navigationGroup  = 'Content';
    protected static ?int    $navigationSort   = 1;
    protected static ?string $navigationLabel  = 'Projects / Blog';

    // ── Form ──────────────────────────────────────────────────────────────────
    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Project Details')
                ->schema([
                    Forms\Components\TextInput::make('title')
                        ->required()->maxLength(200)
                        ->live(onBlur: true)
                        ->afterStateUpdated(fn($state, callable $set) =>
                            $set('slug', \Illuminate\Support\Str::slug($state))),

                    Forms\Components\TextInput::make('slug')
                        ->required()->unique(ignoreRecord: true)->maxLength(200),

                    Forms\Components\TextInput::make('short_description')
                        ->required()->maxLength(300)->columnSpanFull(),

                    Forms\Components\RichEditor::make('description')
                        ->columnSpanFull()->toolbarButtons([
                            'bold','italic','underline','strike',
                            'h2','h3','bulletList','orderedList',
                            'link','blockquote','codeBlock',
                        ]),
                ])->columns(2),

            Forms\Components\Section::make('Media & Links')
                ->schema([
                    Forms\Components\FileUpload::make('screenshot')
                        ->image()->directory('projects')->maxSize(4096)
                        ->columnSpanFull(),

                    Forms\Components\TextInput::make('live_url')
                        ->url()->maxLength(300)->prefix('https://'),
                ])->columns(2),

            Forms\Components\Section::make('Classification')
                ->schema([
                    Forms\Components\TextInput::make('category')
                        ->required()->default('Web Project'),

                    Forms\Components\TextInput::make('client'),

                    Forms\Components\TextInput::make('year')
                        ->placeholder('2024 or 2020–2024'),

                    Forms\Components\TagsInput::make('technologies')
                        ->placeholder('Add technology…')
                        ->suggestions(['Laravel','Next.js','React','MySQL','Android','Java','PHP','Vue.js','TypeScript']),

                    Forms\Components\TextInput::make('emoji')->default('🌐'),

                    Forms\Components\TextInput::make('gradient')
                        ->default('from-violet-600 to-indigo-600')
                        ->helperText('Tailwind gradient classes for card fallback'),
                ])->columns(3),

            Forms\Components\Section::make('Display Settings')
                ->schema([
                    Forms\Components\Toggle::make('featured')->default(false),
                    Forms\Components\Toggle::make('is_active')->default(true),
                    Forms\Components\TextInput::make('sort_order')->numeric()->default(0),
                ])->columns(3),
        ]);
    }

    // ── Table ─────────────────────────────────────────────────────────────────
    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('emoji')->label(''),
                Tables\Columns\TextColumn::make('title')->searchable()->sortable()->weight('bold'),
                Tables\Columns\TextColumn::make('category')->badge()->color('primary'),
                Tables\Columns\TextColumn::make('client')->searchable()->toggleable(),
                Tables\Columns\TextColumn::make('year')->sortable()->toggleable(),
                Tables\Columns\IconColumn::make('featured')->boolean(),
                Tables\Columns\IconColumn::make('is_active')->boolean()->label('Active'),
                Tables\Columns\TextColumn::make('sort_order')->sortable()->label('Order'),
                Tables\Columns\TextColumn::make('updated_at')->dateTime()->sortable()->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('sort_order')
            ->filters([
                Tables\Filters\SelectFilter::make('category')
                    ->options(fn() => Project::distinct()->pluck('category', 'category')),
                Tables\Filters\TernaryFilter::make('featured'),
                Tables\Filters\TernaryFilter::make('is_active')->label('Active'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->reorderable('sort_order');
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListProjects::route('/'),
            'create' => Pages\CreateProject::route('/create'),
            'edit'   => Pages\EditProject::route('/{record}/edit'),
        ];
    }
}
