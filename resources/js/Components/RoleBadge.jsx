import { useTranslations } from '../i18n';

export default function RoleBadge({ role }) {
    const { t } = useTranslations();
    const isAdmin = role === 'admin';
    const isFinance = role === 'finance';

    return (
        <span className={`badge ${isAdmin ? 'bg-primary' : isFinance ? 'bg-warning text-dark' : 'bg-secondary'}`}>
            {t(isAdmin ? 'Admin' : isFinance ? 'Finance manager' : 'Player')}
        </span>
    );
}
