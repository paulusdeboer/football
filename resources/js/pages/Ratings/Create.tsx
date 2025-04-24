import { Head, useForm } from '@inertiajs/react';
import { PageProps } from '@/types';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Button } from '@/Components/ui/button';
import { 
  Card, 
  CardContent, 
  CardHeader, 
  CardTitle 
} from '@/Components/ui/card';
import { 
  Slider 
} from '@/Components/ui/slider';
import { Game, Player } from '@/types/models';
import { FormEvent, useState } from 'react';
import { formatDate } from '@/lib/utils';

type CreatePageProps = {
  game: Game;
  players: Player[];
  currentPlayer: Player;
};

export default function Create({ auth, game, players, currentPlayer }: PageProps<CreatePageProps>) {
  const [ratings, setRatings] = useState<Record<number, number>>({});

  const { data, setData, post, processing, errors } = useForm({
    ratings: [] as { player_id: number; value: number }[],
  });

  const handleRatingChange = (playerId: number, value: number[]) => {
    const newRatings = { ...ratings, [playerId]: value[0] };
    setRatings(newRatings);
    
    const formattedRatings = Object.entries(newRatings).map(([id, value]) => ({
      player_id: parseInt(id),
      value,
    }));
    
    setData('ratings', formattedRatings);
  };

  const handleSubmit = (e: FormEvent) => {
    e.preventDefault();
    post(route('ratings.store', game.id));
  };

  if (!currentPlayer) {
    return (
      <AuthenticatedLayout
        user={auth.user}
        header={<h2 className="font-semibold text-xl text-gray-800 leading-tight">Rate Players</h2>}
      >
        <Head title="Rate Players" />
        <div className="py-12">
          <div className="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div className="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
              <div className="p-4 bg-yellow-50 text-yellow-800 border border-yellow-200 rounded">
                <p>You need to create a player profile before you can rate other players.</p>
                <div className="mt-4">
                  <Button asChild>
                    <a href={route('players.create')}>Create Player Profile</a>
                  </Button>
                </div>
              </div>
            </div>
          </div>
        </div>
      </AuthenticatedLayout>
    );
  }

  return (
    <AuthenticatedLayout
      user={auth.user}
      header={<h2 className="font-semibold text-xl text-gray-800 leading-tight">Rate Players</h2>}
    >
      <Head title="Rate Players" />

      <div className="py-12">
        <div className="max-w-7xl mx-auto sm:px-6 lg:px-8">
          <div className="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
            <div className="mb-6">
              <h3 className="text-lg font-medium">Rate Players for Game on {formatDate(game.played_at)}</h3>
              <p className="text-gray-500 mt-1">
                You are rating as: <strong>{currentPlayer.name}</strong>
              </p>
            </div>
            
            <form onSubmit={handleSubmit} className="space-y-6">
              <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                {players
                  .filter(player => player.id !== currentPlayer.id) // Don't rate yourself
                  .map(player => (
                    <Card key={player.id}>
                      <CardHeader>
                        <CardTitle>{player.name}</CardTitle>
                      </CardHeader>
                      <CardContent>
                        <div className="space-y-4">
                          <div className="flex items-center justify-between">
                            <span>Rating (1-10):</span>
                            <span className="font-bold text-lg">
                              {ratings[player.id] || 5}
                            </span>
                          </div>
                          <Slider
                            defaultValue={[5]}
                            max={10}
                            min={1}
                            step={1}
                            onValueChange={(value) => handleRatingChange(player.id, value)}
                          />
                        </div>
                      </CardContent>
                    </Card>
                  ))}
              </div>
              
              {errors.ratings && (
                <div className="text-red-500 mt-2">{errors.ratings}</div>
              )}
              
              <div className="flex justify-end gap-3">
                <Button type="button" variant="outline" onClick={() => window.history.back()}>
                  Cancel
                </Button>
                <Button type="submit" disabled={processing}>
                  Submit Ratings
                </Button>
              </div>
            </form>
          </div>
        </div>
      </div>
    </AuthenticatedLayout>
  );
}