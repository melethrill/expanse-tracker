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
                                <tr data-category-id="{{ $category->id }}" data-category-name="{{ $category->name }}" style="cursor: pointer;">
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
                <h6 class="text-muted" id="summaryTitle">Total Expenses Logged</h6>
                <h2 class="text-danger fw-bold my-3" id="summaryTotalAmount">€{{ number_format($totalExpenseCents / 100, 2) }}</h2>
                <p class="text-muted small mb-0" id="summarySubtitle">Aggregated across all {{ $categoryExpenses->count() }} categories.</p>
            </div>
        </div>
    </div>
</div>

<div class="row mb-4" id="transactions-section">
    <div class="col-md-12">
        <div class="card shadow-sm">
            <div class="card-header bg-white d-flex justify-content-between align-items-center flex-wrap gap-2">
                <h5 class="mb-0">Expense Transactions</h5>
                <div id="activeFilterBadgeContainer" style="display: none;">
                    <span class="badge bg-primary p-2 align-middle fs-6" id="activeFilterBadge">
                        Showing Category: <strong id="activeCategoryName"></strong>
                    </span>
                    <button id="resetFilterBtn" type="button" class="btn btn-sm btn-outline-secondary ms-2 align-middle">
                        Show All / Reset Filter
                    </button>
                </div>
            </div>
            <div class="card-body p-0">
                @if($transactions->isEmpty())
                    <div class="p-4 text-center text-muted">No expense transactions found for the selected period.</div>
                @else
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0" id="transactionsTable">
                            <thead class="table-light">
                                <tr>
                                    <th>Date</th>
                                    <th>Category</th>
                                    <th>Payment Method</th>
                                    <th>Description</th>
                                    <th class="text-end">Amount</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($transactions as $transaction)
                                    <tr class="transaction-row"
                                        data-category-id="{{ $transaction->category_id }}"
                                        data-category-name="{{ $transaction->category ? $transaction->category->name : '' }}"
                                        data-amount="{{ $transaction->amount }}"
                                        onclick="window.location='{{ route('transactions.edit', $transaction) }}'"
                                        style="cursor: pointer;">
                                        <td>{{ $transaction->date->format('d/m/Y') }}</td>
                                        <td>
                                            <span class="badge bg-secondary">
                                                {{ $transaction->category ? $transaction->category->name : 'Uncategorized' }}
                                            </span>
                                        </td>
                                        <td>
                                            <span class="badge {{ $transaction->transaction_type === 'card' ? 'bg-info' : 'bg-secondary' }}">
                                                {{ ucfirst($transaction->transaction_type) }}
                                            </span>
                                        </td>
                                        <td>{{ $transaction->description ?? '-' }}</td>
                                        <td class="text-end fw-bold text-danger">
                                            €{{ number_format($transaction->amount / 100, 2) }}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot class="table-light">
                                <tr>
                                    <th colspan="4">Filtered Total (<span id="filteredRowCount">{{ $transactions->count() }}</span> transactions)</th>
                                    <th class="text-end text-danger" id="filteredTotalAmount">€{{ number_format($totalExpenseCents / 100, 2) }}</th>
                                </tr>
                            </tfoot>
                        </table>
                        <div id="noMatchingRowsMsg" class="p-4 text-center text-muted" style="display: none;">
                            No transactions found for the selected category filter.
                        </div>
                    </div>
                @endif
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

        let activeCategoryId = null;

        const activeFilterBadgeContainer = document.getElementById('activeFilterBadgeContainer');
        const activeCategoryName = document.getElementById('activeCategoryName');
        const resetFilterBtn = document.getElementById('resetFilterBtn');
        const transactionRows = document.querySelectorAll('.transaction-row');
        const filteredRowCount = document.getElementById('filteredRowCount');
        const filteredTotalAmount = document.getElementById('filteredTotalAmount');
        const noMatchingRowsMsg = document.getElementById('noMatchingRowsMsg');
        const summaryTotalAmount = document.getElementById('summaryTotalAmount');
        const summaryTitle = document.getElementById('summaryTitle');
        const summarySubtitle = document.getElementById('summarySubtitle');

        const initialTotalCents = {{ $totalExpenseCents }};
        const initialCount = {{ $transactions->count() }};
        const totalCategoriesCount = {{ $categoryExpenses->count() }};

        function formatCurrency(cents) {
            return '€' + (cents / 100).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        }

        function filterByCategory(categoryId, categoryName) {
            if (activeCategoryId === String(categoryId)) {
                // If clicking same slice again, toggle off / reset filter
                resetCategoryFilter();
                return;
            }

            activeCategoryId = String(categoryId);
            activeCategoryName.textContent = categoryName;
            activeFilterBadgeContainer.style.display = 'inline-block';

            let visibleCount = 0;
            let filteredCents = 0;

            transactionRows.forEach(row => {
                const rowCategoryId = row.getAttribute('data-category-id');
                const rowAmount = parseInt(row.getAttribute('data-amount') || '0', 10);

                if (rowCategoryId === activeCategoryId) {
                    row.style.display = '';
                    visibleCount++;
                    filteredCents += rowAmount;
                } else {
                    row.style.display = 'none';
                }
            });

            if (filteredRowCount) filteredRowCount.textContent = visibleCount;
            if (filteredTotalAmount) filteredTotalAmount.textContent = formatCurrency(filteredCents);
            if (summaryTotalAmount) summaryTotalAmount.textContent = formatCurrency(filteredCents);
            if (summaryTitle) summaryTitle.textContent = `Total (${categoryName})`;
            if (summarySubtitle) summarySubtitle.textContent = `Filtered by category "${categoryName}".`;

            if (noMatchingRowsMsg) {
                noMatchingRowsMsg.style.display = visibleCount === 0 ? 'block' : 'none';
            }
        }

        function resetCategoryFilter() {
            activeCategoryId = null;
            activeFilterBadgeContainer.style.display = 'none';

            transactionRows.forEach(row => {
                row.style.display = '';
            });

            if (filteredRowCount) filteredRowCount.textContent = initialCount;
            if (filteredTotalAmount) filteredTotalAmount.textContent = formatCurrency(initialTotalCents);
            if (summaryTotalAmount) summaryTotalAmount.textContent = formatCurrency(initialTotalCents);
            if (summaryTitle) summaryTitle.textContent = 'Total Expenses Logged';
            if (summarySubtitle) summarySubtitle.textContent = `Aggregated across all ${totalCategoriesCount} categories.`;

            if (noMatchingRowsMsg) {
                noMatchingRowsMsg.style.display = 'none';
            }
        }

        if (resetFilterBtn) {
            resetFilterBtn.addEventListener('click', resetCategoryFilter);
        }

        // Add row click listener on category breakdown table rows
        document.querySelectorAll('tr[data-category-id]').forEach(row => {
            row.addEventListener('click', function () {
                const catId = this.getAttribute('data-category-id');
                const catName = this.getAttribute('data-category-name');
                filterByCategory(catId, catName);
            });
        });

        const chart = new Chart(ctx, {
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
                onClick: function (event, elements) {
                    if (elements && elements.length > 0) {
                        const index = elements[0].index;
                        const clickedCategory = categoryData[index];
                        if (clickedCategory) {
                            filterByCategory(clickedCategory.id, clickedCategory.name);
                        }
                    }
                },
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
