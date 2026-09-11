import {router} from '@inertiajs/react';
import {useEffect, useRef} from 'react';
import AppLayout from '../Layouts/AppLayout';
import ChartCard from '../Components/ChartCard';
import {useTranslations} from '../i18n';
import route from '../route';
import {clearSelect2SearchPreservingScroll} from '../select2';

const date = (value) => value ? new Date(value).toLocaleDateString('nl-NL') : '—';
const number = (value, decimals = 2) => value === null || value === undefined ? '—' : Number(value).toFixed(decimals);
const compactNumber = (value, decimals = 1) => {
    if (value === null || value === undefined) return '—';
    const numericValue = Number(value);

    return Number.isInteger(numericValue) ? String(numericValue) : numericValue.toFixed(decimals);
};

function StatCard({icon, label, value, hint}) {
    return <div className="col-sm-6 col-xl-3">
        <div className="card dashboard-stat-card h-100">
            <div className="card-body">
                <div className="dashboard-stat-card__icon"><i className={`fas ${icon}`} aria-hidden="true"/></div>
                <div className="dashboard-stat-card__label">{label}</div>
                <div className="dashboard-stat-card__value">{value}</div>
                {hint && <div className="dashboard-stat-card__hint">{hint}</div>}</div>
        </div>
    </div>;
}

function statusLabel(status, t) {
    return t({
        completed: 'Completed',
        pending: 'Pending',
        expired: 'Expired',
        send_failed: 'Send failed',
        revoked: 'Revoked'
    }[status] ?? status);
}

function statusClass(status) {
    return status === 'completed' ? 'success' : status === 'expired' ? 'warning' : status === 'send_failed' ? 'danger' : status === 'revoked' ? 'secondary' : 'primary';
}

function StatusBadges({counts, t}) {
    const statuses = ['completed', 'pending', 'expired', 'send_failed'];
    return <div className="d-flex flex-wrap gap-2">{statuses.map((status) => <span key={status}
                                                                                   className={`badge text-bg-${statusClass(status)}`}>{statusLabel(status, t)}: {counts?.[status] ?? 0}</span>)}</div>;
}

export default function Dashboard({filters, players, summary, ranking, charts, recentGames}) {
    const {t} = useTranslations();
    const periodSelect = useRef(null);
    const playersSelect = useRef(null);
    const selectedPlayerIds = (filters?.player_ids ?? []).map(String);
    const selectedPlayerKey = selectedPlayerIds.join(',');
    const playerScopeHint = summary.selected_player_name ? t('For :name', {name: summary.selected_player_name}) : null;
    const selectionScopeHint = summary.selected_player_count > 0
        ? t('For selected players')
        : null;
    const ratingTrendType = filters.period === '1' ? 'bar' : 'line';
    const showAverageRatingNames = charts.averageRatings.labels.length <= 20;
    const chartOptions = {scales: {y: {min: 5, max: 10}}};
    const averageRatingsOptions = {
        indexAxis: 'y',
        layout: {padding: {left: 12, right: 10}},
        scales: {
            x: {min: 5, max: 10, beginAtZero: false},
            y: {
                ticks: {
                    autoSkip: false,
                    padding: 8,
                    maxRotation: 0,
                    minRotation: 0,
                    ...(showAverageRatingNames ? {} : {callback: () => ''}),
                },
            },
        },
    };
    const playerResultsOptions = {
        indexAxis: 'y',
        layout: {padding: {left: 0, right: 10}},
        scales: {
            x: {beginAtZero: true, ticks: {stepSize: 1}},
            y: {
                afterFit: (axis) => {
                    axis.width = Math.max(axis.width, 120);
                },
                ticks: {autoSkip: false, padding: 8, maxRotation: 0, minRotation: 0},
            },
        },
    };
    const requestsOptions = {cutout: '62%'};

    const updateFilters = (nextFilters) => router.get(route('dashboard'), nextFilters, {
        preserveState: true,
        preserveScroll: true
    });

    useEffect(() => {
        const $period = window.jQuery(periodSelect.current);
        const $players = window.jQuery(playersSelect.current);
        $period.select2({theme: 'bootstrap-5', width: '100%', minimumResultsForSearch: Infinity});
        $players.select2({
            theme: 'bootstrap-5',
            width: '100%',
            placeholder: t('All players'),
            closeOnSelect: false,
            dropdownCssClass: 'select2--small',
            selectionCssClass: 'compact-multiple-selection',
        });

        const updatePeriod = () => updateFilters({period: $period.val(), player_ids: $players.val() ?? []});
        const updatePlayers = () => updateFilters({period: $period.val(), player_ids: $players.val() ?? []});
        const clearPlayerSearch = () => clearSelect2SearchPreservingScroll($players);
        $period.on('change', updatePeriod);
        $players.on('change', updatePlayers);
        $players.on('select2:select', clearPlayerSearch);

        return () => {
            $period.off('change', updatePeriod);
            $players.off('change', updatePlayers);
            $players.off('select2:select', clearPlayerSearch);
            if ($period.hasClass('select2-hidden-accessible')) $period.select2('destroy');
            if ($players.hasClass('select2-hidden-accessible')) $players.select2('destroy');
        };
    }, []);

    useEffect(() => {
        if (periodSelect.current) window.jQuery(periodSelect.current).val(filters.period).trigger('change.select2');
        if (playersSelect.current) window.jQuery(playersSelect.current).val(selectedPlayerIds).trigger('change.select2');
    }, [filters.period, selectedPlayerKey]);
    const translatedAverageRatings = {
        ...charts.averageRatings,
        datasets: charts.averageRatings.datasets.map((dataset) => ({...dataset, label: t(dataset.label)})),
    };
    const translatedRequests = {
        ...charts.ratingRequests,
        labels: charts.ratingRequests.labels.map((label) => statusLabel(label, t)),
        datasets: charts.ratingRequests.datasets.map((dataset) => ({...dataset, label: t(dataset.label)})),
    };
    const translatedPlayerResults = {
        ...charts.playerResults,
        labels: charts.playerResults.labels.map((label) => t({
            Wins: 'Wins',
            Draws: 'Draws',
            Losses: 'Losses'
        }[label] ?? label)),
        datasets: charts.playerResults.datasets.map((dataset) => ({...dataset, label: t(dataset.label)})),
    };
    const selectedPlayerNames = charts.playerResults.player_names ?? [];
    const playerResultsTitle = selectedPlayerNames.length === 1
        ? `${t('Match results for')} ${selectedPlayerNames[0]}`
        : selectedPlayerNames.length > 1
            ? t('Match results for selected players')
            : t('Player match results');
    const playerResultsDescription = selectedPlayerNames.length > 0
        ? t('Wins, draws and losses in the selected period.')
        : t('Select a player to see wins, draws and losses.');

    return (
        <AppLayout title="Dashboard">
            <div className="container-fluid px-4 dashboard-page">
                <div className="card dashboard-intro mb-4">
                    <div className="card-header dashboard-intro__header"><h5
                        className="mb-0">{t('Track player form, rankings and match quality.')}</h5></div>
                    <div className="card-body">
                        <div className="row g-3 align-items-end">
                            <div className="col-12 col-md-4 col-xl-3 dashboard-filter">
                                <label htmlFor="dashboard-period" className="form-label">{t('Period')}</label>
                                <select ref={periodSelect} id="dashboard-period"
                                        className="form-select dashboard-period-select" defaultValue={filters.period}>
                                    <option value="1">{t('Last game')}</option>
                                    <option value="5">{t('Last 5 games')}</option>
                                    <option value="10">{t('Last 10 games')}</option>
                                    <option value="20">{t('Last 20 games')}</option>
                                    <option value="all">{t('All games')}</option>
                                </select>
                            </div>
                            <div className="col-12 col-md-8 col-xl-9 dashboard-filter dashboard-filter--players">
                                <label htmlFor="dashboard-player" className="form-label">{t('Players')}</label>
                                <select ref={playersSelect} id="dashboard-player"
                                        className="form-select dashboard-player-select" multiple
                                        defaultValue={selectedPlayerIds}>
                                    {players.map((player) => <option key={player.id}
                                                                     value={player.id}>{player.name}</option>)}
                                </select>
                            </div>
                        </div>
                    </div>
                </div>

                <div className="row g-3 mb-4">
                    <StatCard icon="fa-star" label={t(summary.headline_rating_label)}
                              value={number(summary.headline_rating)}
                              hint={playerScopeHint ?? (selectionScopeHint ?? t('Across active players'))}/>
                    <StatCard icon="fa-futbol" label={t(summary.games_played_label ?? 'Games played')}
                              value={compactNumber(summary.games_played)} hint={playerScopeHint ?? selectionScopeHint}/>
                    <StatCard icon="fa-star" label={t('Average received rating')}
                              value={number(summary.average_received_rating, 1)}
                              hint={summary.selected_player_count > 0 ? `${playerScopeHint ?? selectionScopeHint} · ${t('Scale 5–10')}` : t('Scale 5–10')}/>
                    <StatCard icon="fa-envelope-open-text" label={t('Open rating requests')}
                              value={summary.open_rating_requests}
                              hint={summary.selected_player_count > 0 ? `${playerScopeHint ?? selectionScopeHint} · ${t('Pending or failed')}` : t('Pending or failed')}/>
                </div>

                <div className="row g-4">
                    <div className="col-12 col-xl-8">
                        <ChartCard
                            id="dashboard-rating-trend"
                            type={ratingTrendType}
                            title={t('Rating at start of game')}
                            description={t('Historical snapshot before each completed game.')}
                            labels={charts.ratingTrend.labels.map(date)}
                            datasets={charts.ratingTrend.datasets}
                            options={chartOptions}
                            emptyMessage={t('No rating history for this selection.')}
                        />
                    </div>
                    <div className="col-12 col-xl-4">
                        <ChartCard
                            id="dashboard-rating-requests"
                            type="doughnut"
                            title={t('Rating requests')}
                            description={t('Status for the selected games.')}
                            labels={translatedRequests.labels}
                            datasets={translatedRequests.datasets}
                            options={requestsOptions}
                            emptyMessage={t('No rating requests for this selection.')}
                        />
                    </div>
                    <div className="col-12 col-xl-6">
                        <ChartCard
                            id="dashboard-average-ratings"
                            type="bar"
                            title={t('Average rating per player')}
                            description={t('Average received rating for the selected period; response count in the tooltip.')}
                            labels={translatedAverageRatings.labels}
                            datasets={translatedAverageRatings.datasets}
                            options={averageRatingsOptions}
                            height="360px"
                            emptyMessage={t('No player ratings for this selection.')}
                        />
                    </div>
                    <div className="col-12 col-xl-6">
                        <ChartCard
                            id="dashboard-team-strength"
                            type="bar"
                            title={playerResultsTitle}
                            description={playerResultsDescription}
                            labels={translatedPlayerResults.labels}
                            datasets={translatedPlayerResults.datasets}
                            options={playerResultsOptions}
                            height="360px"
                            emptyMessage={selectedPlayerNames.length > 0 ? t('No completed games for this player in this period.') : t('Select a player to see results.')}
                        />
                    </div>
                </div>

                <div className="card table-card mb-4">
                    <div className="card-header table-card-header"><h5 className="mb-0">{t('Current ranking')}</h5>
                    </div>
                    <div className="card-body">
                        <div className="table-responsive">
                            <table className="table align-middle mb-0">
                                <thead>
                                <tr>
                                    <th>{t('Rank')}</th>
                                    <th>{t('Player')}</th>
                                    <th>{t('Current rating')}</th>
                                    <th>{t('Start rating')}</th>
                                    <th>{t('Difference')}</th>
                                    <th>{t('Games')}</th>
                                    <th>{t('Average rating')}</th>
                                </tr>
                                </thead>
                                <tbody>
                                {ranking.map((player) => <tr key={player.id}
                                                             className={selectedPlayerIds.includes(String(player.id)) ? 'table-active' : ''}>
                                    <td><span className="dashboard-rank">{player.rank}</span></td>
                                    <td className="fw-semibold">{player.name}</td>
                                    <td>{number(player.rating)}</td>
                                    <td>{number(player.start_rating)}</td>
                                    <td className={player.difference > 0 ? 'text-success' : player.difference < 0 ? 'text-danger' : ''}>{player.difference === null ? '—' : `${player.difference > 0 ? '+' : ''}${number(player.difference)}`}</td>
                                    <td>{player.games_played}</td>
                                    <td>{number(player.average_rating, 1)}{player.ratings_count > 0 &&
                                        <span className="text-muted small ms-1">({player.ratings_count})</span>}</td>
                                </tr>)}
                                </tbody>
                            </table>
                            {ranking.length === 0 &&
                                <div className="chart-empty-state">{t('No active players found.')}</div>}
                        </div>
                    </div>
                </div>

                <div className="card table-card mb-4">
                    <div className="card-header table-card-header"><h5 className="mb-0">{t('Recent games')}</h5></div>
                    <div className="card-body">
                        <div className="table-responsive">
                            <table className="table align-middle mb-0">
                                <thead>
                                <tr>
                                    <th>{t('Game date')}</th>
                                    <th>{t('Result')}</th>
                                    <th>{t('Players')}</th>
                                    <th>{t('Rating requests')}</th>
                                </tr>
                                </thead>
                                <tbody>{recentGames.map((game) => <tr key={game.id}>
                                    <td>{date(game.played_at)}</td>
                                    <td>{game.team1_score} - {game.team2_score}</td>
                                    <td>{game.players_count}</td>
                                    <td><StatusBadges counts={game.request_counts} t={t}/></td>
                                </tr>)}</tbody>
                            </table>
                            {recentGames.length === 0 &&
                                <div className="chart-empty-state">{t('No completed games found.')}</div>}
                        </div>
                    </div>
                </div>
            </div>
        </AppLayout>
    );
}
