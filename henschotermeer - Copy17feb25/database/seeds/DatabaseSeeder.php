<?php

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder {

    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run() {
        $this->call(SiteRolesSeeder::class);
//        $this->call(AvailableDevicesTableSeeder::class);
//        $this->call(LanguagesSeeder::class);
//        $this->call(LanguageMessagesSeeder::class);
//        $this->call(PromoTypePreSeeder::class);
    }

}
