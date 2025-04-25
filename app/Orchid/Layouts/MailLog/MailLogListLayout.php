<?php
declare(strict_types=1);

namespace App\Orchid\Layouts\MailLog;

use Orchid\Screen\Layouts\Table;
use Orchid\Screen\TD;
use App\Models\MailLog;
use Orchid\Screen\Actions\Link;

class MailLogListLayout extends Table
{
    /**
     * Data source.
     *
     * @var string
     */
    public $target = 'mailLogs';

    /**
     * Table columns.
     *
     * @return TD[]
     */
    public function columns(): array
    {
        return [
            TD::make('id', __('ID'))
                ->sort()
                ->render(fn(MailLog $log) => $log->id),

            TD::make('type', __('Type'))
                ->filter(TD::FILTER_TEXT)
                ->render(fn(MailLog $log) => ucfirst($log->type)),

            TD::make('class', __('Class'))
                ->filter(TD::FILTER_TEXT)
                ->render(fn(MailLog $log) => class_basename($log->class)),

            TD::make('channel', __('Channel'))
                ->filter(TD::FILTER_TEXT)
                ->render(fn(MailLog $log) => $log->channel ?? '-'),

            TD::make('subject', __('Subject'))
                ->filter(TD::FILTER_TEXT)
                ->render(fn(MailLog $log) => $log->subject ?? '-'),

            TD::make('recipients', __('Recipients'))
                ->render(fn(MailLog $log) => implode(', ', $log->recipients ?? [])),

            TD::make('sent_at', __('Sent At'))
                ->sort()
                ->render(fn(MailLog $log) => $log->sent_at->format('Y-m-d H:i:s')),

            TD::make(__('Actions'))
                ->align(TD::ALIGN_CENTER)
                ->width('80px')
                ->render(fn(MailLog $log) => Link::make(__('View'))
                    ->route('platform.mail.log', $log->id)
                    ->icon('bs.eye')
                ),
        ];
    }
}
