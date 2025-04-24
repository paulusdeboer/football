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
  SelectValue,
} from '@/Components/ui/select';
import { FormEvent } from 'react';
import { Player } from '@/types/models';

type EditPageProps = {
  player: Player;
};

export default function Edit({ auth, player }: PageProps<EditPageProps>) {
  const { data, setData, patch, processing, errors } = useForm({
    name: player.name,
    type: player.type,
  });

  const handleSubmit = (e: FormEvent) => {
    e.preventDefault();
    patch(route('players.update', player.id));
  };

  return (
    <AuthenticatedLayout
      user={auth.user}
      header={<h2 className="font-semibold text-xl text-gray-800 leading-tight">Edit Player</h2>}
    >
      <Head title="Edit Player" />

      <div className="py-12">
        <div className="max-w-7xl mx-auto sm:px-6 lg:px-8">
          <div className="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
            <form onSubmit={handleSubmit} className="space-y-6">
              <div className="space-y-2">
                <Label htmlFor="name">Name</Label>
                <Input
                  id="name"
                  value={data.name}
                  onChange={(e) => setData('name', e.target.value)}
                  required
                />
                {errors.name && <p className="text-red-500 text-sm">{errors.name}</p>}
              </div>

              <div className="space-y-2">
                <Label htmlFor="type">Player Type</Label>
                <Select
                  defaultValue={data.type}
                  onValueChange={(value) => setData('type', value)}
                  required
                >
                  <SelectTrigger className="w-full">
                    <SelectValue placeholder="Select player type" />
                  </SelectTrigger>
                  <SelectContent>
                    <SelectItem value="attacker">Attacker</SelectItem>
                    <SelectItem value="defender">Defender</SelectItem>
                    <SelectItem value="both">Both</SelectItem>
                  </SelectContent>
                </Select>
                {errors.type && <p className="text-red-500 text-sm">{errors.type}</p>}
              </div>

              <div className="space-y-2">
                <Label htmlFor="rating">Current Rating</Label>
                <Input
                  id="rating"
                  value={player.rating}
                  disabled
                  className="bg-gray-100"
                />
              </div>

              <div className="flex justify-end gap-3">
                <Button type="button" variant="outline" onClick={() => window.history.back()}>
                  Cancel
                </Button>
                <Button type="submit" disabled={processing}>
                  Update Player
                </Button>
              </div>
            </form>
          </div>
        </div>
      </div>
    </AuthenticatedLayout>
  );
}