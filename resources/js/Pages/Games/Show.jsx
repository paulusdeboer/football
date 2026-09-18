import { Link, router } from '@inertiajs/react';
import { useState } from 'react';
import AppLayout from '../../Layouts/AppLayout';
import GivenRatings from '../../Components/GivenRatings';
import Select2Field from '../../Components/Select2Field';
import WhatsappStatus from '../../Components/WhatsappStatus';
import { useTranslations } from '../../i18n';
import route from '../../route';

const date = (value) => value ? new Date(value).toLocaleDateString('nl-NL') : '';
const dateTime = (value) => value ? new Date(value).toLocaleString('nl-NL') : '';

export default function GamesShow({ game, canEditResult, canManageRatingRequests, canManageGames = false, canManageWhatsapp = false, canManageFinance = false, financialCharges = [], givenRatings, ratingRequests, team1Rating, team2Rating, team1Ratings, team2Ratings, whatsappMessage = null, errors = {} }) {
    const { t } = useTranslations();
    const [showRatings, setShowRatings] = useState(true);
    const typeLabel = (type) => ({ attacker: t('Attacker'), defender: t('Defender'), both: t('Both') }[type] ?? type);
    const statusLabel = (status, completedAt = null) => {
        const label = ({
            pending: t('Pending'),
            completed: t('Completed'),
            expired: t('Expired'),
            revoked: t('Revoked'),
            send_failed: t('Send failed'),
        }[status] ?? status);

        return status === 'completed' && completedAt ? `${label} — ${dateTime(completedAt)}` : label;
    };
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
        <AppLayout title={`${t('Game')} ${t('on')} ${date(game.played_at)}`}>
            <div className="container-fluid px-4">
                <div className="card mb-4">
                    <div className="card-header d-flex justify-content-end align-items-center gap-2">
                        <div className="game-detail-actions d-flex flex-wrap justify-content-end align-items-center gap-1">
                            {canManageGames && !hasResult && (
                                <Link href={route('games.edit', game.id)} className="btn btn-primary">
                                    {t('Edit game')}
                                </Link>
                            )}
                            {canManageGames && (!hasResult || canEditResult) && (
                                <Link href={route('games.enter-result', game.id)} className="btn btn-warning game-detail-action">
                                    {hasResult ? t('Edit result') : t('Enter result')}
                                </Link>
                            )}
                            <Link href={route('games.index')} className="btn btn-secondary">{t('Back to games list')}</Link>
                        </div>
                    </div>
                    <div className="card-body">
                        {canManageWhatsapp && <WhatsappStatus message={whatsappMessage} gameId={game.id} error={errors.whatsapp} />}
                        <p><strong>{t('Result')}:</strong> {hasResult ? `${game.team1_score} - ${game.team2_score}` : ''}</p>

                        {(financialCharges.length > 0 || canManageFinance) && (
                            <div className="mb-4">
                                <div className="d-flex justify-content-between align-items-center mb-2">
                                    <h5 className="mb-0">{t('Financial charges')}</h5>
                                    {canManageFinance && (
                                        <span className="actions">
                                            <Link href={route('finance.games.charges.edit', game.id)} className="btn btn-warning btn-sm">{t('Edit financial charges')}</Link>
                                        </span>
                                    )}
                                </div>
                                {financialCharges.length > 0 ? (
                                    <div className="table-responsive">
                                        <table className="table table-sm align-middle">
                                            <thead>
                                                <tr>
                                                    <th>{t('Player')}</th>
                                                    <th>{t('Charged account')}</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                {financialCharges.map(charge => (
                                                    <tr key={charge.participant_id}>
                                                        <td>{charge.participant_name}</td>
                                                        <td>{charge.account_name}</td>
                                                    </tr>
                                                ))}
                                            </tbody>
                                        </table>
                                    </div>
                                ) : <span className="text-muted">{t('No financial charges recorded.')}</span>}
                            </div>
                        )}

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
                                                const canManage = !request.has_submitted_rating && !['completed', 'revoked'].includes(request.status);

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

                <div className="card table-card mb-4">
                    <div className="card-header table-card-header">
                        <i className="fas fa-table me-1" />
                        {t('Ratings list')}
                    </div>
                    <div className="card-body">
                        <GivenRatings {...givenRatings} />
                    </div>
                </div>

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
                <td><span className={`badge text-bg-${request.status === 'completed' ? 'success' : request.status === 'expired' ? 'warning' : request.status === 'send_failed' ? 'danger' : request.status === 'revoked' ? 'secondary' : 'primary'}`}>{statusLabel(request.status, request.completed_at)}</span></td>
                <td>{dateTime(request.sent_at)}</td>
                <td>{dateTime(request.expires_at)}</td>
                <td className="actions">
                    {canManage && (
                        <div className="d-flex flex-wrap gap-1">
                            <button type="button" className="btn btn-sm" onClick={resend}>
                                {t('Resend')}
                            </button>
                            <form onSubmit={replace} className="d-flex gap-1">
                                <Select2Field className="form-select form-select-sm" value={replacementPlayer} onChange={setReplacementPlayer} width="style" allowClear>
                                    <option value="">{t('Random suitable player')}</option>
                                    {request.replacement_players?.map((player) => <option key={player.id} value={player.id}>{player.name}</option>)}
                                </Select2Field>
                                <button type="submit" className="btn btn-sm">{t('Replace')}</button>
                            </form>
                        </div>
                    )}
                </td>
            </tr>
            {request.history?.length > 0 && (
                <tr>
                    <td colSpan="5" className="pt-0">
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
                    </td>
                </tr>
            )}
        </>
    );
}
