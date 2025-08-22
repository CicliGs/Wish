@extends('layouts.app')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/cache-stats.css') }}">
@endpush

@section('content')
<div class="cache-stats-container">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-10">
                <div class="stats-card fade-in">
                    <div class="stats-header">
                        <h1><i class="bi bi-speedometer2 me-3"></i>{{ __('cache.statistics_title') }}</h1>
                        <p>{{ __('cache.statistics_subtitle') }}</p>
                    </div>
                    
                    <div class="stats-content">
                        <div class="row">
                            <div class="col-lg-8">
                                <div class="section-title">
                                    <i class="bi bi-gear-fill"></i>
                                    {{ __('cache.configuration_title') }}
                                </div>
                                <div class="stats-grid">
                                    <div class="stat-item">
                                        <div class="stat-label">{{ __('cache.driver') }}</div>
                                        <div class="stat-value">
                                            <span class="stat-badge badge-primary">{{ $cacheStats['driver'] ?? 'N/A' }}</span>
                                        </div>
                                    </div>
                                    <div class="stat-item">
                                        <div class="stat-label">{{ __('cache.store') }}</div>
                                        <div class="stat-value">
                                            <span class="stat-badge badge-info">{{ $cacheStats['store'] ?? 'N/A' }}</span>
                                        </div>
                                    </div>
                                    <div class="stat-item">
                                        <div class="stat-label">{{ __('cache.prefix') }}</div>
                                        <div class="stat-value">
                                            <span class="stat-badge badge-secondary">{{ $cacheStats['prefix'] ?? 'N/A' }}</span>
                                        </div>
                                    </div>
                                    <div class="stat-item">
                                        <div class="stat-label">{{ __('cache.description') }}</div>
                                        <div class="stat-value">
                                            <span class="stat-badge badge-warning">{{ $cacheStats['description'] ?? 'N/A' }}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="col-lg-4">
                                <div class="section-title">
                                    <i class="bi bi-database-fill"></i>
                                    {{ __('cache.redis_status_title') }}
                                </div>
                                <div id="redis-status" class="redis-status">
                                    <div class="text-center">
                                        <div class="loading-spinner mb-3"></div>
                                        <p class="text-muted">{{ __('cache.checking_connection') }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="section-title">
                            <i class="bi bi-tools"></i>
                            {{ __('cache.management_title') }}
                        </div>
                        <div class="action-buttons">
                            <button class="btn btn-modern btn-danger-modern" onclick="clearAllCache()">
                                <i class="bi bi-arrow-clockwise me-2"></i>
                                {{ __('cache.clear_all_cache') }}
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    window.cacheTranslations = {
        cache_working: '{{ __("cache.cache_working") }}',
        driver_label: '{{ __("cache.driver_label") }}',
        store_label: '{{ __("cache.store_label") }}',
        prefix_label: '{{ __("cache.prefix_label") }}',
        description_label: '{{ __("cache.description_label") }}',
        status_error: '{{ __("cache.status_error") }}',
        failed_to_get_status: '{{ __("cache.failed_to_get_status") }}',
        connection_failed: '{{ __("cache.connection_failed") }}'
    };
</script>
<script src="{{ asset('js/cache-stats.js') }}"></script>
@endsection 