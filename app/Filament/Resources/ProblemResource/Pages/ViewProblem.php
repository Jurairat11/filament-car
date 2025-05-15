<?php

namespace App\Filament\Resources\ProblemResource\Pages;

use App\Filament\Resources\ProblemResource;
use Filament\Actions;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Section;
use Filament\Forms\Form;
use Filament\Resources\Pages\ViewRecord;
use Filament\Forms\Components\View;
use Carbon\Carbon;

class ViewProblem extends ViewRecord
{
    protected static string $resource = ProblemResource::class;

    public function form(Form $form): Form{
        return $form
        ->schema([
            Section::make('Reporter Information')
            ->schema([
                Placeholder::make('user_id')
                    ->label('Employee ID')
                    ->content(fn ($record) => optional($record->user)->FullName),

                Placeholder::make('dept_id')
                    ->label('Department')
                    ->content(fn ($record) => optional($record->department)->dept_name),
                ])->columns(2),

            Section::make('Problem Details')
            ->description(fn ($livewire) =>
                    'Problem ID: ' . ($livewire->form->getRawState()['prob_id'] ?? 'new')
                )

            ->schema([
                Placeholder::make('prob_desc')
                    ->label('Description')
                    ->columnspan(2)
                    ->content(fn ($record) => $record->prob_desc),

                Placeholder::make('prob_date')
                    ->label('Reported Date')
                    ->content(fn ($record) => Carbon::parse($record->prob_date)->format('d/m/Y')),

                Placeholder::make('status')
                    ->label('Status')
                    ->content(fn ($record) => match ($record->status) {
                        'new' => 'New',
                        'accepted' => 'Accepted',
                        'reported' => 'Reported',
                        'in_progress' => 'In progress',
                        'pending_review' => 'Pending review',
                        'dismissed' => 'Dismissed',
                        'reopened' => 'Reopened',
                        'closed' => 'Closed',
                        default => 'Unknown',
                    })
                    ->extraAttributes(['class' => 'text-sm font-medium text-gray-800']),

                Placeholder::make('dismiss_reason')
                    ->label('Reason for Dismissal')
                    ->content(fn ($record) => $record->dismiss_reason ?? '-')
                    ->visible(fn ($record) => $record->status === 'dismissed'),

                View::make('components.problem-view-image')
                ->label('Before Image')
                ->viewData([
                    'path' => $this->getRecord()->prob_img,
                ])
                ->columnSpanFull()

            ])->columns(4),
        ]);
    }
}
