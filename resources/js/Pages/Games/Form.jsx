import { Link, useForm } from '@inertiajs/react';
import { useEffect, useRef } from 'react';
import AppLayout from '../../Layouts/AppLayout';
import { useTranslations } from '../../i18n';
import route from '../../route';

export default function GamesForm({ mode, game, players, selectedPlayers = [], whatsappReady = false }) {
    const { t } = useTranslations();
    const playersSelect = useRef(null);
    const { data, setData, post, put, processing, errors } = useForm({ played_at: game?.played_at ? String(game.played_at).slice(0, 10) : '', players: selectedPlayers.map(String), send_whatsapp: mode === 'create' && whatsappReady, whatsapp_action_key: crypto.randomUUID() });
    const submit = (event) => { event.preventDefault(); mode === 'edit' ? put(route('games.update', game.id)) : post(route('games.store')); };
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
        const clearPlayerSearch = () => {
            $select.next('.select2-container').find('.select2-search__field').val('').trigger('input');
        };
        $select.on('change', updatePlayers);
        $select.on('select2:select', clearPlayerSearch);

        return () => {
            $select.off('change', updatePlayers);
            $select.off('select2:select', clearPlayerSearch);
            if ($select.hasClass('select2-hidden-accessible')) $select.select2('destroy');
        };
    }, []);
    return (
        <AppLayout title={mode === 'edit' ? 'Edit game' : 'Create game'}>
            <div className="container-fluid px-4">
                <div className="card mb-4">
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
                            <div className="form-group mb-3">
                                <label htmlFor="played_at">{t('Select game date')}</label>
                                <input className={`form-control ${errors.played_at ? 'is-invalid' : ''}`} type="date" id="played_at" value={data.played_at} onChange={e => setData('played_at', e.target.value)} required />
                                {errors.played_at && <div className="invalid-feedback">{errors.played_at}</div>}
                            </div>
                            <div className="form-group mb-3">
                                <label htmlFor="players">{t('Select 12 players')} - {data.players.length} {t('players selected')}</label>
                                <select ref={playersSelect} className={`game-players-select ${errors.players ? 'is-invalid' : ''}`} id="players" multiple defaultValue={data.players}>
                                    {players.map(player => <option key={player.id} value={player.id}>{player.name}</option>)}
                                </select>
                                {errors.players && <div className="invalid-feedback">{errors.players}</div>}
                                <small className="form-text text-muted">{t('Select between 10 and 12 players.')}</small>
                            </div>
                            <button disabled={processing} type="submit" className="btn btn-primary">{t('Save')}</button>
                            <Link href={route('games.index')} className="btn btn-secondary ms-2">{t('Back to games list')}</Link>
                        </form>
                    </div>
                </div>
            </div>
        </AppLayout>
    );
}
