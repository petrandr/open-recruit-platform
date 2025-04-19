<?php

declare(strict_types=1);

namespace App\Orchid\Filters\Application;

use Illuminate\Database\Eloquent\Builder;
use Orchid\Filters\Filter;
use Orchid\Screen\Fields\DateTimer;

class SubmittedDateFilter extends Filter
{
    public function name(): string
    {
        return __('Submitted Date');
    }

    public function parameters(): array
    {
        return ['submitted_date'];
    }

    public function run(Builder $builder): Builder
    {
        $date = $this->request->get('submitted_date');
        if ($date) {
            $builder->whereDate('submitted_at', $date);
        }
        return $builder;
    }

    public function display(): array
    {
        return [
            DateTimer::make('submitted_date')
                ->title(__('Submitted Date'))
                ->enableTime(false)
                ->value($this->request->get('submitted_date')),
        ];
    }

    public function value(): string
    {
        return $this->request->get('submitted_date') ?: '';
    }
}