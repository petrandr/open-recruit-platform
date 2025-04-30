<?php declare(strict_types=1);

namespace App\Orchid\Screens\NotificationTemplate;

use App\Orchid\Fields\Ckeditor;
use Orchid\Screen\Screen;
use Illuminate\Http\Request;
use App\Models\NotificationTemplate;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Fields\Select;
use Orchid\Screen\Fields\TextArea;
use Illuminate\Validation\Rule;
use Orchid\Screen\Actions\Link;
use Orchid\Screen\Actions\Button;
use Orchid\Support\Facades\Layout;
use Orchid\Support\Facades\Toast;

class NotificationTemplateEditScreen extends Screen
{
    public $template;

    public function name(): ?string
    {
        return $this->template->exists
            ? __('Edit Template')
            : __('Add Template');
    }

    public function description(): ?string
    {
        return __('Create or edit a notification template');
    }

    public function permission(): ?iterable
    {
        return ['platform.notification.templates'];
    }

    public function query(NotificationTemplate $template): iterable
    {
        return [
            'template' => $template,
        ];
    }

    public function commandBar(): iterable
    {
        return [
            Link::make(__('Back'))
                ->icon('bs.arrow-left')
                ->route('platform.notification.templates'),
        ];
    }

    public function layout(): iterable
    {
        return [
            Layout::block([
                Layout::rows([
                    Input::make('template.name')
                        ->title(__('Template Name'))
                        ->required(),

                    Select::make('template.type')
                        ->title(__('Type'))
                        ->options(config('platform.notification_templates.types', []))
                        ->required(),

                    Input::make('template.subject')
                        ->title(__('Subject'))
                        ->required(),

                    Ckeditor::make('template.body')
                        ->id('template-body')
                        ->title(__('Body'))
                        ->rows(10)
                        ->placeholder("Dear {{candidate_first_name}} {{candidate_last_name}},\n\nWe regret to inform you that your application (#{{application_id}}) for {{job_title}} has been rejected.\n\nRegards,\n{{company}}")
                ]),
                Layout::view('partials.notification-template-placeholders'),
            ])
            ->title($this->template->exists ? __('Edit Template') : __('Add Template'))
            ->description(__('Define the subject and body of this notification template'))
            ->commands([
                Button::make(__('Save'))
                    ->icon('bs.check2')
                    ->method('save'),
            ]),
        ];
    }

    public function save(Request $request, NotificationTemplate $template)
    {
        $types = array_keys(config('platform.notification_templates.types', []));
        $data = $request->validate([
            'template.name'    => 'required|string|max:255',
            'template.type'    => ['required', Rule::in($types)],
            'template.subject' => 'required|string|max:255',
            'template.body'    => 'required|string',
        ]);
        $template->fill($data['template'])->save();
        Toast::info(__('Template saved successfully'));
        return redirect()->route('platform.notification.templates');
    }
}
