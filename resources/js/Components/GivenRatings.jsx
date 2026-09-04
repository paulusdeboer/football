import { useTranslations } from '../i18n';

const ratingBadgeClass = (value) => {
    const score = Number(value);

    if (score < 6) return 'rating-badge--red';
    if (score < 7) return 'rating-badge--orange';
    if (score < 8) return 'rating-badge--yellow';

    return 'rating-badge--green';
};

export default function GivenRatings({ players = [], ratingsByPlayer = [] }) {
    const { t } = useTranslations();

    if (ratingsByPlayer.length === 0) {
        return <span className="text-muted">{t('No ratings given')}</span>;
    }

    return (
        <div className="given-ratings d-flex flex-wrap">
            {ratingsByPlayer.map((group) => (
                <div key={group.rating_player_id}>
                    <strong className="given-ratings__name">{group.rating_player_name}</strong>
                    <table className="given-ratings__table table table-sm mb-0">
                        <tbody>
                            {players.map((player) => {
                                const rating = group.ratings.find((item) => item.rated_player_id === player.id);

                                return (
                                    <tr key={player.id}>
                                        <td>{player.name}</td>
                                        <td className="text-end">
                                            {rating ? (
                                                <span className={`badge rating-badge ${ratingBadgeClass(rating.rating_value)}`}>
                                                    {rating.rating_value}
                                                </span>
                                            ) : (
                                                <span className="badge bg-dark-subtle">-</span>
                                            )}
                                        </td>
                                    </tr>
                                );
                            })}
                        </tbody>
                    </table>
                </div>
            ))}
        </div>
    );
}
