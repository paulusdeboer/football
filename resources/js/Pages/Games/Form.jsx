import { Link, useForm } from '@inertiajs/react';
import { useEffect, useRef } from 'react';
import AppLayout from '../../Layouts/AppLayout';
import PlayerAccountSelect from '../../Components/PlayerAccountSelect';
import { useTranslations } from '../../i18n';
import route from '../../route';
import { openDatePicker } from '../../datePicker';
import { clearSelect2SearchPreservingScroll } from '../../select2';

export default function GamesForm({ mode, game, players, selectedPlayers = [], chargeAccounts = {}, chargeUnits = {}, defaultMatchFeeCents = 0, whatsappReady = false }) {
    const { t } = useTranslations();
    const playersSelect = useRef(null);
    const initialChargeAccounts = Object.fromEntries(Object.entries(chargeAccounts).map(([participantId, accountId]) => [String(participantId), String(accountId)]));
    const initialChargeUnits = Object.fromEntries(Object.entries(chargeUnits).map(([participantId, units]) => [String(participantId), String(units)]));
    const { data, setData, post, put, processing, errors } = useForm({
        played_at: game?.played_at ? String(game.played_at).slice(0, 10) : '',
        players: selectedPlayers.map(String),
        fee: game?.fee_cents !== null && game?.fee_cents !== undefined
            ? (Number(game.fee_cents) / 100).toFixed(2)
            : (Number(defaultMatchFeeCents) / 100).toFixed(2),
        charge_accounts: initialChargeAccounts,
        charge_units: initialChargeUnits,
        send_whatsapp: mode === 'create' && whatsappReady,
        whatsapp_action_key: crypto.randomUUID(),
    });
    const submit = (event) => { event.preventDefault(); mode === 'edit' ? put(route('games.update', game.id)) : post(route('games.store')); };
    const selectedPlayerRecords = players.filter(player => data.players.includes(String(player.id)));
    const chargeAccount = (playerId) => String(data.charge_accounts[String(playerId)] ?? playerId);
    const chargeUnit = (playerId) => String(data.charge_units[String(playerId)] ?? '1');
    const setChargeAccount = (playerId, accountId) => setData('charge_accounts', {
        ...data.charge_accounts,
        [String(playerId)]: String(accountId),
    });
    const amountFor = (playerId) => (Number(chargeUnit(playerId)) || 0) * (Number(data.fee.replace(',', '.')) || 0);
    useEffect(() => {
        const $select = window.jQuery(playersSelect.current);
        $select.select2({
            theme: 'bootstrap-5',
            width: '100%',
            placeholder: t('Select players'),
            closeOnSelect: false,
            dropdownCssClass: 'select2--small',
            selectionCssClass: 'compact-multiple-selection',
        });
        const updatePlayers = () => setData(current => ({ ...current, players: ($select.val() ?? []).map(String) }));
        const clearPlayerSearch = () => clearSelect2SearchPreservingScroll($select);
        $select.on('change', updatePlayers);
        $select.on('select2:selecting', clearPlayerSearch);

        return () => {
            $select.off('change', updatePlayers);
            $select.off('select2:selecting', clearPlayerSearch);
            if ($select.hasClass('select2-hidden-accessible')) $select.select2('destroy');
        };
    }, []);
    return (
        <AppLayout title={mode === 'edit' ? 'Edit game' : 'Create game'}>
            <div className="container-fluid px-4">
                <div className="card mb-4">
                    <div className="card-header d-flex justify-content-end">
                        <Link href={route('games.index')} className="btn btn-secondary">{t('Back to games list')}</Link>
                    </div>
                    <div className="card-body">
                        <form onSubmit={submit}>
                            <div className="mb-3">
                                <div className="form-check">
                                    <input id="send-whatsapp" type="checkbox" className="form-check-input" checked={data.send_whatsapp} disabled={!whatsappReady || processing} onChange={event => setData('send_whatsapp', event.target.checked)} />
                                    <label htmlFor="send-whatsapp" className="form-check-label">{t('WhatsApp send lineup')}</label>
                                </div>
                                {!whatsappReady && <small className="text-muted">{t('WhatsApp setup required')} <Link href={route('whatsapp.index')}>{t('WhatsApp settings')}</Link></small>}
                                {(errors.send_whatsapp || errors.whatsapp || errors.whatsapp_action_key) && <div className="text-danger">{errors.send_whatsapp || errors.whatsapp || errors.whatsapp_action_key}</div>}
                            </div>
                            <div className="row g-3 mb-3">
                                <div className="col-md-6">
                                    <div className="form-group">
                                        <label htmlFor="played_at">{t('Select game date')}</label>
                                        <input className={`form-control ${errors.played_at ? 'is-invalid' : ''}`} type="date" id="played_at" value={data.played_at} onClick={openDatePicker} onChange={e => setData('played_at', e.target.value)} required />
                                        {errors.played_at && <div className="invalid-feedback">{errors.played_at}</div>}
                                    </div>
                                </div>
                                <div className="col-md-6">
                                    <div className="form-group">
                                        <label htmlFor="fee">{t('Fee per game unit')}</label>
                                        <div className="input-group">
                                            <span className="input-group-text">€</span>
                                            <input className={`form-control ${errors.fee ? 'is-invalid' : ''}`} type="number" id="fee" min="0" step="0.01" value={data.fee} onChange={e => setData('fee', e.target.value)} required />
                                        </div>
                                        {errors.fee && <div className="invalid-feedback d-block">{errors.fee}</div>}
                                        {Number(data.fee.replace(',', '.')) === 0 && <small className="form-text text-warning">{t('Set a fee before using financial tracking.')}</small>}
                                    </div>
                                </div>
                            </div>
                            <div className="form-group mb-3">
                                <label htmlFor="players">{t('Select 12 players')} - {data.players.length} {t('players selected')}</label>
                                <select ref={playersSelect} className={`game-players-select ${errors.players ? 'is-invalid' : ''}`} id="players" multiple defaultValue={data.players}>
                                    {players.map(player => <option key={player.id} value={player.id}>{player.name}</option>)}
                                </select>
                                {errors.players && <div className="invalid-feedback">{errors.players}</div>}
                                <small className="form-text text-muted">{t('Select between 10 and 12 players.')}</small>
                            </div>
                            {selectedPlayerRecords.length > 0 && (
                                <div className="card bg-light border-0 mb-3">
                                    <div className="card-body">
                                        <h5 className="mb-1">{t('Financial allocation')}</h5>
                                        <p className="text-muted small mb-3">{t('Each participant is charged once by default. Change the account for cash arrangements.')}</p>
                                        <div className="table-responsive">
                                            <table className="table table-sm align-middle mb-0">
                                                <thead>
                                                    <tr>
                                                        <th>{t('Participant')}</th>
                                                        <th>{t('Charged account')}</th>
                                                        <th className="text-end">{t('Amount')}</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    {selectedPlayerRecords.map(player => (
                                                        <tr key={player.id}>
                                                            <td>{player.name}</td>
                                                            <td>
                                                                <PlayerAccountSelect value={chargeAccount(player.id)} players={players} onChange={accountId => setChargeAccount(player.id, accountId)} />
                                                            </td>
                                                            <td className="text-end">€ {amountFor(player.id).toFixed(2).replace('.', ',')}</td>
                                                        </tr>
                                                    ))}
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            )}
                            <button disabled={processing} type="submit" className="btn btn-primary">{t('Save')}</button>
                        </form>
                    </div>
                </div>
            </div>
        </AppLayout>
    );
}
