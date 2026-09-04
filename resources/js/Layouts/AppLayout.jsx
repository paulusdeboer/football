import { Head, Link, router, usePage } from '@inertiajs/react';
import FlashMessage from '../Components/FlashMessage';
import { useTranslations } from '../i18n';
import route from '../route';

export default function AppLayout({ title, children }) {
    const user = usePage().props.auth?.user;
    const { t } = useTranslations();

    const logout = (event) => {
        event.preventDefault();
        router.post(route('logout'));
    };

    return (
        <>
            <Head title={t(title)} />
            <nav className="sb-topnav navbar navbar-expand navbar-dark bg-dark">
                <Link className="navbar-brand ps-3 app-brand" href={route('dashboard')}>
                    <img className="app-brand-logo" src="/favicon.svg" alt="" aria-hidden="true" />
                    <span>{t('app_name')}</span>
                </Link>
                <button className="btn btn-link btn-sm order-1 order-lg-0 me-4 me-lg-0" id="sidebarToggle" type="button">
                    <i className="fas fa-bars" />
                </button>
                <ul className="navbar-nav ms-auto me-3 me-lg-4">
                    <li className="nav-item dropdown">
                        <button className="nav-link dropdown-toggle" type="button" data-bs-toggle="dropdown">
                            <i className="fas fa-user fa-fw" /> {user?.name}
                        </button>
                        <ul className="dropdown-menu dropdown-menu-end">
                            <li><button className="dropdown-item" onClick={logout}>{t('Logout')}</button></li>
                        </ul>
                    </li>
                </ul>
            </nav>
            <div id="layoutSidenav">
                <div id="layoutSidenav_nav">
                    <nav className="sb-sidenav accordion sb-sidenav-dark" id="sidenavAccordion">
                        <div className="sb-sidenav-menu">
                            <div className="nav">
                                <div className="sb-sidenav-menu-heading">{t('Core')}</div>
                                <Link className="nav-link" href={route('dashboard')}><div className="sb-nav-link-icon"><i className="fas fa-tachometer-alt" /></div>{t('Dashboard')}</Link>
                                <div className="sb-sidenav-menu-heading">{t('Football')}</div>
                                <Link className="nav-link" href={route('games.index')}><div className="sb-nav-link-icon"><i className="fas fa-futbol" /></div>{t('Games')}</Link>
                                <Link className="nav-link" href={route('ratings.index')}><div className="sb-nav-link-icon"><i className="fas fa-star" /></div>{t('Given ratings')}</Link>
                                <Link className="nav-link" href={route('games.create')}><div className="sb-nav-link-icon"><i className="fas fa-plus" /></div>{t('Create game')}</Link>
                                <Link className="nav-link" href={route('players.index')}><div className="sb-nav-link-icon"><i className="fas fa-users" /></div>{t('Players')}</Link>
                                <Link className="nav-link" href={route('players.create')}><div className="sb-nav-link-icon"><i className="fas fa-user-plus" /></div>{t('Create players')}</Link>
                            </div>
                        </div>
                        <div className="sb-sidenav-footer"><div className="small">{t('Logged in as')}:</div>{user?.name}</div>
                    </nav>
                </div>
                <div id="layoutSidenav_content">
                    <main><FlashMessage />{children}</main>
                    <footer className="py-4 bg-light mt-auto">
                        <div className="container-fluid px-4">
                            <div className="small text-muted app-footer-brand">
                                <img className="app-footer-logo" src="/favicon.svg" alt="" aria-hidden="true" />
                                <span>{t('app_name')}</span>
                            </div>
                        </div>
                    </footer>
                </div>
            </div>
        </>
    );
}
