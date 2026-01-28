<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Tiket;
class TiketController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validatedData = request()->validate([
            'event_id' => 'required|exists:events,id',
            'tipe' => 'required|string|max:255',
            'harga' => 'required|numeric|min:0',
            'stok' => 'required|integer|min:0',
        ]);

        // Create the tickets
        Tiket::create($validatedData);

        return redirect()->route('admin.events.show', $validatedData['event_id'])->with('success', 'tickets berhasil ditambahkan.');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $tickets = Tiket::findOrFail($id);

        $validatedData = $request->validate([
            'tipe' => 'required|string|max:255',
            'harga' => 'required|numeric|min:0',
            'stok' => 'required|integer|min:0',
        ]);

        $tickets->update($validatedData);

        return redirect()->route('admin.events.show', $tickets->event_id)->with('success', 'tickets berhasil diperbarui.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $tickets = Tiket::findOrFail($id);
        $eventId = $tickets->event_id;
        $tickets->delete();

        return redirect()->route('admin.events.show', $eventId)->with('success', 'tickets berhasil dihapus.');
    }
}
