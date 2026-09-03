@extends('layouts.app')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-10">
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                <h4 class="mb-0">Edit Family Member</h4>
                <a href="{{ route('family-members.index') }}" class="btn btn-outline-secondary btn-sm">Back</a>
            </div>
            <div class="card-body">
                <form action="{{ route('family-members.update', $familyMember) }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')

                    <h5 class="mb-3 text-primary">Personal Details</h5>
                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <label for="first_name" class="form-label">First Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="first_name" name="first_name" value="{{ old('first_name', $familyMember->first_name) }}" required>
                        </div>
                        <div class="col-md-6">
                            <label for="last_name" class="form-label">Last Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="last_name" name="last_name" value="{{ old('last_name', $familyMember->last_name) }}" required>
                        </div>
                        <div class="col-md-6">
                            <label for="dob" class="form-label">Date of Birth <span class="text-danger">*</span></label>
                            <input type="date" class="form-control" id="dob" name="dob" value="{{ old('dob', $familyMember->dob ? $familyMember->dob->format('Y-m-d') : '') }}" required>
                        </div>
                        <div class="col-md-6">
                            <label for="zodiac_sign" class="form-label">Zodiac Sign</label>
                            <input type="text" class="form-control bg-light" id="zodiac_sign" name="zodiac_sign" readonly value="{{ $familyMember->zodiac_sign }}" placeholder="Auto-calculated">
                        </div>
                    </div>

                    <h5 class="mb-3 text-primary">Identification & Registration Numbers</h5>
                    <div class="row g-3 mb-4">
                        <div class="col-md-4">
                            <label for="family_register_number" class="form-label">Family Register Number</label>
                            <input type="text" class="form-control" id="family_register_number" name="family_register_number" value="{{ old('family_register_number', $familyMember->family_register_number) }}">
                        </div>
                        <div class="col-md-4">
                            <label for="amka" class="form-label">AMKA</label>
                            <input type="text" class="form-control" id="amka" name="amka" value="{{ old('amka', $familyMember->amka) }}">
                        </div>
                        <div class="col-md-4">
                            <label for="vat_number" class="form-label">VAT Number</label>
                            <input type="text" class="form-control" id="vat_number" name="vat_number" value="{{ old('vat_number', $familyMember->vat_number) }}">
                        </div>
                        <div class="col-md-4">
                            <label for="pa_number" class="form-label">PA Number</label>
                            <input type="text" class="form-control" id="pa_number" name="pa_number" value="{{ old('pa_number', $familyMember->pa_number) }}">
                        </div>
                        <div class="col-md-4">
                            <label for="id_number" class="form-label">ID Number</label>
                            <input type="text" class="form-control" id="id_number" name="id_number" value="{{ old('id_number', $familyMember->id_number) }}">
                        </div>
                        <div class="col-md-4">
                            <label for="passport_number" class="form-label">Passport Number</label>
                            <input type="text" class="form-control" id="passport_number" name="passport_number" value="{{ old('passport_number', $familyMember->passport_number) }}">
                        </div>
                    </div>

                    <h5 class="mb-3 text-primary">Identity Images</h5>
                    <div class="row g-3 mb-4">
                        @foreach(['id_image_front' => 'ID Image Front', 'id_image_back' => 'ID Image Back', 'passport_image' => 'Passport Image'] as $field => $label)
                            <div class="col-md-4">
                                <label for="{{ $field }}" class="form-label">{{ $label }}</label>
                                @if($familyMember->$field)
                                    <div class="mb-2">
                                        <img src="{{ asset('storage/' . $familyMember->$field) }}" alt="{{ $label }}" class="img-thumbnail" style="max-height: 120px; object-fit: cover;">
                                        <div class="mt-1">
                                            <a href="{{ route('family-members.download-image', ['familyMember' => $familyMember, 'type' => $field]) }}" class="btn btn-sm btn-outline-primary">Download Image</a>
                                        </div>
                                    </div>
                                @endif
                                <input type="file" class="form-control" id="{{ $field }}" name="{{ $field }}" accept="image/*">
                                @if($familyMember->$field)
                                    <small class="form-text text-muted">Upload a new file to replace current image.</small>
                                @endif
                            </div>
                        @endforeach
                    </div>

                    <div class="d-flex justify-content-end gap-2">
                        <a href="{{ route('family-members.index') }}" class="btn btn-secondary">Cancel</a>
                        <button type="submit" class="btn btn-primary">Update Member</button>
                    </div>
                </form>
            </div>
        </div>

        {{-- Documents Section --}}
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-white py-3">
                <h5 class="mb-0 text-primary">Attached Documents</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive mb-4">
                    <table class="table table-bordered table-striped align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Title</th>
                                <th>Upload Date</th>
                                <th>File Size</th>
                                <th class="text-end" style="width: 150px;">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($familyMember->documents as $doc)
                                <tr>
                                    <td class="fw-bold">{{ $doc->title }}</td>
                                    <td>{{ $doc->created_at ? $doc->created_at->format('Y-m-d H:i') : '-' }}</td>
                                    <td>{{ $doc->file_size ? number_format($doc->file_size / 1024, 2) . ' KB' : '-' }}</td>
                                    <td class="text-end">
                                        <a href="{{ route('family-documents.download', $doc) }}" class="btn btn-sm btn-outline-primary me-1">Download</a>
                                        <form action="{{ route('family-documents.destroy', $doc) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this document?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger">Delete</button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center py-3 text-muted">No documents uploaded for this member.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <hr>

                <h6 class="fw-bold mb-3">Upload New Document</h6>
                <form action="{{ route('family-documents.store', $familyMember) }}" method="POST" enctype="multipart/form-data" class="row g-3 align-items-end">
                    @csrf
                    <div class="col-md-5">
                        <label for="doc_title" class="form-label">Document Title <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="doc_title" name="title" required placeholder="e.g. Birth Certificate">
                    </div>
                    <div class="col-md-5">
                        <label for="doc_file" class="form-label">File <span class="text-danger">*</span></label>
                        <input type="file" class="form-control" id="doc_file" name="file" required>
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-success w-100">Upload</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    function calculateZodiacSign(dateString) {
        if (!dateString) return '';
        const date = new Date(dateString);
        if (isNaN(date.getTime())) return '';

        const month = date.getUTCMonth() + 1;
        const day = date.getUTCDate();

        switch (month) {
            case 1: return day <= 19 ? 'Capricorn' : 'Aquarius';
            case 2: return day <= 18 ? 'Aquarius' : 'Pisces';
            case 3: return day <= 20 ? 'Pisces' : 'Aries';
            case 4: return day <= 19 ? 'Aries' : 'Taurus';
            case 5: return day <= 20 ? 'Taurus' : 'Gemini';
            case 6: return day <= 20 ? 'Gemini' : 'Cancer';
            case 7: return day <= 22 ? 'Cancer' : 'Leo';
            case 8: return day <= 22 ? 'Leo' : 'Virgo';
            case 9: return day <= 22 ? 'Virgo' : 'Libra';
            case 10: return day <= 22 ? 'Libra' : 'Scorpio';
            case 11: return day <= 21 ? 'Scorpio' : 'Sagittarius';
            case 12: return day <= 21 ? 'Sagittarius' : 'Capricorn';
            default: return '';
        }
    }

    document.addEventListener('DOMContentLoaded', function() {
        const dobInput = document.getElementById('dob');
        const zodiacInput = document.getElementById('zodiac_sign');

        function updateZodiac() {
            zodiacInput.value = calculateZodiacSign(dobInput.value);
        }

        dobInput.addEventListener('change', updateZodiac);
        dobInput.addEventListener('input', updateZodiac);
        if (dobInput.value) {
            updateZodiac();
        }
    });
</script>
@endsection
