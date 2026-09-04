# Application inventory

## Current runtime

- Laravel 11 on PHP 8.3, MariaDB/MySQL, Vite and Bootstrap 5.3.
- Authentication currently uses the `users` table and the username (`name`) as login field.
- Sessions, cache and queue are database-backed in production configuration; rating request mail is sent synchronously.
- Production deployment runs through Plesk's Git/deployment tooling.

## Domain behavior

### Players

- A player belongs to one user and stores its rating as an integer multiplied by 100.
- Positions are `attacker`, `defender` or `both`.
- Players can be soft-deleted and restored; deleting a player also soft-deletes its user.
- Creating players accepts multiple rows and creates users with the existing default password behavior.
- The list supports sorting by name, e-mail, rating, type and creation date and remembers filters in the session.

### Games

- A game contains 10–12 players, a date and optional scores.
- Player ratings are snapshotted in `game_player_ratings` when the game is created.
- Teams are assigned as `team1` and `team2` using position-aware balancing and rating optimization.
- Entering a result recalculates ratings and can create up to three rating requests.
- Updating a game rebuilds its team links and game-player snapshots.

### Ratings

- Players rate other players through a signed, temporary link.
- A rating request is valid for 72 hours and a rating player may submit only once per game.
- Administrators can resend or replace requests from the game detail page; each request keeps a lifecycle and audit history.
- Ratings are stored on a 0–10 scale with one decimal place.
- Rating calculation uses the game result, the snapshot rating and the average submitted rating.

## Data baseline

The local production snapshot contains 90 users, 89 players, 70 games, 834 team links, 834 snapshots, 1,458 ratings and 210 rating requests. It contains 22 soft-deleted players/users, one user without a player and two historically uneven games. These records are preserved and treated as parity fixtures.

## Known compatibility notes

- The dashboard currently uses hardcoded example chart data.
- The browser-side rating minimum differs from the backend validation.
- Current result validation is weaker than the browser form.
- The generic `game_player_ratings` resource controller is empty and has no user-facing flow.
- The old legacy SQL dump contains the former Dutch schema and is separate from the current application database.
