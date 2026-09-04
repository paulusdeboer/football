import { Link, useForm, usePage } from '@inertiajs/react';
import GuestLayout from '../../Layouts/GuestLayout';
import { useTranslations } from '../../i18n';
import route from '../../route';

export default function Login({ canResetPassword = true }) {
    const { t } = useTranslations();
    const { data, setData, post, processing, errors } = useForm({ name: '', password: '', remember: false });
    const status = usePage().props.flash?.status;
    const submit = (event) => { event.preventDefault(); post(route('login')); };

    return <GuestLayout title="Login"><div className="card shadow-lg border-0 rounded-lg my-5"><div className="card-header"><h3 className="text-center font-weight-light my-4">{t('Login')}</h3></div><div className="card-body">
        {status && <div className="alert alert-success">{status}</div>}
        <form onSubmit={submit}>
            <div className="form-floating mb-3"><input className={`form-control ${errors.name ? 'is-invalid' : ''}`} id="name" name="name" value={data.name} onChange={e => setData('name', e.target.value)} required autoFocus placeholder="name" /><label htmlFor="name">{t('Username')}</label>{errors.name && <div className="invalid-feedback">{errors.name}</div>}</div>
            <div className="form-floating mb-3"><input className={`form-control ${errors.password ? 'is-invalid' : ''}`} id="inputPassword" type="password" name="password" value={data.password} onChange={e => setData('password', e.target.value)} required placeholder="Password" /><label htmlFor="inputPassword">{t('Password')}</label>{errors.password && <div className="invalid-feedback">{errors.password}</div>}</div>
            <div className="form-check mb-3"><input className="form-check-input" id="inputRememberPassword" type="checkbox" checked={data.remember} onChange={e => setData('remember', e.target.checked)} /><label className="form-check-label" htmlFor="inputRememberPassword">{t('Remember password')}</label></div>
            <div className="d-flex align-items-center justify-content-between mt-4 mb-0">{canResetPassword && <Link className="small" href={route('password.request')}>{t('Forgot password?')}</Link>}<button disabled={processing} type="submit" className="btn btn-primary">{t('Login')}</button></div>
        </form>
    </div></div></GuestLayout>;
}
