import { Link, useForm } from '@inertiajs/react';
import { useState } from 'react';
import AppLayout from '../../Layouts/AppLayout';
import { useTranslations } from '../../i18n';
import route from '../../route';

const empty = () => ({ name: '', email: '', rating: '', type: 'attacker' });
export default function PlayersCreate() {
    const { t } = useTranslations();
    const [rows, setRows] = useState([empty()]);
    const { post, processing, errors, setData } = useForm({ players: rows });
    const replaceRows = nextRows => {
        setRows(nextRows);
        setData('players', nextRows);
    };
    const update = (index, key, value) => replaceRows(rows.map((row, i) => i === index ? { ...row, [key]: value } : row));
    const submit = event => { event.preventDefault(); post(route('players.store')); };
    return <AppLayout title="Create players"><div className="container-fluid px-4"><div className="card mb-4"><div className="card-body"><form onSubmit={submit}>{rows.map((row, index) => <div className="row mb-3" key={index}><div className="col-3"><input className="form-control" value={row.name} onChange={e => update(index,'name',e.target.value)} placeholder={t('Name')} required /></div><div className="col-3"><input className="form-control" type="email" value={row.email} onChange={e => update(index,'email',e.target.value)} placeholder={t('Email')} required /></div><div className="col-3"><input className="form-control" type="number" min="0" max="10" step="0.1" value={row.rating} onChange={e => update(index,'rating',e.target.value)} placeholder={t('Rating')} required /></div><div className="col-2"><select className="form-select" value={row.type} onChange={e => update(index,'type',e.target.value)}><option value="attacker">{t('Attacker')}</option><option value="defender">{t('Defender')}</option><option value="both">{t('Both')}</option></select></div>{rows.length > 1 && <div className="col-1"><button type="button" className="btn btn-danger" onClick={() => replaceRows(rows.filter((_, i) => i !== index))}>{t('Remove')}</button></div>}</div>)}{errors.players && <div className="text-danger mb-3">{errors.players}</div>}<button type="button" className="btn btn-secondary me-2" onClick={() => replaceRows([...rows, empty()])}>{t('Extra player')}</button><button disabled={processing} type="submit" className="btn btn-primary">{t('Save')}</button><Link href={route('players.index')} className="btn btn-secondary ms-2">{t('Back to players list')}</Link></form></div></div></div></AppLayout>;
}
