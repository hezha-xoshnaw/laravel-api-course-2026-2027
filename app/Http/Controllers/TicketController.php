<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreTicketRequest;
use App\Http\Resources\TicketResource;
use App\Models\Ticket;

class TicketController extends Controller
{
    public function index()
    {
        return TicketResource::collection(Ticket::orderBy('id')->get());
    }

    public function show(Ticket $ticket)
    {
        return new TicketResource($ticket);
    }

    public function store(StoreTicketRequest $request)
    {
        $ticket = Ticket::create($request->validated());
        $ticket->refresh();

        return (new TicketResource($ticket))
            ->response()
            ->setStatusCode(201);
    }
}
