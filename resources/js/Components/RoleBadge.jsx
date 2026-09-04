import { useTranslations } from '../i18n';

export default function RoleBadge({ role }) {
    const { t } = useTranslations();
    const isAdmin = role === 'admin';

    return (
        <span className={`badge ${isAdmin ? 'bg-primary' : 'bg-secondary'}`}>
            {t(isAdmin ? 'Admin' : 'Player')}
        </span>
    );
}
