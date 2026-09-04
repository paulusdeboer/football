import { Link, useForm } from '@inertiajs/react';
import GuestLayout from '../../Layouts/GuestLayout';
import { useTranslations } from '../../i18n';
import route from '../../route';

export default function ResetPassword({ token, email }) {
    const { t } = useTranslations();
    const { data, setData, post, processing, errors } = useForm({ token, email: email ?? '', password: '', password_confirmation: '' });
    const submit = (event) => { event.preventDefault(); post(route('password.update')); };
    const field = (name, label, type = 'text') => <div className="form-floating mb-3"><input className={`form-control ${errors[name] ? 'is-invalid' : ''}`} id={name} type={type} value={data[name]} onChange={e => setData(name, e.target.value)} required placeholder={label} /><label htmlFor={name}>{t(label)}</label>{errors[name] && <div className="invalid-feedback">{errors[name]}</div>}</div>;
    return <GuestLayout title="Reset password"><div className="card shadow-lg border-0 rounded-lg my-5"><div className="card-header"><h3 className="text-center font-weight-light my-4">{t('Reset password')}</h3></div><div className="card-body"><form onSubmit={submit}>{field('email', 'Email address', 'email')}{field('password', 'New password', 'password')}{field('password_confirmation', 'Confirm password', 'password')}<div className="d-grid"><button disabled={processing} className="btn btn-primary" type="submit">{t('Reset password')}</button></div></form></div><div className="card-footer text-center py-3"><div className="small"><Link href={route('login')}>{t('Return to login')}</Link></div></div></div></GuestLayout>;
}
