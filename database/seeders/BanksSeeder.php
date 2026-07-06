<?php

namespace Database\Seeders;

use App\Models\Customer\Bank;
use Illuminate\Database\Seeder;

class BanksSeeder extends Seeder
{
    public function run(): void
    {
        $banks = [
            ['name' => 'Kapitalbank', 'mfo' => '01096'],
            ['name' => 'Ipoteka-Bank', 'mfo' => '00893'],
            ['name' => 'Hamkorbank', 'mfo' => '00853'],
            ['name' => 'Agrobank', 'mfo' => '00441'],
            ['name' => 'Microcreditbank', 'mfo' => '00978'],
            ['name' => 'NBU (Milliy Bank)', 'mfo' => '00404'],
            ['name' => 'Uzpromstroybank', 'mfo' => '00447'],
            ['name' => 'Orient Finans Bank', 'mfo' => '01006'],
            ['name' => 'TBC Bank Uzbekistan', 'mfo' => '01133'],
            ['name' => 'Davr-Bank', 'mfo' => '00931'],
        ];

        foreach ($banks as $bank) {
            Bank::firstOrCreate(['mfo' => $bank['mfo']], array_merge($bank, ['is_active' => true]));
        }
    }
}
