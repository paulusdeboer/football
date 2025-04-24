import { cn } from '@/lib/utils';
import { ReactNode } from 'react';

interface AppShellProps {
  children: ReactNode;
  variant?: 'sidebar' | 'header';
}

export function AppShell({ children, variant = 'header' }: AppShellProps) {
  return (
    <div className={cn(
      'min-h-screen bg-gray-50',
      variant === 'sidebar' ? 'flex' : ''
    )}>
      {children}
    </div>
  );
}
