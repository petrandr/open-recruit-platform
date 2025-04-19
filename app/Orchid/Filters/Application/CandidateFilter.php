<?php

declare(strict_types=1);

namespace App\Orchid\Filters\Application;

use Illuminate\Database\Eloquent\Builder;
use Orchid\Filters\Filter;
use Orchid\Screen\Fields\Relation;
use App\Models\Candidate;

class CandidateFilter extends Filter
{
    /**
     * The displayable name of the filter.
     */
    public function name(): string
    {
        return __('Candidate');
    }

    /**
     * The array of matched parameters.
     */
    public function parameters(): array
    {
        return ['applicant_id'];
    }

    /**
     * Apply to a given Eloquent query builder.
     */
    public function run(Builder $builder): Builder
    {
        $id = $this->request->get('applicant_id');
        if ($id) {
            $builder->where('applicant_id', $id);
        }
        return $builder;
    }

    /**
     * Get the display fields.
     */
    public function display(): array
    {
        return [
            Relation::make('applicant_id')
                // Use first_name as base column for searching; display full_name via accessor
                ->fromModel(Candidate::class, 'first_name')
                ->displayAppend('full_name')
                ->searchColumns('first_name', 'last_name', 'email')
                ->allowEmpty()
                ->value($this->request->get('applicant_id'))
                ->title(__('Candidate')),
        ];
    }

    /**
     * Label for the selected value.
     */
    public function value(): string
    {
        $id = $this->request->get('applicant_id');
        return $id && ($c = Candidate::find($id))
            ? $c->first_name . ' ' . $c->last_name
            : '';
    }
}
