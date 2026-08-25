@extends('layouts.app')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
    <h2>Expense Report by Category</h2>
    <form method="GET" action="{{ route('reports.index') }}" class="d-flex align-items-center gap-2">
        <div>
            <select name="year" id="year" class="form-select form-select-sm" onchange="this.form.submit()">
                <option value="">All Years</option>
                @foreach($availableYears as $year)
                    <option value="{{ $year }}" {{ (string)$selectedYear === (string)$year ? 'selected' : '' }}>
                        {{ $year }}
                    </option>
                @endforeach
            </select>
        </div>
        <div>
            <select name="month" id="month" class="form-select form-select-sm" onchange="this.form.submit()">
                <option value="">All Months</option>
                @foreach($months as $monthNum => $monthName)
                    <option value="{{ $monthNum }}" {{ (string)$selectedMonth === (string)$monthNum ? 'selected' : '' }}>
                        {{ $monthName }}
                    </option>
                @endforeach
            </select>
        </div>
        <button type="submit" class="btn btn-sm btn-primary">Filter</button>
        @if($selectedYear || $selectedMonth)
            <a href="{{ route('reports.index') }}" class="btn btn-sm btn-outline-secondary">Clear</a>
        @endif
    </form>
</div>

<div class="row mb-4">
    <div class="col-md-12">
        <div class="card shadow-sm">
            <div class="card-header bg-white">
                <h5 class="mb-0">Expenses Breakdown Chart</h5>
            </div>
            <div class="card-body d-flex justify-content-center align-items-center" style="position: relative; height: 350px;">
                <canvas id="expensesPieChart"></canvas>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-8">
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-white"><h5 class="mb-0">Expenses Breakdown</h5></div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Category</th>
                                <th class="text-end">Total Expenses</th>
                                <th class="text-end">% of Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($categoryExpenses as $category)
                                @php
                                    $percentage = $totalExpenseCents > 0 ? ($category->total_cents / $totalExpenseCents) * 100 : 0;
                                @endphp
                                <tr>
                                    <td><strong>{{ $category->name }}</strong></td>
                                    <td class="text-end fw-bold text-danger">
                                        €{{ number_format($category->total_cents / 100, 2) }}
                                    </td>
                                    <td class="text-end">
                                        {{ number_format($percentage, 1) }}%
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot class="table-light">
                            <tr>
                                <th>Total</th>
                                <th class="text-end text-danger">€{{ number_format($totalExpenseCents / 100, 2) }}</th>
                                <th class="text-end">100.0%</th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card shadow-sm">
            <div class="card-header bg-white"><h5 class="mb-0">Summary</h5></div>
            <div class="card-body text-center p-4">
                <h6 class="text-muted">Total Expenses Logged</h6>
                <h2 class="text-danger fw-bold my-3">€{{ number_format($totalExpenseCents / 100, 2) }}</h2>
                <p class="text-muted small mb-0">Aggregated across all {{ $categoryExpenses->count() }} categories.</p>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const ctx = document.getElementById('expensesPieChart').getContext('2d');
        const categoryData = @json($categoryExpenses);

        const labels = categoryData.map(item => item.name);
        const dataValues = categoryData.map(item => (item.total_cents / 100).toFixed(2));

        const backgroundColors = [
            '#FF6384', '#36A2EB', '#FFCE56', '#4BC0C0', '#9966FF',
            '#FF9F40', '#E7E9ED', '#86C7F3', '#F7464A', '#46BFBD'
        ];

        new Chart(ctx, {
            type: 'pie',
            data: {
                labels: labels,
                datasets: [{
                    data: dataValues,
                    backgroundColor: backgroundColors.slice(0, labels.length)
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    tooltip: {
                        callbacks: {
                            label: function (context) {
                                const label = context.label || '';
                                const value = parseFloat(context.raw || 0).toFixed(2);
                                return `${label}: €${value}`;
                            }
                        }
                    }
                }
            }
        });
    });
</script>
@endsection
