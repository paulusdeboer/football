import { Head, Link, router, usePage } from '@inertiajs/react';
import FlashMessage from '../Components/FlashMessage';
import { useTranslations } from '../i18n';
import route from '../route';

export default function AppLayout({ title, children }) {
    const user = usePage().props.auth?.user;
    const can = usePage().props.auth?.can ?? {};
    const { t } = useTranslations();
    const pageTitle = title === 'Dashboard'
        ? `${t('Dashboard')} - ${t('Welcome')} ${user?.name ?? ''}`.trim()
        : t(title);

    const currentPath = typeof window === 'undefined' ? '' : window.location.pathname;
    const isActive = (section) => section === 'dashboard'
        ? currentPath === '/dashboard'
        : currentPath === `/${section}` || currentPath.startsWith(`/${section}/`);
    const isIndexActive = (section, excludedPaths = []) => isActive(section) && !excludedPaths.includes(currentPath);

    const logout = (event) => {
        event.preventDefault();
        router.post(route('logout'));
    };

    return (
        <>
            <Head title={pageTitle} />
            <div className="football-app">
                <div id="layoutSidenav">
                    <div id="layoutSidenav_nav">
                        <nav className="football-sidebar sb-sidenav accordion sb-sidenav-dark" id="sidenavAccordion">
                            <div className="football-sidebar__brand">
                                <Link className="app-brand" href={route('dashboard')}>
                                    <img className="app-brand-logo" src="/favicon.svg" alt="" aria-hidden="true" />
                                    <span>{t('app_name')}</span>
                                </Link>
                            </div>
                            <div className="sb-sidenav-menu football-sidebar__menu">
                                <div className="nav">
                                    {can.accessAdminArea && <>
                                        <Link className={`nav-link ${isActive('dashboard') ? 'active' : ''}`} href={route('dashboard')}><div className="sb-nav-link-icon"><i className="fas fa-tachometer-alt" /></div>{t('Dashboard')}</Link>
                                        <Link className={`nav-link ${isIndexActive('games', ['/games/create']) ? 'active' : ''}`} href={route('games.index')}><div className="sb-nav-link-icon"><i className="fas fa-futbol" /></div>{t('Games')}</Link>
                                        <Link className={`nav-link ${isActive('ratings') ? 'active' : ''}`} href={route('ratings.index')}><div className="sb-nav-link-icon"><i className="fas fa-star" /></div>{t('Given ratings')}</Link>
                                        <Link className={`nav-link ${currentPath === '/games/create' ? 'active' : ''}`} href={route('games.create')}><div className="sb-nav-link-icon"><i className="fas fa-plus" /></div>{t('Create game')}</Link>
                                        <Link className={`nav-link ${isIndexActive('players', ['/players/create']) ? 'active' : ''}`} href={route('players.index')}><div className="sb-nav-link-icon"><i className="fas fa-users" /></div>{t('Players')}</Link>
                                        <Link className={`nav-link ${currentPath === '/players/create' ? 'active' : ''}`} href={route('players.create')}><div className="sb-nav-link-icon"><i className="fas fa-user-plus" /></div>{t('Create players')}</Link>
                                        <Link className={`nav-link ${currentPath === '/settings/whatsapp' ? 'active' : ''}`} href={route('whatsapp.index')}><div className="sb-nav-link-icon"><i className="fab fa-whatsapp" /></div>{t('WhatsApp')}</Link>
                                    </>}
                                </div>
                            </div>
                            <div className="sb-sidenav-footer football-sidebar__footer"><div className="small">{t('Logged in as')}:</div>{user?.name}</div>
                        </nav>
                    </div>
                    <div id="layoutSidenav_content" className="football-workspace">
                        <header className="football-topbar">
                            <span className="football-topbar__context">{pageTitle}</span>
                            <ul className="navbar-nav">
                                <li className="nav-item dropdown">
                                    <button className="football-account-button nav-link dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                        <i className="fas fa-user fa-fw" /> <span>{user?.name}</span>
                                    </button>
                                    <ul className="dropdown-menu dropdown-menu-end">
                                        <li><button className="dropdown-item" onClick={logout}>{t('Logout')}</button></li>
                                    </ul>
                                </li>
                            </ul>
                        </header>
                        <main><FlashMessage />{children}</main>
                    </div>
                </div>
            </div>
        </>
    );
}
