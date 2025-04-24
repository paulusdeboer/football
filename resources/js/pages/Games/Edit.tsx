import { Head, useForm } from '@inertiajs/react';
import { PageProps } from '@/types';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Button } from '@/Components/ui/button';
import { Input } from '@/Components/ui/input';
import { Label } from '@/Components/ui/label';
import { 
  Card, 
  CardContent, 
  CardHeader, 
  CardTitle 
} from '@/Components/ui/card';
import { FormEvent, useState, useEffect } from 'react';
import { Player, Game } from '@/types/models';

type EditPageProps = {
  game: Game;
  team1Players: number[];
  team2Players: number[];
  players: Player[];
};

export default function Edit({ auth, game, team1Players, team2Players, players }: PageProps<EditPageProps>) {
  const [selectedTeam1Players, setSelectedTeam1Players] = useState<number[]>(team1Players);
  const [selectedTeam2Players, setSelectedTeam2Players] = useState<number[]>(team2Players);

  const { data, setData, patch, processing, errors } = useForm({
    played_at: game.played_at.substring(0, 16),
    team1_players: team1Players,
    team2_players: team2Players,
    team1_score: game.team1_score?.toString() || '',
    team2_score: game.team2_score?.toString() || '',
  });

  const handleTeam1Change = (playerId: number) => {
    if (selectedTeam1Players.includes(playerId)) {
      const newTeam = selectedTeam1Players.filter(id => id !== playerId);
      setSelectedTeam1Players(newTeam);
      setData('team1_players', newTeam);
    } else {
      // Remove from team 2 if player is already there
      if (selectedTeam2Players.includes(playerId)) {
        const newTeam2 = selectedTeam2Players.filter(id => id !== playerId);
        setSelectedTeam2Players(newTeam2);
        setData('team2_players', newTeam2);
      }
      
      const newTeam = [...selectedTeam1Players, playerId];
      setSelectedTeam1Players(newTeam);
      setData('team1_players', newTeam);
    }
  };

  const handleTeam2Change = (playerId: number) => {
    if (selectedTeam2Players.includes(playerId)) {
      const newTeam = selectedTeam2Players.filter(id => id !== playerId);
      setSelectedTeam2Players(newTeam);
      setData('team2_players', newTeam);
    } else {
      // Remove from team 1 if player is already there
      if (selectedTeam1Players.includes(playerId)) {
        const newTeam1 = selectedTeam1Players.filter(id => id !== playerId);
        setSelectedTeam1Players(newTeam1);
        setData('team1_players', newTeam1);
      }
      
      const newTeam = [...selectedTeam2Players, playerId];
      setSelectedTeam2Players(newTeam);
      setData('team2_players', newTeam);
    }
  };

  const handleSubmit = (e: FormEvent) => {
    e.preventDefault();
    patch(route('games.update', game.id));
  };

  return (
    <AuthenticatedLayout
      user={auth.user}
      header={<h2 className="font-semibold text-xl text-gray-800 leading-tight">Edit Game</h2>}
    >
      <Head title="Edit Game" />

      <div className="py-12">
        <div className="max-w-7xl mx-auto sm:px-6 lg:px-8">
          <div className="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
            <form onSubmit={handleSubmit} className="space-y-6">
              <div className="space-y-2">
                <Label htmlFor="played_at">Date and Time</Label>
                <Input
                  id="played_at"
                  type="datetime-local"
                  value={data.played_at}
                  onChange={(e) => setData('played_at', e.target.value)}
                  required
                />
                {errors.played_at && <p className="text-red-500 text-sm">{errors.played_at}</p>}
              </div>

              <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                <Card>
                  <CardHeader>
                    <CardTitle>Team 1</CardTitle>
                  </CardHeader>
                  <CardContent className="space-y-4">
                    <div className="space-y-2">
                      <Label htmlFor="team1_score">Score</Label>
                      <Input
                        id="team1_score"
                        type="number"
                        min="0"
                        value={data.team1_score}
                        onChange={(e) => setData('team1_score', e.target.value)}
                      />
                      {errors.team1_score && <p className="text-red-500 text-sm">{errors.team1_score}</p>}
                    </div>

                    <div>
                      <Label>Select Players</Label>
                      <div className="grid grid-cols-2 sm:grid-cols-3 gap-2 mt-2">
                        {players.map((player) => (
                          <div 
                            key={`team1-${player.id}`}
                            className={`p-2 border rounded cursor-pointer text-center ${
                              selectedTeam1Players.includes(player.id) 
                                ? 'bg-primary text-primary-foreground' 
                                : 'bg-background'
                            }`}
                            onClick={() => handleTeam1Change(player.id)}
                          >
                            {player.name}
                          </div>
                        ))}
                      </div>
                      {errors.team1_players && <p className="text-red-500 text-sm mt-2">{errors.team1_players}</p>}
                    </div>
                  </CardContent>
                </Card>

                <Card>
                  <CardHeader>
                    <CardTitle>Team 2</CardTitle>
                  </CardHeader>
                  <CardContent className="space-y-4">
                    <div className="space-y-2">
                      <Label htmlFor="team2_score">Score</Label>
                      <Input
                        id="team2_score"
                        type="number"
                        min="0"
                        value={data.team2_score}
                        onChange={(e) => setData('team2_score', e.target.value)}
                      />
                      {errors.team2_score && <p className="text-red-500 text-sm">{errors.team2_score}</p>}
                    </div>

                    <div>
                      <Label>Select Players</Label>
                      <div className="grid grid-cols-2 sm:grid-cols-3 gap-2 mt-2">
                        {players.map((player) => (
                          <div 
                            key={`team2-${player.id}`}
                            className={`p-2 border rounded cursor-pointer text-center ${
                              selectedTeam2Players.includes(player.id) 
                                ? 'bg-primary text-primary-foreground' 
                                : 'bg-background'
                            }`}
                            onClick={() => handleTeam2Change(player.id)}
                          >
                            {player.name}
                          </div>
                        ))}
                      </div>
                      {errors.team2_players && <p className="text-red-500 text-sm mt-2">{errors.team2_players}</p>}
                    </div>
                  </CardContent>
                </Card>
              </div>

              <div className="flex justify-end gap-3">
                <Button type="button" variant="outline" onClick={() => window.history.back()}>
                  Cancel
                </Button>
                <Button type="submit" disabled={processing}>
                  Update Game
                </Button>
              </div>
            </form>
          </div>
        </div>
      </div>
    </AuthenticatedLayout>
  );
}