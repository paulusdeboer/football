import { Link, useForm } from '@inertiajs/react';
import GuestLayout from '../../Layouts/GuestLayout';
import { useTranslations } from '../../i18n';
import route from '../../route';

export default function ForgotPassword({ status }) {
    const { t } = useTranslations();
    const { data, setData, post, processing, errors } = useForm({ name: '' });
    const submit = (event) => { event.preventDefault(); post(route('password.email')); };
    return <GuestLayout title="Reset password"><div className="card shadow-lg border-0 rounded-lg my-5"><div className="card-header"><h3 className="text-center font-weight-light my-4">{t('Reset password')}</h3></div><div className="card-body">
        {status && <div className="alert alert-success">{status}</div>}
        <form onSubmit={submit}><div className="form-floating mb-3"><input className={`form-control ${errors.name ? 'is-invalid' : ''}`} id="inputName" value={data.name} onChange={e => setData('name', e.target.value)} required autoFocus placeholder="name" /><label htmlFor="inputName">{t('Username')}</label>{errors.name && <div className="invalid-feedback">{errors.name}</div>}</div><div className="d-grid gap-2"><button disabled={processing} type="submit" className="btn btn-primary">{t('Send password reset link')}</button></div></form>
    </div><div className="card-footer text-center py-3"><div className="small"><Link href={route('login')}>{t('Return to login')}</Link></div></div></div></GuestLayout>;
}
