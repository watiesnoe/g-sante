<div class="row">
    @forelse ($clients as $client)
        <div class="col-md-6 col-xl-3 mb-4">
            <div class="block block-rounded text-center h-100">
                <div class="block-content block-content-full bg-image"
                     style="background-image: url('{{ asset('assets/media/photos/photo' . rand(1, 20) . '.jpg') }}'); height: 120px;">
                    <img class="img-avatar img-avatar-thumb mt-5"
                         src="{{ asset('assets/media/avatars/avatar' . rand(1, 10) . '.jpg') }}"
                         alt="Client Avatar">
                </div>
                <div class="block-content block-content-full block-content-sm bg-body-light">
                    <div class="fw-semibold">{{ $client->prenom }} {{ $client->nom }}</div>
                    <div class="fs-sm text-muted">{{ $client->email }}</div>
                </div>
                <div class="block-content block-content-full">
                    <a class="btn btn-sm btn-alt-primary" href="tel:{{ $client->telephone }}">
                        <i class="fa fa-phone me-1"></i> {{ $client->telephone }}
                    </a>
                    <a class="btn btn-sm btn-alt-secondary mt-2" href="{{ route('utilisateurs.show', $client->id) }}">
                        <i class="fa fa-user-circle text-muted me-1"></i> Profil
                    </a>
                </div>
            </div>
        </div>
    @empty
        <div class="col-12">
            <div class="alert alert-warning text-center">
                Aucun client trouvé.
            </div>
        </div>
    @endforelse
</div>

<!-- Pagination -->
<div class="d-flex justify-content-center mt-4">
    {{ $clients->links('pagination::bootstrap-5') }}
</div>
