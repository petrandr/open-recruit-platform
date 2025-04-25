<?php

declare(strict_types=1);

namespace App\Orchid\Filters\Application;

use App\Support\ApplicationStatus;
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
                ->options(
                    collect(ApplicationStatus::all())
                        ->mapWithKeys(fn($meta, $key) => [$key => $meta['label']])
                        ->toArray()
                )
                ->empty()
                ->value($this->request->get('status'))
                ->title(__('Status')),
        ];
    }

	public function value(): string
	{
		$status = $this->request->get('status');
		if (! $status) {
			return '';
		}
		$statuses = ApplicationStatus::all();
		return $statuses[$status]['label'] ?? ucfirst($status);
	}
}
