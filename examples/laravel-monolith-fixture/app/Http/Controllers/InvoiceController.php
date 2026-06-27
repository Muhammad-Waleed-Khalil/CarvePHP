<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Invoice;
use Illuminate\Http\JsonResponse;

class InvoiceController
{
    public function index(): JsonResponse
    {
        return response()->json(Invoice::all());
    }

    public function store(): JsonResponse
    {
        return response()->json(Invoice::create(request()->all()), 201);
    }
}
