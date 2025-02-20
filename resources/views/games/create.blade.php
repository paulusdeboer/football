@extends('layouts.app')

@section('extra-scripts')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
@endsection

@section('content')
    <div class="d-flex justify-content-between align-items-end">
        <div>
            <h1 class="mt-4">{{ __('Welcome') }} {{ $user->name }}</h1>
            <ol class="breadcrumb mb-4">
                <li class="breadcrumb-item active">{{ __('Games') }}</li>
            </ol>
        </div>
    </div>
    <div class="card mb-4">
        <div class="card-header">{{ __('Create a new game') }}</div>
        <div class="card-body">
            @include('games.partials.form')
        </div>
    </div>
@endsection
