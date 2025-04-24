import { AppContent } from '@/Components/app-content';
import { AppShell } from '@/Components/app-shell';
import { AppSidebar } from '@/Components/app-sidebar';
import { AppSidebarHeader } from '@/Components/app-sidebar-header';
import { type PropsWithChildren, ReactNode } from 'react';
import { Link } from '@inertiajs/react';
import { User } from '@/types/models';

interface AuthenticatedLayoutProps {
  user: User;
  header?: ReactNode;
  children: ReactNode;
}

export default function AuthenticatedLayout({ user, header, children }: AuthenticatedLayoutProps) {
  return (
    <AppShell variant="sidebar">
      <div className="bg-white py-2 px-4 shadow-sm">
        <div className="max-w-7xl mx-auto flex justify-between items-center">
          <Link href="/">
            <h1 className="text-xl font-bold">Football Rating</h1>
          </Link>
          <div className="flex items-center gap-2">
            <span className="text-sm text-gray-600">Welkom, {user.name}</span>
          </div>
        </div>
      </div>

      <aside className="w-64 bg-gray-100 h-screen fixed left-0 top-0 pt-16 shadow-sm">
        <div className="p-4">
          <nav className="space-y-1">
            <Link 
              href={route('dashboard')}
              className="block px-3 py-2 rounded-md hover:bg-gray-200 transition-colors"
            >
              Dashboard
            </Link>
            <Link 
              href={route('players.index')}
              className="block px-3 py-2 rounded-md hover:bg-gray-200 transition-colors"
            >
              Players
            </Link>
            <Link 
              href={route('games.index')}
              className="block px-3 py-2 rounded-md hover:bg-gray-200 transition-colors"
            >
              Games
            </Link>
            <Link 
              href={route('ratings.index')}
              className="block px-3 py-2 rounded-md hover:bg-gray-200 transition-colors"
            >
              Ratings
            </Link>
            <hr className="my-2 border-gray-300" />
            <Link 
              href={route('profile.edit')}
              className="block px-3 py-2 rounded-md hover:bg-gray-200 transition-colors"
            >
              Profile
            </Link>
            <Link 
              href={route('logout')} 
              method="post" 
              as="button"
              className="w-full text-left px-3 py-2 rounded-md hover:bg-gray-200 transition-colors"
            >
              Log Out
            </Link>
          </nav>
        </div>
      </aside>

      <main className="ml-64 pt-4 px-4 pb-12 min-h-screen">
        {header && (
          <header className="mb-6">
            <div className="max-w-7xl mx-auto">
              {header}
            </div>
          </header>
        )}
        {children}
      </main>
    </AppShell>
  );
}