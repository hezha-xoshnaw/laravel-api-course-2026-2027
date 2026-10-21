<?php

namespace Database\Seeders;

use App\Models\Ticket;
use Illuminate\Database\Seeder;

class TicketSeeder extends Seeder
{
    public function run(): void
    {
        foreach ([
            ['title' => 'Printer is offline', 'status' => 'open'],
            ['title' => 'Cannot sign in', 'status' => 'closed'],
        ] as $sample) {
            $ticket = Ticket::firstOrNew(['title' => $sample['title']]);
            $ticket->status = $sample['status'];
            $ticket->save();
        }
    }
}
