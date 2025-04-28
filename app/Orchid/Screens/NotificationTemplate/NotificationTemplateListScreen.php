<?php declare(strict_types=1);

namespace App\Orchid\Screens\NotificationTemplate;

use Orchid\Screen\Screen;
use App\Models\NotificationTemplate;
use App\Orchid\Layouts\NotificationTemplate\NotificationTemplateListLayout;
use Orchid\Screen\Actions\Link;
use Illuminate\Support\Facades\Toast;
use Illuminate\Http\Request;

class NotificationTemplateListScreen extends Screen
{
    public function name(): ?string
    {
        return __('Notification Templates');
    }

    public function description(): ?string
    {
        return __('Manage email notification templates');
    }

    public function permission(): ?iterable
    {
        return ['platform.notification.templates'];
    }

    public function query(): iterable
    {
        return [
            'templates' => NotificationTemplate::orderBy('updated_at', 'desc')->paginate(),
        ];
    }

    public function commandBar(): iterable
    {
        return [
            Link::make(__('Add Template'))
                ->icon('bs.plus')
                ->route('platform.notification.templates.create'),
        ];
    }

    public function layout(): iterable
    {
        return [
            NotificationTemplateListLayout::class,
        ];
    }
}