<?php
declare(strict_types=1);

namespace App\Orchid\Screens\MailLog;

use Orchid\Screen\Screen;
use App\Models\MailLog;
use Orchid\Support\Facades\Layout;
use Orchid\Screen\Fields\Label;
use Orchid\Screen\Actions\Link;
use Illuminate\Http\Request;

class MailLogViewScreen extends Screen
{
    /**
     * The mail log model instance.
     *
     * @var MailLog
     */
    public $mailLog;

    /**
     * Screen name displayed in header.
     */
    public function name(): ?string
    {
        return __('Mail Log Detail');
    }

    /**
     * Screen description.
     */
    public function description(): ?string
    {
        return __('Details of the selected mail or notification');
    }

    /**
     * Permission required to view this screen.
     */
    public function permission(): ?iterable
    {
        return ['platform.mail-logs'];
    }

    /**
     * Query data for the screen.
     *
     * @param Request $request
     * @return array<string, mixed>
     */
    public function query(Request $request): iterable
    {
        $mailLog = MailLog::findOrFail($request->route('id'));
        return [
            'mailLog' => $mailLog,
        ];
    }

    /**
     * Action buttons for the screen.
     *
     * @return \Orchid\Screen\Action[]
     */
    public function commandBar(): iterable
    {
        return [
            Link::make(__('Back to Logs'))
                ->icon('bs.arrow-left')
                ->route('platform.mail.logs'),
        ];
    }

    /**
     * Layout elements for the screen.
     *
     * @return \Orchid\Screen\Layout[]
     */
    public function layout(): iterable
    {
        return [
            Layout::rows([
                Label::make('mailLog.id')->title(__('ID')),
                Label::make('mailLog.type')->title(__('Type')),
                Label::make('mailLog.class')->title(__('Class')),
                Label::make('mailLog.channel')->title(__('Channel')),
                Label::make('mailLog.subject')->title(__('Subject')),
                Label::make('mailLog.recipients')->title(__('Recipients'))
                    ->value(implode(', ', $this->mailLog->recipients ?? [])),
                Label::make('mailLog.cc')->title(__('CC'))
                    ->value(implode(', ', $this->mailLog->cc ?? [])),
                Label::make('mailLog.bcc')->title(__('BCC'))
                    ->value(implode(', ', $this->mailLog->bcc ?? [])),
                Label::make('mailLog.body')->title(__('Body'))
                    ->value(nl2br(e($this->mailLog->body))),
                Label::make('mailLog.sent_at')->title(__('Sent At')),
            ]),
        ];
    }
}
