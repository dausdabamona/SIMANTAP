@extends('layouts.app')
@section('title', $page ?? 'Segera Hadir')
@section('page_title', $page ?? 'Segera Hadir')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-6 text-center py-5">
        <div class="mb-4" style="font-size:4rem; opacity:.3">
            <i class="bi bi-tools"></i>
        </div>
        <h4 class="fw-bold">Sedang Dalam Pengembangan</h4>
        <p class="text-muted">
            Halaman <strong>{{ $page ?? 'ini' }}</strong> sedang dibangun.<br>
            Kembali ke <a href="{{ route('dashboard') }}" class="text-decoration-none">Dashboard</a>.
        </p>
        <a href="{{ route('dashboard') }}" class="btn btn-primary">
            <i class="bi bi-house me-1"></i>Kembali ke Dashboard
        </a>
    </div>
</div>
@endsection
