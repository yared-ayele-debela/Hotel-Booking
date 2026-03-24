{{-- Shared dashboard UI (KPI + chart cards) --}}
<style>
    .dash-hero {
        border: none;
        border-radius: 1rem;
        background: linear-gradient(135deg, #0f172a 0%, #1e3a5f 45%, #1d4ed8 100%);
        color: #fff;
        box-shadow: 0 12px 40px rgba(15, 23, 42, 0.25);
        overflow: hidden;
        position: relative;
    }
    .dash-hero::after {
        content: '';
        position: absolute;
        inset: 0;
        background: radial-gradient(ellipse 80% 60% at 100% 0%, rgba(255, 255, 255, 0.12), transparent 55%);
        pointer-events: none;
    }
    .dash-hero .card-body {
        position: relative;
        z-index: 1;
    }
    .dash-hero h5 {
        color: #fff;
        font-weight: 600;
        letter-spacing: -0.02em;
    }
    .dash-hero .text-muted {
        color: rgba(255, 255, 255, 0.75) !important;
    }

    .dash-stat-card {
        border: 1px solid rgba(15, 23, 42, 0.06);
        border-radius: 1rem;
        background: #fff;
        box-shadow: 0 1px 3px rgba(15, 23, 42, 0.04);
        transition: transform 0.2s ease, box-shadow 0.2s ease;
        height: 100%;
    }
    .dash-stat-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 12px 28px rgba(15, 23, 42, 0.08);
    }
    .dash-stat-card .dash-stat-label {
        font-size: 0.7rem;
        font-weight: 600;
        letter-spacing: 0.06em;
        text-transform: uppercase;
        color: #64748b;
        margin-bottom: 0.35rem;
    }
    .dash-stat-card .dash-stat-value {
        font-size: 1.65rem;
        font-weight: 700;
        letter-spacing: -0.03em;
        color: #0f172a;
        line-height: 1.2;
    }
    .dash-stat-icon {
        width: 3rem;
        height: 3rem;
        border-radius: 0.85rem;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.35rem;
        flex-shrink: 0;
    }

    .dash-chart-card {
        border: 1px solid rgba(15, 23, 42, 0.06);
        border-radius: 1rem;
        background: #fff;
        box-shadow: 0 1px 3px rgba(15, 23, 42, 0.04);
        overflow: hidden;
    }
    .dash-chart-card .card-header {
        background: linear-gradient(180deg, #fafbfc 0%, #fff 100%);
        border-bottom: 1px solid rgba(15, 23, 42, 0.06);
        padding: 1rem 1.25rem;
    }
    .dash-chart-card .card-title {
        font-size: 1rem;
        font-weight: 600;
        color: #0f172a;
        letter-spacing: -0.02em;
    }
    .dash-chart-card .card-subtitle {
        font-size: 0.8rem;
        color: #64748b;
        margin-top: 0.15rem;
    }
    .dash-chart-card .card-body {
        padding: 1.25rem;
    }

    .dash-commission-box {
        border-radius: 1rem;
        border: 1px solid rgba(15, 23, 42, 0.06);
        background: linear-gradient(160deg, #f8fafc 0%, #fff 100%);
    }
    .dash-commission-box .display-rate {
        font-size: 2rem;
        font-weight: 700;
        letter-spacing: -0.03em;
        color: #0f172a;
    }

    .dash-table-card .table thead th {
        font-size: 0.7rem;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        font-weight: 600;
        color: #64748b;
        border-bottom-width: 1px;
    }
</style>
