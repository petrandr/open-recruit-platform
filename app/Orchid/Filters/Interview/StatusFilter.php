<?php

declare(strict_types=1);

namespace App\Orchid\Filters\Interview;

use Illuminate\Database\Eloquent\Builder;
use Orchid\Filters\Filter;
use Orchid\Screen\Fields\Select;
use App\Support\Interview as InterviewSupport;

class StatusFilter extends Filter
{
    public function name(): string
    {
        return __('Status');
    }

    public function parameters(): array
    {
        return ['status'];
    }

    public function run(Builder $builder): Builder
    {
        $status = $this->request->get('status');
        if ($status) {
            $builder->where('status', $status);
        }
        return $builder;
    }

    public function display(): array
    {
        $options = collect(InterviewSupport::statuses())
            ->mapWithKeys(fn($meta, $key) => [$key => $meta['label']])
            ->toArray();

        return [
            Select::make('status')
                ->options($options)
                ->empty()
                ->value($this->request->get('status'))
                ->title(__('Status')),
        ];
    }

    public function value(): string
    {
        $status = $this->request->get('status');
        if (!$status) {
            return '';
        }
        return InterviewSupport::statuses()[$status]['label'] ?? ucfirst($status);
    }
}