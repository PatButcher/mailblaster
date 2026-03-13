@extends('layouts.admin')
@section('title', 'Contacts - MailBlast')
@section('page-title', 'Generate Contacts')

@section('content')
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0">Generate Contacts</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Home</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('admin.contacts.index') }}">Contacts</a></li>
                    <li class="breadcrumb-item active">Generate</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<div class="content">
    <div class="container-fluid">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Generate Numerical Email Addresses</h3>
            </div>
            <form action="{{ route('admin.contacts.generate.post') }}" method="POST">
                @csrf
                <div class="card-body">
                    <div class="form-group">
                        <label for="start_number">Starting Number</label>
                        <input type="number" name="start_number" class="form-control @error('start_number') is-invalid @enderror" id="start_number" placeholder="e.g., 1" value="{{ old('start_number', 1) }}" min="0" required>
                        @error('start_number')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                        @enderror
                    </div>
                    <div class="form-group">
                        <label for="count">Number of Addresses to Generate</label>
                        <input type="number" name="count" class="form-control @error('count') is-invalid @enderror" id="count" placeholder="e.g., 100" value="{{ old('count', 100) }}" min="1" max="99999" required>
                        @error('count')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                        @enderror
                    </div>
                    <div class="form-group">
                        <label for="digits">Number of Digits (e.g., 5 for 00001)</label>
                        <input type="number" name="digits" class="form-control @error('digits') is-invalid @enderror" id="digits" placeholder="e.g., 5" value="{{ old('digits', 5) }}" min="1" max="10" required>
                        @error('digits')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                        @enderror
                    </div>
                    <div class="form-group">
                        <label for="domain">Domain</label>
                        <input type="text" name="domain" class="form-control @error('domain') is-invalid @enderror" id="domain" placeholder="e.g., example.com" value="{{ old('domain', 'example.com') }}" required>
                        @error('domain')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                        @enderror
                    </div>
                    <div class="form-group">
                        <label for="tags">Tags (comma-separated)</label>
                        <input type="text" name="tags" class="form-control @error('tags') is-invalid @enderror" id="tags" placeholder="e.g., new, leads" value="{{ old('tags') }}">
                        @error('tags')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                        @enderror
                    </div>
                </div>
                <div class="card-footer">
                    <button type="submit" class="btn btn-primary">Generate & Add Contacts</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
