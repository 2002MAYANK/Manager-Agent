@extends('layout')

@section('title', 'Generated Report | AI Manager')
@section('page-title', 'Report Details')

@push('styles')
<style>
    .report-shell {
        max-width: 980px;
        margin: 0 auto;
    }

    .report-card {
        padding: 26px;
    }

    .report-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        gap: 18px;
        margin-bottom: 24px;
    }

    .report-title {
        font-size: 28px;
        font-weight: 800;
        margin: 0 0 8px;
    }

    .report-score {
        min-width: 142px;
        padding: 18px;
        border: 1px solid rgba(32, 198, 107, .28);
        border-radius: 8px;
        background: rgba(32, 198, 107, .1);
        text-align: center;
    }

    .report-score strong {
        display: block;
        font-size: 34px;
        line-height: 1;
        color: var(--green);
    }

    .report-grid {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 16px;
    }

    .report-section {
        min-height: 220px;
        padding: 18px;
        border: 1px solid var(--panel-border);
        border-radius: 8px;
        background: rgba(12, 21, 36, .72);
    }

    .report-section h2 {
        font-size: 17px;
        font-weight: 800;
        margin: 0 0 16px;
    }

    .report-item {
        padding: 12px 0;
        border-top: 1px solid rgba(148, 163, 184, .12);
    }

    .report-item:first-of-type {
        border-top: 0;
        padding-top: 0;
    }

    .report-actions {
        margin-top: 24px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 16px;
    }

    @media (max-width: 991.98px) {
        .report-grid {
            grid-template-columns: 1fr;
        }
    }

    @media (max-width: 575.98px) {
        .report-header,
        .report-actions {
            flex-direction: column;
            align-items: stretch;
        }

        .report-score {
            text-align: left;
        }
    }
</style>
@endpush

@section('content')
    @php
        $productivity = $reportModel->team_productivity ?? 0;
        $topPerformers = $report['top_performers'] ?? [];
        $attentionRequired = $report['attention_required'] ?? [];
        $risks = $report['risks'] ?? [];

        $displayText = function ($item, array $keys, string $fallback) {
            if (is_string($item)) {
                return $item;
            }

            if (! is_array($item)) {
                return $fallback;
            }

            foreach ($keys as $key) {
                if (! empty($item[$key])) {
                    return $item[$key];
                }
            }

            return $fallback;
        };
    @endphp

    <div class="report-shell">
        <div class="glass-card report-card">
            <div class="report-header">
                <div>
                    <h1 class="report-title">AI Performance Report #{{ str_pad($reportModel->id, 4, '0', STR_PAD_LEFT) }}</h1>
                    <div class="muted-text">
                        Generated on {{ \Carbon\Carbon::parse($reportModel->generated_at)->format('M d, Y h:i A') }}<br>
                        Period: 
                        @if($reportModel->start_date && $reportModel->end_date)
                            {{ \Carbon\Carbon::parse($reportModel->start_date)->format('M d, Y') }} - {{ \Carbon\Carbon::parse($reportModel->end_date)->format('M d, Y') }}
                        @else
                            All Time
                        @endif
                    </div>
                </div>

                <div class="report-score">
                    <strong>{{ (int) $productivity }}%</strong>
                    <span class="muted-text">Productivity</span>
                </div>
            </div>

            <div class="report-grid">
                <section class="report-section">
                    <h2 class="text-success"><i class="bi bi-trophy me-2"></i>Top Performers</h2>
                    @forelse ($topPerformers as $person)
                        <div class="report-item">
                            {{ $displayText($person, ['name', 'employee_name'], 'Employee') }}
                        </div>
                    @empty
                        <div class="muted-text">No top performers returned.</div>
                    @endforelse
                </section>

                <section class="report-section">
                    <h2 style="color: var(--orange);"><i class="bi bi-exclamation-triangle me-2"></i>Attention Required</h2>
                    @forelse ($attentionRequired as $person)
                        <div class="report-item">
                            {{ $displayText($person, ['name', 'employee_name', 'reason'], 'Attention item') }}
                        </div>
                    @empty
                        <div class="muted-text">No attention items returned.</div>
                    @endforelse
                </section>

                <section class="report-section">
                    <h2 class="text-danger"><i class="bi bi-shield-exclamation me-2"></i>Risks</h2>
                    @forelse ($risks as $risk)
                        <div class="report-item">
                            {{ $displayText($risk, ['title', 'risk', 'description'], 'Project risk') }}
                        </div>
                    @empty
                        <div class="muted-text">No risks returned.</div>
                    @endforelse
                </section>
            </div>

            <div class="report-actions">
                <a href="{{ url('/reports') }}" class="btn btn-outline-light" style="border-color: var(--panel-border);">
                    <i class="bi bi-arrow-left me-2"></i> Back to History
                </a>
                <div class="d-flex gap-2">
                    <a href="{{ url('/reports/' . $reportModel->id . '/export') }}" class="btn-ai d-inline-flex align-items-center text-decoration-none" style="background: linear-gradient(135deg, #1b6b3a, #20c66b); box-shadow: 0 10px 24px rgba(32, 198, 107, .25);">
                        <i class="bi bi-download me-2"></i> Export Report
                    </a>
                    <a href="{{ url('/') }}" class="btn-ai d-inline-flex align-items-center text-decoration-none">
                        <i class="bi bi-grid-1x2 me-2"></i> Go to Dashboard
                    </a>
                </div>
            </div>
        </div>
    </div>
@endsection
