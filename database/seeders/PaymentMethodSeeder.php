<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PaymentMethodSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // check if the table is empty
        if (DB::table('payment_methods')->get()->count() == 0) {
            DB::table('payment_methods')->insert([
                [
                    'name' => 'ABA Payway',
                    'slug' => 'aba-payway',
                    'description' => 'Pay with ABA Payway',
                    'default' => true,
                ],
                [
                    'name' => 'Wing',
                    'slug' => 'wing',
                    'description' => 'Pay with Wing',
                    'default' => false,
                ],
                [
                    'name' => 'Pi Pay',
                    'slug' => 'pi-pay',
                    'description' => 'Pay with Pi Pay',
                    'default' => false,
                ],
            ]);
        } else {
            echo "\e[31mTable is not empty, therefore NOT ";
            echo "\e[32mSeeding \e[0mPaymentMethodTable\e[0m\n";
        }
    }
}
