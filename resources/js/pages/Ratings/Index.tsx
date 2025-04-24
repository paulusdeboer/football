import { Head, Link } from '@inertiajs/react';
import { PageProps } from '@/types';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { 
  Table, 
  TableBody, 
  TableCaption, 
  TableCell, 
  TableHead, 
  TableHeader, 
  TableRow 
} from '@/Components/ui/table';
import { Rating } from '@/types/models';
import { formatDate } from '@/lib/utils';

type RatingsPageProps = {
  ratings: Rating[];
};

export default function Index({ auth, ratings }: PageProps<RatingsPageProps>) {
  return (
    <AuthenticatedLayout
      user={auth.user}
      header={<h2 className="font-semibold text-xl text-gray-800 leading-tight">Ratings</h2>}
    >
      <Head title="Ratings" />

      <div className="py-12">
        <div className="max-w-7xl mx-auto sm:px-6 lg:px-8">
          <div className="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
            <h3 className="text-lg font-medium mb-6">All Player Ratings</h3>
            
            <Table>
              <TableCaption>A list of all player ratings</TableCaption>
              <TableHeader>
                <TableRow>
                  <TableHead>Game Date</TableHead>
                  <TableHead>Rated Player</TableHead>
                  <TableHead>Rated By</TableHead>
                  <TableHead>Rating</TableHead>
                </TableRow>
              </TableHeader>
              <TableBody>
                {ratings.map((rating) => (
                  <TableRow key={rating.id}>
                    <TableCell>
                      <Link 
                        href={route('games.show', rating.game?.id)} 
                        className="text-blue-600 hover:underline"
                      >
                        {rating.game ? formatDate(rating.game.played_at) : 'Unknown game'}
                      </Link>
                    </TableCell>
                    <TableCell>{rating.ratedPlayer?.name || 'Unknown player'}</TableCell>
                    <TableCell>{rating.ratingPlayer?.name || 'Unknown player'}</TableCell>
                    <TableCell>{rating.rating_value}</TableCell>
                  </TableRow>
                ))}
                {ratings.length === 0 && (
                  <TableRow>
                    <TableCell colSpan={4} className="text-center py-4">
                      No ratings found
                    </TableCell>
                  </TableRow>
                )}
              </TableBody>
            </Table>
          </div>
        </div>
      </div>
    </AuthenticatedLayout>
  );
}