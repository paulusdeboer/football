import { Head, Link } from '@inertiajs/react';
import { PageProps } from '@/types';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Card, CardContent, CardHeader, CardTitle } from '@/Components/ui/card';

export default function Dashboard({ auth }: PageProps) {
  return (
    <AuthenticatedLayout
      user={auth.user}
      header={<h2 className="font-semibold text-xl text-gray-800 leading-tight">Dashboard</h2>}
    >
      <Head title="Dashboard" />

      <div className="py-12">
        <div className="max-w-7xl mx-auto sm:px-6 lg:px-8">
          <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <Link href={route('players.index')}>
              <Card className="hover:shadow-lg transition-shadow">
                <CardHeader>
                  <CardTitle>Spelers</CardTitle>
                </CardHeader>
                <CardContent>
                  <p>Beheer spelers, bekijk ratings en voeg nieuwe spelers toe.</p>
                </CardContent>
              </Card>
            </Link>

            <Link href={route('games.index')}>
              <Card className="hover:shadow-lg transition-shadow">
                <CardHeader>
                  <CardTitle>Wedstrijden</CardTitle>
                </CardHeader>
                <CardContent>
                  <p>Plan wedstrijden, stel teams samen en houd scores bij.</p>
                </CardContent>
              </Card>
            </Link>

            <Link href={route('ratings.index')}>
              <Card className="hover:shadow-lg transition-shadow">
                <CardHeader>
                  <CardTitle>Beoordelingen</CardTitle>
                </CardHeader>
                <CardContent>
                  <p>Beoordeel spelers na afloop van wedstrijden.</p>
                </CardContent>
              </Card>
            </Link>
          </div>

          <div className="mt-8 bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
            <h3 className="text-lg font-medium mb-4">Welkom bij het Football Rating Systeem</h3>
            <p className="mb-4">
              Dit systeem helpt bij het organiseren van voetbalwedstrijden en het bijhouden van spelerratings op basis van beoordelingen.
            </p>
            <h4 className="font-medium mt-6 mb-2">Hoe het werkt:</h4>
            <ol className="list-decimal pl-5 space-y-2">
              <li>Maak spelers aan en wijs ze een type toe (aanvaller, verdediger of beide)</li>
              <li>Creëer wedstrijden en verdeel spelers over twee teams</li>
              <li>Voer de eindstand in na afloop van de wedstrijd</li>
              <li>Laat spelers elkaar beoordelen voor hun prestaties</li>
              <li>Het systeem berekent automatisch nieuwe ratings op basis van de beoordelingen</li>
            </ol>
          </div>
        </div>
      </div>
    </AuthenticatedLayout>
  );
}
