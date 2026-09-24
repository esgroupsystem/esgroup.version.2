{{-- Any other 5xx status. --}}
@include('errors.page', ['code' => $exception->getStatusCode()])
