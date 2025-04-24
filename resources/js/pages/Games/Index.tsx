import { Head, Link, router } from '@inertiajs/react';
import { PageProps } from '@/types';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Button } from '@/Components/ui/button';
import { 
  Table, 
  TableBody, 
  TableCaption, 
  TableCell, 
  TableHead, 
  TableHeader, 
  TableRow 
} from '@/Components/ui/table';
import { Game } from '@/types/models';
import { formatDate } from '@/lib/utils';

type GamesPageProps = {
  games: Game[];
};

export default function Index({ auth, games }: PageProps<GamesPageProps>) {
  const handleDelete = (id: number) => {
    if (confirm('Are you sure you want to delete this game?')) {
      router.delete(route('games.destroy', id));
    }
  };

  return (
    <AuthenticatedLayout
      user={auth.user}
      header={<h2 className="font-semibold text-xl text-gray-800 leading-tight">Games</h2>}
    >
      <Head title="Games" />

      <div className="py-12">
        <div className="max-w-7xl mx-auto sm:px-6 lg:px-8">
          <div className="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
            <div className="flex justify-between items-center mb-6">
              <h3 className="text-lg font-medium">All Games</h3>
              <Link href={route('games.create')}>
                <Button>Add New Game</Button>
              </Link>
            </div>
            
            <Table>
              <TableCaption>A list of all games</TableCaption>
              <TableHeader>
                <TableRow>
                  <TableHead>Date</TableHead>
                  <TableHead>Score</TableHead>
                  <TableHead>Actions</TableHead>
                </TableRow>
              </TableHeader>
              <TableBody>
                {games.map((game) => (
                  <TableRow key={game.id}>
                    <TableCell>{formatDate(game.played_at)}</TableCell>
                    <TableCell>
                      {game.team1_score !== null && game.team2_score !== null
                        ? `${game.team1_score} - ${game.team2_score}`
                        : 'No score yet'}
                    </TableCell>
                    <TableCell className="flex space-x-2">
                      <Link href={route('games.show', game.id)}>
                        <Button variant="outline" size="sm">View</Button>
                      </Link>
                      <Link href={route('games.edit', game.id)}>
                        <Button variant="outline" size="sm">Edit</Button>
                      </Link>
                      <Link href={route('ratings.create', game.id)}>
                        <Button variant="outline" size="sm">Rate</Button>
                      </Link>
                      <Button 
                        variant="destructive" 
                        size="sm" 
                        onClick={() => handleDelete(game.id)}
                      >
                        Delete
                      </Button>
                    </TableCell>
                  </TableRow>
                ))}
              </TableBody>
            </Table>
          </div>
        </div>
      </div>
    </AuthenticatedLayout>
  );
}