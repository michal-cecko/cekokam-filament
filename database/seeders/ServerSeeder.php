<?php

namespace Database\Seeders;

use App\Models\Other\Server;
use Illuminate\Database\Seeder;

class ServerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $servers = [
            [
                'name' => 'A',
                'color' => '#00FF00',
                'server_link' => 'http://172.16.2.0/',
                'ip_link' => 'http://172.16.2.{IP}/',
            ],
            [
                'name' => 'B',
                'color' => '#F0FF00',
                'server_link' => 'http://172.16.4.0/',
                'ip_link' => 'http://172.16.4.{IP}/',
            ],
        ];

        foreach ($servers as $server) {
            if (! Server::where('name', $server['name'])->exists()) {
                Server::create($server);
            }
        }
    }
}
