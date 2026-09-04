<?php

namespace App\Mail;

use App\Models\Game;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class RatingRequestMail extends Mailable
{
    use Queueable, SerializesModels;

    public $game;
    public $url;

    public function __construct(Game $game, $url)
    {
        $this->game = $game;
        $this->url = $url;
    }

    public function build()
    {
        return $this->subject(__('Rate your fellow players'))
            ->from(config('mail.from.address'), 'Vrijdag voetbal')
            ->view('emails.rating_request')
            ->text('emails.rating_request_plain')
            ->with([
                'url' => $this->url,
                'logoUrl' => asset('favicon.svg'),
                'gameDate' => Carbon::parse($this->game->played_at)->format('d-m-Y'),
            ]);
    }
}
