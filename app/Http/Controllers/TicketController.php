<?php

namespace App\Http\Controllers;

use App\Http\Requests\PreviewTicketRequest;
use App\Http\Resources\TicketResource;

class TicketController extends Controller
{
    public function index()
    {
        return TicketResource::collection($this->sampleTickets());
    }

    public function show(string $id)
    {
        foreach ($this->sampleTickets() as $ticket) {
            if ((string) $ticket['id'] === $id) {
                return new TicketResource($ticket);
            }
        }

        return response()->json(['message' => 'Ticket not found'], 404);
    }

    public function preview(PreviewTicketRequest $request)
    {
        $data = $request->validated();

        return new TicketResource([
            'title' => $data['title'],
            'status' => 'open',
        ]);
    }

    private function sampleTickets(): array
    {
        return [
            ['id' => 1, 'title' => 'Printer is offline', 'status' => 'open'],
            ['id' => 2, 'title' => 'Cannot sign in', 'status' => 'closed'],
        ];
    }
}
