@foreach (['success', 'danger', 'warning', 'info'] as $msg)
    @if(session($msg))
        <div class="alert alert-{{ $msg }} alert-dismissible fade show" role="alert">
            {{ session($msg) }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif
@endforeach
