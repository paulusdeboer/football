import { useForm } from '@inertiajs/react';
import GuestLayout from '../../Layouts/GuestLayout';
import { useTranslations } from '../../i18n';

const date = value => value ? new Date(value).toLocaleDateString('nl-NL') : '';
export default function RatingsForm({ game, player, team1Players, team2Players, hasRated, storeUrl }) {
    const { t } = useTranslations();
    const { data, setData, post, processing, errors } = useForm({ ratings: {} });
    const setRating = (id, value) => setData('ratings', { ...data.ratings, [id]: value });
    const submit = event => { event.preventDefault(); post(storeUrl, { preserveScroll: true }); };
    const input = teamPlayer => <div className="mb-3" key={teamPlayer.id}><label htmlFor={`rating_${teamPlayer.id}`}>{teamPlayer.name}</label><input className="form-control" type="number" min="5" max="10" step="0.1" disabled={teamPlayer.id === player.id} id={`rating_${teamPlayer.id}`} value={data.ratings[teamPlayer.id] ?? ''} onChange={e => setRating(teamPlayer.id, e.target.value)} placeholder={t('Enter rating')} /></div>;
    return <GuestLayout title="Rate players"><div className="card shadow-lg border-0 rounded-lg my-5"><div className="card-header"><h3 className="text-center font-weight-light my-4">{t('Hello :name, rate players for the game on :date', { name: player.name, date: date(game.played_at) })}</h3></div><div className="card-body">{!hasRated && <p>{t('Give your rating for each player between 5 and 10.')}</p>}{hasRated ? <p className="text-center">{t('You have already submitted a rating for this game.')}</p> : <form onSubmit={submit}><p><strong>{t('Result')}:</strong> {game.team1_score !== null ? `${game.team1_score} - ${game.team2_score}` : ''}</p><div className="row"><div className="col"><h5>{t('Team 1')}</h5>{team1Players.map(input)}</div><div className="col"><h5>{t('Team 2')}</h5>{team2Players.map(input)}</div></div>{errors.rating && <div className="text-danger">{errors.rating}</div>}<button disabled={processing} type="submit" className="btn btn-primary">{t('Submit ratings')}</button></form>}</div></div></GuestLayout>;
}
