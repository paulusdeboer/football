import { Link, useForm } from '@inertiajs/react';
import AppLayout from '../../Layouts/AppLayout';
import { useTranslations } from '../../i18n';
import route from '../../route';

export default function PlayersEdit({ player }) {
    const { t } = useTranslations();
    const { data, setData, put, processing, errors } = useForm({ name: player.name ?? '', email: player.email ?? '', rating: player.rating ?? '', type: player.type ?? 'both' });
    const submit = event => { event.preventDefault(); put(route('players.update', player.id)); };
    return <AppLayout title="Edit player"><div className="container-fluid px-4"><h1 className="mt-4">{t('Edit player')}</h1><div className="card mb-4"><div className="card-body"><form onSubmit={submit}><div className="row mb-3"><div className="col-3"><input className={`form-control ${errors.name ? 'is-invalid' : ''}`} value={data.name} onChange={e => setData('name',e.target.value)} placeholder={t('Name')} required /></div><div className="col-3"><input className={`form-control ${errors.email ? 'is-invalid' : ''}`} type="email" value={data.email} onChange={e => setData('email',e.target.value)} placeholder={t('Email')} required /></div><div className="col-3"><input className={`form-control ${errors.rating ? 'is-invalid' : ''}`} type="number" min="0" max="10" step="0.01" value={data.rating} onChange={e => setData('rating',e.target.value)} placeholder={t('Rating')} required /></div><div className="col-2"><select className="form-select" value={data.type} onChange={e => setData('type',e.target.value)}><option value="attacker">{t('Attacker')}</option><option value="defender">{t('Defender')}</option><option value="both">{t('Both')}</option></select></div></div><button disabled={processing} type="submit" className="btn btn-primary">{t('Save player')}</button><Link href={route('players.index')} className="btn btn-secondary ms-2">{t('Back to players list')}</Link></form></div></div></div></AppLayout>;
}
