<?php

declare(strict_types=1);

namespace App\Orchid\Filters\Application;

use Illuminate\Database\Eloquent\Builder;
use Orchid\Filters\Filter;
use Orchid\Screen\Fields\Relation;
use App\Models\JobListing;

class JobFilter extends Filter
{
    public function name(): string
    {
        return __('Job');
    }

    public function parameters(): array
    {
        return ['job_id'];
    }

    public function run(Builder $builder): Builder
    {
        $id = $this->request->get('job_id');
        if ($id) {
            $builder->where('job_id', $id);
        }
        return $builder;
    }

    public function display(): array
    {
        return [
            Relation::make('job_id')
                ->fromModel(JobListing::class, 'title')
                ->displayAppend('title_with_status')
                ->searchColumns('title')
                ->allowEmpty()
                ->value($this->request->get('job_id'))
                ->title(__('Job')),
        ];
    }

    public function value(): string
    {
        $id = $this->request->get('job_id');
        if ($id && ($j = JobListing::find($id))) {
            return $j->title_with_status;
        }
        return '';
    }
}