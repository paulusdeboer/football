import { useForm } from '@inertiajs/react';
import GuestLayout from '../../Layouts/GuestLayout';
import { useTranslations } from '../../i18n';
import route from '../../route';

export default function ConfirmPassword() {
    const { t } = useTranslations();
    const { data, setData, post, processing, errors } = useForm({ password: '' });
    const submit = (event) => { event.preventDefault(); post(route('password.confirm')); };
    return <GuestLayout title="Confirm password"><div className="card shadow-lg border-0 rounded-lg my-5"><div className="card-header"><h3 className="text-center font-weight-light my-4">{t('Confirm password')}</h3></div><div className="card-body"><p className="text-center">{t('Please confirm your password before continuing.')}</p><form onSubmit={submit}><div className="form-floating mb-3"><input className={`form-control ${errors.password ? 'is-invalid' : ''}`} id="password" type="password" value={data.password} onChange={e => setData('password', e.target.value)} required placeholder="Password" /><label htmlFor="password">{t('Password')}</label>{errors.password && <div className="invalid-feedback">{errors.password}</div>}</div><div className="d-grid"><button disabled={processing} className="btn btn-primary" type="submit">{t('Confirm password')}</button></div></form></div></div></GuestLayout>;
}
