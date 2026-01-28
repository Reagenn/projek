<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\typeticket;
class TypeticketController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
{
		$typetickets = typeticket::all();
		return view('admin.tipetiket.index', compact('typetickets'));
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
        $payload = $request->validate([
            'nama' => 'required|string|max:255',
        ]);

        if (!isset($payload['nama'])) {
            return redirect()->route('typetickets.index')->with('error', 'Nama typ wajib diisi.');
        }

        typeticket::create([
            'name' => $payload['nama'],
        ]);

        return redirect()->route('admin.typetickets.index')->with('success', 'typ berhasil ditambahkan.');
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
        $payload = $request->validate([
            'nama' => 'required|string|max:255',
        ]);

        if (!isset($payload['nama'])) {
            return redirect()->route('typetickets.index')->with('error', 'Nama typ wajib diisi.');
        }

        $type = typeticket::findOrFail($id);
        $type->name = $payload['nama'];
        $type->save();

        return redirect()->route('admin.typetickets.index')->with('success', 'typ berhasil diperbarui.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        typeticket::destroy($id);
        return redirect()->route('admin.typetickets.index')->with('success', 'typ berhasil dihapus.');
    }
}
