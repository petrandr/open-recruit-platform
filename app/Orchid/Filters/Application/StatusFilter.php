<?php

declare(strict_types=1);

namespace App\Orchid\Filters\Application;

use Illuminate\Database\Eloquent\Builder;
use Orchid\Filters\Filter;
use Orchid\Screen\Fields\Select;

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
        return [
            Select::make('status')
                ->options([
                    'submitted'   => __('Submitted'),
                    'under review'=> __('Under Review'),
                    'accepted'    => __('Accepted'),
                    'rejected'    => __('Rejected'),
                ])
                ->empty()
                ->value($this->request->get('status'))
                ->title(__('Status')),
        ];
    }

    public function value(): string
    {
        $status = $this->request->get('status');
        return $status ? ucfirst($status) : '';
    }
}