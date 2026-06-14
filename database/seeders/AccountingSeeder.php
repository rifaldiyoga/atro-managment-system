<?php

namespace Database\Seeders;

use App\Models\Account;
use App\Models\AccountGroup;
use App\Models\AccountingSetting;
use Illuminate\Database\Seeder;

class AccountingSeeder extends Seeder
{
  public function run(): void
  {
    $groups = [
      ['code' => 'ASSET', 'name' => 'Aset', 'type' => 'ASSET'],
      ['code' => 'LIABILITY', 'name' => 'Liabilitas', 'type' => 'LIABILITY'],
      ['code' => 'EQUITY', 'name' => 'Ekuitas', 'type' => 'EQUITY'],
      ['code' => 'REVENUE', 'name' => 'Pendapatan', 'type' => 'REVENUE'],
      ['code' => 'COGS', 'name' => 'Harga Pokok Penjualan', 'type' => 'EXPENSE'],
      ['code' => 'EXPENSE', 'name' => 'Beban', 'type' => 'EXPENSE'],
    ];

    foreach ($groups as $group) {
      AccountGroup::updateOrCreate(['code' => $group['code']], $group);
    }

    $accounts = [
      ['code' => '1000', 'name' => 'Kas/Bank', 'type' => 'ASSET', 'normal_balance' => 'DEBIT', 'group' => 'ASSET', 'is_cash' => true, 'key' => 'cash_bank_account_id'],
      ['code' => '1100', 'name' => 'Piutang Usaha', 'type' => 'ASSET', 'normal_balance' => 'DEBIT', 'group' => 'ASSET', 'key' => 'accounts_receivable_account_id'],
      ['code' => '1200', 'name' => 'Persediaan Barang', 'type' => 'ASSET', 'normal_balance' => 'DEBIT', 'group' => 'ASSET', 'key' => 'inventory_account_id'],
      ['code' => '2000', 'name' => 'Hutang Usaha', 'type' => 'LIABILITY', 'normal_balance' => 'CREDIT', 'group' => 'LIABILITY', 'key' => 'accounts_payable_account_id'],
      ['code' => '2100', 'name' => 'PPN Keluaran', 'type' => 'LIABILITY', 'normal_balance' => 'CREDIT', 'group' => 'LIABILITY', 'key' => 'output_tax_account_id'],
      ['code' => '2200', 'name' => 'Goods Received Clearing', 'type' => 'LIABILITY', 'normal_balance' => 'CREDIT', 'group' => 'LIABILITY', 'key' => 'gr_clearance_account_id'],
      ['code' => '5000', 'name' => 'Pendapatan Penjualan', 'type' => 'REVENUE', 'normal_balance' => 'CREDIT', 'group' => 'REVENUE', 'key' => 'sales_revenue_account_id'],
      ['code' => '5100', 'name' => 'Diskon Penjualan', 'type' => 'REVENUE', 'normal_balance' => 'DEBIT', 'group' => 'REVENUE', 'key' => 'sales_discount_account_id'],
      ['code' => '6000', 'name' => 'Harga Pokok Penjualan', 'type' => 'EXPENSE', 'normal_balance' => 'DEBIT', 'group' => 'COGS', 'key' => 'cogs_account_id'],
      ['code' => '6100', 'name' => 'Pembelian', 'type' => 'EXPENSE', 'normal_balance' => 'DEBIT', 'group' => 'EXPENSE', 'key' => 'purchase_account_id'],
      ['code' => '6200', 'name' => 'PPN Masukan', 'type' => 'ASSET', 'normal_balance' => 'DEBIT', 'group' => 'ASSET', 'key' => 'input_tax_account_id'],
      ['code' => '6300', 'name' => 'Diskon Pembelian', 'type' => 'EXPENSE', 'normal_balance' => 'CREDIT', 'group' => 'EXPENSE', 'key' => 'purchase_discount_account_id'],
    ];

    foreach ($accounts as $row) {
      $group = AccountGroup::where('code', $row['group'])->first();
      $account = Account::updateOrCreate(
        ['code' => $row['code']],
        [
          'name' => $row['name'],
          'type' => $row['type'],
          'normal_balance' => $row['normal_balance'],
          'accgrp_id' => $group?->id,
          'is_cash' => $row['is_cash'] ?? false,
          'active' => true,
        ]
      );

      AccountingSetting::updateOrCreate(
        ['key' => $row['key']],
        ['value' => (string) $account->id, 'note' => $row['name']]
      );
    }
  }
}
