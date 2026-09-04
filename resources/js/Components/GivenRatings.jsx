import { useTranslations } from '../i18n';

export default function GivenRatings({ players = [], ratingsByPlayer = [] }) {
    const { t } = useTranslations();

    if (ratingsByPlayer.length === 0) {
        return <span className="text-muted">{t('No ratings given')}</span>;
    }

    return (
        <div className="d-flex flex-wrap gap-5">
            {ratingsByPlayer.map((group) => (
                <div key={group.rating_player_id}>
                    <strong>{group.rating_player_name}</strong>
                    <table className="table table-sm mb-0">
                        <tbody>
                            {players.map((player) => {
                                const rating = group.ratings.find((item) => item.rated_player_id === player.id);

                                return (
                                    <tr key={player.id}>
                                        <td>{player.name}</td>
                                        <td className="text-end">
                                            {rating ? (
                                                <span className="badge bg-primary">{rating.rating_value}</span>
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
