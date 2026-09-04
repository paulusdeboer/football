import { Link, useForm } from '@inertiajs/react';
import AppLayout from '../../Layouts/AppLayout';
import { useTranslations } from '../../i18n';
import route from '../../route';

export default function EnterResult({ game, team1Players, team2Players, hasSentRatingRequests }) {
    const { t } = useTranslations();
    const { data, setData, post, processing, errors } = useForm({
        team1_score: game.team1_score ?? '',
        team2_score: game.team2_score ?? '',
        send_rating_requests: !hasSentRatingRequests,
    });

    const submit = (event) => {
        event.preventDefault();
        post(route('games.store-result', game.id));
    };

    return (
        <AppLayout title="Enter result">
            <div className="container-fluid px-4">
                <div className="card mb-4">
                    <div className="card-body">
                        <form onSubmit={submit} id="game-form">
                            <div className="form-check mb-3">
                                <input
                                    className="form-check-input"
                                    id="send_rating_requests"
                                    type="checkbox"
                                    checked={data.send_rating_requests}
                                    disabled={hasSentRatingRequests}
                                    onChange={(event) => setData('send_rating_requests', event.target.checked)}
                                />
                                <label className="form-check-label" htmlFor="send_rating_requests">
                                    {t('Send rating request e-mails')}
                                </label>
                            </div>

                            <div className="row">
                                <div className="col-3">
                                    <h5>{t('Players in team 1')}</h5>
                                    <ul>
                                        {team1Players.map((player) => <li key={player.id}>{player.name}</li>)}
                                    </ul>
                                </div>
                                <div className="col-3">
                                    <h5>{t('Players in team 2')}</h5>
                                    <ul>
                                        {team2Players.map((player) => <li key={player.id}>{player.name}</li>)}
                                    </ul>
                                </div>
                            </div>

                            <div className="row">
                                <div className="col-3 form-group">
                                    <label htmlFor="team1_score">{t('Team 1 score')}</label>
                                    <input
                                        className={`form-control ${errors.team1_score ? 'is-invalid' : ''}`}
                                        type="number"
                                        min="0"
                                        id="team1_score"
                                        value={data.team1_score}
                                        onChange={(event) => setData('team1_score', event.target.value)}
                                        required
                                    />
                                    {errors.team1_score && <div className="invalid-feedback">{errors.team1_score}</div>}
                                </div>
                                <div className="col-3 form-group">
                                    <label htmlFor="team2_score">{t('Team 2 score')}</label>
                                    <input
                                        className={`form-control ${errors.team2_score ? 'is-invalid' : ''}`}
                                        type="number"
                                        min="0"
                                        id="team2_score"
                                        value={data.team2_score}
                                        onChange={(event) => setData('team2_score', event.target.value)}
                                        required
                                    />
                                    {errors.team2_score && <div className="invalid-feedback">{errors.team2_score}</div>}
                                </div>
                            </div>
                        </form>

                        <button disabled={processing} type="submit" form="game-form" className="btn btn-primary mt-3">
                            {t('Save result')}
                        </button>
                    </div>
                </div>

                <Link href={route('games.index')} className="btn btn-secondary">
                    {t('Back to games list')}
                </Link>
            </div>
        </AppLayout>
    );
}
