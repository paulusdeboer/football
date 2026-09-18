import { Link, router } from '@inertiajs/react';
import { useEffect, useRef, useState } from 'react';
import AppLayout from '../../Layouts/AppLayout';
import RoleBadge from '../../Components/RoleBadge';
import { useTranslations } from '../../i18n';
import route from '../../route';
import { clearSelect2SearchPreservingScroll } from '../../select2';

const date = (value) => value ? new Date(value).toLocaleDateString('nl-NL') : '';

export default function PlayersIndex({ players, sortBy, sortDirection, includeDeleted, canManagePlayers = false }) {
    const { t } = useTranslations();
    const playerFilter = useRef(null);
    const [selectedPlayerIds, setSelectedPlayerIds] = useState([]);

    useEffect(() => {
        const $select = window.jQuery(playerFilter.current);
        const updateFilter = () => setSelectedPlayerIds(($select.val() ?? []).map(String));
        const clearPlayerSearch = () => clearSelect2SearchPreservingScroll($select);

        $select.select2({
            theme: 'bootstrap-5',
            width: '100%',
            placeholder: t('All players'),
            closeOnSelect: false,
            dropdownCssClass: 'select2--small',
            selectionCssClass: 'compact-multiple-selection',
        });
        $select.on('change', updateFilter);
        $select.on('select2:selecting', clearPlayerSearch);

        return () => {
            $select.off('change', updateFilter);
            $select.off('select2:selecting', clearPlayerSearch);
            if ($select.hasClass('select2-hidden-accessible')) $select.select2('destroy');
        };
    }, []);

    useEffect(() => {
        const availablePlayerIds = new Set(players.map(player => String(player.id)));
        const validPlayerIds = selectedPlayerIds.filter(playerId => availablePlayerIds.has(playerId));

        if (validPlayerIds.length !== selectedPlayerIds.length) {
            setSelectedPlayerIds(validPlayerIds);
            if (playerFilter.current) window.jQuery(playerFilter.current).val(validPlayerIds).trigger('change.select2');
        }
    }, [players, selectedPlayerIds]);

    const sort = (column) => router.get(
        route('players.index'),
        {
            sort_by: column,
            sort_direction: sortBy === column && sortDirection === 'asc' ? 'desc' : 'asc',
            include_deleted: includeDeleted,
        },
        { preserveState: true },
    );

    const toggleDeleted = () => router.get(
        route('players.index'),
        {
            sort_by: sortBy,
            sort_direction: sortDirection,
            include_deleted: includeDeleted === '1' ? '0' : '1',
        },
        { preserveState: true },
    );

    const remove = (player) => {
        if (window.confirm(`${t('Delete')} ${player.name}?`)) {
            router.delete(route('players.destroy', player.id));
        }
    };

    const restore = (player) => router.patch(route('players.restore', player.id));
    const arrow = (column) => sortBy === column
        ? <i className={`fas fa-chevron-${sortDirection === 'asc' ? 'up' : 'down'} table-sort-icon`} aria-hidden="true" />
        : null;
    const openPlayer = (event, playerId) => {
        if (event.target.closest('a, button, select, input, textarea, form')) return;

        router.visit(canManagePlayers ? route('players.edit', playerId) : route('finance.players.show', playerId));
    };
    const visiblePlayers = selectedPlayerIds.length > 0
        ? players.filter(player => selectedPlayerIds.includes(String(player.id)))
        : players;

    return (
        <AppLayout title="Players">
            <div className="container-fluid px-4">
                <div className="card table-card mb-4">
                    <div className="card-header table-card-header d-flex flex-nowrap justify-content-between align-items-center gap-2">
                        <div className="d-flex flex-grow-1 flex-wrap align-items-center gap-2">
                            <button type="button" onClick={toggleDeleted} className={`btn btn-sm ${includeDeleted === '1' ? 'btn-secondary' : 'btn-primary'}`}>
                                {includeDeleted === '1' ? t('Active players only') : t('All players (including inactive)')}
                            </button>
                            <label htmlFor="player-filter" className="visually-hidden">{t('Player')}</label>
                            <div className="players-filter-select-wrapper">
                                <select ref={playerFilter} id="player-filter" className="form-select player-multiselect" multiple defaultValue={[]}>
                                    {players.map(player => <option key={player.id} value={player.id}>{player.name}</option>)}
                                </select>
                            </div>
                        </div>
                        {canManagePlayers && <Link href={route('players.create')} className="btn btn-primary flex-shrink-0 text-nowrap">{t('Create player')}</Link>}
                    </div>

                    <div className="card-body">
                        <div className="table-responsive">
                            <table className="table">
                                <thead>
                                    <tr>
                                        {[
                                            ['name', 'Player name'],
                                            ['username', 'Username'],
                                            ['email', 'Email'],
                                            ['rating', 'Rating'],
                                            ['type', 'Type'],
                                            ['created_at', 'Created at'],
                                        ].map(([key, label]) => (
                                            <th key={key}>
                                                <button type="button" className="table-sort-link" onClick={() => sort(key)}>
                                                    {t(label)} {arrow(key)}
                                                </button>
                                            </th>
                                        ))}
                                        <th>{t('Role')}</th>
                                        {canManagePlayers && <th>{t('Actions')}</th>}
                                    </tr>
                                </thead>
                                <tbody>
                                    {visiblePlayers.map((player) => (
                                        <tr
                                            key={player.id}
                                            className={player.deleted_at ? 'table-secondary' : 'table-row-linkable'}
                                            onClick={player.deleted_at ? undefined : (event) => openPlayer(event, player.id)}
                                            onKeyDown={player.deleted_at ? undefined : (event) => {
                                                if (event.key === 'Enter' || event.key === ' ') {
                                                    event.preventDefault();
                                                    openPlayer(event, player.id);
                                                }
                                            }}
                                            tabIndex={player.deleted_at ? undefined : '0'}
                                            role={player.deleted_at ? undefined : 'link'}
                                        >
                                            <td>{player.name}</td>
                                            <td>{player.user?.name ?? t('No username')}</td>
                                            <td>{player.user?.email ?? t('No email')}</td>
                                            <td>{(player.rating / 100).toFixed(2)}</td>
                                            <td>{t(player.type === 'attacker' ? 'Attacker' : player.type === 'defender' ? 'Defender' : 'Both')}</td>
                                            <td>{date(player.created_at)}</td>
                                            <td><RoleBadge role={player.user?.role ?? 'player'} /></td>
                                            {canManagePlayers && <td className="actions">
                                                {player.deleted_at ? (
                                                    <button type="button" onClick={() => restore(player)} className="btn btn-success btn-sm">
                                                        {t('Restore')}
                                                    </button>
                                                ) : (
                                                    <>
                                                        <Link href={route('players.edit', player.id)} className="btn btn-warning btn-sm me-1">
                                                            {t('Edit player')}
                                                        </Link>
                                                        <button type="button" onClick={() => remove(player)} className="btn btn-danger btn-sm">
                                                            {t('Delete player')}
                                                        </button>
                                                    </>
                                                )}
                                            </td>}
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
