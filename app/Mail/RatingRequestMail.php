<?php

namespace App\Mail;

use App\Models\Game;
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
        return $this->subject('Rate your fellow players')
            ->view('emails.rating_request')
            ->with(['url' => $this->url]);
    }
}
