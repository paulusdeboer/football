import { Head, Link } from '@inertiajs/react';
import { PageProps } from '@/types';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Button } from '@/Components/ui/button';
import { 
  Card, 
  CardContent, 
  CardHeader, 
  CardTitle 
} from '@/Components/ui/card';
import { formatDate } from '@/lib/utils';
import { Game, Player } from '@/types/models';

type ShowPageProps = {
  game: Game;
  team1Players: Player[];
  team2Players: Player[];
};

export default function Show({ auth, game, team1Players, team2Players }: PageProps<ShowPageProps>) {
  return (
    <AuthenticatedLayout
      user={auth.user}
      header={<h2 className="font-semibold text-xl text-gray-800 leading-tight">Game Details</h2>}
    >
      <Head title="Game Details" />

      <div className="py-12">
        <div className="max-w-7xl mx-auto sm:px-6 lg:px-8">
          <div className="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
            <div className="flex justify-between items-center mb-6">
              <h3 className="text-lg font-medium">Game on {formatDate(game.played_at)}</h3>
              <div className="flex gap-2">
                <Link href={route('ratings.create', game.id)}>
                  <Button>Rate Players</Button>
                </Link>
                <Link href={route('games.edit', game.id)}>
                  <Button variant="outline">Edit Game</Button>
                </Link>
                <Link href={route('games.index')}>
                  <Button variant="outline">Back to Games</Button>
                </Link>
              </div>
            </div>
            
            <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
              <Card>
                <CardHeader>
                  <CardTitle className="flex justify-between">
                    <span>Team 1</span>
                    {game.team1_score !== null && (
                      <span className="text-xl font-bold">{game.team1_score}</span>
                    )}
                  </CardTitle>
                </CardHeader>
                <CardContent>
                  <ul className="space-y-2">
                    {team1Players.map((player) => (
                      <li key={player.id} className="flex justify-between items-center p-2 border-b">
                        <span>{player.name}</span>
                        <span className="text-gray-500">
                          Rating: {player.rating}
                        </span>
                      </li>
                    ))}
                  </ul>
                </CardContent>
              </Card>

              <Card>
                <CardHeader>
                  <CardTitle className="flex justify-between">
                    <span>Team 2</span>
                    {game.team2_score !== null && (
                      <span className="text-xl font-bold">{game.team2_score}</span>
                    )}
                  </CardTitle>
                </CardHeader>
                <CardContent>
                  <ul className="space-y-2">
                    {team2Players.map((player) => (
                      <li key={player.id} className="flex justify-between items-center p-2 border-b">
                        <span>{player.name}</span>
                        <span className="text-gray-500">
                          Rating: {player.rating}
                        </span>
                      </li>
                    ))}
                  </ul>
                </CardContent>
              </Card>
            </div>

            {game.team1_score === null && game.team2_score === null && (
              <div className="mt-6 p-4 bg-yellow-50 border border-yellow-200 rounded text-center">
                <p>No score has been added for this game yet.</p>
              </div>
            )}
          </div>
        </div>
      </div>
    </AuthenticatedLayout>
  );
}