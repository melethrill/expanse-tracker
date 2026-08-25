@extends('layouts.app')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>Expense Report by Category</h2>
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
                                        ${{ number_format($category->total_cents / 100, 2) }}
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
                                <th class="text-end text-danger">${{ number_format($totalExpenseCents / 100, 2) }}</th>
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
                <h2 class="text-danger fw-bold my-3">${{ number_format($totalExpenseCents / 100, 2) }}</h2>
                <p class="text-muted small mb-0">Aggregated across all {{ $categoryExpenses->count() }} categories.</p>
            </div>
        </div>
    </div>
</div>
@endsection
