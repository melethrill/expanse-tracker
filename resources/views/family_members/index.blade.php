@extends('layouts.app')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>Family Members</h2>
    <a href="{{ route('family-members.create') }}" class="btn btn-primary">Add Family Member</a>
</div>

<div class="card shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Full Name</th>
                        <th>Date of Birth</th>
                        <th>Zodiac Sign</th>
                        <th>AMKA</th>
                        <th>ID Number</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($familyMembers as $member)
                        <tr style="cursor: pointer;" onclick="window.location='{{ route('family-members.edit', $member) }}'">
                            <td class="fw-bold">{{ $member->first_name }} {{ $member->last_name }}</td>
                            <td>{{ $member->dob ? $member->dob->format('Y-m-d') : '-' }}</td>
                            <td><span class="badge bg-info text-dark">{{ $member->zodiac_sign ?? '-' }}</span></td>
                            <td>{{ $member->amka ?? '-' }}</td>
                            <td>{{ $member->id_number ?? '-' }}</td>
                            <td class="text-end" onclick="event.stopPropagation();">
                                <a href="{{ route('family-members.edit', $member) }}" class="btn btn-sm btn-outline-secondary me-1">Edit</a>
                                <form action="{{ route('family-members.destroy', $member) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this family member?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger">Delete</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-4 text-muted">
                                No family members found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($familyMembers->hasPages())
        <div class="card-footer">
            {{ $familyMembers->links() }}
        </div>
    @endif
</div>
@endsection
