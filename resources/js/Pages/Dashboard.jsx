import AppLayout from '../Layouts/AppLayout';
import ChartCard from '../Components/ChartCard';
import { useTranslations } from '../i18n';

export default function Dashboard({ dates, ratings, gameLabels, averageRatings }) {
    const { t } = useTranslations();
    return <AppLayout title="Dashboard"><div className="container-fluid px-4"><div className="row"><div className="col-xl-6"><h5>{t('Player rating growth')}</h5><ChartCard id="playerRatingGrowth" type="line" labels={dates} values={ratings} label={t('Ratings')} /></div><div className="col-xl-6"><h5>{t('Average rating per game')}</h5><ChartCard id="averageRatingPerGame" type="bar" labels={gameLabels} values={averageRatings} label={t('Average ratings')} /></div></div></div></AppLayout>;
}
