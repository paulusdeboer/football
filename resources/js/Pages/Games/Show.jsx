import { Link, router } from '@inertiajs/react';
import { useState } from 'react';
import AppLayout from '../../Layouts/AppLayout';
import GivenRatings from '../../Components/GivenRatings';
import { useTranslations } from '../../i18n';
import route from '../../route';

const date = (value) => value ? new Date(value).toLocaleDateString('nl-NL') : '';
const dateTime = (value) => value ? new Date(value).toLocaleString('nl-NL') : '';

export default function GamesShow({ game, canEditResult, canManageRatingRequests, givenRatings, ratingRequests, team1Rating, team2Rating, team1Ratings, team2Ratings, errors = {} }) {
    const { t } = useTranslations();
    const [showRatings, setShowRatings] = useState(true);
    const typeLabel = (type) => ({ attacker: t('Attacker'), defender: t('Defender'), both: t('Both') }[type] ?? type);
    const statusLabel = (status) => ({
        pending: t('Pending'),
        completed: t('Completed'),
        expired: t('Expired'),
        revoked: t('Revoked'),
        send_failed: t('Send failed'),
    }[status] ?? status);
    const renderTeam = (ratings) => (
        <ul>
            {[...ratings]
                .sort((a, b) => (a.player?.name ?? '').localeCompare(b.player?.name ?? ''))
                .map((item) => (
                    <li key={item.id}>
                        {`(${typeLabel(item.type)}) ${item.player?.name ?? ''}`}
                        {showRatings && <span className="rating-value"> - {item.rating}</span>}
                    </li>
                ))}
        </ul>
    );
    const hasResult = game.team1_score !== null && game.team2_score !== null;

    return (
        <AppLayout title="Game">
            <div className="container-fluid px-4">
                <h1 className="mt-4">{t('Welcome')}</h1>

                <div className="card mb-4">
                    <div className="card-header d-flex justify-content-between">
                        <span>{t('Game')} {t('on')} {date(game.played_at)}</span>
                        <span>
                            {!hasResult && (
                                <Link href={route('games.edit', game.id)} className="btn btn-primary btn-sm me-2">
                                    {t('Edit game')}
                                </Link>
                            )}
                            {(!hasResult || canEditResult) && (
                                <Link href={route('games.enter-result', game.id)} className="btn btn-secondary btn-sm">
                                    {hasResult ? t('Edit result') : t('Enter result')}
                                </Link>
                            )}
                        </span>
                    </div>
                    <div className="card-body">
                        <p><strong>{t('Result')}:</strong> {hasResult ? `${game.team1_score} - ${game.team2_score}` : ''}</p>

                        {ratingRequests.length > 0 && (
                            <div className="mb-4">
                                <h5>{t('Rating requests')}</h5>
                                {errors.player_id && <div className="alert alert-danger">{errors.player_id}</div>}
                                <div className="table-responsive">
                                    <table className="table table-sm align-middle">
                                        <thead>
                                            <tr>
                                                <th>{t('Player')}</th>
                                                <th>{t('Status')}</th>
                                                <th>{t('Sent at')}</th>
                                                <th>{t('Valid until')}</th>
                                                <th>{t('Actions')}</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            {ratingRequests.map((request) => {
                                                const canManage = !['completed', 'revoked'].includes(request.status);

                                                return (
                                                    <RatingRequestRow
                                                        key={request.id}
                                                        request={request}
                                                        gameId={game.id}
                                                        canManage={canManageRatingRequests && canManage}
                                                        statusLabel={statusLabel}
                                                        t={t}
                                                    />
                                                );
                                            })}
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        )}

                        <div className="row">
                            <div className="col">
                                <h5>{t('Players in team 1')} ({team1Rating})</h5>
                                {renderTeam(team1Ratings)}
                            </div>
                            <div className="col">
                                <h5>{t('Players in team 2')} ({team2Rating})</h5>
                                {renderTeam(team2Ratings)}
                            </div>
                        </div>

                        <button className="btn btn-sm btn-primary" onClick={() => setShowRatings(!showRatings)}>
                            {showRatings ? t('Hide ratings') : t('Show ratings')}
                        </button>
                    </div>
                </div>

                <div className="card mb-4">
                    <div className="card-header">
                        <i className="fas fa-table me-1" />
                        {t('Ratings list')}
                    </div>
                    <div className="card-body">
                        <GivenRatings {...givenRatings} />
                    </div>
                </div>

                <Link href={route('games.index')} className="btn btn-secondary">
                    {t('Back to games list')}
                </Link>
            </div>
        </AppLayout>
    );
}

function RatingRequestRow({ request, gameId, canManage, statusLabel, t }) {
    const [replacementPlayer, setReplacementPlayer] = useState('');

    const resend = () => {
        if (window.confirm(t('Confirm resend rating request'))) {
            router.post(route('rating-requests.resend', [gameId, request.id]), {}, { preserveScroll: true });
        }
    };

    const replace = (event) => {
        event.preventDefault();
        if (window.confirm(t('Confirm replace rating request'))) {
            router.post(route('rating-requests.replace', [gameId, request.id]), {
                player_id: replacementPlayer || null,
            }, { preserveScroll: true });
        }
    };

    return (
        <>
            <tr>
                <td>{request.player_name} ({request.player_email})</td>
                <td><span className={`badge text-bg-${request.status === 'completed' ? 'success' : request.status === 'expired' ? 'warning' : request.status === 'send_failed' ? 'danger' : request.status === 'revoked' ? 'secondary' : 'primary'}`}>{statusLabel(request.status)}</span></td>
                <td>{dateTime(request.sent_at)}</td>
                <td>{dateTime(request.expires_at)}</td>
                <td>
                    {canManage && (
                        <div className="d-flex flex-wrap gap-1">
                            <button type="button" className="btn btn-outline-primary btn-sm" onClick={resend}>
                                {t('Resend')}
                            </button>
                            <form onSubmit={replace} className="d-flex gap-1">
                                <select className="form-select form-select-sm" value={replacementPlayer} onChange={(event) => setReplacementPlayer(event.target.value)}>
                                    <option value="">{t('Random suitable player')}</option>
                                    {request.replacement_players?.map((player) => <option key={player.id} value={player.id}>{player.name}</option>)}
                                </select>
                                <button type="submit" className="btn btn-outline-warning btn-sm">{t('Replace')}</button>
                            </form>
                        </div>
                    )}
                </td>
            </tr>
            {request.history?.length > 0 && (
                <tr>
                    <td colSpan="5" className="pt-0">
                        <details>
                            <summary>{t('View history')}</summary>
                            <ul className="small mt-2 mb-0">
                                {request.history.map((event, index) => (
                                    <li key={`${request.id}-${index}`}>
                                        {t(event.type === 'initial_send' ? 'Initial send' : event.type === 'send_failed' ? 'Send failed' : event.type === 'resend' ? 'Resend' : event.type === 'replace' ? 'Replaced' : 'Completed')}
                                        {' — '}{dateTime(event.created_at)}
                                        {event.actor_name ? ` (${event.actor_name})` : ''}
                                        {event.previous_player_name && event.new_player_name && event.previous_player_name !== event.new_player_name ? `: ${event.previous_player_name} → ${event.new_player_name}` : ''}
                                    </li>
                                ))}
                            </ul>
                        </details>
                    </td>
                </tr>
            )}
        </>
    );
}
