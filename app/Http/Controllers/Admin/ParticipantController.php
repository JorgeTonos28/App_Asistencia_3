<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Participant;
use Illuminate\Http\Request;

class ParticipantController extends Controller
{
    public function index(Request $request)
    {
        $query = Participant::withCount('attendances')->latest();

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%")
                  ->orWhere('document_number', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%")
                  ->orWhere('institution_department', 'like', "%{$search}%");
            });
        }

        $participants = $query->paginate(20)->withQueryString();

        return view('admin.participants.index', compact('participants'));
    }

    public function show(Participant $participant)
    {
        $participant->load(['attendances.event']);
        return view('admin.participants.show', compact('participant'));
    }

    public function edit(Participant $participant)
    {
        return view('admin.participants.edit', compact('participant'));
    }

    public function update(Request $request, Participant $participant)
    {
        $validated = $request->validate([
            'document_number' => 'required|string|max:50|unique:participants,document_number,' . $participant->id,
            'first_name' => 'required|string|max:100',
            'last_name' => 'required|string|max:100',
            'phone' => 'required|string|max:50',
            'email' => 'nullable|email|max:150',
            'institution_department' => 'nullable|string|max:150',
        ]);

        $participant->update($validated);

        return redirect()->route('admin.participants.index')->with('success', 'Participante actualizado exitosamente.');
    }

    public function destroy(Participant $participant)
    {
        $participant->delete();
        return redirect()->route('admin.participants.index')->with('success', 'Participante eliminado del catálogo.');
    }
}
