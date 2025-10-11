@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <h2 class="mb-5">Edycja profilu</h2>
            @if (session('status') === 'profile-updated')
                <div class="alert alert-success">Zapisano zmiany profilu.</div>
            @endif
            <div class="mb-4">
                @include('profile.partials.update-profile-information-form')
            </div>
            <div class="mb-4">
                @include('profile.partials.update-password-form')
            </div>
            <div>
                @include('profile.partials.delete-user-form')
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
  document.addEventListener('DOMContentLoaded', () => {
    const sel = document.getElementById('theme');
    if (sel) {
      sel.addEventListener('change', () => {
        document.documentElement.setAttribute('data-bs-theme', sel.value || 'dark');
      });
    }
  });
</script>
@endpush
