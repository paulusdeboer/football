import { Link, useForm } from '@inertiajs/react';
import AppLayout from '../../Layouts/AppLayout';
import PlayerAccountSelect from '../../Components/PlayerAccountSelect';
import { useTranslations } from '../../i18n';
import route from '../../route';

const date = value => value ? new Date(value).toLocaleDateString('nl-NL') : '';

export default function FinanceGameCharges({ game, players = [], participants = [], defaultMatchFeeCents = 0 }) {
    const { t } = useTranslations();
    const initialAccounts = Object.fromEntries(participants.map(participant => [String(participant.id), String(participant.account_player_id)]));
    const initialUnits = Object.fromEntries(participants.map(participant => [String(participant.id), String(participant.units ?? 1)]));
    const { data, setData, put, processing, errors } = useForm({
        fee: game.fee_cents !== null && game.fee_cents !== undefined
            ? (Number(game.fee_cents) / 100).toFixed(2)
            : (Number(defaultMatchFeeCents) / 100).toFixed(2),
        charge_accounts: initialAccounts,
        charge_units: initialUnits,
    });

    const setAccount = (id, value) => setData('charge_accounts', { ...data.charge_accounts, [String(id)]: value });
    const amountFor = participant => (Number(data.charge_units[String(participant.id)]) || 0) * (Number(data.fee.replace(',', '.')) || 0);
    const submit = event => {
        event.preventDefault();
        put(route('finance.games.charges.update', game.id));
    };

    return (
        <AppLayout title="Edit financial charges">
            <div className="container-fluid px-4">
                <div className="card mb-4">
                    <div className="card-header d-flex flex-wrap justify-content-between align-items-center gap-2">
                        <div className="text-muted">{t('Game')} {t('on')} {date(game.played_at)}</div>
                        <Link href={route('games.show', game.id)} className="btn btn-secondary">{t('Back to game')}</Link>
                    </div>
                    <div className="card-body">
                        <form onSubmit={submit}>
                            <div className="mb-3" style={{ maxWidth: '20rem' }}>
                                <label className="form-label" htmlFor="game-fee">{t('Fee per game unit')}</label>
                                <div className="input-group">
                                    <span className="input-group-text">€</span>
                                    <input id="game-fee" className={`form-control ${errors.fee ? 'is-invalid' : ''}`} type="number" min="0" step="0.01" value={data.fee} onChange={event => setData('fee', event.target.value)} required />
                                </div>
                                {errors.fee && <div className="invalid-feedback d-block">{errors.fee}</div>}
                            </div>

                            <div className="table-responsive">
                                <table className="table align-middle">
                                    <thead>
                                        <tr>
                                            <th>{t('Participant')}</th>
                                            <th>{t('Charged account')}</th>
                                            <th className="text-end">{t('Amount')}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        {participants.map(participant => (
                                            <tr key={participant.id}>
                                                <td>{participant.name}</td>
                                                <td>
                                                    <PlayerAccountSelect
                                                        value={data.charge_accounts[String(participant.id)] ?? String(participant.id)}
                                                        players={players}
                                                        onChange={value => setAccount(participant.id, value)}
                                                    />
                                                </td>
                                                <td className="text-end">€ {amountFor(participant).toFixed(2).replace('.', ',')}</td>
                                            </tr>
                                        ))}
                                    </tbody>
                                </table>
                            </div>
                            <button type="submit" className="btn btn-primary mt-3" disabled={processing}>{t('Save financial charges')}</button>
                        </form>
                    </div>
                </div>
            </div>
        </AppLayout>
    );
}
