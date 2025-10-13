@props(['titre', 'icon' => ''])

@extends('layouts.app')

@section('titre', $titre)

@section('content')
    <div class="container mt-4">
        <div class="row">
            <!-- Sidebar -->
            @include('layouts.partials.configside')

            <div class="col-xl-9 col-lg-8">
                <div class="d-flex justify-content-end mb-3">
                    {{ $actions ?? '' }}
                </div>
                
                <div class="block block-rounded">
                    <div class="block-header block-header-default">
                        <h3 class="block-title">
                            @if($icon) <i class="{{ $icon }} me-1"></i> @endif
                            {{ $titre }}
                        </h3>
                    </div>

                    <div class="block-content">
                        {{ $slot }}
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    {{ $modals ?? '' }}
@endsection
