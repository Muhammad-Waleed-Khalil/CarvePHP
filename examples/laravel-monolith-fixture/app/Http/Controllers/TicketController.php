<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Ticket;
use Illuminate\Http\JsonResponse;

class TicketController
{
    public function index(): JsonResponse
    {
        return response()->json(Ticket::all());
    }

    public function store(): JsonResponse
    {
        return response()->json(Ticket::create(request()->all()), 201);
    }
}
