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

            <form action="{{ route('accounts.update', $account->id) }}"
                  method="POST">
                @csrf
                @method('PUT')

                <div class="row mb-3">
                    <div class="col-6">
                        <input type="text" name="name" class="form-control"
                               placeholder="{{ __('Account Name') }}"
                               value="{{ old('name', $account->name) }}"
                               required>
                    </div>
                    <div class="col-6">
                        <div class="input-group">
                            <span class="input-group-text">€</span>
                            <input type="number" name="balance"
                                   class="form-control"
                                   value="{{ old('balance', $account->balance) }}"
                                   placeholder="{{ __('Balance') }}" required
                                   step="0.01" disabled>
                        </div>
                    </div>
                </div>

                <button type="submit"
                        class="btn btn-primary">{{ __('Update Account') }}</button>
            </form>
        </div>
    </div>

    <!-- Transaction Table -->
    <div class="card mb-4">
        <div class="card-header">{{ __('Transactions') }}</div>
        <div class="card-body">
            <form id="transaction-form">
                @csrf
                <div class="row mb-3">
                    <div class="col-2">
                        <input type="date" name="date" class="form-control"
                               required>
                    </div>
                    <div class="col-3">
                        <select name="player_id"
                                class="form-control form-select">
                            <option
                                value="">{{ __('Select Player (Optional)') }}</option>
                            @foreach ($players as $player)
                                <option
                                    value="{{ $player->id }}">{{ $player->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-2">
                        <div class="input-group">
                            <span class="input-group-text">€</span>
                            <input type="number" name="amount"
                                   class="form-control"
                                   placeholder="{{ __('Amount') }}" required
                                   step="0.01">
                        </div>
                    </div>
                    <div class="col-2 d-flex align-items-end">
                        <button type="submit"
                                class="btn btn-primary w-100">{{ __('Add Transaction') }}</button>
                    </div>
                </div>
            </form>
            <table class="table table-bordered"
                   id="transaction-table">
                <thead>
                <tr>
                    <th>{{ __('Date') }}</th>
                    <th>{{ __('Player') }}</th>
                    <th>{{ __('Amount') }}</th>
                    <th>{{ __('Actions') }}</th>
                </tr>
                </thead>
                <tbody>
                @foreach ($transactions as $transaction)
                    <tr>
                        <td>{{ $transaction->date }}</td>
                        <td>{{ $transaction->player ? $transaction->player->name : 'N/A' }}</td>
                        <td>
                            €{{ number_format($transaction->amount, 2, ',', '.') }}</td>
                        <td>
                            <button class="btn btn-danger btn-sm"
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
                                    <td>${response.player_name ? response.player_name : 'N/A'}</td>
                                    <td>€${response.amount}</td>
                                    <td><button class="btn btn-danger btn-sm" data-id="${response.id}">{{ __('Delete') }}</button></td>
                                </tr>
                            `;
                            $('#transaction-table tbody').prepend(newRow);
                        },
                        error: function (xhr) {
                            console.error(xhr.responseText);
                            alert('Error: ' + xhr.responseText);
                        }
                    });
                });
            }
        }, 100);
    });
</script>
