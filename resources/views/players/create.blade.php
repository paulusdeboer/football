@extends('layouts.app')

@section('extra-scripts')
    <!-- Styles for select2 -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" />

    <!-- Scripts for select2 -->
    <script src="https://cdn.jsdelivr.net/npm/jquery@3.5.0/dist/jquery.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    @vite(['resources/js/select2-bootstrap.js'])
@endsection

@section('content')
    <div class="d-flex justify-content-between align-items-end">
        <div>
            <h1 class="mt-4">{{ __('Welcome') }} {{ $user->name }}</h1>
            <ol class="breadcrumb mb-4">
                <li class="breadcrumb-item active">{{ __('Players') }}</li>
            </ol>
        </div>
        <button type="submit" form="game-form" class="btn btn-primary mb-3">{{ __('Create Player') }}</button>
    </div>
    <div class="card mb-4">
        <div class="card-header">{{ __('Create a New Player') }}</div>

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
            <form action="{{ route('players.store') }}" id="player-form" method="POST">
                @csrf

                <!-- Name field -->
                <div class="form-group">
                    <label for="name">{{ __('Name') }}:</label>
                    <input type="text" id="name" name="name" class="form-control mb-3" required>

                    @error('name')
                    <div class="alert alert-danger">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Email field -->
                <div class="form-group">
                    <label for="email">{{ __('Email') }}:</label>
                    <input type="email" id="email" name="email" class="form-control mb-3" required>

                    @error('email')
                    <div class="alert alert-danger">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Player rating field -->
                <div class="form-group">
                    <label for="rating">{{ __('Rating') }}:</label>
                    <input type="number" id="rating" name="rating" class="form-control mb-3" required min="5.0" max="10.0" step="0.1">

                    @error('rating')
                    <div class="alert alert-danger">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Player type selection -->
                <div class="form-group">
                    <label for="single-select-field">{{ __('Select player type') }}</label>
                    <select id="single-select-field" name="type" class="form-control form-select mb-3" required>
                        <option value="attacker">{{ __('Attacker') }}</option>
                        <option value="defender">{{ __('Defender') }}</option>
                        <option value="both">{{ __('All-rounder') }}</option>
                    </select>

                    @error('type')
                    <div class="alert alert-danger">{{ $message }}</div>
                    @enderror
                </div>
            </form>
            <a href="{{ route('games.index') }}" class="btn btn-secondary mt-3">{{ __('Back to Games List') }}</a>
        </div>
    </div>
@endsection


