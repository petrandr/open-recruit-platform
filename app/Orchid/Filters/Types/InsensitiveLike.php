<?php
declare(strict_types=1);

namespace App\Orchid\Filters\Types;

use Illuminate\Database\Eloquent\Builder;
use Orchid\Filters\BaseHttpEloquentFilter;

/**
 * Case-insensitive LIKE filter using LOWER(column) LIKE LOWER(value).
 */
class InsensitiveLike extends BaseHttpEloquentFilter
{
    /**
     * Apply the case-insensitive LIKE filter to the query.
     */
    public function run(Builder $builder): Builder
    {
        // Prepare the value with wildcards
        $value = '%'.$this->getHttpValue().'%';

        // Wrap the column name according to the query grammar
        $grammar = $builder->getQuery()->getGrammar();
        $wrapped = $grammar->wrap($this->column);

        // Use LOWER() on both column and value for case-insensitive match
        return $builder->whereRaw(
            "LOWER({$wrapped}) LIKE ?",
            [strtolower($value)]
        );
    }
}