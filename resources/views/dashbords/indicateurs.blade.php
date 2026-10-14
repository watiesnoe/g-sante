{{-- EF-13.01 : indicateurs du jour selon les modules actifs et le rôle --}}
@php $tuiles = app(\App\Services\TableauDeBordService::class)->indicateurs(); @endphp
@if(count($tuiles))
    <div class="row g-3 mb-4">
        @foreach($tuiles as $t)
            <div class="col-6 col-md-4 col-xl-3">
                <a href="{{ $t['lien'] ?? '#' }}" class="block block-rounded block-link-shadow h-100 mb-0 border-start border-4 border-{{ $t['couleur'] }}">
                    <div class="block-content block-content-full d-flex justify-content-between align-items-center">
                        <div>
                            <div class="fs-3 fw-bold">{{ $t['valeur'] }}</div>
                            <div class="fw-semibold text-muted">{{ $t['titre'] }}</div>
                            <div class="small text-muted">{{ $t['detail'] }}</div>
                        </div>
                        <i class="fa {{ $t['icone'] }} fa-2x text-{{ $t['couleur'] }} opacity-50"></i>
                    </div>
                </a>
            </div>
        @endforeach
    </div>
@endif
