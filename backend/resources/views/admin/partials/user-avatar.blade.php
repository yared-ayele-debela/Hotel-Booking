@php
    $px = (int) ($size ?? 36);
    $extraClass = $class ?? '';
@endphp
@if($user->avatarUrl())
    <img src="{{ $user->avatarUrl() }}" alt="" class="rounded-circle {{ $extraClass }}" width="{{ $px }}" height="{{ $px }}" style="object-fit:cover;">
@else
    <span class="rounded-circle bg-secondary text-white d-inline-flex align-items-center justify-content-center {{ $extraClass }}" style="width:{{ $px }}px;height:{{ $px }}px;font-size:{{ max(10, (int) round($px / 2.5)) }}px;">{{ $user->avatarInitial() }}</span>
@endif
