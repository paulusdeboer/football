import { Link, useForm } from '@inertiajs/react';
import GuestLayout from '../../Layouts/GuestLayout';
import { useTranslations } from '../../i18n';
import route from '../../route';

export default function Register() {
    const { t } = useTranslations();
    const { data, setData, post, processing, errors } = useForm({ name: '', email: '', password: '', password_confirmation: '' });
    const submit = (event) => { event.preventDefault(); post(route('register')); };
    const field = (name, label, type = 'text') => <div className="form-floating mb-3"><input className={`form-control ${errors[name] ? 'is-invalid' : ''}`} id={name} type={type} value={data[name]} onChange={e => setData(name, e.target.value)} required autoComplete={type === 'password' ? 'new-password' : name} placeholder={label} /><label htmlFor={name}>{t(label)}</label>{errors[name] && <div className="invalid-feedback">{errors[name]}</div>}</div>;
    return <GuestLayout title="Create account"><div className="card shadow-lg border-0 rounded-lg my-5"><div className="card-header"><h3 className="text-center font-weight-light my-4">{t('Create account')}</h3></div><div className="card-body"><form onSubmit={submit}>{field('name', 'Name')}{field('email', 'Email address', 'email')}{field('password', 'Password', 'password')}{field('password_confirmation', 'Confirm password', 'password')}<div className="d-grid"><button disabled={processing} className="btn btn-primary" type="submit">{t('Create account')}</button></div></form></div><div className="card-footer text-center py-3"><div className="small"><Link href={route('login')}>{t('Have an account? Go to login')}</Link></div></div></div></GuestLayout>;
}
