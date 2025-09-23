<?php

namespace App\Exports;

use App\Models\Contact;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class ClientesExport implements FromCollection, WithHeadings
{
    protected $business_id;

    public function __construct($business_id)
    {
        $this->business_id = $business_id;
    }

    public function collection()
    {
        return Contact::where('business_id', $this->business_id)
                      ->where('type', 'customer')
                      ->with('cidade')
                      ->get()
                      ->map(function($contact) {
                          return [
                              'name' => $contact->name,
                              'cpf_cnpj' => $contact->cpf_cnpj,
                              'ie_rg' => $contact->ie_rg,
                              'rua' => $contact->rua,
                              'numero' => $contact->numero,
                              'bairro' => $contact->bairro,
                              'cep' => $contact->cep,
                              'email' => $contact->email,
                              'mobile' => $contact->mobile,
                              'landline' => $contact->landline,
                              'city_id' => $contact->city_id ? $contact->cidade->nome : null,
                          ];
                      });
    }

    public function headings(): array
    {
        return [
            'Nome', 'CPF/CNPJ', 'IE/RG', 'Rua', 'Número', 'Bairro', 'CEP', 'Email', 
            'Celular', 'Telefone', 'Cidade'
        ];
    }
}
