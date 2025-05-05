<?php

declare(strict_types=1);

namespace App\Orchid\Filters\Interview;

use Illuminate\Database\Eloquent\Builder;
use Orchid\Filters\Filter;
use Orchid\Screen\Fields\Relation;
use App\Models\User;

class InterviewerFilter extends Filter
{
    public function name(): string
    {
        return __('Interviewer');
    }

    public function parameters(): array
    {
        return ['interviewer_id'];
    }

    public function run(Builder $builder): Builder
    {
        $id = $this->request->get('interviewer_id');
        if ($id) {
            $builder->where('interviewer_id', $id);
        }
        return $builder;
    }

    public function display(): array
    {
        return [
            Relation::make('interviewer_id')
                ->fromModel(User::class, 'name')
                ->searchColumns('name', 'email')
                ->allowEmpty()
                ->value($this->request->get('interviewer_id'))
                ->title(__('Interviewer')),
        ];
    }

    public function value(): string
    {
        $id = $this->request->get('interviewer_id');
        if ($id && ($u = User::find($id))) {
            return $u->name;
        }
        return '';
    }
}