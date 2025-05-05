<?php

declare(strict_types=1);

namespace App\Orchid\Filters\Interview;

use Illuminate\Database\Eloquent\Builder;
use Orchid\Filters\Filter;
use Orchid\Screen\Fields\Relation;
use App\Models\Candidate;

class CandidateFilter extends Filter
{
    public function name(): string
    {
        return __('Candidate');
    }

    public function parameters(): array
    {
        return ['candidate_id'];
    }

    public function run(Builder $builder): Builder
    {
        $id = $this->request->get('candidate_id');
        if ($id) {
            $builder->whereHas('application.candidate', function (Builder $q) use ($id) {
                $q->where('id', $id);
            });
        }
        return $builder;
    }

    public function display(): array
    {
        return [
            Relation::make('candidate_id')
                ->fromModel(Candidate::class, 'first_name')
                ->displayAppend('full_name')
                ->searchColumns('first_name', 'last_name', 'email')
                ->allowEmpty()
                ->value($this->request->get('candidate_id'))
                ->title(__('Candidate')),
        ];
    }

    public function value(): string
    {
        $id = $this->request->get('candidate_id');
        if ($id && ($c = Candidate::find($id))) {
            return $c->first_name . ' ' . $c->last_name;
        }
        return '';
    }
}