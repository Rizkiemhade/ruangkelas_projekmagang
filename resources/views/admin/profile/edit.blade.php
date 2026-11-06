@extends('layouts.admin')

@section('title', 'Edit Profil')

@push('scripts')
    <script src="{{ asset('assets/js/profile-photo.js') }}" defer></script>
@endpush

@section('content')
    <div class="page-header profile-page-header">
        <div>
            <h1 class="page-title">Edit profil</h1>
            <p class="page-subtitle">Kelola informasi akun dan foto profil admin.</p>
        </div>
        <a class="btn-custom btn-custom-outline-secondary" href="{{ route('admin.dashboard') }}">
            <i class="bi bi-arrow-left" aria-hidden="true"></i> Kembali
        </a>
    </div>

    <form
        class="profile-editor"
        method="post"
        enctype="multipart/form-data"
        action="{{ route('admin.profile.update') }}"
        data-profile-form
    >
        @csrf
        @method('put')

        <section class="profile-card profile-photo-card" aria-labelledby="profile-photo-heading">
            <div class="profile-photo-preview-wrap">
                <div class="profile-photo-preview">
                    <img
                        src="{{ $user->profile_photo_path ? Storage::disk('public')->url($user->profile_photo_path) : asset('assets/images/avatar.png') }}"
                        alt="Pratinjau foto profil"
                        data-profile-photo-preview
                        data-default-src="{{ $user->profile_photo_path ? Storage::disk('public')->url($user->profile_photo_path) : asset('assets/images/avatar.png') }}"
                    >
                </div>
                <span class="profile-photo-camera" aria-hidden="true"><i class="bi bi-camera-fill"></i></span>
            </div>

            <div class="profile-photo-details">
                <div>
                    <span class="profile-section-eyebrow">Akun administrator</span>
                    <h2 id="profile-photo-heading">{{ $user->name }}</h2>
                    <p>{{ $user->email }}</p>
                </div>

                <input
                    class="profile-photo-input"
                    id="profile-photo-input"
                    type="file"
                    name="profile_photo"
                    accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp"
                    data-profile-photo-input
                >
                <div class="profile-photo-actions">
                    <label class="btn-custom btn-custom-primary profile-photo-select" for="profile-photo-input">
                        <i class="bi bi-camera" aria-hidden="true"></i>
                        <span data-photo-button-label>Ganti foto</span>
                    </label>
                    <button class="profile-photo-reset" type="button" data-photo-reset hidden>Batalkan pilihan</button>
                </div>
                <p class="profile-photo-hint">JPG, PNG, atau WebP · Maks. 2 MB</p>
                <p class="profile-photo-error" role="alert" data-photo-error hidden></p>
                @error('profile_photo')<small class="field-error">{{ $message }}</small>@enderror
            </div>

            <div class="profile-photo-crop" data-photo-crop hidden>
                <label for="profile-photo-zoom">Sesuaikan pembingkaian</label>
                <div class="profile-photo-zoom-control">
                    <i class="bi bi-dash" aria-hidden="true"></i>
                    <input
                        id="profile-photo-zoom"
                        type="range"
                        min="1"
                        max="3"
                        step="0.05"
                        value="1"
                        aria-label="Perbesar atau perkecil foto"
                        data-photo-zoom
                    >
                    <i class="bi bi-plus" aria-hidden="true"></i>
                </div>
                <small>Geser untuk mengatur zoom. Foto akan dipotong menjadi lingkaran.</small>
            </div>
        </section>

        <section class="profile-card profile-information-card" aria-labelledby="profile-information-heading">
            <div class="profile-card-heading">
                <span class="profile-heading-icon"><i class="bi bi-person-lines-fill" aria-hidden="true"></i></span>
                <div>
                    <h2 id="profile-information-heading">Informasi pribadi</h2>
                    <p>Informasi ini digunakan untuk akun administrator Anda.</p>
                </div>
            </div>

            <div class="profile-fields">
                <label class="profile-field" for="profile-name">
                    <span>Nama lengkap</span>
                    <input
                        class="form-control-custom"
                        id="profile-name"
                        name="name"
                        value="{{ old('name', $user->name) }}"
                        required
                        maxlength="255"
                        autocomplete="name"
                    >
                    @error('name')<small class="field-error">{{ $message }}</small>@enderror
                </label>
                <label class="profile-field" for="profile-email">
                    <span>Alamat email</span>
                    <input
                        class="form-control-custom"
                        id="profile-email"
                        type="email"
                        name="email"
                        value="{{ old('email', $user->email) }}"
                        required
                        maxlength="255"
                        autocomplete="email"
                    >
                    @error('email')<small class="field-error">{{ $message }}</small>@enderror
                </label>
            </div>
        </section>

        <section class="profile-card profile-password-card" aria-labelledby="profile-password-heading">
            <div class="profile-card-heading">
                <span class="profile-heading-icon"><i class="bi bi-shield-lock" aria-hidden="true"></i></span>
                <div>
                    <h2 id="profile-password-heading">Keamanan akun</h2>
                    <p>Kosongkan bagian ini jika tidak ingin mengganti kata sandi.</p>
                </div>
            </div>

            <div class="profile-fields profile-password-fields">
                <label class="profile-field" for="current-password">
                    <span>Kata sandi saat ini</span>
                    <input
                        class="form-control-custom"
                        id="current-password"
                        type="password"
                        name="current_password"
                        autocomplete="current-password"
                    >
                    @error('current_password')<small class="field-error">{{ $message }}</small>@enderror
                </label>
                <label class="profile-field" for="new-password">
                    <span>Kata sandi baru</span>
                    <input
                        class="form-control-custom"
                        id="new-password"
                        type="password"
                        name="password"
                        autocomplete="new-password"
                    >
                    @error('password')<small class="field-error">{{ $message }}</small>@enderror
                </label>
                <label class="profile-field" for="password-confirmation">
                    <span>Konfirmasi kata sandi baru</span>
                    <input
                        class="form-control-custom"
                        id="password-confirmation"
                        type="password"
                        name="password_confirmation"
                        autocomplete="new-password"
                    >
                </label>
            </div>
        </section>

        <div class="profile-form-actions">
            <p><i class="bi bi-info-circle" aria-hidden="true"></i> Perubahan profil berlaku setelah disimpan.</p>
            <button class="btn-custom btn-custom-primary profile-save-button" type="submit" data-profile-save>
                <i class="bi bi-check2" aria-hidden="true"></i> Simpan perubahan
            </button>
        </div>
    </form>
@endsection
