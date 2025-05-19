@extends('layouts.app')

@section('content')
    <div class="d-flex justify-content-between align-items-end">
        <div>
            <h1 class="mt-4">{{ __('Settings') }}</h1>
            <ol class="breadcrumb mb-4">
                <li class="breadcrumb-item active">{{ __('Settings') }}</li>
            </ol>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    <div class="card mb-4">
        <div class="card-header">{{ __('Application Settings') }}</div>

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

            <form action="{{ route('settings.update') }}" method="POST">
                @csrf
                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>{{ __('Setting') }}</th>
                                <th>{{ __('Value') }}</th>
                                <th>{{ __('Description') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($settings as $index => $setting)
                                <tr>
                                    <td>
                                        {{ $setting->display_name }}
                                        <input type="hidden" name="settings[{{ $index }}][key]" value="{{ $setting->key }}">
                                    </td>
                                    <td>
                                        @if($setting->key === 'cost_per_game')
                                            <div class="input-group">
                                                <span class="input-group-text">€</span>
                                                <input type="number" class="form-control" name="settings[{{ $index }}][value]"
                                                    value="{{ old('settings.' . $index . '.value', $setting->value) }}" required step="0.01">
                                            </div>
                                        @else
                                            <input type="text" class="form-control" name="settings[{{ $index }}][value]"
                                                value="{{ old('settings.' . $index . '.value', $setting->value) }}" required>
                                        @endif
                                    </td>
                                    <td>
                                        {{ $setting->description }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="text-center">{{ __('No settings found.') }}</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <button type="submit" class="btn btn-primary">{{ __('Save Settings') }}</button>
            </form>
        </div>
    </div>
@endsection