<?
session_start();

if(isset($_GET['debug'])) {
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);
}
global $usuario;

//1 sefacil.com.br
//2 sefacilsistemas.com.br
//0  nao selecionado

$cnpj='';

$cliente_sefacil_id=0;


if(isset($_POST['db_wings']))
{
    $usuario=$_POST['db_wings'];
    $cnpj=$_POST['cnpj'];

    $_SESSION['db_wings']=$_POST['db_wings'];
    $_SESSION['cnpj']=$_POST['cnpj'];

     include("conexaoMySql.php"); //MYSQL  SEFACIL   $db

     include("conexao.php"); //MYSQL WINGS    $dbwings

    //include("conexaoSefacil.php"); //MYSQL sefacil antigo    $dbsefacil

    $continuar=1;
}else{
    if(isset($_SESSION['db_wings']))
    {
       $usuario=$_SESSION['db_wings'];
        $cnpj=$_SESSION['cnpj'];

        include("conexaoMySql.php"); //MYSQL  SEFACIL   $db

        include("conexao.php"); //MYSQL WINGS    $dbwings

       // include("conexaoSefacil.php"); //MYSQL sefacil antigo    $dbsefacil

        $continuar=1;
    }else{
        echo "<br><b>Nenhuma Base Wings Conectada !!</b><bR>";
        $continuar=0;
    }
}

if($continuar==1) {

    $sel = $db->start();
    $qr = $sel->query("SELECT * FROM business ");
    $num = $sel->numRows($qr);

    $selcli = $dbwings->start();
    $qrcli = $selcli->query("SELECT * FROM cliente ");
    $numcli = $selcli->numRows($qrcli);


    $selcli2 = $db->start();
    $qrcli2 = $selcli2->query("select * from contacts where business_id='".$_POST['cliente']."'");
    $numcli2 = $selcli2->numRows($qrcli2);


    $selpro = $dbwings->start();
    $qrpro = $selpro->query("SELECT * FROM produto");
    $numpro = $selpro->numRows($qrpro);

    $selpro2 = $db->start();
    $qrpro2 = $selpro2->query("SELECT * FROM products where business_id='".$_POST['cliente']."'");
    $numpro2 = $selpro2->numRows($qrpro2);


    $selnf = $dbwings->start();
    $qrnf = $selnf->query("SELECT * FROM nfe");
    $numnf = $selnf->numRows($qrnf);


    $selnf2 = $db->start();
    $qrnf2 = $selnf2->query("SELECT * FROM transactions  where business_id='".$_POST['cliente']."'");
    $numnf2 = $selnf2->numRows($qrnf2);

    $selnfce = $dbwings->start();
    $qrnfce = $selnfce->query("SELECT * FROM nfce");
    $numnfce = $selnfce->numRows($qrnfce);


    $seldef=$db->start();
    $qrdef=$seldef->query("select * from contacts where business_id=27 and is_default=1");
    $numdef=$seldef->numRows($qrdef);

/*
    $selclisefacil = $dbsefacil->start();
    $qrclisefacil = $selclisefacil->query("select * from cliente");

*/
}

if($_POST['action']=='importar_clientes')
{
     //senha do bando   root@123
    
    //mysqldump -u root -p sefacil_13756737000180 cliente produto nfe nfecte nfemdfe nfce > 13756737000180.sql
    //mysqldump -u root -p sefacil_36714062000102 cliente produto nfe nfecte nfemdfe nfce > 36714062000102.sql
    //mysqldump -u root -p sefacil_00668932000107 cliente produto nfe nfecte nfemdfe nfce > 00668932000107.sql    //CARDAN GOIAS
    //mysqldump -u root -p sefacil_36714062000102 cliente produto nfe nfecte nfemdfe nfce > 36714062000102.sql    //CARDAN GOIAS
    //mysqldump -u root -p sefacil_36714062000102 cliente produto nfe nfecte nfemdfe nfce > 36714062000102.sql
    //mysqldump -u root -p sefacil_20308577000172 cliente produto nfe nfecte nfemdfe nfce > 20308577000172.sql      //
    //mysqldump -u root -p sefacil_09258630000135 cliente produto nfe nfecte nfemdfe nfce > 09258630000135.sql  //SOS COLCHOES

    //mysqldump -u root -p sefacil_33726228000112 cliente produto nfe nfecte nfemdfe nfce > 33726228000112.sql  /  13784401000121

    //mysqldump -u root -p sefacil_13784401000121 cliente produto nfe nfecte nfemdfe nfce > 13784401000121.sql   //

    //mysqldump -u root -p sefacil_37392917000199 cliente produto nfe nfecte nfemdfe nfce > 37392917000199.sql

    //mysqldump -u root -p sefacil_15529485000182 cliente produto nfe nfecte nfemdfe nfce > 15529485000182.sql  //

    //mysqldump -u root -p sefacil_36237769000175 cliente produto nfe nfecte nfemdfe nfce > 36237769000175.sql

    //mysqldump -u root -p sefacil_22183202000159 cliente produto nfe nfecte nfemdfe nfce > 22183202000159.sql  //

    //mysqldump -u root -p sefacil_34771176000169 cliente produto nfe nfecte nfemdfe nfce > 34771176000169.sql

    //  root@123

    while($rescli=$selcli->fetchObject($qrcli))
    {

        $selcid=$db->start();
        $qrcid=$selcid->query("SELECT * FROM cities where nome='".$rescli->xMun."' ");
        $numcid=$selcid->numRows($qr);
        if($numcid>0)
        {
            $rescid=$selcid->fetchObject($qrcid);
            $cidade=$rescid->id;
        }else{
           $cidade=5571;  //CIDADE PADRAO
        }

        //59997664191 11     599.976.641-91
        //07546845000126 14  07.546.845/0001-26
        if(strlen($rescli->CNPJ)==11)  //PESSOAS FISICA
        {
            $t1=substr($rescli->CNPJ,0,3);
            $t2=substr($rescli->CNPJ,3,3);
            $t3=substr($rescli->CNPJ,6,3);
            $t4=substr($rescli->CNPJ,strlen($rescli->CNPJ)-2,2);

            $doc=$t1.".".$t2.".".$t3."-".$t4;
        }else{
            if(strlen($rescli->CNPJ)==14)  //PESSO JURIDICA
            {
                $t1=substr($rescli->CNPJ,0,2);
                $t2=substr($rescli->CNPJ,2,3);
                $t3=substr($rescli->CNPJ,5,3);
                $t4=substr($rescli->CNPJ,8,4);
                $t5=substr($rescli->CNPJ,strlen($rescli->CNPJ)-2,2);

                $doc=$t1.".".$t2.".".$t3."/".$t4."-".$t5;
            }else{
                $doc=$rescli->CNPJ;
            }
        }

      //  echo $rescli->CNPJ."   ->>   ".$doc." OK <br>";

        $aux=explode('/',$rescli->datacadastro);
        $dt=$aux[2]."-".$aux[1]."-".$aux[0]." ".date("H:i:s");

        $sql = "INSERT INTO contacts (business_id, city_id, cpf_cnpj, ie_rg, consumidor_final, contribuinte, rua, numero, bairro, cep, type, supplier_business_name, name, email,";
        $sql .= "contact_id, contact_status, tax_number, city, state, country, landmark, mobile, landline, alternate_number, pay_term_number, pay_term_type, credit_limit, created_by, total_rp,";
        $sql .= "total_rp_used, total_rp_expired, is_default, shipping_address, position, customer_group_id, custom_field1, custom_field2, custom_field3, custom_field4, deleted_at, created_at, updated_at)";
        $sql .= " VALUES ('".$_POST['cliente']."', '".$cidade."', '".$doc."', '".$rescli->IE."', '1', '1', '".$rescli->xLgr."', '".$rescli->nro."', '".$rescli->xBairro."', '".$rescli->CEP."', 'customer',NULL, '".$rescli->xNome."', '".$rescli->email."',NULL,'active', NULL, NULL, NULL, NULL, NULL, '', NULL, NULL, NULL, NULL, NULL, '1', '0', '0', '0',";
        $sql .= "'0', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '".$dt."', '".$dt."');";

        $sel = $db->start();
        $qr = $sel->query($sql);

        //echo $sql."<br><br>";

        echo $rescli->xNome." OK <br>";

        $sel = $db->start();
        $qr = $sel->query("SELECT LAST_INSERT_ID() as id;");
        $res = $sel->fetchObject($qr);

    }

    echo "<script>window.open('index2.php','_self');</script>";
}

if($_POST['action']=='importar_produtos')
{
    while($respro=$selpro->fetchObject($qrpro)) {

        $seluser=$db->start();
        $qruser=$seluser->query("SELECT * FROM users where business_id='".$_POST['cliente']."'");
        $numuser=$seluser->numRows($qruser);
        if($numuser>0)
        {
            $resuser=$seluser->fetchObject($qruser);
            $user_id=$resuser->id;

        }else{

        }

        $selbloc=$db->start();
        $qrbloc=$selbloc->query("SELECT * FROM business_locations where  business_id='".$_POST['cliente']."'");
        $resbloc=$selbloc->fetchObject($qrbloc);

        $seluni=$db->start();
        $qruni=$seluni->query("SELECT * FROM units where business_id='".$_POST['cliente']."'");
        $numuni=$seluni->numRows($qruni);
        if($numuni==0)
        {
           echo "Nenhuma unidade de medida encontrada conversão paralizada.!!!";
           exit;
        }else{
            $resuni=$seluni->fetchObject($qruni);
            $uni_id=$resuni->id;

        }


        $produtoId=0;

        $nome_produto=str_ireplace("'","",$respro->xProd);

        //INSERE PRODUTO
        $sql = "INSERT INTO products (name, business_id, type, unit_id, sub_unit_ids, brand_id, category_id, sub_category_id, tax, tax_type, enable_stock, alert_quantity, sku, barcode_type,"; //14
        $sql.= "expiry_period, expiry_period_type, enable_sr_no, weight, product_custom_field1, product_custom_field2, product_custom_field3, product_custom_field4, image, product_description, "; ///10
        $sql.= "created_by, warranty_id, is_inactive, not_for_selling, created_at, updated_at, perc_icms, perc_pis, perc_cofins, perc_ipi, cfop_interno, cfop_externo, cst_csosn, cst_pis, cst_cofins,";//15
        $sql.= "cst_ipi, ncm, cest) VALUES ( '".$nome_produto."', '".$_POST['cliente']."', 'single', '".$uni_id."', NULL, NULL, NULL, NULL, NULL, '', '1', NULL, '".$respro->cProd."', 'C128', NULL, NULL, '0', NULL, NULL, "; //3
                                        //10                                                                                                              //14
        $sql.= "NULL, NULL, NULL, NULL, NULL, '".$user_id."', NULL, '0',";
        $sql.= "'0', NULL, NULL, '0.00', '0.00', '0.00', '0.00', '".$respro->CFOP."', '";
        $sql.=$respro->CFOP."', '".$respro->CST."', '".$respro->CSTPIS."', '".$respro->CSTCOFINS."', '".$respro->CSTIPI."', '".$respro->NCM."', '".$respro->CEST."');";

        $sel = $db->start();
        $qr = $sel->query($sql);

        $sel = $db->start();
        $qr = $sel->query("SELECT LAST_INSERT_ID() as id;");
        $res = $sel->fetchObject($qr);

        $produtoId=$res->id;

        $sql="INSERT INTO product_locations (product_id, location_id) VALUES ('".$produtoId."', '".$resbloc->id."');";
        $sel = $db->start();
        $qr = $sel->query($sql);
        $res = $sel->fetchObject($qr);

        $sql="INSERT INTO product_variations (variation_template_id, name, product_id, is_dummy, created_at, updated_at) VALUES ( NULL, '".$nome_produto."', '".$produtoId."', '1', NULL, NULL);";

        $sel = $db->start();
        $qr = $sel->query($sql);
        $res = $sel->fetchObject($qr);

        $sel = $db->start();
        $qr = $sel->query("SELECT LAST_INSERT_ID() as id;");
        $res = $sel->fetchObject($qr);

        $produto_variacaoId=$res->id;

        $sql="INSERT INTO variations ( name, product_id, sub_sku, product_variation_id, variation_value_id, default_purchase_price, dpp_inc_tax, profit_percent, default_sell_price, sell_price_inc_tax,";
        $sql.="created_at, updated_at, deleted_at, combo_variations) VALUES ( '".$nome_produto."', '".$produtoId."', '".$respro->cProd."', '".$produto_variacaoId."', NULL, NULL, '0.0000', '0.0000', '".number_format($respro->vProd, 2, '.', '')."', '".$respro->vProd."', NULL, NULL, NULL, NULL);";

        $sel = $db->start();
        $qr = $sel->query($sql);
        $res = $sel->fetchObject($qr);

        $sel = $db->start();
        $qr = $sel->query("SELECT LAST_INSERT_ID() as id;");
        $res = $sel->fetchObject($qr);

        $variacaoId=$res->id;


        if($respro->quantidade>0)
        {
            $qtd=$respro->quantidade;
        }else{
            $qtd=0;
        }


        $sql="INSERT INTO variation_location_details ( product_id, product_variation_id, variation_id, location_id, qty_available,created_at,updated_at) ";
        $sql.="VALUES ( '".$produtoId."', '".$variacaoId."', '".$variacaoId."', '".$resbloc->id."', '".$qtd."','".date("Y-m-d H:i:s")."','".date("Y-m-d H:i:s")."');";

        $sel = $db->start();
        $qr = $sel->query($sql);

        echo $respro->cProd." ".$respro->xProd." CONVERTIDO<br>";

    }

    echo "<script>window.open('index2.php','_self');</script>";
}

if($_POST['action']=='importar_nfce')
{

    $seluser=$db->start();
    $qruser=$seluser->query("SELECT * FROM users where business_id='".$_POST['cliente']."'");
    $numuser=$seluser->numRows($qruser);
    if($numuser>0)
    {
        $resuser=$seluser->fetchObject($qruser);
        $user_id=$resuser->id;

    }

    $selbloc=$db->start();
    $qrbloc=$selbloc->query("SELECT * FROM business_locations where  business_id='".$_POST['cliente']."'");
    $resbloc=$selbloc->fetchObject($qrbloc);

    $selcfop=$db->start();
    $qrcfop=$selcfop->query("SELECT * FROM natureza_operacaos where business_id='".$_POST['cliente']."' and natureza='VENDA'");
    $numcfop=$selcfop->numRows($qrcfop);
    if($numcfop>0) {
        $rescfop = $selcfop->fetchObject($qrcfop);

        $cfopId = $rescfop->id;

        while ($resnfce = $selnfce->fetchObject($qrnfce))
        {

            $selcid = $db->start();
            $qrcid = $selcid->query("SELECT * FROM cities where nome like '%" . $resnfce->destxMun . "%' ");
            $numcid = $selcid->numRows($qr);
            if ($numcid > 0) {
                $rescid = $selcid->fetchObject($qrcid);
                $cidade = $rescid->id;
            } else {
                $cidade = 1;
            }


            $selbusca = $db->start();
            $qrbusca = $selbusca->query("select * from contacts where business_id='" . $_POST['cliente'] . "' and name='" . $resnfce->destxNome . "'");
            $numbusca = $selbusca->numRows($qrbusca);
            if ($numbusca > 0) {
                $resbusca = $selbusca->fetchObject($qrbusca);

                $clienteId = $resbusca->id;
                $contato_id = $resbusca->contact_id;
                $flag=1;
            } else {
                $clienteId = '';
                $contato_id = '';

                $selpad=$db->start();
                $qrpad=$selpad->query("select * from contacts where business_id='" . $_POST['cliente']."' and is_default=1");
                $numpad=$selpad->numRows($qrpad);
                if($numpad>0)
                {
                    $respad=$selpad->fetchObject($qrpad);

                    $clienteId = $respad->id;
                    $contato_id = $respad->contact_id;

                    $flag=1;
                }else{
                    $flag=0;
                }
            }

            if($flag==1) {

                $dataEmi = $resnfce->dhEmi;
                $dataSai = $resnfce->dhSaiEnt;

                $temp1 = explode('/', $dataEmi);
                $dataEmi = $temp1[2] . "-" . $temp1[1] . "-" . $temp1[0] . " " . $resnfce->hEmi;

                $temp2 = explode('/', $dataSai);
                $dataSai = $temp2[2] . "-" . $temp2[1] . "-" . $temp2[0] . " " . $resnfce->hSaiEnt;

                $sql = "INSERT INTO transactions ( business_id, location_id, res_table_id, res_waiter_id, res_order_status, type, sub_type, status, is_quotation, payment_status, adjustment_type, contact_id,";
                $sql .= "customer_group_id, invoice_no, ref_no, subscription_no, subscription_repeat_on, transaction_date, total_before_tax, tax_id, tax_amount, discount_type, discount_amount, rp_redeemed,";
                $sql .= "rp_redeemed_amount, shipping_details, shipping_address, shipping_status, delivered_to, shipping_charges, additional_notes, staff_note, round_off_amount, final_total, expense_category_id,";
                $sql .= "expense_for, commission_agent, document, is_direct_sale, is_suspend, exchange_rate, total_amount_recovered, transfer_parent_id, return_parent_id, opening_stock_product_id, created_by, ";
                $sql .= "import_batch, import_time, types_of_service_id, packing_charge, packing_charge_type, service_custom_field_1, service_custom_field_2, service_custom_field_3, service_custom_field_4, ";
                $sql .= "is_created_from_api, rp_earned, order_addresses, is_recurring, recur_interval, recur_interval_type, recur_repetitions, recur_stopped_on, recur_parent_id, invoice_token, pay_term_number, ";
                $sql .= "pay_term_type, selling_price_group_id, created_at, updated_at, natureza_id, placa, uf, valor_frete, tipo, qtd_volumes, numeracao_volumes, especie, peso_liquido, peso_bruto, numero_nfe, ";
                $sql .= "numero_nfce, numero_nfe_entrada, chave, chave_entrada, sequencia_cce, cpf_nota, troco, valor_recebido, transportadora_id, estado) VALUES (";
                $sql .= "'" . $_POST['cliente'] . "', '" . $resbloc->id . "', NULL, NULL, NULL, 'sell', NULL, 'received',";
                $sql .= " '0', 'paid', 'normal', '" . $clienteId . "', NULL, '000', '000', NULL, NULL, '" . date("Y-m-d H:i:s") . "', '" . $resnfce->vNF . "', NULL, '0.0000', NULL, '0.0000', '0', '0.0000', NULL, NULL, NULL, NULL, '0.0000', NULL, NULL, '0.0000', '0.0000',";
                $sql .= "NULL, '" . $user_id . "', NULL, NULL, '0', '0', '1.000', NULL, NULL, " . $resnfce->vNF . ", NULL, '1', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0', '0', NULL, '0', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL,";
                $sql .= "NULL, '" . $dataEmi . "', '" . $dataSai . "', '" . $cfopId . "', 'PLACA', 'UF', '0.00', '0', '0', 'VOLUME', 'ESPECIE', '0.000', '0.000', '', '" . $resnfce->nNF . "', '',";
                $sql .= "'" . $resnfce->chavenfe . "', '" . $resnfce->chavenfe . "', '0', '', '0.00', '" . $resnfce->vNF . "', NULL, 'APROVADO');";


                echo $sql . "<bR><br>";

                $sel = $db->start();
                $qr = $sel->query($sql);

                $sel = $db->start();
                $qr = $sel->query("SELECT LAST_INSERT_ID() as id;");
                $res = $sel->fetchObject($qr);

                $nfid = $res->id;

                echo $nfid . " NFCE inserida<br>";
            }

        }
    }
    else{
        $cfopId=0;
        echo "<b>Erro FALTA DE CFOP VENDA</b>";
    }


    echo "<script>window.open('index2.php','_self');</script>";

}

if($_POST['action']=='importar_nfe')
{

    $seluser=$db->start();
    $qruser=$seluser->query("SELECT * FROM users where business_id='".$_POST['cliente']."'");
    $numuser=$seluser->numRows($qruser);
    if($numuser>0)
    {
        $resuser=$seluser->fetchObject($qruser);
        $user_id=$resuser->id;

    }

    $selbloc=$db->start();
    $qrbloc=$selbloc->query("SELECT * FROM business_locations where  business_id='".$_POST['cliente']."'");
    $resbloc=$selbloc->fetchObject($qrbloc);

    $selcfop=$db->start();
    $qrcfop=$selcfop->query("SELECT * FROM natureza_operacaos where business_id='".$_POST['cliente']."' and natureza='VENDA'");
    $numcfop=$selcfop->numRows($qrcfop);
    if($numcfop>0) {
        $rescfop = $selcfop->fetchObject($qrcfop);

        $cfopId = $rescfop->id;


        while ($resnf = $selnf->fetchObject($qrnf)) {

            $selcid = $db->start();
            $qrcid = $selcid->query("SELECT * FROM cities where nome like '%" . $resnf->destxMun . "%' ");
            $numcid = $selcid->numRows($qr);
            if ($numcid > 0) {
                $rescid = $selcid->fetchObject($qrcid);
                $cidade = $rescid->id;
            } else {
                $cidade = 1;
            }


            $selbusca = $db->start();
            $qrbusca = $selbusca->query("select * from contacts where business_id='" . $_POST['cliente'] . "' and name='" . $resnf->destxNome . "'");
            $numbusca = $selbusca->numRows($qrbusca);
            if ($numbusca > 0) {
                $resbusca = $selbusca->fetchObject($qrbusca);

                $clienteId = $resbusca->id;
                $contato_id = $resbusca->contact_id;
            } else {
                $clienteId = '';
                $contato_id = '';
            }


            $dataEmi = $resnf->dhEmi;
            $dataSai = $resnf->dhSaiEnt;


            $temp1 = explode('/', $dataEmi);
            $dataEmi = $temp1[2] . "-" . $temp1[1] . "-" . $temp1[0] . " " . $resnf->hEmi;

            $temp2 = explode('/', $dataSai);
            $dataSai = $temp2[2] . "-" . $temp2[1] . "-" . $temp2[0] . " " . $resnf->hSaiEnt;


            $sql = "INSERT INTO transactions ( business_id, location_id, res_table_id, res_waiter_id, res_order_status, type, sub_type, status, is_quotation, payment_status, adjustment_type, contact_id,";
            $sql .= "customer_group_id, invoice_no, ref_no, subscription_no, subscription_repeat_on, transaction_date, total_before_tax, tax_id, tax_amount, discount_type, discount_amount, rp_redeemed,";
            $sql .= "rp_redeemed_amount, shipping_details, shipping_address, shipping_status, delivered_to, shipping_charges, additional_notes, staff_note, round_off_amount, final_total, expense_category_id,";
            $sql .= "expense_for, commission_agent, document, is_direct_sale, is_suspend, exchange_rate, total_amount_recovered, transfer_parent_id, return_parent_id, opening_stock_product_id, created_by, ";
            $sql .= "import_batch, import_time, types_of_service_id, packing_charge, packing_charge_type, service_custom_field_1, service_custom_field_2, service_custom_field_3, service_custom_field_4, ";
            $sql .= "is_created_from_api, rp_earned, order_addresses, is_recurring, recur_interval, recur_interval_type, recur_repetitions, recur_stopped_on, recur_parent_id, invoice_token, pay_term_number, ";
            $sql .= "pay_term_type, selling_price_group_id, created_at, updated_at, natureza_id, placa, uf, valor_frete, tipo, qtd_volumes, numeracao_volumes, especie, peso_liquido, peso_bruto, numero_nfe, ";
            $sql .= "numero_nfce, numero_nfe_entrada, chave, chave_entrada, sequencia_cce, cpf_nota, troco, valor_recebido, transportadora_id, estado) VALUES (";
            $sql .= "'" . $_POST['cliente'] . "', '" . $resbloc->id . "', NULL, NULL, NULL, 'sell', NULL, 'received',";
            $sql .= " '0', 'paid', 'normal', '" . $clienteId . "', NULL, '000', '000', NULL, NULL, '" . date("Y-m-d H:i:s") . "', '" . $resnf->vNF . "', NULL, '0.0000', NULL, '0.0000', '0', '0.0000', NULL, NULL, NULL, NULL, '0.0000', NULL, NULL, '0.0000', '0.0000',";
            $sql .= "NULL, '".$user_id."', NULL, NULL, '0', '0', '1.000', NULL, NULL, " . $resnf->vNF . ", NULL, '1', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0', '0', NULL, '0', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL,";
            $sql .= "NULL, '" . $dataEmi . "', '" . $dataSai . "', '" . $cfopId . "', 'PLACA', 'UF', '0.00', '0', '0', 'VOLUME', 'ESPECIE', '0.000', '0.000', '" . $resnf->nNF . "', '0', '',";
            $sql .= "'" . $resnf->chavenfe . "', '" . $resnf->chavenfe . "', '0', '', '0.00', '" . $resnf->vNF . "', NULL, 'APROVADO');";


            echo $sql . "<bR><br>";

            $sel = $db->start();
            $qr = $sel->query($sql);

            $sel = $db->start();
            $qr = $sel->query("SELECT LAST_INSERT_ID() as id;");
            $res = $sel->fetchObject($qr);

            $nfid = $res->id;

            echo $nfid . " NFE inserida<br>";

        }
    }
    else{
        $cfopId=0;
        echo "<b>Erro FALTA DE CFOP VENDA</b>";
    }


    echo "<script>window.open('index2.php','_self');</script>";

}

if($_POST['action']=='delete_importacao_produto')
{
    if(strlen($_POST['cliente'])>0) {

        if ($_POST['cliente'] == '1') {

            $selup = $db->start();
            $qrup = $selup->query("delete from contacts where business_id=1 and id<>1  and is_default=0");

            $sel = $db->start();
            $qr = $sel->query("SELECT * FROM products where business_id=1");
            $num = $sel->numRows($qr);
            while ($res = $sel->fetchObject($qr)) {

                if (($res->id != 4) && ($res->id != 5)) {

                    $selup = $db->start();
                    $qrup = $selup->query("delete from product_locations where product_id='" . $res->id . "'");

                    $selup = $db->start();
                    $qrup = $selup->query("delete from variation_location_details where product_id='" . $res->id . "'");

                    $selup = $db->start();
                    $qrup = $selup->query("delete from variations where product_id='" . $res->id . "'");

                    $selup = $db->start();
                    $qrup = $selup->query("delete from product_variations where product_id='" . $res->id . "'");

                    $selup = $db->start();
                    $qrup = $selup->query("delete from products where id='" . $res->id . "'");


                    echo $res->id . "  ->  " . $res->name . " <b>DEL</b> OK <br>";
                }
            }

            $selup = $db->start();
            $qrup = $selup->query("delete from transactions where business_id=1");


            echo "<script>window.open('index2.php','_self');</script>";


        } else {


            if ($_POST['senha'] == '101010') {


                $sel = $db->start();
                $qr = $sel->query("SELECT * FROM products where business_id='" . $_POST['cliente'] . "'");
                $num = $sel->numRows($qr);
                while ($res = $sel->fetchObject($qr)) {

                    if (($res->id != 4) && ($res->id != 5)) {


                        $selup = $db->start();
                        $qrup = $selup->query("delete from product_locations where product_id='" . $res->id . "'");

                        $selup = $db->start();
                        $qrup = $selup->query("delete from variation_location_details where product_id='" . $res->id . "'");

                        $selup = $db->start();
                        $qrup = $selup->query("delete from variations where product_id='" . $res->id . "'");

                        $selup = $db->start();
                        $qrup = $selup->query("delete from product_variations where product_id='" . $res->id . "'");

                        $selup = $db->start();
                        $qrup = $selup->query("delete from products where id='" . $res->id . "'");

                        echo $res->id . "  ->  " . $res->name . " <b>DEL</b> OK <br>";
                    }
                }



                echo "<script>window.open('index2.php','_self');</script>";
            } else {
                echo "<b>Senha de Exclusão Incorreta!!</b>";
            }
        }
    }else{
        echo "<b>Falta de Parâmetro POST</b>";
    }
}

if($_POST['action']=='delete_importacao')
{

    if(strlen($_POST['cliente'])>0) {

        if ($_POST['cliente'] == '1') {

            $selup = $db->start();
            $qrup = $selup->query("delete from contacts where business_id=1 and id<>1 and  is_default=0");

            $sel = $db->start();
            $qr = $sel->query("SELECT * FROM products where business_id=1");
            $num = $sel->numRows($qr);
            while ($res = $sel->fetchObject($qr)) {

                if (($res->id != 4) && ($res->id != 5)) {

                    $selup = $db->start();
                    $qrup = $selup->query("delete from product_locations where product_id='" . $res->id . "'");

                    $selup = $db->start();
                    $qrup = $selup->query("delete from variation_location_details where product_id='" . $res->id . "'");

                    $selup = $db->start();
                    $qrup = $selup->query("delete from variations where product_id='" . $res->id . "'");

                    $selup = $db->start();
                    $qrup = $selup->query("delete from product_variations where product_id='" . $res->id . "'");

                    $selup = $db->start();
                    $qrup = $selup->query("delete from products where id='" . $res->id . "'");


                    echo $res->id . "  ->  " . $res->name . " <b>DEL</b> OK <br>";
                }
            }

            $selup = $db->start();
            $qrup = $selup->query("delete from transactions where business_id=1");


            echo "<script>window.open('index2.php','_self');</script>";


        } else {


            if ($_POST['senha'] == '101010') {

                $selup = $db->start();
                $qrup = $selup->query("delete from contacts where business_id='" . $_POST['cliente'] . "' and is_default=0");

                $sel = $db->start();
                $qr = $sel->query("SELECT * FROM products where business_id='" . $_POST['cliente'] . "'");
                $num = $sel->numRows($qr);
                while ($res = $sel->fetchObject($qr)) {

                    if (($res->id != 4) && ($res->id != 5)) {


                        $selup = $db->start();
                        $qrup = $selup->query("delete from product_locations where product_id='" . $res->id . "'");

                        $selup = $db->start();
                        $qrup = $selup->query("delete from variation_location_details where product_id='" . $res->id . "'");

                        $selup = $db->start();
                        $qrup = $selup->query("delete from variations where product_id='" . $res->id . "'");

                        $selup = $db->start();
                        $qrup = $selup->query("delete from product_variations where product_id='" . $res->id . "'");

                        $selup = $db->start();
                        $qrup = $selup->query("delete from products where id='" . $res->id . "'");

                        echo $res->id . "  ->  " . $res->name . " <b>DEL</b> OK <br>";
                    }
                }

                $selup = $db->start();
                $qrup = $selup->query("delete from transactions where business_id='" . $_POST['cliente'] . "'");


                echo "<script>window.open('index2.php','_self');</script>";
            } else {
                echo "<b>Senha de Exclusão Incorreta!!</b>";
            }
        }
    }else{
        echo "<b>Falta de Parâmetro POST</b>";
    }
}

if($_POST['action']=='concluir')
{
    $fp = fopen($_SESSION['cnpj'], 'w');
    fwrite($fp, '1');
    fwrite($fp, '23');
    fclose($fp);

    echo "<script>window.open('index2.php','_self');</script>";
}

if($_POST['action']=='selecionar_conversor')
{
  $_SESSION['conversor']=$_POST['tipo_conversor'];

}

if($_POST['action']=='processa_cep')
{
        $selcli = $dbwings->start();
        $qrcli = $selcli->query("SELECT * FROM cliente ");
        while($rescli=$selcli->fetchObject($qrcli))
        {

            $selc=$db->start();
            $qrc=$selc->query("select c.*,ci.nome as nome_cidade from contacts c ,cities ci where c.city_id=ci.id and c.business_id='".$_POST['cliente']."' and c.name='".$rescli->xNome."'");
            $resc=$selc->fetchObject($qrc);


            $sql="update contacts set cep='".$rescli->CEP."' where id='".$resc->id."' and business_id='".$_POST['cliente']."'";

            $selup=$db->start();
            $qrup=$selup->query($sql);


            echo "BASEWINGS=".$rescli->CEP." BASEGESTAO".$resc->cep."<br>";
        }
}

if($_POST['action']=='processa_cidades')
{
    $selcli = $dbwings->start();
    $qrcli = $selcli->query("SELECT * FROM cliente ");
    while($rescli=$selcli->fetchObject($qrcli))
    {

        $cidade='0';
        $selcid=$db->start();
        $qrcid=$selcid->query("SELECT * FROM cities where nome='".$rescli->xMun."' ");
        $numcid=$selcid->numRows($qr);
        if($numcid>0)
        {
            $rescid=$selcid->fetchObject($qrcid);
            $cidade=$rescid->id;
            $nomcid=$rescid->nome;
        }else{
            $cidade=0;
            $nomcid="**CIDADE NAO ENCONTRADA**";
        }


        echo $rescli->xNome."BASEWINGS=".$rescli->xMun." BASE GESTAO=";

        $selc=$db->start();
        $qrc=$selc->query("select c.*,ci.nome as nome_cidade from contacts c ,cities ci where c.city_id=ci.id and c.business_id='".$_POST['cliente']."' and c.name='".$rescli->xNome."'");
        $resc=$selc->fetchObject($qrc);

        if(trim($rescli->xMun)!=trim($resc->nome_cidade))
        {
            $str= "<font color='red'>ATUALIZAR</font>".$resc->city_id." ";



        }else{
           $str='';
        }

        if($cidade!=$resc->city_id)
        {
            $confirma=' <font color="blue">OK CONFIRMA UPDATE</font>';

            $sql="update contacts set city_id='".$cidade."',city_id_temp='".$resc->city_id."' where id='".$resc->id."' and business_id='".$_POST['cliente']."'";

            $selup=$db->start();
            $qrup=$selup->query($sql);

        }else{
            $confirma='';
            $sql='';
        }
        echo $resc->nome_cidade." - ".$nomcid." CITYID=".$cidade.$str.$confirma."<br>";



    }
}

if($_POST['action']=='reprocessar_codigo_produto')
{
    $selpro = $db->start();
    $qrpro = $selpro->query("SELECT * FROM products where business_id='".$_POST['cliente']."'");
    $numpro = $selpro->numRows($qrpro);
    if($numpro>0)
    {
        while($respro=$selpro->fetchObject($qrpro))
        {

            /*
             * variations ( name, product_id, sub_sku, product_variation_id, variation_value_id, default_purchase_price, dpp_inc_tax, profit_percent, default_sell_price, sell_price_inc_tax,";
              $sql.="created_at, updated_at, deleted_at, combo_variations) VALUES ( '"
             */

            $sql="update variations set sub_sku='".$respro->sku."' where product_id='".$respro->id."'";

            $selv=$db->start();
            $qrv=$selv->query($sql);



            echo "Variation updated OK ".$sql."<bR>";



        }

        echo "<script>window.open('index2.php','_self');</script>";
    }
}


if($_POST['action']=='update_cfop')
{


    $sql="update products set products.cfop_interno='".$_POST['cfop_interno']."',cfop_externo='".$_POST['cfop_externo']."' where business_id='".$_POST['cliente']."'";

    $sel=$db->start();
    $qr=$sel->query($sql);

    echo $sql."<bR>";

    echo "<script>window.open('index2.php?msg=CFOPS ATUALIZADOS','_self');</script>";
}

if($_POST['action']=='update_cstipi')
{


    $sql="update products set products.cst_pis='".$_POST['CSTPIS']."',cst_ipi='".$_POST['CSTIPI']."',products.cst_cofins='".$_POST['CSTCONFINS']."',cst_csosn='".$_POST['CSTCSOSN']."' where business_id='".$_POST['cliente']."'";

    $sel=$db->start();
    $qr=$sel->query($sql);

    echo $sql."<bR>";

    echo "<script>window.open('index2.php?msg=CST PIS CST IPI CST COFINS ATUALIZADO','_self');</script>";
}


if($_POST['action']=='reprocessar_saldo_cliente')
{


$selbusca = $db->start();
$qrbusca = $selbusca->query("select * from contacts where business_id='" . $_POST['cliente'] . "'");
$numbusca = $selbusca->numRows($qrbusca);

    while($resbusca = $selbusca->fetchObject($qrbusca))
    {

        $sql=" where business_id='".$_POST['cliente']."'";

        $sel=$db->start();
        $qr=$sel->query($sql);

        echo $sql."<bR>";
    }

    echo "<script>window.open('index2.php?msg=SALDO CLIENTES REPROCESSADOS','_self');</script>";
}


function getConcluir($cnpj)
{
    if(file_exists($cnpj))
    {
        return " OK !";
    }else{
        return "";
    }
}
?>
<script>
    function importar_clientes()
    {
        document.getElementById('action').value='importar_clientes';
        document.form1.submit();
    }
    function importar_produtos()
    {
        document.getElementById('action').value='importar_produtos';
        document.form1.submit();
    }
    function importar_nfe()
    {
        document.getElementById('action').value='importar_nfe';
        document.form1.submit();
    }

    function importar_nfce()
    {
        document.getElementById('action').value='importar_nfce';
        document.form1.submit();
    }
    function delete_importacao()
    {
        document.getElementById('action').value='delete_importacao';
        document.form1.submit();
    }
    function delete_importacao_produto()
    {
        document.getElementById('action').value='delete_importacao_produto';
        document.form1.submit();
    }

    function selecionar_base_wings()
    {

        var idx= document.getElementById('db_wings').selectedIndex;
        var cnpj=document.getElementById('db_wings').options[idx].innerHTML;

        document.getElementById('cnpj').value=cnpj;

        document.form1.submit();

    }

    function selecionar_base_sefacil()
    {

    /*    var idx= document.getElementById('cliente_sefacil').selectedIndex;
        var id=document.getElementById('cliente_sefacil').options[idx].innerHTML;

        document.getElementById('cliente_sefacil_id').value=id;

        document.form1.submit();
*/
    }

    function criar_pastas()
    {
        document.getElementById('frame').style.display='inline';
        document.getElementById('frame').src='criar_folder.php?id=<? echo $cnpj;?>';

        document.getElementById('btn_criar_pasta').style.display='none';
        document.getElementById('btn_importar_xml').style.display='inline';

        window.open('criar_folder.php?id=<? echo $cnpj;  ?>','frame');

    }

    function importar_xml()
    {
        document.getElementById('frame').style.display='none';
        document.getElementById('frame2').style.display='inline';
        document.getElementById('frame2').src='http://sefacil.com.br/converter.php?id=<? echo $cnpj;?>';

        window.open('http://sefacil.com.br/converter.php?id=<? echo $cnpj;  ?>','frame2');
    }
    function concluir()
    {
        document.getElementById('action').value='concluir';
        document.form1.submit();
    }
    function selecionar_tipo_conversor(id)
    {
        document.getElementById('action').value='selecionar_conversor';
        document.getElementById('tipo_conversor').value=id;
        document.form1.submit();
    }
    function processa_cidades()
    {
        document.getElementById('action').value='processa_cidades';
        document.form1.submit();
    }
    function processa_cep()
    {
        document.getElementById('action').value='processa_cep';
        document.form1.submit();
    }
    function reprocessar_codigo_produto()
    {
        document.getElementById('action').value='reprocessar_codigo_produto';
        document.form1.submit();
    }
    function update_cstipi()
    {
        document.getElementById('action').value='update_cstipi';
        document.form1.submit();
    }
    function update_cfop()
    {
        document.getElementById('action').value='update_cfop';
        document.form1.submit();
    }
    function reprocessar_saldo_cliente()
    {
        document.getElementById('action').value='reprocessar_saldo_cliente';
        document.form1.submit();
    }
</script>
<!doctype html>
<html lang="en">
<head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous">
</head>
<body>
<form name="form1" method="post">
<?

//1 sefacil.com.br
//2 sefacilsistemas.com.br
//0  nao selecionado


//if((!isset($_SESSION['conversor']))||($_SESSION['conversor']=='0')){?>
    <!--
    <div class="card-body">
    <input type="hidden" name="tipo_conversor" id="tipo_conversor" value="">
    <button type="button" class="btn btn-success btn-block" onclick="selecionar_tipo_conversor('2')">Conversor = > www.sefacilsistemas.com.br/app/ (SISTEMA ANTIGO)</button>
    <button type="button" class="btn btn-danger btn-block" onclick="selecionar_tipo_conversor('1')">Conversor = > www.sefacil.com.br/app/ (SISTEMA MAIS ANTIGO AINDA)></button>
    <input type="hidden" name="action" id="action" value="">
</div>
-->

<? // }else{?>

    <input type="hidden" name="action" id="action" value="listar">
    <input type="hidden" name="cnpj" id="cnpj" value="<? echo $cnpj;?>">
    <input type="hidden" name="cliente_sefacil_id" id="cliente_sefacil_id" value="<? echo $cliente_sefacil_id;?>">
<div align="center">
<h1>CONVERSOR SEFACIL.COM.BR  PRIMEIRO SISTEMA</h1>

   <!-- <select class="form-select" name="cliente_sefacil" id="cliente_sefacil" aria-label="Default select example" style="width: 60%;color:red" onchange="selecionar_base_sefacil()">
        <option selected>SELELECIONE O CLIENTE DO SISTEMA SEFACIL</option>
        <? /*while($resclisefacil=$selclisefacil->fetchObject($qrclisefacil))
        {

            $aux=str_replace(".","",$resclisefacil->CNPJ);
            $aux=str_replace("/","",$aux);
            $aux=str_replace("-","",$aux);
            ?>
            <option value="<? echo $resclisefacil->clienteid;?>"><? echo $resclisefacil->clienteid." - ".$aux." - ".$resclisefacil->xNome.getConcluir($aux);?></option>
        <? }*/ ?>
    </select>
     <br>
    <br>-->

    <select class="form-select" name="db_wings" id="db_wings" aria-label="Default select example"  style="width: 60%" onchange="selecionar_base_wings()">
        <option selected>Selecione a Empresa Para Converter</option>
        <option value="wings10" <? if($_SESSION['db_wings']=='wings10'){ ?> selected <? } ?>>13756737000180<? echo getConcluir('13756737000180');?></option>
        <option value="wings11" <? if($_SESSION['db_wings']=='wings11'){ ?> selected <? } ?>>00668932000107<? echo getConcluir('00668932000107');?></option>
        <option value="wings12" <? if($_SESSION['db_wings']=='wings12'){ ?> selected <? } ?>>36714062000102<? echo getConcluir('36714062000102');?></option>
        <option value="wings13" <? if($_SESSION['db_wings']=='wings13'){ ?> selected <? } ?>>20308577000172<? echo getConcluir('20308577000172');?></option>
        <option value="wings14" <? if($_SESSION['db_wings']=='wings14'){ ?> selected <? } ?>>09258630000135<? echo getConcluir('09258630000135');?></option>
        <option value="wings16" <? if($_SESSION['db_wings']=='wings16'){ ?> selected <? } ?>>15221985000152<? echo getConcluir('15221985000152');?></option>
        <option value="wings17" <? if($_SESSION['db_wings']=='wings17'){ ?> selected <? } ?>>33726228000112<? echo getConcluir('33726228000112');?></option>
        <option value="wings18" <? if($_SESSION['db_wings']=='wings18'){ ?> selected <? } ?>>13784401000121<? echo getConcluir('13784401000121');?></option>
        <option value="wings19" <? if($_SESSION['db_wings']=='wings19'){ ?> selected <? } ?>>37392917000199<? echo getConcluir('37392917000199');?></option>
        <option value="wings20" <? if($_SESSION['db_wings']=='wings20'){ ?> selected <? } ?>>15529485000182<? echo getConcluir('15529485000182');?></option>
        <option value="wings21" <? if($_SESSION['db_wings']=='wings21'){ ?> selected <? } ?>>36237769000175<? echo getConcluir('36237769000175');?></option>
        <option value="wings22" <? if($_SESSION['db_wings']=='wings22'){ ?> selected <? } ?>>22183202000159<? echo getConcluir('22183202000159');?></option>
        <option value="wings23" <? if($_SESSION['db_wings']=='wings23'){ ?> selected <? } ?>>34771176000169<? echo getConcluir('34771176000169');?></option>


    </select>
    <bR><? $cnpj=trim(str_ireplace(' OK !','',$cnpj));?><bR>

    <select class="form-select" name="cliente" aria-label="Default select example" style="width: 60%">
        <option selected>Selecione o Cliente Alvo</option>
        <? while($res=$sel->fetchObject($qr)){

            $aux=str_replace(".","",$res->cnpj);
            $aux=str_replace("/","",$aux);
            $aux=str_replace("-","",$aux);
            ?>
        <option <? if($cnpj==$aux){ echo "selected"; }?> value="<? echo $res->id;?>"><? echo $res->id." - ".$aux." - ".$res->name.getConcluir($aux);?></option>
        <? } ?>
    </select>
    <br>
    <? if($numdef==0){?>
       <div align="center"><font color="red"> Cliente Padrão Não Encontrado</font></div>
    <? } ?>


</div><br>

<div class="card-body">
    <button type="button" class="btn btn-success btn-block" onclick="importar_clientes()">Cliente <? echo $numcli." / ".$numcli2;?></button>
</div>
    <div class="card-body">
        <button type="button" class="btn btn-success btn-block" onclick="importar_produtos()">Produto <? echo $numpro." / ".$numpro2;?></button>
        <br>
        CST IPI=<input type="text" name="CSTIPI" value="99"><bR>
        CST COFINS=<input type="text" name="CSTCONFINS" value="49"><bR>
        CST PIS=<input type="text" name="CSTPIS" value="49"><bR>
        CST CSOSN=<input type="text" name="CSTCSOSN" value="49"><bR>
        <button type="button" class="btn btn-success" onclick="update_cstipi()">Update CSTIPI/CSTCOFINS </button>

        <!--update products set products.cfop_interno='49',cst_ipi='99',products.cst_cofins='49' where business_id=38-->
        <br>
        cfop_interno (ESTADUAL)=<input type="text" name="cfop_interno" value="49"><bR>
        cfop_externo (INTERESTADUAL)=<input type="text" name="cfop_externo" value="49"><bR>
        <button type="button" class="btn btn-success" onclick="update_cfop()">Update CFOP </button>

    </div>
    <div class="card-body">
        <button type="button" class="btn btn-success btn-block" onclick="importar_nfe()">NFE <? echo $numnf." / ".$numnf2;?></button>
    </div>
    <div class="card-body">
        <button type="button" class="btn btn-success btn-block" onclick="importar_nfce()">NFCE <? echo $numnfce;?></button>
    </div>

    <div class="card-body">
        <button id="btn_importar_xml" style="display: none;" type="button" class="btn btn-success btn-block" onclick="importar_xml()">XMLs NFE/NFCE</button>
        <button id="btn_criar_pasta" style="display: inline;" type="button" class="btn btn-success btn-block" onclick="criar_pastas()">CRIAR PASTAS PARA OS XMLs NFE/NFCE</button>
    </div>
    <iframe id="frame" name="frame" src="" width="100%" height="800px" style="display:none"> </iframe>
    <iframe id="frame2" name="frame2" src="" width="100%" height="800px" style="display:none"> </iframe>
    <div class="card-body">
        <button type="button" class="btn btn-success btn-block" onclick="delete_importacao()">Deletar Dados Importados, CUIDADO !</button>
        <button type="button" class="btn btn-success btn-block" onclick="delete_importacao_produto()">Deletar Produtos!</button>
        Senha Exclusão :<input type="password" name="senha" id="senha" value="">
    </div>
    <div class="card-body">
        <button type="button" class="btn btn-success btn-block" onclick="processa_cidades()">Processar Cidades</button>
        <input type="checkbox" name="cbexecuta_cidade" id="cbexecuta_cidade" value="">
    </div>
    <div class="card-body">
        <button type="button" class="btn btn-success btn-block" onclick="processa_cep()">Processar CEP</button>
    </div>

    <div class="card-body">
        <button type="button" class="btn btn-success btn-block" onclick="reprocessar_codigo_produto()">Reprocessar CÓDIGO PRODUTO</button>
    </div>

    <div class="card-body">
        <button type="button" class="btn btn-success btn-block" onclick="reprocessar_saldo_cliente()">Reprocessar SALDO CLIENTE</button>
    </div>

    <div class="card-body">
        <button type="button" class="btn btn-success btn-block" onclick="concluir()">Concluir Importação, Gravar como Já concluida.</button>
    </div>


</form>
<? //} ?>
<script src="https://code.jquery.com/jquery-3.3.1.slim.min.js" integrity="sha384-q8i/X+965DzO0rT7abK41JStQIAqVgRVzpbzo5smXKp4YfRvH+8abtTE1Pi6jizo" crossorigin="anonymous"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js" integrity="sha384-UO2eT0CpHqdSJQ6hJty5KVphtPhzWj9WO1clHTMGa3JDZwrnQq4sF86dIHNDz0W1" crossorigin="anonymous"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js" integrity="sha384-JjSmVgyd0p3pXB1rRibZUAYoIIy6OrQ6VrjIEaFf/nJGzIxFDsf4x0xIM+B07jRM" crossorigin="anonymous"></script>
</body>
</html>