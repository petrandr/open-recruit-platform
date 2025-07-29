<?php

declare(strict_types=1);

namespace App\Orchid\Screens\Industry;

use Orchid\Screen\Screen;
use App\Models\Industry;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Actions\Link;
use Orchid\Screen\Actions\Button;
use Orchid\Support\Facades\Layout;
use Orchid\Support\Facades\Toast;
use Illuminate\Http\Request;

class IndustryEditScreen extends Screen
{
    /**
     * Industry model instance.
     *
     * @var Industry|null
     */
    public ?Industry $industry = null;

    /**
     * Query data.
     */
    public function query(Industry $industry = null): iterable
    {
        // On create, $industry will be null; initialize a new model
        $this->industry = $industry ?? new Industry();
        return [
            'industry' => $this->industry,
        ];
    }

    /**
     * Display header name.
     */
    public function name(): ?string
    {
        if (! $this->industry || ! $this->industry->exists) {
            return __('Add Industry');
        }
        return __('Edit Industry');
    }

    /**
     * Display header description.
     */
    public function description(): ?string
    {
        return __('Create or edit a job industry');
    }

    /**
     * Permission.
     */
    public function permission(): ?iterable
    {
        return ['platform.industries'];
    }

    /**
     * Action buttons.
     */
    public function commandBar(): iterable
    {
        return [
            Link::make(__('Back'))
                ->icon('bs.arrow-left')
                ->route('platform.industries'),
        ];
    }

    /**
     * Views.
     */
    public function layout(): iterable
    {
        return [
            Layout::block([
                Layout::rows([
                    Input::make('industry.name')
                        ->title(__('Name'))
                        ->required(),
                ]),
            ])
            ->title((! $this->industry || ! $this->industry->exists)
                ? __('Add Industry')
                : __('Edit Industry')
            )
            ->description(__('Define the industry name'))
            ->commands([
                Button::make(__('Save'))
                    ->icon('bs.check2')
                    ->method('save'),
            ]),
        ];
    }

    /**
     * Save industry.
     */
    public function save(Request $request, Industry $industry)
    {
        $data = $request->validate([
            'industry.name' => 'required|string|max:255',
        ]);

        $industry->fill($data['industry'])->save();

        Toast::info(__('Industry saved successfully'));

        return redirect()->route('platform.industries');
    }
}