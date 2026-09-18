import { Link, useForm } from '@inertiajs/react';
import { useState } from 'react';
import AppLayout from '../../Layouts/AppLayout';
import Select2Field from '../../Components/Select2Field';
import { useTranslations } from '../../i18n';
import route from '../../route';

const empty = () => ({ name: '', email: '', rating: '', type: 'attacker' });
export default function PlayersCreate() {
    const { t } = useTranslations();
    const [rows, setRows] = useState([empty()]);
    const { post, processing, errors, setData } = useForm({ players: rows });
    const errorFor = (index, field) => errors[`players.${index}.${field}`];
    const replaceRows = nextRows => {
        setRows(nextRows);
        setData('players', nextRows);
    };
    const update = (index, key, value) => replaceRows(rows.map((row, i) => i === index ? { ...row, [key]: value } : row));
    const submit = event => { event.preventDefault(); post(route('players.store')); };
    return (
        <AppLayout title="Create players">
            <div className="container-fluid px-4">
                <div className="card mb-4">
                    <div className="card-header d-flex justify-content-end">
                        <Link href={route('players.index')} className="btn btn-secondary">{t('Back to players list')}</Link>
                    </div>
                    <div className="card-body">
                        <form onSubmit={submit}>
                            <div className="table-responsive">
                                <table className="table align-middle">
                                    <thead>
                                        <tr>
                                            <th>{t('Player name')}</th>
                                            <th>{t('Email')}</th>
                                            <th>{t('Rating')}</th>
                                            <th>{t('Type')}</th>
                                            <th></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        {rows.map((row, index) => {
                                            const nameError = errorFor(index, 'name');
                                            const emailError = errorFor(index, 'email');
                                            const ratingError = errorFor(index, 'rating');
                                            const typeError = errorFor(index, 'type');

                                            return (
                                                <tr key={index}>
                                                    <td>
                                                        <input
                                                            className={`form-control ${nameError ? 'is-invalid' : ''}`}
                                                            value={row.name}
                                                            onChange={e => update(index, 'name', e.target.value)}
                                                            placeholder={t('Name')}
                                                            required
                                                        />
                                                        {nameError && <div className="invalid-feedback">{nameError}</div>}
                                                    </td>
                                                    <td>
                                                        <input
                                                            className={`form-control ${emailError ? 'is-invalid' : ''}`}
                                                            type="email"
                                                            value={row.email}
                                                            onChange={e => update(index, 'email', e.target.value)}
                                                            placeholder={t('Email')}
                                                            required
                                                        />
                                                        {emailError && <div className="invalid-feedback">{emailError}</div>}
                                                    </td>
                                                    <td>
                                                        <input
                                                            className={`form-control ${ratingError ? 'is-invalid' : ''}`}
                                                            type="number"
                                                            min="0"
                                                            max="10"
                                                            step="0.1"
                                                            value={row.rating}
                                                            onChange={e => update(index, 'rating', e.target.value)}
                                                            placeholder={t('Rating')}
                                                            required
                                                        />
                                                        {ratingError && <div className="invalid-feedback">{ratingError}</div>}
                                                    </td>
                                                    <td>
                                                        <Select2Field
                                                            className={`form-select ${typeError ? 'is-invalid' : ''}`}
                                                            value={row.type}
                                                            onChange={value => update(index, 'type', value)}
                                                        >
                                                            <option value="attacker">{t('Attacker')}</option>
                                                            <option value="defender">{t('Defender')}</option>
                                                            <option value="both">{t('Both')}</option>
                                                        </Select2Field>
                                                        {typeError && <div className="invalid-feedback">{typeError}</div>}
                                                    </td>
                                                    <td className="actions">
                                                        <button
                                                            type="button"
                                                            className="btn btn-danger btn-sm table-remove-button"
                                                            onClick={() => replaceRows(rows.filter((_, i) => i !== index))}
                                                            disabled={rows.length === 1}
                                                        >
                                                            {t('Remove')}
                                                        </button>
                                                    </td>
                                                </tr>
                                            );
                                        })}
                                    </tbody>
                                </table>
                            </div>
                            {errors.players && <div className="text-danger mb-3">{errors.players}</div>}
                            <div className="d-flex flex-wrap gap-2 mt-3">
                                <button type="button" className="btn btn-secondary" onClick={() => replaceRows([...rows, empty()])}>
                                    {t('Extra player')}
                                </button>
                                <button disabled={processing} type="submit" className="btn btn-primary">{t('Save')}</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </AppLayout>
    );
}
