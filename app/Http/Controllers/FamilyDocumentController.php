<?php

namespace App\Http\Controllers;

use App\Models\FamilyDocument;
use App\Models\FamilyMember;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class FamilyDocumentController extends Controller
{
    public function store(Request $request, FamilyMember $familyMember)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'file' => 'required|file|max:20480',
        ]);

        $file = $request->file('file');
        $filePath = $file->store('family_documents', 'public');
        $fileType = $file->getClientMimeType();
        $fileSize = $file->getSize();

        $familyMember->documents()->create([
            'title' => $validated['title'],
            'file_path' => $filePath,
            'file_type' => $fileType,
            'file_size' => $fileSize,
        ]);

        return redirect()->route('family-members.edit', $familyMember)
            ->with('success', 'Document uploaded successfully.');
    }

    public function download(FamilyDocument $document)
    {
        if (!$document->file_path || !Storage::disk('public')->exists($document->file_path)) {
            abort(404);
        }

        return Storage::disk('public')->download($document->file_path, $document->title . '.' . pathinfo($document->file_path, PATHINFO_EXTENSION));
    }

    public function destroy(FamilyDocument $document)
    {
        $familyMemberId = $document->family_member_id;

        if ($document->file_path && Storage::disk('public')->exists($document->file_path)) {
            Storage::disk('public')->delete($document->file_path);
        }

        $document->delete();

        return redirect()->route('family-members.edit', $familyMemberId)
            ->with('success', 'Document deleted successfully.');
    }
}
