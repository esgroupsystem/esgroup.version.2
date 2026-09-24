{{-- Any other 4xx status. --}}
@include('errors.page', ['code' => $exception->getStatusCode()])
