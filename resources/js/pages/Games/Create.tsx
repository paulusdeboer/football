import { Head, useForm } from '@inertiajs/react';
import { PageProps } from '@/types';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Button } from '@/Components/ui/button';
import { Input } from '@/Components/ui/input';
import { Label } from '@/Components/ui/label';
import { 
  Select, 
  SelectContent, 
  SelectItem, 
  SelectTrigger, 
  SelectValue 
} from '@/Components/ui/select';
import { 
  Card, 
  CardContent, 
  CardHeader, 
  CardTitle 
} from '@/Components/ui/card';
import { FormEvent, useState } from 'react';
import { Player } from '@/types/models';

type CreatePageProps = {
  players: Player[];
};

export default function Create({ auth, players }: PageProps<CreatePageProps>) {
  const [team1Players, setTeam1Players] = useState<number[]>([]);
  const [team2Players, setTeam2Players] = useState<number[]>([]);

  const { data, setData, post, processing, errors } = useForm({
    played_at: new Date().toISOString().slice(0, 16),
    team1_players: [] as number[],
    team2_players: [] as number[],
    team1_score: '',
    team2_score: '',
  });

  const handleTeam1Change = (playerId: number) => {
    if (team1Players.includes(playerId)) {
      const newTeam = team1Players.filter(id => id !== playerId);
      setTeam1Players(newTeam);
      setData('team1_players', newTeam);
    } else {
      // Remove from team 2 if player is already there
      if (team2Players.includes(playerId)) {
        const newTeam2 = team2Players.filter(id => id !== playerId);
        setTeam2Players(newTeam2);
        setData('team2_players', newTeam2);
      }
      
      const newTeam = [...team1Players, playerId];
      setTeam1Players(newTeam);
      setData('team1_players', newTeam);
    }
  };

  const handleTeam2Change = (playerId: number) => {
    if (team2Players.includes(playerId)) {
      const newTeam = team2Players.filter(id => id !== playerId);
      setTeam2Players(newTeam);
      setData('team2_players', newTeam);
    } else {
      // Remove from team 1 if player is already there
      if (team1Players.includes(playerId)) {
        const newTeam1 = team1Players.filter(id => id !== playerId);
        setTeam1Players(newTeam1);
        setData('team1_players', newTeam1);
      }
      
      const newTeam = [...team2Players, playerId];
      setTeam2Players(newTeam);
      setData('team2_players', newTeam);
    }
  };

  const handleSubmit = (e: FormEvent) => {
    e.preventDefault();
    post(route('games.store'));
  };

  return (
    <AuthenticatedLayout
      user={auth.user}
      header={<h2 className="font-semibold text-xl text-gray-800 leading-tight">Create Game</h2>}
    >
      <Head title="Create Game" />

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
                      <Label htmlFor="team1_score">Score (optional)</Label>
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
                              team1Players.includes(player.id) 
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
                      <Label htmlFor="team2_score">Score (optional)</Label>
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
                              team2Players.includes(player.id) 
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
                  Create Game
                </Button>
              </div>
            </form>
          </div>
        </div>
      </div>
    </AuthenticatedLayout>
  );
}