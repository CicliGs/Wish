@extends('layouts.app')

@push('styles')
<style>
.detailed-stats-container {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    min-height: 100vh;
    padding: 2rem 0;
}

.stats-card {
    background: rgba(255, 255, 255, 0.95);
    backdrop-filter: blur(10px);
    border-radius: 20px;
    border: 1px solid rgba(255, 255, 255, 0.2);
    box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
    transition: all 0.3s ease;
    overflow: hidden;
    margin-bottom: 2rem;
}

.stats-header {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    padding: 2rem;
    text-align: center;
    position: relative;
    overflow: hidden;
}

.stats-header h1 {
    font-size: 2.5rem;
    font-weight: 700;
    margin: 0;
    position: relative;
    z-index: 1;
}

.stats-header p {
    font-size: 1.1rem;
    opacity: 0.9;
    margin: 0.5rem 0 0 0;
    position: relative;
    z-index: 1;
}

.stats-content {
    padding: 2rem;
}

.section-title {
    font-size: 1.5rem;
    font-weight: 600;
    color: #2d3748;
    margin-bottom: 1.5rem;
    display: flex;
    align-items: center;
    gap: 0.5rem;
    border-bottom: 2px solid #e2e8f0;
    padding-bottom: 0.5rem;
}

.section-title i {
    color: #667eea;
}

.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 1.5rem;
    margin-bottom: 2rem;
}

.stats-grid.compact {
    grid-template-columns: 1fr;
    gap: 0.75rem;
}

.stats-grid.compact .stat-item {
    padding: 0.75rem;
    margin: 0;
}

.stats-grid.compact .stat-value {
    font-size: 1.1rem;
}

.stats-grid.compact .stat-badge {
    padding: 0.25rem 0.5rem;
    font-size: 0.8rem;
}

.stat-item {
    background: linear-gradient(135deg, #f7fafc 0%, #edf2f7 100%);
    border-radius: 15px;
    padding: 1.5rem;
    border: 1px solid #e2e8f0;
    transition: all 0.3s ease;
    position: relative;
    overflow: hidden;
    text-align: center;
}

.stat-item::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    width: 4px;
    height: 100%;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    transition: all 0.3s ease;
}

.stat-item:hover {
    transform: translateY(-5px);
    box-shadow: 0 15px 30px rgba(0, 0, 0, 0.1);
}

.stat-item:hover::before {
    width: 8px;
}

.stat-label {
    font-size: 0.9rem;
    color: #718096;
    font-weight: 500;
    margin-bottom: 0.5rem;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.stat-value {
    font-size: 2rem;
    font-weight: 700;
    color: #2d3748;
    margin: 0;
}

.stat-subtitle {
    font-size: 0.8rem;
    color: #a0aec0;
    margin-top: 0.5rem;
}

.progress-container {
    background: #e2e8f0;
    border-radius: 10px;
    height: 8px;
    margin-top: 1rem;
    overflow: hidden;
}

.progress-bar {
    height: 100%;
    border-radius: 10px;
    transition: width 0.6s ease;
}

.progress-success {
    background: linear-gradient(90deg, #48bb78 0%, #38a169 100%);
}

.progress-warning {
    background: linear-gradient(90deg, #ed8936 0%, #dd6b20 100%);
}

.progress-danger {
    background: linear-gradient(90deg, #f56565 0%, #e53e3e 100%);
}

.progress-info {
    background: linear-gradient(90deg, #4299e1 0%, #3182ce 100%);
}

.metric-card {
    background: white;
    border-radius: 15px;
    padding: 1.5rem;
    border: 1px solid #e2e8f0;
    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
    transition: all 0.3s ease;
}

.metric-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
}

.metric-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 1rem;
}

.metric-title {
    font-weight: 600;
    color: #2d3748;
    font-size: 1.1rem;
}

.metric-icon {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 1.2rem;
}

.icon-success {
    background: linear-gradient(135deg, #48bb78 0%, #38a169 100%);
}

.icon-info {
    background: linear-gradient(135deg, #4299e1 0%, #3182ce 100%);
}

.icon-warning {
    background: linear-gradient(135deg, #ed8936 0%, #dd6b20 100%);
}

.icon-danger {
    background: linear-gradient(135deg, #f56565 0%, #e53e3e 100%);
}

.metric-value {
    font-size: 2.5rem;
    font-weight: 700;
    color: #2d3748;
    margin: 0;
}

.metric-description {
    color: #718096;
    font-size: 0.9rem;
    margin-top: 0.5rem;
}

.refresh-button {
    position: fixed;
    bottom: 2rem;
    right: 2rem;
    width: 60px;
    height: 60px;
    border-radius: 50%;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    border: none;
    box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2);
    transition: all 0.3s ease;
    z-index: 1000;
}

.refresh-button:hover {
    transform: scale(1.1);
    box-shadow: 0 15px 35px rgba(0, 0, 0, 0.3);
}

.refresh-button i {
    font-size: 1.5rem;
}

.loading {
    opacity: 0.6;
    pointer-events: none;
}

.fade-in {
    animation: fadeIn 0.6s ease-in;
}

@keyframes fadeIn {
    from { opacity: 0; transform: translateY(20px); }
    to { opacity: 1; transform: translateY(0); }
}

.btn-modern {
    padding: 0.75rem 1.5rem;
    border-radius: 25px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    border: none;
    transition: all 0.3s ease;
    position: relative;
    overflow: hidden;
    text-decoration: none;
    display: inline-block;
}

.btn-primary-modern {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
}

.btn-primary-modern:hover {
    transform: translateY(-2px);
    box-shadow: 0 10px 25px rgba(102, 126, 234, 0.3);
    color: white;
}

@media (max-width: 768px) {
    .stats-header h1 {
        font-size: 2rem;
    }
    
    .stats-grid {
        grid-template-columns: 1fr;
    }
    
    .refresh-button {
        bottom: 1rem;
        right: 1rem;
        width: 50px;
        height: 50px;
    }
}
</style>
@endpush

@section('content')
<div class="detailed-stats-container">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-12">
                <div class="stats-card fade-in">
                    <div class="stats-header">
                        <h1><i class="bi bi-graph-up me-3"></i>{{ __('cache.detailed_statistics_title') }}</h1>
                        <p>{{ __('cache.detailed_statistics_subtitle') }}</p>
                    </div>
                    
                    <div class="stats-content">
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <div></div>
                            <a href="{{ route('cache.stats') }}" class="btn btn-modern btn-primary-modern">
                                <i class="bi bi-speedometer2 me-2"></i>
                                {{ __('cache.basic_statistics') }}
                            </a>
                        </div>
                        
                        <div class="section-title">
                            <i class="bi bi-activity"></i>
                            {{ __('cache.performance_metrics') }}
                        </div>
                        <div class="stats-grid" id="performance-metrics">
                            <div class="metric-card">
                                <div class="metric-header">
                                    <div class="metric-title">{{ __('cache.hit_rate') }}</div>
                                    <div class="metric-icon icon-success">
                                        <i class="bi bi-target"></i>
                                    </div>
                                </div>
                                <div class="metric-value" id="hit-rate">--</div>
                                <div class="metric-description">{{ __('cache.hit_rate_description') }}</div>
                                <div class="progress-container">
                                    <div class="progress-bar progress-success" id="hit-rate-bar" style="width: 0%"></div>
                                </div>
                            </div>
                            
                            <div class="metric-card">
                                <div class="metric-header">
                                    <div class="metric-title">{{ __('cache.memory_usage') }}</div>
                                    <div class="metric-icon icon-info">
                                        <i class="bi bi-memory"></i>
                                    </div>
                                </div>
                                <div class="metric-value" id="memory-usage">--</div>
                                <div class="metric-description">{{ __('cache.memory_usage_description') }}</div>
                                <div class="progress-container">
                                    <div class="progress-bar progress-warning" id="memory-bar" style="width: 0%"></div>
                                </div>
                            </div>
                            
                            <div class="metric-card">
                                <div class="metric-header">
                                    <div class="metric-title">{{ __('cache.connected_clients') }}</div>
                                    <div class="metric-icon icon-warning">
                                        <i class="bi bi-people"></i>
                                    </div>
                                </div>
                                <div class="metric-value" id="connected-clients">--</div>
                                <div class="metric-description">{{ __('cache.connected_clients_description') }}</div>
                            </div>
                            
                            <div class="metric-card">
                                <div class="metric-header">
                                    <div class="metric-title">{{ __('cache.database_size') }}</div>
                                    <div class="metric-icon icon-danger">
                                        <i class="bi bi-database"></i>
                                    </div>
                                </div>
                                <div class="metric-value" id="db-size">--</div>
                                <div class="metric-description">{{ __('cache.database_size_description') }}</div>
                            </div>
                        </div>
                        
                        <div class="section-title">
                            <i class="bi bi-info-circle"></i>
                            {{ __('cache.system_information') }}
                        </div>
                        <div class="stats-grid" id="system-info">
                            <div class="stat-item">
                                <div class="stat-label">{{ __('cache.redis_version') }}</div>
                                <div class="stat-value" id="redis-version">--</div>
                            </div>
                            
                            <div class="stat-item">
                                <div class="stat-label">{{ __('cache.uptime') }}</div>
                                <div class="stat-value" id="uptime">--</div>
                                <div class="stat-subtitle">{{ __('cache.uptime_description') }}</div>
                            </div>
                            
                            <div class="stat-item">
                                <div class="stat-label">{{ __('cache.commands_processed') }}</div>
                                <div class="stat-value" id="commands-processed">--</div>
                                <div class="stat-subtitle">{{ __('cache.commands_processed_description') }}</div>
                            </div>
                            
                            <div class="stat-item">
                                <div class="stat-label">{{ __('cache.connections_received') }}</div>
                                <div class="stat-value" id="connections-received">--</div>
                                <div class="stat-subtitle">{{ __('cache.connections_received_description') }}</div>
                            </div>
                        </div>
                        
                        <div class="section-title">
                            <i class="bi bi-clock-history"></i>
                            {{ __('cache.memory_storage') }}
                        </div>
                        <div class="stats-grid" id="memory-storage">
                            <div class="stat-item">
                                <div class="stat-label">{{ __('cache.peak_memory') }}</div>
                                <div class="stat-value" id="peak-memory">--</div>
                                <div class="stat-subtitle">{{ __('cache.peak_memory_description') }}</div>
                            </div>
                            
                            <div class="stat-item">
                                <div class="stat-label">{{ __('cache.last_save') }}</div>
                                <div class="stat-value" id="last-save">--</div>
                                <div class="stat-subtitle">{{ __('cache.last_save_description') }}</div>
                            </div>
                            
                            <div class="stat-item">
                                <div class="stat-label">{{ __('cache.keyspace_hits') }}</div>
                                <div class="stat-value" id="keyspace-hits">--</div>
                                <div class="stat-subtitle">{{ __('cache.keyspace_hits_description') }}</div>
                            </div>
                            
                            <div class="stat-item">
                                <div class="stat-label">{{ __('cache.keyspace_misses') }}</div>
                                <div class="stat-value" id="keyspace-misses">--</div>
                                <div class="stat-subtitle">{{ __('cache.keyspace_misses_description') }}</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <button class="refresh-button" onclick="refreshStats()" id="refresh-btn">
        <i class="bi bi-arrow-clockwise"></i>
    </button>
</div>

<script src="{{ asset('js/cache-detailed-stats.js') }}"></script>
@endsection 