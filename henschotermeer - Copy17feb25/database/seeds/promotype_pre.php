<?php

use Illuminate\Database\Seeder;

class promotype_pre extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $promotype = \App\PromoType::where('title', 'Unlimited Usage.')->first();
        if (!$promotype) {
            $promotype = new \App\PromoType();
            $promotype->title = 'Unlimited Usage.';
            $promotype->save();
        }
        $promotype = \App\PromoType::where('title', 'Valid for limited time period.')->first();
        if (!$promotype) {
            $promotype = new \App\PromoType();
            $promotype->title = 'Valid for limited time period.';
            $promotype->save();
        }
        $promotype = \App\PromoType::where('title', 'Valid for limited number of times.')->first();
        if (!$promotype) {
            $promotype = new \App\PromoType();
            $promotype->title = 'Valid for limited number of times.';
            $promotype->save();
        }
    }
}
