import { ReactNode } from 'react';

interface AppSidebarProps {
  children?: ReactNode;
}

export function AppSidebar({ children }: AppSidebarProps) {
  return (
    <aside className="w-64 bg-white border-r border-gray-200 fixed inset-y-0 left-0">
      {children}
    </aside>
  );
}
