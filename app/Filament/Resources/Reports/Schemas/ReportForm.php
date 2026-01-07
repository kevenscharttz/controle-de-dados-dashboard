<?php

namespace App\Filament\Resources\Reports\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Radio;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use App\Models\Organization;
use App\Models\Report;
use Illuminate\Support\Facades\Auth;

class ReportForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('title')
                    ->label('Título')
                    ->helperText('Nome único para identificar o relatório')
                    ->required()
                    ->unique(table: 'reports', column: 'title', ignoreRecord: true)
                    ->string()
                    ->maxLength(100)
                    ->live(onBlur: true)
                    ->validationMessages([
                        'required' => 'O título é obrigatório.',
                        'max' => 'O título deve ter no máximo 100 caracteres.',
                        'unique' => 'Já existe um relatório com este título.'
                    ]),

                Select::make('organization_id')
                    ->label('Organização')
                    ->helperText('Selecione a organização proprietária')
                    ->options(function () {
                        $user = Auth::user();
                        if (!$user) {
                            return [];
                        }
                        
                        // Super-admin pode ver todas as organizações
                        if ($user->hasRole('super-admin')) {
                            return Organization::pluck('name', 'id');
                        }
                        
                        // Outros usuários só veem suas organizações
                        $orgIds = $user->organizations()->pluck('id');
                        return Organization::whereIn('id', $orgIds)->pluck('name', 'id');
                    })
                    ->default(function () {
                        $user = Auth::user();
                        if (!$user) {
                            return null;
                        }
                        
                        // Se tem apenas uma organização, seleciona automaticamente
                        $orgIds = $user->organizations()->pluck('id');
                        if ($orgIds->count() === 1) {
                            return $orgIds->first();
                        }
                        
                        return null;
                    })
                    ->required()
                    ->live()
                    ->searchable()
                    ->validationMessages([
                        'required' => 'A organização é obrigatória.'
                    ]),

                Textarea::make('description')
                    ->label('Descrição')
                    ->helperText('Descreva o propósito e conteúdo do relatório')
                    ->maxLength(500)
                    ->rows(3)
                    ->columnSpanFull(),

                Select::make('platform')
                    ->label('Plataforma Utilizada')
                    ->helperText('Selecione a plataforma de BI utilizada')
                    ->options([
                        'Power BI' => 'Power BI',
                        'Metabase' => 'Metabase',
                        'Tableau' => 'Tableau',
                        'Grafana' => 'Grafana',
                        'Looker' => 'Looker',
                        'QlikView' => 'QlikView',
                        'Outros' => 'Outros',
                    ])
                    ->required()
                    ->searchable()
                    ->live()
                    ->validationMessages([
                        'required' => 'A plataforma é obrigatória.'
                    ]),

                TextInput::make('platform_custom')
                    ->label('Plataforma Personalizada')
                    ->helperText('Especifique se selecionou "Outros"')
                    ->string()
                    ->maxLength(50),

                TextInput::make('url')
                    ->label('URL da Plataforma')
                    ->helperText('Cole a URL pública ou embutível do relatório')
                    ->required()
                    ->url()
                    ->string()
                    ->maxLength(500)
                    ->placeholder('https://app.powerbi.com/view?r=...')
                    ->live(onBlur: true)
                    ->validationMessages([
                        'required' => 'A URL é obrigatória.',
                        'url' => 'Informe uma URL válida.',
                        'max' => 'A URL deve ter no máximo 500 caracteres.'
                    ]),

                Radio::make('visibility')
                    ->label('Visibilidade')
                    ->helperText('Defina se o relatório será público ou privado')
                    ->options([
                        'public' => 'Público - Visível para todos os usuários da organização',
                        'private' => 'Privado - Visível apenas para usuários autorizados',
                    ])
                    ->required()
                    ->live()
                    ->inline()
                    ->validationMessages([
                        'required' => 'A visibilidade é obrigatória.'
                    ]),

                Select::make('viewers')
                    ->label('Quem pode visualizar (privado)')
                    ->helperText('Selecione os membros da organização que poderão visualizar este relatório quando privado')
                    ->multiple()
                    ->preload()
                    ->searchable()
                    ->visible(fn (Get $get) => $get('visibility') === 'private')
                    ->dehydrated(fn (Get $get) => $get('visibility') === 'private')
                    ->relationship(
                        name: 'viewers',
                        titleAttribute: 'name',
                        modifyQueryUsing: function ($query, Get $get) {
                            $orgId = $get('organization_id');
                            if ($orgId) {
                                $query->whereHas('organizations', function ($q) use ($orgId) {
                                    $q->where('organizations.id', $orgId);
                                });
                            } else {
                                // Sem organização escolhida, não lista usuários
                                $query->whereRaw('0 = 1');
                            }
                        }
                    ),

                Placeholder::make('multiple_reports_info')
                    ->label('')
                    ->content('ℹ️ Você pode criar múltiplos relatórios para esta organização.')
                    ->columnSpanFull(),

                TagsInput::make('tags')
                    ->label('Tags')
                    ->helperText('Adicione palavras-chave para facilitar a busca e categorização')
                    ->placeholder('Ex: vendas, marketing, financeiro')
                    ->suggestions([
                        'vendas', 'marketing', 'financeiro', 'operacional',
                        'recursos-humanos', 'ti', 'qualidade', 'produção'
                    ])
                    ->validationMessages([
                        'array' => 'As tags devem ser uma lista de palavras-chave.'
                    ])
                    ->columnSpanFull(),
            ]);
    }
}
