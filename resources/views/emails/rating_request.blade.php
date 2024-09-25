<p>{{ __('Hello') }},</p>

<p>{{ __('email.rate_players_for_game', ['game' => $game->played_at]) }}</p>

<p>{{ __('Please use the following link to submit your ratings:') }}</p>

<a href="{{ $url }}">{{ __('Rate Players') }}</a>
