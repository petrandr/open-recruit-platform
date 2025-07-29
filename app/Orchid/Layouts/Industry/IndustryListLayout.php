<?php

declare(strict_types=1);

namespace App\Orchid\Layouts\Industry;

use Orchid\Screen\Layouts\Table;
use Orchid\Screen\TD;
use Orchid\Screen\Actions\Link;
use App\Models\Industry;

class IndustryListLayout extends Table
{
    /**
     * Data source.
     *
     * @var string
     */
    public $target = 'industries';

    /**
     * @return TD[]
     */
    public function columns(): array
    {
        return [
            TD::make('id', __('ID'))
                ->sort(),

            TD::make('name', __('Name'))
                ->sort()
                ->render(fn(Industry $industry) => Link::make($industry->name)
                    ->route('platform.industries.edit', $industry->id)
                ),

            TD::make('created_at', __('Created'))
                ->sort()
                ->render(fn(Industry $industry) => $industry->created_at->toDateTimeString()),
        ];
    }
}