@php use Carbon\Carbon; @endphp
@extends('layouts.app')

@section('content')
    <div class="d-flex justify-content-between align-items-end">
        <div>
            <h1 class="mt-4">{{ __('Accounts') }}</h1>
            <ol class="breadcrumb mb-4">
                <li class="breadcrumb-item active">{{ __('Accounts') }}</li>
            </ol>
        </div>
        <a href="{{ route('accounts.create') }}"
           class="btn btn-primary mb-3">{{ __('Add Account') }}</a>
    </div>

    <div class="card mb-4">
        <div
            class="card-header d-flex justify-content-between align-items-center">
            <div>
                <i class="fas fa-table me-1"></i>
                {{ __('Account List') }}
            </div>
        </div>

        <div class="card-body">
            <table class="table">
                <thead>
                <tr>
                    <th>
                        <a class="align-middle"
                           href="{{ route('accounts.index', ['sort_by' => 'name', 'sort_direction' => $sortBy === 'name' && $sortDirection === 'asc' ? 'desc' : 'asc']) }}">
                            {{ __('Account Name') }}
                            {!! $sortBy === 'name' ? ($sortDirection === 'asc'
                                ? '<svg stroke="currentColor" fill="currentColor" stroke-width="0" version="1.2" baseProfile="tiny" viewBox="0 0 24 24" height="14px" width="14px" xmlns="http://www.w3.org/2000/svg"><path d="M5.8 9.7l6.2 6.3 6.2-6.3c.2-.2.3-.5.3-.7s-.1-.5-.3-.7c-.2-.2-.4-.3-.7-.3h-11c-.3 0-.5.1-.7.3-.2.2-.3.4-.3.7s.1.5.3.7z"></path></svg>'
                                : '<svg stroke="currentColor" fill="currentColor" stroke-width="0" version="1.2" baseProfile="tiny" viewBox="0 0 24 24" height="14px" width="14px" xmlns="http://www.w3.org/2000/svg"><path d="M18.2 13.3l-6.2-6.3-6.2 6.3c-.2.2-.3.5-.3.7s.1-.5.3-.7c.2-.2.4-.3.7-.3h11c.3 0 .5-.1.7-.3.2-.2.3-.5.3-.7s-.1-.5-.3-.7z"></path></svg>'
                            ) : '' !!}
                        </a>
                    </th>
                    <th>
                        <a class="align-middle"
                           href="{{ route('accounts.index', ['sort_by' => 'balance', 'sort_direction' => $sortBy === 'balance' && $sortDirection === 'asc' ? 'desc' : 'asc']) }}">
                            {{ __('Balance') }}
                            {!! $sortBy === 'balance' ? ($sortDirection === 'asc'
                                ? '<svg stroke="currentColor" fill="currentColor" stroke-width="0" version="1.2" baseProfile="tiny" viewBox="0 0 24 24" height="14px" width="14px" xmlns="http://www.w3.org/2000/svg"><path d="M5.8 9.7l6.2 6.3 6.2-6.3c.2-.2.3-.5.3-.7s-.1-.5-.3-.7c-.2-.2-.4-.3-.7-.3h-11c-.3 0-.5.1-.7.3-.2.2-.3.4-.3.7s.1.5.3.7z"></path></svg>'
                                : '<svg stroke="currentColor" fill="currentColor" stroke-width="0" version="1.2" baseProfile="tiny" viewBox="0 0 24 24" height="14px" width="14px" xmlns="http://www.w3.org/2000/svg"><path d="M18.2 13.3l-6.2-6.3-6.2 6.3c-.2.2-.3.5-.3.7s.1-.5.3-.7c.2-.2.4-.3.7-.3h11c.3 0 .5-.1.7-.3.2-.2.3-.5.3-.7s-.1-.5-.3-.7z"></path></svg>'
                            ) : '' !!}
                        </a>
                    </th>
                    <th>
                        <a class="align-middle"
                           href="{{ route('accounts.index', ['sort_by' => 'created_at', 'sort_direction' => $sortBy === 'created_at' && $sortDirection === 'asc' ? 'desc' : 'asc']) }}">
                            {{ __('Created At') }}
                            {!! $sortBy === 'created_at' ? ($sortDirection === 'asc'
                                ? '<svg stroke="currentColor" fill="currentColor" stroke-width="0" version="1.2" baseProfile="tiny" viewBox="0 0 24 24" height="14px" width="14px" xmlns="http://www.w3.org/2000/svg"><path d="M5.8 9.7l6.2 6.3 6.2-6.3c.2-.2.3-.5.3-.7s-.1-.5-.3-.7c-.2-.2-.4-.3-.7-.3h-11c-.3 0-.5.1-.7.3-.2.2-.3.4-.3.7s.1.5.3.7z"></path></svg>'
                                : '<svg stroke="currentColor" fill="currentColor" stroke-width="0" version="1.2" baseProfile="tiny" viewBox="0 0 24 24" height="14px" width="14px" xmlns="http://www.w3.org/2000/svg"><path d="M18.2 13.3l-6.2-6.3-6.2 6.3c-.2.2-.3.5-.3.7s.1-.5.3-.7c.2-.2.4-.3.7-.3h11c.3 0 .5-.1.7-.3.2-.2.3-.5.3-.7s-.1-.5-.3-.7z"></path></svg>'
                            ) : '' !!}
                        </a>
                    </th>
                    <th>
                        <a class="align-middle"
                           href="{{ route('accounts.index', ['sort_by' => 'updated_at', 'sort_direction' => $sortBy === 'updated_at' && $sortDirection === 'asc' ? 'desc' : 'asc']) }}">
                            {{ __('Updated At') }}
                            {!! $sortBy === 'updated_at' ? ($sortDirection === 'asc'
                                ? '<svg stroke="currentColor" fill="currentColor" stroke-width="0" version="1.2" baseProfile="tiny" viewBox="0 0 24 24" height="14px" width="14px" xmlns="http://www.w3.org/2000/svg"><path d="M5.8 9.7l6.2 6.3 6.2-6.3c.2-.2.3-.5.3-.7s-.1-.5-.3-.7c-.2-.2-.4-.3-.7-.3h-11c-.3 0-.5.1-.7.3-.2.2-.3.4-.3.7s.1.5.3.7z"></path></svg>'
                                : '<svg stroke="currentColor" fill="currentColor" stroke-width="0" version="1.2" baseProfile="tiny" viewBox="0 0 24 24" height="14px" width="14px" xmlns="http://www.w3.org/2000/svg"><path d="M18.2 13.3l-6.2-6.3-6.2 6.3c-.2.2-.3.5-.3.7s.1-.5.3-.7c.2-.2.4-.3.7-.3h11c.3 0 .5-.1.7-.3.2-.2.3-.5.3-.7s-.1-.5-.3-.7z"></path></svg>'
                            ) : '' !!}
                        </a>
                    </th>
                    <th>{{ __('Actions') }}</th>
                </tr>
                </thead>
                <tbody>
                @foreach ($accounts as $account)
                    <tr>
                        <td>{{ $account->name }}</td>
                        <td>
                            €{{ number_format($account->balance, 2, ',', '.') }}</td>
                        <td>{{ Carbon::parse($account->created_at)->format('d-m-Y') }}</td>
                        <td>{{ Carbon::parse($account->updated_at)->format('d-m-Y') }}</td>
                        <td class="actions">
                            <a href="{{ route('accounts.edit', $account) }}"
                               class="btn btn-warning btn-sm">{{ __('Edit') }}</a>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endsection
