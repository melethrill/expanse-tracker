@extends('layouts.app')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-10">
        <div class="card shadow-sm">
            <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                <h4 class="mb-0">Add Family Member</h4>
                <a href="{{ route('family-members.index') }}" class="btn btn-outline-secondary btn-sm">Back</a>
            </div>
            <div class="card-body">
                <form action="{{ route('family-members.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf

                    <h5 class="mb-3 text-primary">Personal Details</h5>
                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <label for="first_name" class="form-label">First Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="first_name" name="first_name" value="{{ old('first_name') }}" required>
                        </div>
                        <div class="col-md-6">
                            <label for="last_name" class="form-label">Last Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="last_name" name="last_name" value="{{ old('last_name') }}" required>
                        </div>
                        <div class="col-md-6">
                            <label for="dob" class="form-label">Date of Birth <span class="text-danger">*</span></label>
                            <input type="date" class="form-control" id="dob" name="dob" value="{{ old('dob') }}" required>
                        </div>
                        <div class="col-md-6">
                            <label for="zodiac_sign" class="form-label">Zodiac Sign</label>
                            <input type="text" class="form-control bg-light" id="zodiac_sign" name="zodiac_sign" readonly placeholder="Auto-calculated">
                        </div>
                    </div>

                    <h5 class="mb-3 text-primary">Identification & Registration Numbers</h5>
                    <div class="row g-3 mb-4">
                        <div class="col-md-4">
                            <label for="family_register_number" class="form-label">Family Register Number</label>
                            <input type="text" class="form-control" id="family_register_number" name="family_register_number" value="{{ old('family_register_number') }}">
                        </div>
                        <div class="col-md-4">
                            <label for="amka" class="form-label">AMKA</label>
                            <input type="text" class="form-control" id="amka" name="amka" value="{{ old('amka') }}">
                        </div>
                        <div class="col-md-4">
                            <label for="vat_number" class="form-label">VAT Number</label>
                            <input type="text" class="form-control" id="vat_number" name="vat_number" value="{{ old('vat_number') }}">
                        </div>
                        <div class="col-md-4">
                            <label for="pa_number" class="form-label">PA Number</label>
                            <input type="text" class="form-control" id="pa_number" name="pa_number" value="{{ old('pa_number') }}">
                        </div>
                        <div class="col-md-4">
                            <label for="id_number" class="form-label">ID Number</label>
                            <input type="text" class="form-control" id="id_number" name="id_number" value="{{ old('id_number') }}">
                        </div>
                        <div class="col-md-4">
                            <label for="passport_number" class="form-label">Passport Number</label>
                            <input type="text" class="form-control" id="passport_number" name="passport_number" value="{{ old('passport_number') }}">
                        </div>
                    </div>

                    <h5 class="mb-3 text-primary">Identity Images</h5>
                    <div class="row g-3 mb-4">
                        <div class="col-md-4">
                            <label for="id_image_front" class="form-label">ID Image Front</label>
                            <input type="file" class="form-control" id="id_image_front" name="id_image_front" accept="image/*">
                        </div>
                        <div class="col-md-4">
                            <label for="id_image_back" class="form-label">ID Image Back</label>
                            <input type="file" class="form-control" id="id_image_back" name="id_image_back" accept="image/*">
                        </div>
                        <div class="col-md-4">
                            <label for="passport_image" class="form-label">Passport Image</label>
                            <input type="file" class="form-control" id="passport_image" name="passport_image" accept="image/*">
                        </div>
                    </div>

                    <div class="d-flex justify-content-end gap-2">
                        <a href="{{ route('family-members.index') }}" class="btn btn-secondary">Cancel</a>
                        <button type="submit" class="btn btn-primary">Save Member</button>
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
