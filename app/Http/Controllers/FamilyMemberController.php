<?php

namespace App\Http\Controllers;

use App\Models\FamilyMember;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class FamilyMemberController extends Controller
{
    public function index()
    {
        $familyMembers = FamilyMember::latest()->paginate(10);
        return view('family_members.index', compact('familyMembers'));
    }

    public function create()
    {
        return view('family_members.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'dob' => 'required|date',
            'family_register_number' => 'nullable|string|max:255',
            'amka' => 'nullable|string|max:255',
            'vat_number' => 'nullable|string|max:255',
            'pa_number' => 'nullable|string|max:255',
            'id_number' => 'nullable|string|max:255',
            'passport_number' => 'nullable|string|max:255',
            'id_image_front' => 'nullable|image|max:10240',
            'id_image_back' => 'nullable|image|max:10240',
            'passport_image' => 'nullable|image|max:10240',
        ]);

        foreach (['id_image_front', 'id_image_back', 'passport_image'] as $imageKey) {
            if ($request->hasFile($imageKey)) {
                $validated[$imageKey] = $request->file($imageKey)->store('family_members/images', 'public');
            }
        }

        $familyMember = FamilyMember::create($validated);

        return redirect()->route('family-members.index')
            ->with('success', 'Family member created successfully.');
    }

    public function edit(FamilyMember $familyMember)
    {
        $familyMember->load('documents');
        return view('family_members.edit', compact('familyMember'));
    }

    public function update(Request $request, FamilyMember $familyMember)
    {
        $validated = $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'dob' => 'required|date',
            'family_register_number' => 'nullable|string|max:255',
            'amka' => 'nullable|string|max:255',
            'vat_number' => 'nullable|string|max:255',
            'pa_number' => 'nullable|string|max:255',
            'id_number' => 'nullable|string|max:255',
            'passport_number' => 'nullable|string|max:255',
            'id_image_front' => 'nullable|image|max:10240',
            'id_image_back' => 'nullable|image|max:10240',
            'passport_image' => 'nullable|image|max:10240',
        ]);

        foreach (['id_image_front', 'id_image_back', 'passport_image'] as $imageKey) {
            if ($request->hasFile($imageKey)) {
                if ($familyMember->$imageKey && Storage::disk('public')->exists($familyMember->$imageKey)) {
                    Storage::disk('public')->delete($familyMember->$imageKey);
                }
                $validated[$imageKey] = $request->file($imageKey)->store('family_members/images', 'public');
            }
        }

        $familyMember->update($validated);

        return redirect()->route('family-members.index')
            ->with('success', 'Family member updated successfully.');
    }

    public function destroy(FamilyMember $familyMember)
    {
        foreach (['id_image_front', 'id_image_back', 'passport_image'] as $imageKey) {
            if ($familyMember->$imageKey && Storage::disk('public')->exists($familyMember->$imageKey)) {
                Storage::disk('public')->delete($familyMember->$imageKey);
            }
        }

        foreach ($familyMember->documents as $document) {
            if ($document->file_path && Storage::disk('public')->exists($document->file_path)) {
                Storage::disk('public')->delete($document->file_path);
            }
        }

        $familyMember->delete();

        return redirect()->route('family-members.index')
            ->with('success', 'Family member deleted successfully.');
    }

    public function downloadImage(FamilyMember $familyMember, string $type)
    {
        if (!in_array($type, ['id_image_front', 'id_image_back', 'passport_image'])) {
            abort(404);
        }

        $filePath = $familyMember->$type;

        if (!$filePath || !Storage::disk('public')->exists($filePath)) {
            abort(404);
        }

        return Storage::disk('public')->download($filePath);
    }
}
