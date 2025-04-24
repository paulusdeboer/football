import { ReactNode } from 'react';

interface AppSidebarHeaderProps {
  children?: ReactNode;
  breadcrumbs?: { title: string; href?: string }[];
}

export function AppSidebarHeader({ children, breadcrumbs = [] }: AppSidebarHeaderProps) {
  return (
    <header className="mb-6">
      {breadcrumbs.length > 0 && (
        <nav className="mb-2 text-sm">
          <ul className="flex gap-1">
            {breadcrumbs.map((breadcrumb, index) => (
              <li key={index} className="flex items-center">
                {index > 0 && <span className="mx-1 text-gray-400">/</span>}
                {breadcrumb.href ? (
                  <a href={breadcrumb.href} className="text-gray-600 hover:text-gray-900">
                    {breadcrumb.title}
                  </a>
                ) : (
                  <span className="text-gray-800 font-medium">{breadcrumb.title}</span>
                )}
              </li>
            ))}
          </ul>
        </nav>
      )}
      {children}
    </header>
  );
}
