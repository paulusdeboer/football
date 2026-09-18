import { Link, useForm } from '@inertiajs/react';
import AppLayout from '../../Layouts/AppLayout';
import RoleBadge from '../../Components/RoleBadge';
import Select2Field from '../../Components/Select2Field';
import { useTranslations } from '../../i18n';
import route from '../../route';

export default function PlayersEdit({ player }) {
    const { t } = useTranslations();
    const { data, setData, put, processing, errors } = useForm({
        name: player.name ?? '',
        username: player.username ?? '',
        email: player.email ?? '',
        rating: player.rating ?? '',
        type: player.type ?? 'both',
        role: player.role ?? 'player',
    });

    const submit = event => {
        event.preventDefault();
        put(route('players.update', player.id));
    };

    return (
        <AppLayout title="Edit player">
            <div className="container-fluid px-4">
                <form onSubmit={submit}>
                    <div className="row g-4 mb-4">
                        <div className="col-lg-7">
                            <div className="card h-100">
                                <div className="card-header d-flex justify-content-between align-items-center gap-2">
                                    <h5 className="mb-0">{t('Player details')}</h5>
                                    <Link href={route('players.index')} className="btn btn-secondary">{t('Back to players list')}</Link>
                                </div>
                                <div className="card-body d-flex flex-column">
                                    <div className="row g-3">
                                        <div className="col-12">
                                            <label className="form-label" htmlFor="name">{t('Player name')}</label>
                                            <input
                                                id="name"
                                                className={`form-control ${errors.name ? 'is-invalid' : ''}`}
                                                value={data.name}
                                                onChange={e => setData('name', e.target.value)}
                                                required
                                            />
                                            {errors.name && <div className="invalid-feedback">{errors.name}</div>}
                                        </div>
                                        <div className="col-md-6">
                                            <label className="form-label" htmlFor="rating">{t('Rating')}</label>
                                            <input
                                                id="rating"
                                                className={`form-control ${errors.rating ? 'is-invalid' : ''}`}
                                                type="number"
                                                min="0"
                                                max="10"
                                                step="0.01"
                                                value={data.rating}
                                                onChange={e => setData('rating', e.target.value)}
                                                required
                                            />
                                            {errors.rating && <div className="invalid-feedback">{errors.rating}</div>}
                                        </div>
                                        <div className="col-md-6">
                                            <label className="form-label" htmlFor="type">{t('Type')}</label>
                                            <Select2Field id="type" className={`form-select ${errors.type ? 'is-invalid' : ''}`} value={data.type} onChange={value => setData('type', value)}>
                                                <option value="attacker">{t('Attacker')}</option>
                                                <option value="defender">{t('Defender')}</option>
                                                <option value="both">{t('Both')}</option>
                                            </Select2Field>
                                            {errors.type && <div className="invalid-feedback">{errors.type}</div>}
                                        </div>
                                    </div>
                                    <button disabled={processing} type="submit" className="btn btn-primary align-self-start mt-auto">{t('Save player')}</button>
                                </div>
                            </div>
                        </div>

                        <div className="col-lg-5">
                            <div className="card h-100">
                                <div className="card-header d-flex justify-content-between align-items-center">
                                    <h5 className="mb-0">{t('User details')}</h5>
                                    <RoleBadge role={data.role} />
                                </div>
                                <div className="card-body">
                                    <div className="mb-3">
                                        <label className="form-label" htmlFor="username">{t('Username')}</label>
                                        <input
                                            id="username"
                                            className={`form-control ${errors.username ? 'is-invalid' : ''}`}
                                            value={data.username}
                                            onChange={e => setData('username', e.target.value)}
                                            required
                                        />
                                        <div className="form-text">{t('Used to log in.')}</div>
                                        {errors.username && <div className="invalid-feedback">{errors.username}</div>}
                                    </div>
                                    <div className="mb-3">
                                        <label className="form-label" htmlFor="email">{t('Email')}</label>
                                        <input
                                            id="email"
                                            className={`form-control ${errors.email ? 'is-invalid' : ''}`}
                                            type="email"
                                            value={data.email}
                                            onChange={e => setData('email', e.target.value)}
                                            required
                                        />
                                        {errors.email && <div className="invalid-feedback">{errors.email}</div>}
                                    </div>
                                    <div>
                                        <label className="form-label" htmlFor="role">{t('Role')}</label>
                                        <Select2Field id="role" className={`form-select ${errors.role ? 'is-invalid' : ''}`} value={data.role} onChange={value => setData('role', value)}>
                                            <option value="player">{t('Player')}</option>
                                            <option value="finance">{t('Finance manager')}</option>
                                            <option value="admin">{t('Admin')}</option>
                                        </Select2Field>
                                        {errors.role && <div className="invalid-feedback">{errors.role}</div>}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </AppLayout>
    );
}
