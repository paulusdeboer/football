@extends('layouts.app')

@section('content')
    <div class="d-flex justify-content-between align-items-end">
        <div>
            <h1 class="mt-4">{{ __('Edit Account') }}</h1>
            <ol class="breadcrumb mb-4">
                <li class="breadcrumb-item active">{{ __('Accounts') }}</li>
            </ol>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-header">{{ __('Edit Account') }}</div>

        <div class="card-body">
            @if ($errors->any())
                <div class="alert alert-danger">
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('accounts.update', $account->id) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="row mb-3">
                    <div class="col-md-6 mb-3 mb-md-0">
                        <input type="text" name="name" class="form-control" placeholder="{{ __('Account Name') }}"
                            value="{{ old('name', $account->name) }}" required>
                    </div>
                    <div class="col-md-6">
                        <div class="input-group">
                            <span class="input-group-text">€</span>
                            <input type="number" name="balance" class="form-control"
                                value="{{ old('balance', $account->balance) }}" placeholder="{{ __('Balance') }}" required
                                step="0.01" disabled>
                        </div>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary">{{ __('Update Account') }}</button>
            </form>
        </div>
    </div>

    <!-- Transaction Table -->
    <div class="card mb-4">
        <div class="card-header">{{ __('Transactions') }}</div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered" id="transaction-table">
                    <thead>
                        <tr>
                            <th class="date-col" style="width: 25%">{{ __('Date') }}</th>
                            <th class="player-col" style="width: 35%">{{ __('Player') }}</th>
                            <th class="amount-col" style="width: 25%">{{ __('Amount') }}</th>
                            <th class="action-col" style="width: 15%">{{ __('Actions') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr id="input-row">
                            <form id="transaction-form">
                                @csrf
                                <td>
                                    <input type="date" name="date" class="form-control" required>
                                </td>
                                <td>
                                    <select name="player_id" class="form-control form-select">
                                        <option value="">{{ __('Select Player (Optional)') }}</option>
                                        @foreach ($players as $player)
                                            <option value="{{ $player->id }}">{{ $player->name }}</option>
                                        @endforeach
                                    </select>
                                </td>
                                <td>
                                    <div class="input-group">
                                        <span class="input-group-text">€</span>
                                        <input type="number" name="amount" class="form-control"
                                            placeholder="{{ __('Amount') }}" required step="0.01">
                                    </div>
                                </td>
                                <td>
                                    <button type="submit" class="btn btn-primary w-100">{{ __('Add') }}</button>
                                </td>
                            </form>
                        </tr>
                        @foreach ($transactions as $transaction)
                            <tr>
                                <td>{{ $transaction->date }}</td>
                                <td class="text-truncate"
                                    title="{{ $transaction->player ? $transaction->player->name : 'N/A' }}">
                                    {{ $transaction->player ? $transaction->player->name : 'N/A' }}
                                </td>
                                <td class="amount-cell text-end">€ {{ number_format($transaction->amount, 2, ',', '.') }}</td>
                                <td>
                                    <button class="btn btn-danger btn-sm delete-transaction w-100"
                                        data-id="{{ $transaction->id }}">{{ __('Delete') }}</button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
@endsection

    <script>
        document.addEventListener("DOMContentLoaded", function () {
            var waitForJQuery = setInterval(function () {
                if (window.jQuery) {
                    clearInterval(waitForJQuery);

                    $('#transaction-form').on('submit', function (e) {
                        e.preventDefault();

                        let formData = $(this).serialize();
                        let accountId = @json($account->id);
                        formData += '&account_id=' + accountId;

                        $.ajax({
                            url: '{{ route("transactions.store") }}',
                            method: 'POST',
                            data: formData,
                            headers: {
                                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                            },
                            success: function (response) {
                                let newRow = `
                                <tr>
                                    <td>${response.date}</td>
                                    <td class="text-truncate" title="${response.player_name ? response.player_name : 'N/A'}">${response.player_name ? response.player_name : 'N/A'}</td>
                                    <td class="amount-cell text-end">€ ${Number(response.amount).toFixed(2).replace('.', ',')}</td>
                                    <td><button class="btn btn-danger btn-sm delete-transaction w-100" data-id="${response.id}">{{ __('Delete') }}</button></td>
                                </tr>
                            `;
                                // Voeg de nieuwe rij toe na de input-rij
                                $(newRow).insertAfter('#input-row');
                                attachDeleteListeners();

                                // Formulier velden leegmaken
                                $('#transaction-form')[0].reset();

                                // Update het account balance als het beschikbaar is
                                if (response.account_balance !== null) {
                                    $('input[name="balance"]').val(response.account_balance);
                                }
                            },
                            error: function (xhr) {
                                console.error(xhr.responseText);
                                alert('Error: ' + xhr.responseText);
                            }
                        });
                    });

                    // Functie voor het toevoegen van event listeners aan alle delete knoppen
                    function attachDeleteListeners() {
                        $('.delete-transaction').off('click').on('click', function () {
                            if (confirm('{{ __("Are you sure you want to delete this transaction?") }}')) {
                                const transactionId = $(this).data('id');
                                const row = $(this).closest('tr');

                                $.ajax({
                                    url: `{{ url('transactions') }}/${transactionId}`,
                                    method: 'DELETE',
                                    headers: {
                                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                                    },
                                    success: function (response) {
                                        row.fadeOut(300, function () {
                                            $(this).remove();
                                        });

                                        // Update het account balance als het beschikbaar is
                                        if (response.account_balance !== null) {
                                            $('input[name="balance"]').val(response.account_balance);
                                        }
                                    },
                                    error: function (xhr) {
                                        console.error(xhr.responseText);
                                        alert('Error: ' + xhr.responseText);
                                    }
                                });
                            }
                        });
                    }

                    // Direct de delete event listeners toevoegen op pagina laden
                    attachDeleteListeners();
                }
            }, 100);
        });
    </script>