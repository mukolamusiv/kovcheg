<?php

namespace App\Services;

use App\Models\Invoice;
use App\Models\Production;

class ProductionService
{

    /**
     * @param array $data
     * @return Production
     */
    public static function createProduction(array $data): Production
    {
        return Production::getConnection()->transaction(function () use ($data) {
            $production = Production::create([
                'name' => $data['name'],

            ]);
            return $production;
        });
    }


    /**
     * @param Production $production
     * @param array $data
     * @return Production
     */
    public static function updateProduction(Production $production, array $data): Production
    {
        return Production::getConnection()->transaction(function () use ($production, $data) {
            $production->update([
                'name' => $data['name'],
            ]);

            return $production;
        });
    }

    /**
     * @param Production $production
     * @return bool
     */
    public static function deleteProduction(Production $production): bool
    {
        return Production::getConnection()->transaction(function () use ($production) {
            return $production->delete();
        });
    }

    /**
     * @param Production $production
     * @return bool
     */
    public static function restoreProduction(Production $production): bool
    {
        return Production::getConnection()->transaction(function () use ($production) {
            return $production->restore();
        });
    }


    public static function addCostomer(Production $production, array $data): Production
    {
        if($data['costomer_id'] == null){
            return $production;
        }
        return Production::getConnection()->transaction(function () use ($production, $data) {
            $production->customers()->attach($data['customer_id'], [
                'quantity' => $data['quantity'],
                'price' => $data['price'],
                'description' => $data['description'],
                'date_writing_off' => $data['date_writing_off'],
                'invoice_id' => $data['invoice_id'],
                'warehouse_id' => $data['warehouse_id'],
            ]);

            return $production;
        });
    }

    /**
     * @param array $data
     * @return Invoice
     */
    public static function createInvoice(Production $production, array $data): Invoice
    {
        return Invoice::getConnection()->transaction(function () use ($data) {
            $invoice = Invoice::create([
                'number' => $data['number'],
                'date' => $data['date'],
                'customer_id' => $data['customer_id'],
                'total_amount' => $data['total_amount'],
                'status' => $data['status'],
            ]);
            return $invoice;
        });
    }

}
