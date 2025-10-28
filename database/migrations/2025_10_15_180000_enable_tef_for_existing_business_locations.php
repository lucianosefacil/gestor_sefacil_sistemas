<?php

use Illuminate\Database\Migrations\Migration;
use App\Models\BusinessLocation;

class EnableTefForExistingBusinessLocations extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Habilitar TEF para todas as business locations existentes
        $locations = BusinessLocation::all();
        
        foreach ($locations as $location) {
            $default_payment_accounts = !empty($location->default_payment_accounts) 
                ? json_decode($location->default_payment_accounts, true) 
                : [];
            
            // Adicionar TEF como habilitado se ainda não existir
            if (!isset($default_payment_accounts['tef'])) {
                $default_payment_accounts['tef'] = [
                    'is_enabled' => 1,
                    'account' => null
                ];
            }
            
            $location->default_payment_accounts = json_encode($default_payment_accounts);
            $location->save();
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Reverter - remover TEF das configurações
        $locations = BusinessLocation::all();
        
        foreach ($locations as $location) {
            $default_payment_accounts = !empty($location->default_payment_accounts) 
                ? json_decode($location->default_payment_accounts, true) 
                : [];
            
            // Remover TEF da configuração
            if (isset($default_payment_accounts['tef'])) {
                unset($default_payment_accounts['tef']);
            }
            
            $location->default_payment_accounts = json_encode($default_payment_accounts);
            $location->save();
        }
    }
}