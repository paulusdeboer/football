import { Link, useForm } from '@inertiajs/react';
import { useEffect, useRef } from 'react';
import Select2Field from '../../Components/Select2Field';
import AppLayout from '../../Layouts/AppLayout';
import { openDatePicker } from '../../datePicker';
import { useTranslations } from '../../i18n';
import route from '../../route';
import { clearSelect2SearchPreservingScroll } from '../../select2';

const emptyEntry = today => ({ row_key: crypto.randomUUID(), player_id: '', occurred_on: today, amount: '' });

export default function FinanceTopUps({ players = [], today }) {
    const { t } = useTranslations();
    const { data, setData, post, processing, errors } = useForm({
        type: 'top_up',
        entries: [emptyEntry(today)],
    });

    const updateEntry = (index, field, value) => {
        const entries = data.entries.map((entry, entryIndex) => entryIndex === index ? { ...entry, [field]: value } : entry);
        setData('entries', entries);
    };
    const addEntry = () => setData('entries', [...data.entries, emptyEntry(today)]);
    const removeEntry = index => setData('entries', data.entries.filter((_, entryIndex) => entryIndex !== index));
    const submit = event => {
        event.preventDefault();
        post(route('finance.topups.store'));
    };

    return (
        <AppLayout title="Enter top-ups">
            <div className="container-fluid px-4">
                <div className="card mb-4">
                    <div className="card-header d-flex justify-content-end">
                        <Link href={route('finance.index')} className="btn btn-secondary">{t('Back to finance')}</Link>
                    </div>
                    <div className="card-body">
                        <form onSubmit={submit}>
                            <div className="row g-3 mb-4">
                                <div className="col-md-5">
                                    <label className="form-label" htmlFor="transaction-type">{t('Transaction type')}</label>
                                    <Select2Field id="transaction-type" className="form-select" value={data.type} onChange={value => setData('type', value)}>
                                        <option value="top_up">{t('Top-up')}</option>
                                        <option value="opening_balance">{t('Opening balance')}</option>
                                    </Select2Field>
                                </div>
                                <div className="col-md-7 d-flex align-items-end">
                                    <div className="form-text">{data.type === 'opening_balance' ? t('Use this once to enter the controlled starting balance from Excel.') : t('Each row is saved as a separate top-up transaction.')}</div>
                                </div>
                            </div>

                            <div className="table-responsive">
                                <table className="table align-middle">
                                    <thead>
                                        <tr>
                                            <th>{t('Player')}</th>
                                            <th>{t('Date')}</th>
                                            <th>{t('Amount')}</th>
                                            <th></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        {data.entries.map((entry, index) => (
                                            <tr key={entry.row_key}>
                                                <td>
                                                    <TopUpPlayerSelect
                                                        value={entry.player_id}
                                                        players={players}
                                                        invalid={Boolean(errors[`entries.${index}.player_id`])}
                                                        onChange={value => updateEntry(index, 'player_id', value)}
                                                    />
                                                    {errors[`entries.${index}.player_id`] && <div className="invalid-feedback">{errors[`entries.${index}.player_id`]}</div>}
                                                </td>
                                                <td>
                                                    <input className={`form-control ${errors[`entries.${index}.occurred_on`] ? 'is-invalid' : ''}`} type="date" value={entry.occurred_on} onClick={openDatePicker} onChange={event => updateEntry(index, 'occurred_on', event.target.value)} required />
                                                    {errors[`entries.${index}.occurred_on`] && <div className="invalid-feedback">{errors[`entries.${index}.occurred_on`]}</div>}
                                                </td>
                                                <td>
                                                    <div className="input-group">
                                                        <span className="input-group-text">€</span>
                                                        <input className={`form-control ${errors[`entries.${index}.amount`] ? 'is-invalid' : ''}`} type="number" min={data.type === 'opening_balance' ? undefined : '0.01'} step="0.01" value={entry.amount} onChange={event => updateEntry(index, 'amount', event.target.value)} required />
                                                    </div>
                                                    {errors[`entries.${index}.amount`] && <div className="invalid-feedback d-block">{errors[`entries.${index}.amount`]}</div>}
                                                </td>
                                                <td className="text-end actions">
                                                    <button type="button" className="btn btn-danger btn-sm table-remove-button" onClick={() => removeEntry(index)} disabled={data.entries.length === 1}>{t('Remove')}</button>
                                                </td>
                                            </tr>
                                        ))}
                                    </tbody>
                                </table>
                            </div>

                            <div className="d-flex flex-wrap gap-2 mt-3">
                                <button type="button" className="btn btn-secondary" onClick={addEntry}>{t('Add row')}</button>
                                <button type="submit" className="btn btn-primary" disabled={processing}>{t('Save transactions')}</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </AppLayout>
    );
}

function TopUpPlayerSelect({ value, players, invalid, onChange }) {
    const { t } = useTranslations();
    const selectRef = useRef(null);

    useEffect(() => {
        const $select = window.jQuery(selectRef.current);
        const clearSearch = () => clearSelect2SearchPreservingScroll($select);

        $select.select2({
            theme: 'bootstrap-5',
            width: '100%',
            placeholder: t('Select player'),
            allowClear: true,
            dropdownCssClass: 'select2--small',
        });
        $select.on('select2:selecting', clearSearch);

        return () => {
            $select.off('select2:selecting', clearSearch);
            if ($select.hasClass('select2-hidden-accessible')) $select.select2('destroy');
        };
    }, []);

    useEffect(() => {
        const $select = window.jQuery(selectRef.current);
        const updateValue = () => onChange($select.val() ?? '');

        $select.on('change', updateValue);

        return () => $select.off('change', updateValue);
    }, [onChange]);

    useEffect(() => {
        if (selectRef.current) window.jQuery(selectRef.current).val(value ?? '').trigger('change.select2');
    }, [value]);

    return (
        <select ref={selectRef} className={`form-select finance-player-select ${invalid ? 'is-invalid' : ''}`} defaultValue={value ?? ''} required>
            <option value="">{t('Select player')}</option>
            {players.map(player => <option key={player.id} value={player.id}>{player.name}</option>)}
        </select>
    );
}
