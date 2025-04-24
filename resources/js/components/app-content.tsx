import { cn } from '@/lib/utils';
import { ReactNode } from 'react';

interface AppContentProps {
  children: ReactNode;
  variant?: 'sidebar' | 'header';
}

export function AppContent({ children, variant = 'header' }: AppContentProps) {
  return (
    <div className={cn(
      'flex-1',
      variant === 'sidebar' ? 'pl-64' : ''
    )}>
      {children}
    </div>
  );
}
