<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Payment;
use Illuminate\Http\JsonResponse;

class PaymentController
{
    public function store(): JsonResponse
    {
        return response()->json(Payment::create(request()->all()), 201);
    }
}
