@extends('layouts.app')

@section('title', 'My Profile')

@section('content')
<div class="page-header">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3">
        <div>
            <h2><i class="bi bi-person-circle me-2"></i>My Profile</h2>
            <p class="text-muted mb-0">View and manage your account information</p>
        </div>
        <a href="{{ route('user.dashboard') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-2"></i>Back to Dashboard
        </a>
    </div>
</div>

<div class="row">
    <div class="col-12 col-md-8">
        <div class="card mb-3">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-info-circle me-2"></i>Profile Information</h5>
            </div>
            <div class="card-body">
                <div class="row g-4">
                    <div class="col-12 col-md-6">
                        <div class="mb-3">
                            <label class="text-muted small text-uppercase fw-semibold">Name</label>
                            <div class="fw-semibold fs-5">{{ $user->name }}</div>
                        </div>
                    </div>
                    <div class="col-12 col-md-6">
                        <div class="mb-3">
                            <label class="text-muted small text-uppercase fw-semibold">Email Address</label>
                            <div class="fw-semibold fs-5">{{ $user->email }}</div>
                        </div>
                    </div>
                    <div class="col-12 col-md-6">
                        <div class="mb-3">
                            <label class="text-muted small text-uppercase fw-semibold">Role</label>
                            <div>
                                <span class="badge bg-primary fs-6 px-3 py-2">
                                    <i class="bi bi-person me-1"></i>User
                                </span>
                            </div>
                        </div>
                    </div>
                    <div class="col-12 col-md-6">
                        <div class="mb-3">
                            <label class="text-muted small text-uppercase fw-semibold">Member Since</label>
                            <div class="fw-semibold">
                                <i class="bi bi-calendar3 me-1"></i>{{ $user->created_at->format('F d, Y') }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-key me-2"></i>Change Password</h5>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('user.profile.password') }}">
                    @csrf
                    <div class="mb-3">
                        <label for="current_password" class="form-label fw-semibold">
                            <i class="bi bi-lock me-1"></i>Current Password <span class="text-danger">*</span>
                        </label>
                        <div class="password-input-wrapper">
                            <input type="password" 
                                   class="form-control @error('current_password') is-invalid @enderror" 
                                   id="current_password" 
                                   name="current_password" 
                                   required>
                            <button type="button" class="password-toggle-btn" aria-label="Toggle password visibility">
                                <i class="bi bi-eye-slash"></i>
                            </button>
                        </div>
                        @error('current_password')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="mb-3">
                        <label for="password" class="form-label fw-semibold">
                            <i class="bi bi-key me-1"></i>New Password <span class="text-danger">*</span>
                        </label>
                        <div class="password-input-wrapper">
                            <input type="password" 
                                   class="form-control @error('password') is-invalid @enderror" 
                                   id="password" 
                                   name="password" 
                                   required>
                            <button type="button" class="password-toggle-btn" aria-label="Toggle password visibility">
                                <i class="bi bi-eye-slash"></i>
                            </button>
                        </div>
                        @error('password')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <small class="text-muted">Password must be at least 8 characters long.</small>
                    </div>
                    <div class="mb-3">
                        <label for="password_confirmation" class="form-label fw-semibold">
                            <i class="bi bi-key-fill me-1"></i>Confirm New Password <span class="text-danger">*</span>
                        </label>
                        <div class="password-input-wrapper">
                            <input type="password" 
                                   class="form-control" 
                                   id="password_confirmation" 
                                   name="password_confirmation" 
                                   required>
                            <button type="button" class="password-toggle-btn" aria-label="Toggle password visibility">
                                <i class="bi bi-eye-slash"></i>
                            </button>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-check-circle me-2"></i>Update Password
                    </button>
                </form>
            </div>
        </div>
    </div>

    <div class="col-12 col-md-4 mt-3 mt-md-0">
        <div class="card">
            <div class="card-body text-center">
                <div class="bg-primary bg-gradient rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 100px; height: 100px;">
                    <span class="text-white fw-bold" style="font-size: 2.5rem;">{{ substr($user->name, 0, 1) }}</span>
                </div>
                <h4 class="mb-1">{{ $user->name }}</h4>
                <p class="text-muted mb-3">{{ $user->email }}</p>
                <span class="badge bg-primary fs-6 px-3 py-2">
                    <i class="bi bi-person me-1"></i>User
                </span>
            </div>
        </div>
    </div>
</div>
@endsection

