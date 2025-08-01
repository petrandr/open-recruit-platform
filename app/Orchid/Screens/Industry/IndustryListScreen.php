<?php

declare(strict_types=1);

namespace App\Orchid\Screens\Industry;

use Orchid\Screen\Screen;
use App\Models\Industry;
use App\Orchid\Layouts\Industry\IndustryListLayout;
use Orchid\Screen\Actions\Link;

class IndustryListScreen extends Screen
{
    /**
     * Display header name.
     */
    public function name(): ?string
    {
        return __('Industries');
    }

    /**
     * Display header description.
     */
    public function description(): ?string
    {
        return __('Manage job industries');
    }

    /**
     * Permission.
     */
    public function permission(): ?iterable
    {
        return ['platform.industries'];
    }

    /**
     * Query data.
     */
    public function query(): iterable
    {
        return [
            'industries' => Industry::orderBy('name')->paginate(),
        ];
    }

    /**
     * Action buttons.
     */
    public function commandBar(): iterable
    {
        return [
            Link::make(__('Add Industry'))
                ->icon('bs.plus')
                ->route('platform.industries.create'),
        ];
    }

    /**
     * Views.
     */
    public function layout(): iterable
    {
        return [
            IndustryListLayout::class,
        ];
    }
}
