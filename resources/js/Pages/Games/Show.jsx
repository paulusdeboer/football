import { Link } from '@inertiajs/react';
import { useState } from 'react';
import AppLayout from '../../Layouts/AppLayout';
import GivenRatings from '../../Components/GivenRatings';
import { useTranslations } from '../../i18n';
import route from '../../route';

const date = (value) => value ? new Date(value).toLocaleDateString('nl-NL') : '';

export default function GamesShow({ game, canEditResult, givenRatings, ratingRequests, team1Rating, team2Rating, team1Ratings, team2Ratings }) {
    const { t } = useTranslations();
    const [showRatings, setShowRatings] = useState(true);
    const typeLabel = (type) => ({ attacker: t('Attacker'), defender: t('Defender'), both: t('Both') }[type] ?? type);
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
                            <>
                                <h5>{t('Rating requests have been sent to')}</h5>
                                <ul>
                                    {ratingRequests.map((request) => <li key={request.id}>{request.player_name} ({request.player_email})</li>)}
                                </ul>
                            </>
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
