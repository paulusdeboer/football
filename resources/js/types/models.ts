export interface User {
  id: number;
  name: string;
  email: string;
  email_verified_at: string | null;
}

export interface Player {
  id: number;
  name: string;
  rating: number;
  previous_rating: number | null;
  type: 'attacker' | 'defender' | 'both';
  user_id: number;
  created_at: string;
  updated_at: string;
  deleted_at: string | null;
  user?: User;
}

export interface Game {
  id: number;
  played_at: string;
  team1_score: number | null;
  team2_score: number | null;
  created_at: string;
  updated_at: string;
  teams?: Player[];
}

export interface Rating {
  id: number;
  game_id: number;
  rated_player_id: number;
  rating_player_id: number;
  rating_value: number;
  created_at: string;
  updated_at: string;
  ratedPlayer?: Player;
  ratingPlayer?: Player;
  game?: Game;
}