@extends('layouts.app')
@section('title', 'Profil Saya')
@section('page_title', 'Profil Saya')
@section('breadcrumb')
    <li class="breadcrumb-item active">Profil</li>
@endsection

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-8">

        <div class="card mb-4">
            <div class="card-header bg-transparent">
                <i class="bi bi-person-circle me-2"></i>Informasi Profil
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('profile.update') }}" enctype="multipart/form-data">
                    @csrf @method('PATCH')
                    <div class="row g-3">
                        <div class="col-12 text-center mb-2">
                            <img src="{{ auth()->user()->avatar_url }}"
                                 class="rounded-circle border" width="90" height="90"
                                 id="avatarPreview" alt="avatar">
                            <div class="mt-2">
                                <label class="btn btn-sm btn-outline-secondary">
                                    <i class="bi bi-camera me-1"></i>Ganti Foto
                                    <input type="file" name="avatar" class="d-none" accept="image/*"
                                           onchange="previewAvatar(this)">
                                </label>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Nama Lengkap</label>
                            <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                                   value="{{ old('name', auth()->user()->name) }}" required>
                            @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Email</label>
                            <input type="email" name="email" class="form-control @error('email') is-invalid @enderror"
                                   value="{{ old('email', auth()->user()->email) }}" required>
                            @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">NIP</label>
                            <input type="text" name="nip" class="form-control"
                                   value="{{ old('nip', auth()->user()->nip) }}" placeholder="18 digit NIP">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Jabatan</label>
                            <input type="text" name="jabatan" class="form-control"
                                   value="{{ old('jabatan', auth()->user()->jabatan) }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Telepon</label>
                            <input type="text" name="telepon" class="form-control"
                                   value="{{ old('telepon', auth()->user()->telepon) }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Role</label>
                            <input type="text" class="form-control" readonly
                                   value="{{ auth()->user()->getRoleNames()->first() ?? '-' }}">
                        </div>
                        <div class="col-12">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-save me-1"></i>Simpan Perubahan
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        {{-- Ganti Password --}}
        <div class="card">
            <div class="card-header bg-transparent">
                <i class="bi bi-key me-2"></i>Ganti Password
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('password.update') }}">
                    @csrf @method('PUT')
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label">Password Lama</label>
                            <input type="password" name="current_password"
                                   class="form-control @error('current_password','updatePassword') is-invalid @enderror"
                                   required>
                            @error('current_password', 'updatePassword')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Password Baru</label>
                            <input type="password" name="password"
                                   class="form-control @error('password','updatePassword') is-invalid @enderror"
                                   required>
                            @error('password', 'updatePassword')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Konfirmasi Password</label>
                            <input type="password" name="password_confirmation" class="form-control" required>
                        </div>
                        <div class="col-12">
                            <button type="submit" class="btn btn-warning">
                                <i class="bi bi-shield-lock me-1"></i>Update Password
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

    </div>
</div>
@endsection

@push('scripts')
<script>
function previewAvatar(input) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = e => document.getElementById('avatarPreview').src = e.target.result;
        reader.readAsDataURL(input.files[0]);
    }
}
</script>
@endpush
