@php use Carbon\Carbon; @endphp
@extends('layouts.app')

@section('content')
    <div class="d-flex justify-content-between align-items-end">
        <div>
            <h1 class="mt-4">{{ __('Welcome') }} {{ $user->name }}</h1>
            <ol class="breadcrumb mb-4">
                <li class="breadcrumb-item active">{{ __('Players') }}</li>
            </ol>
        </div>
        <a href="{{ route('players.create') }}" class="btn btn-primary mb-3">{{ __('Create player') }}</a>
    </div>
    <div class="card mb-4">
        <div class="card-header">
            <i class="fas fa-table me-1"></i>
            {{ __('Player list') }}
        </div>

        <div class="card-body">
            <table class="table">
                <thead>
                <tr>
                    <th>{{ __('Name') }}</th>
                    <th>{{ __('Email') }}</th>
                    <th>{{ __('Rating') }}</th>
                    <th>{{ __('Created at') }}</th>
                    <th>{{ __('Actions') }}</th>
                </tr>
                </thead>
                <tbody>
                @foreach ($players as $player)
                    <tr>
                        <td>{{ $player->name }}</td>
                        <td>{{ $player->user?->email ?? __('No email') }}</td>
                        <td>{{ $player->rating }}</td>
                        <td>{{ Carbon::parse($player->created_at)->format('d-m-Y') }}</td>
                        <td>
                            <a href="{{ route('players.edit', $player) }}"
                               class="btn btn-warning btn-sm">{{ __('Edit player') }}</a>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endsection
