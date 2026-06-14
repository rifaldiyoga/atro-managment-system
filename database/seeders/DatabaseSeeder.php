<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
  /**
   * Seed the application's database.
   */
  public function run(): void
  {
    $this->call([
      UserSeeder::class,            // users (no dependencies)
      TaxSeeder::class,             // tax (no dependencies)
      SalesmanSeeder::class,        // srepgrp → srep
      BusinessPartnerSeeder::class, // bp
      BpAddrSeeder::class,          // bpaddr → depends on bp
      ItemSeeder::class,            // items
      ItemSupplierSeeder::class,    // itemsupplier → depends on items + bp (VEND)
      RegSeeder::class,             // reg
      AccountingSeeder::class,      // accgrp → acc → defa
    ]);
  }
}
