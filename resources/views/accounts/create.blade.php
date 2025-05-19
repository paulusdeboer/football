@extends('layouts.app')

@section('content')
    <div class="d-flex justify-content-between align-items-end">
        <div>
            <h1 class="mt-4">{{ __('Create Account') }}</h1>
            <ol class="breadcrumb mb-4">
                <li class="breadcrumb-item active">{{ __('Accounts') }}</li>
            </ol>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-header">{{ __('New Account') }}</div>

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

            <form action="{{ route('accounts.store') }}" method="POST">
                @csrf

                <div class="row mb-3">
                    <div class="col-md-6 mb-3 mb-md-0">
                        <input type="text" name="name" class="form-control"
                               placeholder="{{ __('Account Name') }}" required>
                    </div>
                    <div class="col-md-6">
                        <div class="input-group">
                            <span class="input-group-text">€</span>
                            <input type="number" name="balance"
                                   class="form-control"
                                   placeholder="{{ __('Initial Balance') }}"
                                   required step="0.01">
                        </div>
                    </div>
                </div>

                <button type="submit"
                        class="btn btn-primary">{{ __('Save Account') }}</button>
            </form>
        </div>
    </div>
    <div class="mt-3">
        <a href="{{ route('accounts.index') }}"
           class="btn btn-secondary">{{ __('Back to accounts list') }}</a>
    </div>
@endsection
