<?php

namespace App\Filament\Resources\ProblemResource\Pages;

use App\Filament\Resources\ProblemResource;
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

            Section::make('Problem Information')
            ->description(fn ($livewire) =>
                    'Problem ID: ' . ($livewire->form->getRawState()['prob_id'] ?? 'new')
                )

            ->schema([

                Section::make('Reporter Information')
                    ->schema([
                        Placeholder::make('user_id')
                            ->label('รหัสพนักงาน')
                            ->content(fn ($record) => optional($record->user)->FullName),

                        Placeholder::make('dept_id')
                            ->label('แผนกผู้แจ้ง')
                            ->content(fn ($record) => optional($record->department)->dept_name),

                        Placeholder::make('prob_date')
                            ->label('วันที่แจ้งอันตราย')
                            ->content(fn ($record) => Carbon::parse($record->prob_date)->format('d/m/Y')),
                ])->columns(3),

                Section::make('Problem Details')
                    //->description('Details about the reported problem')
                    ->description(fn ($livewire) =>
                    'Status: ' . ucfirst(str_replace('_', ' ', $livewire->form->getRawState()['status'] ?? '')
                ))
                    ->schema([

                        Placeholder::make('title')
                            ->label('เรื่อง')
                            ->content(fn ($record) => $record->title),

                        Placeholder::make('place')
                            ->label('สถานที่')
                            ->content(fn ($record) => $record->place),

                        Placeholder::make('prob_desc')
                            ->label('รายละเอียดอันตรายที่พบ')
                            // ->columnspan(2)
                            ->content(fn ($record) => $record->prob_desc),

                        View::make('components.problem-view-image')
                            ->label('รูปภาพอันตรายที่พบ')
                            ->viewData([
                                'path' => $this->getRecord()->prob_img_path,
                            ])
                            ->columnSpanFull(),

                        Placeholder::make('dismiss_reason')
                            ->label('สาเหตุไม่รับการแจ้งอันตราย')
                            ->content(fn ($record) => $record->dismiss_reason ?? '-')
                            ->visible(fn ($record) => $record->status === 'dismissed'),

                ])->columns(3),




            ])->columns(4),
        ]);
    }
}
