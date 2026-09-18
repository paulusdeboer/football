import { Link, router, useForm } from '@inertiajs/react';
import { useState } from 'react';
import AppLayout from '../../Layouts/AppLayout';
import { openDatePicker } from '../../datePicker';
import { useTranslations } from '../../i18n';
import route from '../../route';

const amount = cents => {
    const value = Number(cents || 0) / 100;
    return `${value < 0 ? '-' : ''}€ ${Math.abs(value).toFixed(2).replace('.', ',')}`;
};

const date = value => value ? new Date(value).toLocaleDateString('nl-NL') : '';

export default function FinancePlayer({ player, balanceCents = 0, transactions = [] }) {
    const { t } = useTranslations();

    return (
        <AppLayout title={`${t('Player balance')}: ${player.name}`}>
            <div className="container-fluid px-4">
                <div className={`card mb-4 ${balanceCents < 0 ? 'border-danger' : ''}`}>
                    <div className="card-header d-flex justify-content-end">
                        <Link href={route('finance.index')} className="btn btn-secondary">{t('Back to finance')}</Link>
                    </div>
                    <div className="card-body d-flex flex-wrap justify-content-between align-items-center gap-3">
                        <div>
                            {player.inactive && <span className="badge bg-secondary mb-2">{t('Inactive')}</span>}
                            <div className="text-muted small">{t('Current balance')}</div>
                            <div className={`display-6 fw-semibold ${balanceCents < 0 ? 'text-danger' : 'text-success'}`}>{amount(balanceCents)}</div>
                        </div>
                        {balanceCents < 0 && <div className="alert alert-danger mb-0">{t('This balance is negative.')}</div>}
                    </div>
                </div>

                <div className="card table-card">
                    <div className="card-header table-card-header"><h5 className="mb-0">{t('Transaction history')}</h5></div>
                    <div className="card-body">
                        <div className="table-responsive">
                            <table className="table align-middle">
                                <thead>
                                    <tr>
                                        <th>{t('Date')}</th>
                                        <th>{t('Type')}</th>
                                        <th>{t('Game')}</th>
                                        <th>{t('Participant')}</th>
                                        <th className="text-end">{t('Amount')}</th>
                                        <th>{t('Actions')}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    {transactions.map(transaction => (
                                        <TransactionRow key={transaction.id} transaction={transaction} t={t} />
                                    ))}
                                </tbody>
                            </table>
                            {transactions.length === 0 && <div className="chart-empty-state">{t('No transactions found.')}</div>}
                        </div>
                    </div>
                </div>
            </div>
        </AppLayout>
    );
}

function TransactionRow({ transaction, t }) {
    const editable = transaction.editable;
    const clickable = editable || Boolean(transaction.game_id);
    const [editing, setEditing] = useState(false);
    const formId = `transaction-edit-${transaction.id}`;
    const form = useForm({
        occurred_on: transaction.occurred_on ?? '',
        amount: (Number(transaction.amount_cents || 0) / 100).toFixed(2),
    });
    const submit = event => {
        event.preventDefault();
        form.put(route('finance.transactions.update', transaction.id), {
            preserveScroll: true,
            onSuccess: () => setEditing(false),
        });
    };
    const activateRow = event => {
        if (!clickable || event.target.closest('a, button, select, input, textarea, form, details, summary')) return;
        if (editable) {
            setEditing(true);
        } else {
            router.visit(route('finance.games.charges.edit', transaction.game_id));
        }
    };
    const activateRowWithKeyboard = event => {
        if ((event.key !== 'Enter' && event.key !== ' ') || !clickable || event.target.closest('a, button, select, input, textarea, form, details, summary')) return;
        event.preventDefault();
        if (editable) {
            setEditing(true);
        } else {
            router.visit(route('finance.games.charges.edit', transaction.game_id));
        }
    };

    return (
        <tr
            className={clickable ? 'table-row-linkable' : undefined}
            onClick={activateRow}
            onKeyDown={activateRowWithKeyboard}
            tabIndex={clickable ? 0 : undefined}
            role={clickable ? (editable ? 'button' : 'link') : undefined}
        >
            <td>
                {editing ? (
                    <input
                        form={formId}
                        className="form-control form-control-sm"
                        type="date"
                        value={form.data.occurred_on}
                        onClick={openDatePicker}
                        onChange={event => form.setData('occurred_on', event.target.value)}
                        required
                    />
                ) : date(transaction.occurred_on)}
            </td>
            <td>{t(transaction.type === 'top_up' ? 'Top-up' : transaction.type === 'opening_balance' ? 'Opening balance' : 'Game charge')}</td>
            <td>{transaction.game_id ? <Link href={route('finance.games.charges.edit', transaction.game_id)}>{date(transaction.game_date)}</Link> : '—'}</td>
            <td>{transaction.participant_name ?? '—'}</td>
            <td className={`text-end ${transaction.amount_cents < 0 ? 'text-danger' : 'text-success'}`}>
                {editing ? (
                    <div className="input-group input-group-sm" style={{ minWidth: '9rem' }}>
                        <span className="input-group-text">€</span>
                        <input
                            form={formId}
                            className="form-control"
                            type="number"
                            step="0.01"
                            value={form.data.amount}
                            onChange={event => form.setData('amount', event.target.value)}
                            required
                        />
                    </div>
                ) : amount(transaction.amount_cents)}
            </td>
            <td className="actions">
                {editable && !editing && <button type="button" className="btn btn-warning btn-sm" onClick={() => setEditing(true)}>{t('Edit')}</button>}
                {editable && editing && (
                    <form id={formId} onSubmit={submit}>
                        <button type="submit" className="btn btn-sm btn-primary" disabled={form.processing}>{t('Save')}</button>
                    </form>
                )}
            </td>
        </tr>
    );
}
