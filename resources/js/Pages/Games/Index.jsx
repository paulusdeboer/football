import { Link, router } from '@inertiajs/react';
import AppLayout from '../../Layouts/AppLayout';
import { useTranslations } from '../../i18n';
import route from '../../route';

const date = (value) => value ? new Date(value).toLocaleDateString('nl-NL') : '';

export default function GamesIndex({ games, sortBy, sortDirection, latestCompletedGameId }) {
    const { t } = useTranslations();

    const sort = (column) => router.get(
        route('games.index'),
        {
            sort_by: column,
            sort_direction: sortBy === column && sortDirection === 'asc' ? 'desc' : 'asc',
        },
        { preserveState: true },
    );

    const remove = (game) => {
        const gameDate = date(game.played_at);
        const message = t('confirm.game_deletion', { date: gameDate });

        if (window.confirm(message)) {
            router.delete(route('games.destroy', game.id));
        }
    };

    const sortIcon = (column) => {
        if (sortBy !== column) return null;

        return <i className={`fas fa-chevron-${sortDirection === 'asc' ? 'up' : 'down'} table-sort-icon`} aria-hidden="true" />;
    };

    const openGame = (event, gameId) => {
        if (event.target.closest('a, button, select, input, textarea, form')) return;

        router.visit(route('games.show', gameId));
    };

    return (
        <AppLayout title="Games">
            <div className="container-fluid px-4">
                <div className="card table-card mb-4">
                    <div className="card-header table-card-header d-flex justify-content-end align-items-center gap-3">
                        <Link href={route('games.create')} className="btn btn-primary">
                            {t('Create game')}
                        </Link>
                    </div>
                    <div className="card-body">
                        <div className="table-responsive">
                            <table className="table">
                                <thead>
                                    <tr>
                                        <th>
                                            <button type="button" className="table-sort-link" onClick={() => sort('played_at')}>
                                                {t('Game date')} {sortIcon('played_at')}
                                            </button>
                                        </th>
                                        <th>
                                            <button type="button" className="table-sort-link" onClick={() => sort('team1_score')}>
                                                {t('Team 1 score')} {sortIcon('team1_score')}
                                            </button>
                                        </th>
                                        <th>
                                            <button type="button" className="table-sort-link" onClick={() => sort('team2_score')}>
                                                {t('Team 2 score')} {sortIcon('team2_score')}
                                            </button>
                                        </th>
                                        <th>{t('Players')}</th>
                                        <th>{t('Rating requests')}</th>
                                        <th>{t('Status')}</th>
                                        <th>{t('Actions')}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    {games.map((game) => {
                                        const hasResult = game.team1_score !== null && game.team2_score !== null;

                                        return (
                                            <tr
                                                key={game.id}
                                                className="table-row-linkable"
                                                onClick={(event) => openGame(event, game.id)}
                                                onKeyDown={(event) => {
                                                    if (event.key === 'Enter' || event.key === ' ') {
                                                        event.preventDefault();
                                                        openGame(event, game.id);
                                                    }
                                                }}
                                                tabIndex="0"
                                                role="link"
                                            >
                                                <td className="game-date">{date(game.played_at)}</td>
                                                <td>{game.team1_score ?? ''}</td>
                                                <td>{game.team2_score ?? ''}</td>
                                                <td><span className="badge bg-secondary">{game.teams_count}</span></td>
                                                <td><span className="badge bg-primary">{game.rating_requests_count}</span></td>
                                                <td>
                                                    <span className={`badge ${hasResult ? 'bg-success' : 'bg-warning'}`}>
                                                        {hasResult ? t('Completed') : t('Pending')}
                                                    </span>
                                                </td>
                                                <td className="actions">
                                                    <Link href={route('games.show', game.id)} className="btn btn-info btn-sm me-1">
                                                        {t('View')}
                                                    </Link>
                                                    {hasResult ? (
                                                        Number(game.id) === Number(latestCompletedGameId) && (
                                                            <Link href={route('games.enter-result', game.id)} className="btn btn-warning btn-sm">
                                                                {t('Edit result')}
                                                            </Link>
                                                        )
                                                    ) : (
                                                        <>
                                                            <Link href={route('games.edit', game.id)} className="btn btn-warning btn-sm me-1">
                                                                {t('Edit game')}
                                                            </Link>
                                                            <Link href={route('games.enter-result', game.id)} className="btn btn-warning btn-sm me-1">
                                                                {t('Enter result')}
                                                            </Link>
                                                            <button type="button" onClick={() => remove(game)} className="btn btn-danger btn-sm">
                                                                {t('Delete game')}
                                                            </button>
                                                        </>
                                                    )}
                                                </td>
                                            </tr>
                                        );
                                    })}
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </AppLayout>
    );
}
