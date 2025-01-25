@extends('layouts.main')

@section('content')
<!-- CONTENT HEADER -->
<section class="content-header">
    <div class="container-fluid">
        <div class="mb-2 row">
            <div class="col-sm-6">
                <h1>{{__('Application API')}}</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="{{route('home')}}">{{__('Dashboard')}}</a></li>
                    <li class="breadcrumb-item"><a href="{{route('admin.api.index')}}">{{__('Application API')}}</a></li>
                    <li class="breadcrumb-item"><a class="text-muted" href="{{route('admin.api.create')}}">{{__('Create')}}</a></li>
                </ol>
            </div>
        </div>
    </div>
</section>
<!-- END CONTENT HEADER -->

<!-- MAIN CONTENT -->
<section class="content">
    <div class="container-fluid">
        <form action="{{route('admin.api.store')}}" method="POST">
            @csrf
            <!-- Card: Description -->
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body">
                            <div class="form-group">
                                <label for="description">{{__('Description')}}</label>
                                <input value="{{old('description')}}" id="description" name="description" type="text" class="form-control @error('description') is-invalid @enderror">
                                @error('description')
                                    <div class="invalid-feedback">
                                        {{$message}}
                                    </div>
                                @enderror
                            </div>
                            <div class="form-group">
                                <label for="allowed_ips">{{__('Allowed IPs')}}</label>
                                <select
                                    id="allowed_ips"
                                    name="allowed_ips[]"
                                    class="form-control select2"
                                    multiple="multiple"
                                    data-placeholder="{{__('Type an IP and press Enter')}}"
                                >
                                    @if(old('allowed_ips'))
                                        @foreach(old('allowed_ips') as $ip)
                                            <option value="{{ $ip }}" selected>{{ $ip }}</option>
                                        @endforeach
                                    @endif
                                </select>
                                @error('allowed_ips')
                                    <div class="invalid-feedback">
                                        {{$message}}
                                    </div>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Card: Permissions -->
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body">
                            <div id="abilities-container" class="row">
                                @foreach($permissions as $key => $permission)
                                    <div class="mb-4 col-md-6 col-lg-4">
                                        <div class="form-group">
                                            <label>{{ $key }}</label>
                                            <div class="mt-2">
                                                <div class="custom-control custom-switch">
                                                    <input type="checkbox"
                                                        class="custom-control-input"
                                                        id="{{ $permission }}_read"
                                                        name="abilities[{{ $permission }}][]"
                                                        value="read"
                                                        onchange="handleReadChange('{{ $permission }}')"
                                                        {{ in_array('read', old("abilities.{$permission}", [])) ? 'checked' : '' }}>
                                                    <label class="custom-control-label" for="{{ $permission }}_read">
                                                        {{__('Read')}}
                                                    </label>
                                                </div>
                                                <div class="mt-2 custom-control custom-switch">
                                                    <input type="checkbox"
                                                        class="custom-control-input"
                                                        id="{{ $permission }}_write"
                                                        name="abilities[{{ $permission }}][]"
                                                        value="write"
                                                        onchange="handleWriteChange('{{ $permission }}')"
                                                        {{ in_array('write', old("abilities.{$permission}", [])) ? 'checked' : '' }}>
                                                    <label class="custom-control-label" for="{{ $permission }}_write">
                                                        {{__('Write')}}
                                                    </label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                            <!-- Save Button -->
                            <div class="row">
                                <div class="text-right col-12">
                                    <button type="submit" class="btn btn-primary">
                                        {{__('Save')}}
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</section>
<!-- END CONTENT -->
    <script>
        $(document).ready(function() {
            $('#allowed_ips').select2({
                tags: true,
                tokenSeparators: [',', ' '],
                width: '100%',
                createTag: function(params) {
                    var term = $.trim(params.term);
                    if (term === '') {
                        return null;
                    }
                    return {
                        id: term,
                        text: term,
                        newTag: true
                    };
                }
            });
        });
    </script>
@endsection
