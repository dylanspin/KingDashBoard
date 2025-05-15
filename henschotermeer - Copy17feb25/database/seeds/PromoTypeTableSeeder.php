<?php

use Illuminate\Database\Seeder;

class PromoTypeTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
         //insert some dummy records
         DB::table('promo_types')->insert(array(
            array(
                'id'=>1,
                'title'=>'Unlimited Usage.'
            ),
            array(
                'id'=>2,
                'title'=>'Valid for limited time period.'
            ),
            array(
                'id'=>3,
                'title'=>'Valid for limited number of times.'
            ),

          ));
    }
}
