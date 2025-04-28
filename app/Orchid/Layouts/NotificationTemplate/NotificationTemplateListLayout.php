<?php declare(strict_types=1);

namespace App\Orchid\Layouts\NotificationTemplate;

use Orchid\Screen\Layouts\Table;
use Orchid\Screen\TD;
use App\Models\NotificationTemplate;
use Orchid\Screen\Actions\Link;

class NotificationTemplateListLayout extends Table
{
    /**
     * Data source.
     * @var string
     */
    public $target = 'templates';

    /**
     * @return TD[]
     */
    public function columns(): array
    {
        return [
            TD::make('id', __('ID'))
                ->sort()
                ->render(fn(NotificationTemplate $tpl) => $tpl->id),

            TD::make('name', __('Name'))
                ->sort()
                ->render(fn(NotificationTemplate $tpl) => Link::make($tpl->name)
                    ->route('platform.notification.templates.edit', $tpl->id)
                ),

            TD::make('type', __('Type'))
                ->filter(TD::FILTER_TEXT)
                ->render(fn(NotificationTemplate $tpl) =>
                    config('platform.notification_templates.types.' . $tpl->type, $tpl->type)
                ),

            TD::make('subject', __('Subject'))
                ->render(fn(NotificationTemplate $tpl) => $tpl->subject),

            TD::make('updated_at', __('Last Updated'))
                ->sort()
                ->render(fn(NotificationTemplate $tpl) => $tpl->updated_at->format('Y-m-d H:i:s')),
        ];
    }
}