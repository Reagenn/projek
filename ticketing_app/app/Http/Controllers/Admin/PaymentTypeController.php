<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\PaymentType;

class PaymentTypeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $paymentTypes = PaymentType::all();
        return view('pages.admin.payment_type.index', compact('paymentTypes'));
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
            'name' => 'required|string|max:255|unique:payment_types,name',
        ]);

        PaymentType::create([
            'name' => $payload['name'],
        ]);

        return redirect()->route('admin.payment-types.index')->with('success', 'Tipe Pembayaran berhasil ditambahkan.');
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
            'name' => 'required|string|max:255|unique:payment_types,name,' . $id,
        ]);

        $paymentType = PaymentType::findOrFail($id);
        $paymentType->name = $payload['name'];
        $paymentType->save();

        return redirect()->route('admin.payment-types.index')->with('success', 'Tipe Pembayaran berhasil diperbarui.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        PaymentType::destroy($id);
        return redirect()->route('admin.payment-types.index')->with('success', 'Tipe Pembayaran berhasil dihapus.');
    }
}
