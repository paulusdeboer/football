import { Link, router, useForm } from '@inertiajs/react';
import { useEffect, useRef, useState } from 'react';
import AppLayout from '../../Layouts/AppLayout';
import { useTranslations } from '../../i18n';
import route from '../../route';
import { clearSelect2SearchPreservingScroll } from '../../select2';

const amount = (cents, signed = false) => {
    const value = Number(cents || 0) / 100;
    const prefix = signed && value > 0 ? '+' : '';
    return `${prefix}€ ${value.toFixed(2).replace('.', ',')}`;
};

const date = value => value ? new Date(value).toLocaleDateString('nl-NL') : '';
const monthOptions = [
    ['01', 'January'], ['02', 'February'], ['03', 'March'], ['04', 'April'],
    ['05', 'May'], ['06', 'June'], ['07', 'July'], ['08', 'August'],
    ['09', 'September'], ['10', 'October'], ['11', 'November'], ['12', 'December'],
];

export default function FinanceIndex({ month, defaultMatchFeeCents = 0, players = [], transactions = [], totals = {} }) {
    const { t } = useTranslations();
    const settings = useForm({ default_match_fee: (Number(defaultMatchFeeCents) / 100).toFixed(2) });
    const monthSelect = useRef(null);
    const yearSelect = useRef(null);
    const playerFilter = useRef(null);
    const [selectedPlayerIds, setSelectedPlayerIds] = useState([]);
    const [yearPart = String(new Date().getFullYear()), monthPart = '01'] = String(month ?? '').split('-');
    const selectedYear = Number(yearPart) || new Date().getFullYear();
    const currentYear = new Date().getFullYear();
    const years = Array.from({ length: 11 }, (_, index) => currentYear - 5 + index);
    if (!years.includes(selectedYear)) years.push(selectedYear);
    years.sort((a, b) => a - b);

    const changeMonth = (year, selectedMonth) => router.get(
        route('finance.index'),
        { month: `${year}-${selectedMonth}` },
        { preserveState: true },
    );
    useEffect(() => {
        const $month = window.jQuery(monthSelect.current);
        const $year = window.jQuery(yearSelect.current);
        const selectOptions = {
            theme: 'bootstrap-5',
            width: 'style',
            minimumResultsForSearch: Infinity,
            dropdownCssClass: 'select2--small',
        };
        const updateMonth = () => changeMonth(String($year.val()), String($month.val()));
        const updateYear = () => changeMonth(String($year.val()), String($month.val()));

        $month.select2(selectOptions);
        $year.select2(selectOptions);
        $month.on('change', updateMonth);
        $year.on('change', updateYear);

        return () => {
            $month.off('change', updateMonth);
            $year.off('change', updateYear);
            if ($month.hasClass('select2-hidden-accessible')) $month.select2('destroy');
            if ($year.hasClass('select2-hidden-accessible')) $year.select2('destroy');
        };
    }, []);
    useEffect(() => {
        if (monthSelect.current) window.jQuery(monthSelect.current).val(monthPart).trigger('change.select2');
        if (yearSelect.current) window.jQuery(yearSelect.current).val(String(selectedYear)).trigger('change.select2');
    }, [monthPart, selectedYear]);

    useEffect(() => {
        const $select = window.jQuery(playerFilter.current);
        const updateFilter = () => setSelectedPlayerIds(($select.val() ?? []).map(String));
        const clearPlayerSearch = () => clearSelect2SearchPreservingScroll($select);

        $select.select2({
            theme: 'bootstrap-5',
            width: '100%',
            placeholder: t('All players'),
            closeOnSelect: false,
            dropdownCssClass: 'select2--small',
            selectionCssClass: 'compact-multiple-selection',
        });
        $select.on('change', updateFilter);
        $select.on('select2:selecting', clearPlayerSearch);

        return () => {
            $select.off('change', updateFilter);
            $select.off('select2:selecting', clearPlayerSearch);
            if ($select.hasClass('select2-hidden-accessible')) $select.select2('destroy');
        };
    }, []);

    useEffect(() => {
        const availablePlayerIds = new Set(players.map(player => String(player.id)));
        const validPlayerIds = selectedPlayerIds.filter(playerId => availablePlayerIds.has(playerId));

        if (validPlayerIds.length !== selectedPlayerIds.length) {
            setSelectedPlayerIds(validPlayerIds);
            if (playerFilter.current) window.jQuery(playerFilter.current).val(validPlayerIds).trigger('change.select2');
        }
    }, [players, selectedPlayerIds]);
    const saveSettings = event => {
        event.preventDefault();
        settings.put(route('finance.settings.update'), { preserveScroll: true });
    };
    const statusLabel = status => ({ negative: t('Negative balance'), low: t('Low balance'), ok: t('OK') }[status] ?? status);
    const visiblePlayers = selectedPlayerIds.length > 0
        ? players.filter(player => selectedPlayerIds.includes(String(player.id)))
        : players;
    const visibleTransactions = selectedPlayerIds.length > 0
        ? transactions.filter(transaction => selectedPlayerIds.includes(String(transaction.player_id)))
        : transactions;

    return (
        <AppLayout title="Finance">
            <div className="container-fluid px-4">
                <div className="row g-3 mb-4">
                    <div className="col-12 col-xl-5">
                        <div className="card h-100">
                            <div className="card-header d-flex flex-wrap justify-content-between align-items-center gap-2">
                                <h5 className="mb-0">{t('Financial settings')}</h5>
                                <div className="d-flex flex-wrap gap-2">
                                    <Link href={route('finance.topups')} className="btn btn-primary">{t('Enter top-ups')}</Link>
                                </div>
                            </div>
                            <div className="card-body">
                                <form onSubmit={saveSettings}>
                                    <label className="form-label" htmlFor="default-match-fee">{t('Default fee per game unit')}</label>
                                    <div className="input-group mb-2">
                                        <span className="input-group-text">€</span>
                                        <input id="default-match-fee" className={`form-control ${settings.errors.default_match_fee ? 'is-invalid' : ''}`} type="number" min="0" step="0.01" value={settings.data.default_match_fee} onChange={event => settings.setData('default_match_fee', event.target.value)} required />
                                        {settings.errors.default_match_fee && <div className="invalid-feedback">{settings.errors.default_match_fee}</div>}
                                    </div>
                                    <div className="form-text mb-3">{t('New games use this fee unless a different fee is entered.')}</div>
                                    <button type="submit" className="btn btn-primary" disabled={settings.processing}>{t('Save settings')}</button>
                                </form>
                            </div>
                        </div>
                    </div>
                    <div className="col-12 col-xl-7">
                        <div className="card h-100">
                            <div className="card-header d-flex flex-wrap justify-content-between align-items-center gap-2">
                                <h5 className="mb-0">{t('Month closing')}</h5>
                                <div className="d-flex gap-2">
                                    <select ref={monthSelect} className="form-select finance-period-select finance-month-select" style={{ width: '10rem' }} defaultValue={monthPart} aria-label={t('Month')}>
                                        {monthOptions.map(([value, label]) => <option key={value} value={value}>{t(label)}</option>)}
                                    </select>
                                    <select ref={yearSelect} className="form-select finance-period-select finance-year-select" style={{ width: '6.5rem' }} defaultValue={String(selectedYear)} aria-label={t('Year')}>
                                        {years.map(year => <option key={year} value={year}>{year}</option>)}
                                    </select>
                                </div>
                            </div>
                            <div className="card-body">
                                <div className="row g-3">
                                    <Summary label={t('Top-ups')} value={amount(totals.topups_cents)} />
                                    <Summary label={t('Game charges')} value={amount(totals.charges_cents)} />
                                    <Summary label={t('Closing balances')} value={amount(totals.closing_balance_cents)} />
                                </div>
                                <p className="text-muted small mt-3 mb-0">{t('This report is for control against Excel. It does not lock the month.')}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div className="card table-card mb-4">
                    <div className="card-header table-card-header d-flex flex-wrap align-items-center gap-2">
                        <h5 className="mb-0 flex-shrink-0">{t('Player balances')}</h5>
                        <label htmlFor="finance-player-filter" className="visually-hidden">{t('Player')}</label>
                        <div className="players-filter-select-wrapper">
                            <select ref={playerFilter} id="finance-player-filter" className="form-select player-multiselect" multiple defaultValue={[]}>
                                {players.map(player => <option key={player.id} value={player.id}>{player.name}</option>)}
                            </select>
                        </div>
                        <a href={`${route('finance.export')}?month=${month}`} className="btn btn-secondary ms-auto flex-shrink-0">{t('Export CSV')}</a>
                    </div>
                    <div className="card-body">
                        <div className="table-responsive">
                            <table className="table align-middle">
                                <thead>
                                    <tr>
                                        <th>{t('Player')}</th>
                                        <th className="text-end">{t('Opening balance')}</th>
                                        <th className="text-end">{t('Top-ups')}</th>
                                        <th className="text-end">{t('Game charges')}</th>
                                        <th className="text-end">{t('Closing balance')}</th>
                                        <th>{t('Status')}</th>
                                        <th>{t('Actions')}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    {visiblePlayers.map(player => (
                                        <tr
                                            key={player.id}
                                            className="table-row-linkable"
                                            onClick={event => {
                                                if (!event.target.closest('a, button, select, input, textarea, form')) {
                                                    router.visit(route('finance.players.show', player.id));
                                                }
                                            }}
                                            onKeyDown={event => {
                                                if ((event.key === 'Enter' || event.key === ' ') && !event.target.closest('a, button, select, input, textarea, form')) {
                                                    event.preventDefault();
                                                    router.visit(route('finance.players.show', player.id));
                                                }
                                            }}
                                            tabIndex="0"
                                            role="link"
                                        >
                                            <td>
                                                <span>{player.name}</span>
                                                {player.inactive && <span className="badge bg-secondary ms-2">{t('Inactive')}</span>}
                                            </td>
                                            <td className="text-end">{amount(player.opening_balance_cents)}</td>
                                            <td className="text-end">{amount(player.topups_cents, true)}</td>
                                            <td className="text-end">{amount(-player.charges_cents, true)}</td>
                                            <td className="text-end fw-semibold">{amount(player.closing_balance_cents)}</td>
                                            <td><span className={`badge ${player.status === 'negative' ? 'bg-danger' : player.status === 'low' ? 'bg-warning text-dark' : 'bg-success'}`}>{statusLabel(player.status)}</span></td>
                                            <td className="actions">
                                                <Link href={route('finance.players.show', player.id)} className="btn btn-warning btn-sm">{t('Edit')}</Link>
                                            </td>
                                        </tr>
                                    ))}
                                </tbody>
                            </table>
                            {visiblePlayers.length === 0 && <div className="chart-empty-state">{t('No players found.')}</div>}
                        </div>
                    </div>
                </div>

                <div className="card table-card mb-4">
                    <div className="card-header table-card-header"><h5 className="mb-0">{t('Transactions in selected month')}</h5></div>
                    <div className="card-body">
                        <div className="table-responsive">
                            <table className="table align-middle">
                                <thead>
                                    <tr>
                                        <th>{t('Date')}</th>
                                        <th>{t('Player')}</th>
                                        <th>{t('Type')}</th>
                                        <th>{t('Game')}</th>
                                        <th className="text-end">{t('Amount')}</th>
                                        <th>{t('Actions')}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    {visibleTransactions.map(transaction => (
                                        <tr
                                            key={transaction.id}
                                            className="table-row-linkable"
                                            onClick={event => {
                                                if (!event.target.closest('a, button, select, input, textarea, form')) {
                                                    router.visit(route('finance.players.show', transaction.player_id));
                                                }
                                            }}
                                            onKeyDown={event => {
                                                if ((event.key === 'Enter' || event.key === ' ') && !event.target.closest('a, button, select, input, textarea, form')) {
                                                    event.preventDefault();
                                                    router.visit(route('finance.players.show', transaction.player_id));
                                                }
                                            }}
                                            tabIndex="0"
                                            role="link"
                                        >
                                            <td>{date(transaction.occurred_on)}</td>
                                            <td>{transaction.player_name}</td>
                                            <td>{t(transaction.type === 'top_up' ? 'Top-up' : transaction.type === 'opening_balance' ? 'Opening balance' : 'Game charge')}</td>
                                            <td>{transaction.game_id ? <Link href={route('finance.games.charges.edit', transaction.game_id)}>{date(transaction.game_date)}</Link> : '—'}</td>
                                            <td className={`text-end ${transaction.amount_cents < 0 ? 'text-danger' : 'text-success'}`}>{amount(transaction.amount_cents, true)}</td>
                                            <td className="actions">{transaction.editable && <Link href={route('finance.players.show', transaction.player_id)} className="btn btn-warning btn-sm me-1">{t('Edit')}</Link>}</td>
                                        </tr>
                                    ))}
                                </tbody>
                            </table>
                            {visibleTransactions.length === 0 && <div className="chart-empty-state">{t('No transactions in this month.')}</div>}
                        </div>
                    </div>
                </div>
            </div>
        </AppLayout>
    );
}

function Summary({ label, value }) {
    return (
        <div className="col-12 col-md-4">
            <div className="border rounded p-3 h-100">
                <div className="text-muted small">{label}</div>
                <div className="fs-5 fw-semibold">{value}</div>
            </div>
        </div>
    );
}
