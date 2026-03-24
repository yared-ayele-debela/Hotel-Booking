{{-- Hotel / travel themed panel background. Set $authHotelBackground before including (defaults to login-bg). --}}
@php
    $authHotelBackground = $authHotelBackground ?? asset('images/auth/login-bg.jpg');
@endphp
<style>
.auth-bg--hotel-booking {
  background-image: url('{{ $authHotelBackground }}') !important;
  background-position: center center;
  background-size: cover;
  background-repeat: no-repeat;
}
.auth-bg--hotel-booking .bg-overlay.bg-primary {
  opacity: 1 !important;
  background: linear-gradient(135deg, rgba(15, 23, 42, 0.88) 0%, rgba(30, 58, 138, 0.45) 55%, rgba(15, 118, 110, 0.25) 100%) !important;
}
@media (min-width: 768px) {
  .auth-bg--hotel-booking {
    min-height: 100vh;
  }
}
</style>
