<?php

namespace App\Utils;

use App\Models\RequestCertificate;
use App\Models\User;
use Carbon\Carbon;
use DateTime;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Modules\Superadmin\Entities\Subscription;
use NFePHP\Common\Certificate;


class ValidationsGenericRequestUtil extends Util
{
    /**
     * @note verifica se há autorização para tela
     * @param $abilitties
     * @return void
     */
    
    public static function isFromBusiness($transaction)
    {
        if (empty($transaction)) {
            abort(404, 'Not Found');
        }
    }

    /**
     * @note Verifica se a linceça expirou
     * @param Request $request
     * @return false[]|string[]
     */
    public static function licenseInFinished(\Illuminate\Http\Request $request)
    {
        if ($request->user()->business_id == 1) {
            return ['status' => false];
        }

        //faz a diferenças de dias entre o dia de hoje e o dia de hoje
        $dataInicio = Carbon::create(now());

        $dataFimLicenciamento = DB::table('subscriptions')
            ->where('end_date', '>', now())
            ->where('business_id', $request->user()->business_id)
            ->whereNull('deleted_at')
            ->where('end_date', '>', now())->max('subscriptions.end_date');


        if (empty($dataFimLicenciamento)) {
            return ['status' => 'error'];
        }

        
        $dataFim = Carbon::create($dataFimLicenciamento);
        //verificq se não licença ativas

        if (empty($dataFim)) {
            return ['status' => 'error',
                'message' => '<b>A sua licença expirou, por favor renove sua licença</b>'
                . (request()->user()->business->created_by_user_id  == null|| request()->user()->business->created_by_user_id  == 1 ? '</br><a class="btn btn-system btn-xs" href="/subscription">Renovar Agora</a>' : "")];
        }


        $days = $dataInicio->diff($dataFim)->days;

        if ($days < 5) {
            return ['status' => 'warning',
                'message' => '<b class="text-black">Seu pacote de licença expira em '
                    . $days
                    . ' dias</b>'
                . (request()->user()->business->created_by_user_id  == null|| request()->user()->business->created_by_user_id  == 1 ?'</br>  <a class="btn btn-system btn-xs" href="/subscription">Renovar Agora</a> ':'')];
        }


        return ['status' => false];
    }

    /** @note Verifica se o mesmo é um superAdmin no sistema
     * @param $id
     * @return bool|void
     */
    

    public static function finishedCertificate(\Illuminate\Http\Request $request)
    {
        try {
            if (RequestCertificate::where('business_id', auth()->user()->business_id)->where('status', 'PAGO')->count() > 0) {
                return ['alert' => false];
            }

            //carregar Certificado
            $infoCertificado = Certificate::readPfx($request->user()->business->certificado, base64_decode($request->user()->business->senha_certificado));

            //pega objeto data de certificado
            $expirarCertificado = Carbon::create($infoCertificado->publicKey->validTo->format('Y-m-d H:i:s'));

            // pega data de hoje
            $nowDate = Carbon::now();

            //dias de diferenças
            $diffDays = $nowDate->diffInDays($expirarCertificado);

            if ($nowDate >= $expirarCertificado) {  
                $diffDays = ($diffDays * -1);
            }

            if ($diffDays < 7 && $diffDays > 1) {
                return ['alert' => true, 'message' => 'Faltam ' . $diffDays . ' dias para o vencimento do certificado clique no botão e renove agora mesmo!', 'module' => 'certificado', 'icon' => "info"];
            } elseif ($diffDays == 1) {
                return ['alert' => true, 'message' => 'Faltam ' . $diffDays . ' dia para o vencimento do certificado clique no botão e renove agora mesmo!', 'module' => 'certificado', 'icon' => "info"];
            } elseif ($diffDays < 1 && $diffDays > 0) {
                return ['alert' => true, 'message' => 'Faltam algumas horas para o vencimento do certificado clique no botão e renove agora mesmo!', 'module' => 'certificado', 'icon' => "info"];
            } elseif ($diffDays < 0) {
                return ['alert' => true, 'message' => 'Certificado vencido clique no botão e renove agora mesmo!', 'module' => 'certificado', 'icon' => "info"];
            }
            return ['alert' => false];
        } catch (\Exception $exception) {
            return ['alert' => true, 'message' => 'Não tem certificado de nota fiscal? marque já uma entrevista!', 'module' => 'certificado', 'icon' => "info"];
        }

    }

}
