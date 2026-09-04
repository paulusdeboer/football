import { Link, router } from '@inertiajs/react';
import AppLayout from '../../Layouts/AppLayout';
import RoleBadge from '../../Components/RoleBadge';
import { useTranslations } from '../../i18n';
import route from '../../route';

const date = (value) => value ? new Date(value).toLocaleDateString('nl-NL') : '';

export default function PlayersIndex({ players, sortBy, sortDirection, includeDeleted }) {
    const { t } = useTranslations();

    const sort = (column) => router.get(
        route('players.index'),
        {
            sort_by: column,
            sort_direction: sortBy === column && sortDirection === 'asc' ? 'desc' : 'asc',
            include_deleted: includeDeleted,
        },
        { preserveState: true },
    );

    const toggleDeleted = () => router.get(
        route('players.index'),
        {
            sort_by: sortBy,
            sort_direction: sortDirection,
            include_deleted: includeDeleted === '1' ? '0' : '1',
        },
        { preserveState: true },
    );

    const remove = (player) => {
        if (window.confirm(`${t('Delete')} ${player.name}?`)) {
            router.delete(route('players.destroy', player.id));
        }
    };

    const restore = (player) => router.patch(route('players.restore', player.id));
    const arrow = (column) => sortBy === column
        ? <i className={`fas fa-chevron-${sortDirection === 'asc' ? 'up' : 'down'} table-sort-icon`} aria-hidden="true" />
        : null;
    const openPlayer = (event, playerId) => {
        if (event.target.closest('a, button, select, input, textarea, form')) return;

        router.visit(route('players.edit', playerId));
    };

    return (
        <AppLayout title="Players">
            <div className="container-fluid px-4">
                <div className="d-flex justify-content-end align-items-center">
                    <Link href={route('players.create')} className="btn btn-primary">
                        {t('Create player')}
                    </Link>
                </div>

                <div className="card table-card mb-4">
                    <div className="card-header table-card-header d-flex justify-content-end align-items-center">
                        <button type="button" onClick={toggleDeleted} className={`btn btn-sm ${includeDeleted === '1' ? 'btn-secondary' : 'btn-primary'}`}>
                            {includeDeleted === '1' ? t('Active players only') : t('All players (including inactive)')}
                        </button>
                    </div>

                    <div className="card-body">
                        <div className="table-responsive">
                            <table className="table">
                                <thead>
                                    <tr>
                                        {[
                                            ['name', 'Player name'],
                                            ['username', 'Username'],
                                            ['email', 'Email'],
                                            ['rating', 'Rating'],
                                            ['type', 'Type'],
                                            ['created_at', 'Created at'],
                                        ].map(([key, label]) => (
                                            <th key={key}>
                                                <button type="button" className="table-sort-link" onClick={() => sort(key)}>
                                                    {t(label)} {arrow(key)}
                                                </button>
                                            </th>
                                        ))}
                                        <th>{t('Role')}</th>
                                        <th>{t('Actions')}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    {players.map((player) => (
                                        <tr
                                            key={player.id}
                                            className={player.deleted_at ? 'table-secondary' : 'table-row-linkable'}
                                            onClick={player.deleted_at ? undefined : (event) => openPlayer(event, player.id)}
                                            onKeyDown={player.deleted_at ? undefined : (event) => {
                                                if (event.key === 'Enter' || event.key === ' ') {
                                                    event.preventDefault();
                                                    openPlayer(event, player.id);
                                                }
                                            }}
                                            tabIndex={player.deleted_at ? undefined : '0'}
                                            role={player.deleted_at ? undefined : 'link'}
                                        >
                                            <td>{player.name}</td>
                                            <td>{player.user?.name ?? t('No username')}</td>
                                            <td>{player.user?.email ?? t('No email')}</td>
                                            <td>{(player.rating / 100).toFixed(2)}</td>
                                            <td>{t(player.type === 'attacker' ? 'Attacker' : player.type === 'defender' ? 'Defender' : 'Both')}</td>
                                            <td>{date(player.created_at)}</td>
                                            <td><RoleBadge role={player.user?.role ?? 'player'} /></td>
                                            <td className="actions">
                                                {player.deleted_at ? (
                                                    <button type="button" onClick={() => restore(player)} className="btn btn-success btn-sm">
                                                        {t('Restore')}
                                                    </button>
                                                ) : (
                                                    <>
                                                        <Link href={route('players.edit', player.id)} className="btn btn-warning btn-sm me-1">
                                                            {t('Edit player')}
                                                        </Link>
                                                        <button type="button" onClick={() => remove(player)} className="btn btn-danger btn-sm">
                                                            {t('Delete player')}
                                                        </button>
                                                    </>
                                                )}
                                            </td>
                                        </tr>
                                    ))}
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </AppLayout>
    );
}
