<?php

namespace App\Filament\Resources\Organizations\Schemas;
use Illuminate\Support\Str;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\FileUpload;
use Illuminate\Support\Facades\Auth;
use Filament\Schemas\Schema;
use App\Models\User;

class OrganizationForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label('Nome da Organização')
                    ->helperText('Nome oficial da empresa ou instituição')
                    ->required()
                    ->unique(table: 'organizations', column: 'name', ignoreRecord: true)
                    ->string()
                    ->maxLength(100)
                    ->live(onBlur: true)
                    ->afterStateUpdated(function ($state, callable $set) {
                        $set('slug', Str::slug($state));
                    })
                    ->validationMessages([
                        'required' => 'O nome da organização é obrigatório.',
                        'unique' => 'Já existe uma organização com este nome.',
                        'max' => 'O nome deve ter no máximo 100 caracteres.'
                    ]),

                TextInput::make('slug')
                    ->label('Slug')
                    ->helperText('Identificador único para URLs (gerado automaticamente)')
                    ->disabled()
                    ->unique(table: 'organizations', column: 'slug', ignoreRecord: true)
                    ->string()
                    ->maxLength(120)
                    ->dehydrated(),

                Textarea::make('description')
                    ->label('Descrição')
                    ->helperText('Descreva a organização, seus objetivos e área de atuação')
                    ->maxLength(500)
                    ->string()
                    ->rows(3)
                    ->columnSpanFull()
                    ->validationMessages([
                        'max' => 'A descrição deve ter no máximo 500 caracteres.'
                    ]),

                Toggle::make('single_dashboard')
                    ->label('Usar apenas um dashboard nesta organização')
                    ->helperText('Quando ativo: ao acessar Dashboards, vai direto para criar/visualizar o único dashboard.')
                    ->default(false),

                Placeholder::make('logo_section_title')
                    ->label('Logo da Organização')
                    ->content('Configure o logo que será exibido para esta organização')
                    ->columnSpanFull(),

                FileUpload::make('logo_url')
                    ->label('Logo')
                    ->helperText('Faça upload do logo da organização (PNG, JPG, SVG - máx. 2MB)')
                    ->image()
                    ->imageEditor()
                    ->imageEditorAspectRatios([
                        '16:9',
                        '4:3',
                        '1:1',
                    ])
                    ->maxSize(2048) // 2MB em KB
                    ->acceptedFileTypes(['image/png', 'image/jpeg', 'image/jpg', 'image/svg+xml'])
                    ->directory('organizations/logos')
                    ->disk('public')
                    ->visibility('public')
                    ->storeFileNamesIn('logo_filename')
                    ->columnSpanFull(),

                TextInput::make('logo_alt_text')
                    ->label('Texto Alternativo')
                    ->helperText('Descrição do logo para acessibilidade')
                    ->placeholder('Logo da Organização')
                    ->maxLength(255),

                // Removed manual width/height fields to prevent arbitrary resizing from the form.
                // Image sizing is controlled via the image editor (aspect ratios) and upload limits above.

                Select::make('users')
                    ->label('Usuários Associados')
                    ->helperText('Você será automaticamente associado à organização. Selecione outros usuários se necessário.')
                    ->relationship(
                        name: 'users',
                        titleAttribute: 'name',
                        modifyQueryUsing: fn ($query) => $query->orderBy('name')
                    )
                    ->multiple()
                    ->preload()
                    ->optionsLimit(1000)
                    ->searchable()
                    ->default(function () {
                        $user = Auth::user();
                        return $user ? [$user->id] : [];
                    })
                    ->createOptionForm([
                        TextInput::make('name')
                            ->label('Nome')
                            ->required()
                            ->string()
                            ->maxLength(255),
                        TextInput::make('email')
                            ->label('E-mail')
                            ->email()
                            ->required()
                            ->unique('users', 'email')
                            ->string()
                            ->maxLength(255),
                        TextInput::make('password')
                            ->label('Senha')
                            ->password()
                            ->required()
                            ->string()
                            ->minLength(8)
                            ->maxLength(100)
                            // Hash password before persisting (keeps parity with User create form)
                            ->dehydrateStateUsing(fn($state) => filled($state) ? bcrypt($state) : null),
                    ])
                    ->columnSpanFull()
                    ->validationMessages([
                        'array' => 'Selecione pelo menos um usuário para a organização.',
                        'required' => 'Você deve estar associado à organização que está criando.'
                    ])
                    ->rules([
                        'required',
                        'array',
                        'min:1'
                    ]),
            ]);
    }
}