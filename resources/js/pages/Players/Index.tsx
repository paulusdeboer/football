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
import { Player } from '@/types/models';

type PlayersPageProps = {
  players: Player[];
};

export default function Index({ auth, players }: PageProps<PlayersPageProps>) {
  const handleDelete = (id: number) => {
    if (confirm('Are you sure you want to delete this player?')) {
      router.delete(route('players.destroy', id));
    }
  };

  return (
    <AuthenticatedLayout
      user={auth.user}
      header={<h2 className="font-semibold text-xl text-gray-800 leading-tight">Players</h2>}
    >
      <Head title="Players" />

      <div className="py-12">
        <div className="max-w-7xl mx-auto sm:px-6 lg:px-8">
          <div className="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
            <div className="flex justify-between items-center mb-6">
              <h3 className="text-lg font-medium">All Players</h3>
              <Link href={route('players.create')}>
                <Button>Add New Player</Button>
              </Link>
            </div>
            
            <Table>
              <TableCaption>A list of all players</TableCaption>
              <TableHeader>
                <TableRow>
                  <TableHead>Name</TableHead>
                  <TableHead>Type</TableHead>
                  <TableHead>Rating</TableHead>
                  <TableHead>Actions</TableHead>
                </TableRow>
              </TableHeader>
              <TableBody>
                {players.map((player) => (
                  <TableRow key={player.id}>
                    <TableCell>{player.name}</TableCell>
                    <TableCell className="capitalize">{player.type}</TableCell>
                    <TableCell>{player.rating}</TableCell>
                    <TableCell className="flex space-x-2">
                      <Link href={route('players.edit', player.id)}>
                        <Button variant="outline" size="sm">Edit</Button>
                      </Link>
                      <Button 
                        variant="destructive" 
                        size="sm" 
                        onClick={() => handleDelete(player.id)}
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