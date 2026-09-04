import AppLayout from '../../Layouts/AppLayout';
import GivenRatings from '../../Components/GivenRatings';
import { useTranslations } from '../../i18n';

const date = (value) => {
    if (!value) return '';
    const parsed = new Date(value);
    return [parsed.getDate(), parsed.getMonth() + 1, parsed.getFullYear()]
        .map((part) => String(part).padStart(2, '0')).join('-');
};

export default function RatingsIndex({ games }) {
    const { t } = useTranslations();

    return (
        <AppLayout title="Given ratings">
            <div className="container-fluid px-4">
                <h1 className="mt-4">{t('Welcome')}</h1>

                <div className="card mb-4">
                    <div className="card-header">
                        <i className="fas fa-table me-1" />
                        {t('Ratings list')}
                    </div>
                    <div className="card-body">
                        <div className="table-responsive">
                            <table className="table">
                                <thead>
                                    <tr>
                                        <th>{t('Game date')}</th>
                                        <th>{t('Ratings')}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    {games.map((game) => (
                                        <tr key={game.id}>
                                            <td>{date(game.played_at)}</td>
                                            <td><GivenRatings {...game} /></td>
                                        </tr>
                                    ))}
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </AppLayout>
    );
}
