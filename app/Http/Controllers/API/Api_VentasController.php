<?php

namespace App\Http\Controllers\API;

use DateTime;
use stdClass;
use DateTimeZone;
use App\Models\BPLatam;
use App\Models\OH_TEST;
use App\Models\OL_TEST;
use App\Models\OP_TEST;
use App\Models\User_TV;
use App\Models\countrys;
use App\Models\sales_tv;
use App\Models\Sap_User;
use App\Models\Taxcodes;
use App\Models\Contracts;
use App\Models\EstadosCI;
use App\Models\Warehouses;
use App\Models\CIINFO_TEST;
use App\Models\OH_CHL_TEST;
use App\Models\OL_CHL_TEST;
use App\Models\OP_CHL_TEST;
use Illuminate\Http\Request;
use App\Models\Contracts_test;
use App\Models\sales_payments;
use App\Models\sales_products;
use App\Models\Address_Logbook;
use App\Models\CIINFOCOMP_TEST;
use App\Models\Control_ci_test;
use App\Models\PaymentAccounts;
use App\Models\CIINFOENVIO_TEST;
use App\Models\Products_Warranty;
use Illuminate\Support\Facades\DB;
use App\Models\warranties_rejected;
use App\Http\Controllers\Controller;
use App\Models\BusinessPartner;
use App\Models\BusinessPartner_Test;
use App\Models\BusinessPartnerAccInfo;
use App\Models\warranties_in_process;
use App\Models\BusinessPartnerAccInfo_Test;
use App\Models\BusinessPartnerAddress;
use App\Models\BusinessPartnerAddress_Test;
use App\Models\BusinessPartnerTaxInfo;
use App\Models\BusinessPartnerTaxInfo_Test;
use App\Models\CIINFO;
use App\Models\CIINFOCOMP;
use App\Models\CIINFOENVIO;
use App\Models\Control_ci;
use App\Models\OH;
use App\Models\OH_CHL;
use App\Models\OL;
use App\Models\OL_CHL;
use App\Models\OP;
use App\Models\OP_CHL;

class Api_VentasController extends Controller
{
    public function ventaProd(Request $request)
    {
        $data['status'] = 200;
        if (!isset($request->id)) {
            $data['status'] = 300;
            $data['error '] = "No se recibió el Id de la Venta";
            return json_encode($data);
        }
        $val_id = is_numeric($request->id);
        if (!$val_id) {
            $data['status'] = 301;
            $data['error '] = "El ID de venta es incorrecto : " . $request->id;
            return json_encode($data);
        }
        $stopper = 0;
        if (isset($request->stopper)) {
            $stopper = $request->stopper;
        }
        //creacion de objeto que guarda la información
        $info = new stdClass();
        $countrys = ['tst', 'COL', 'MEX', 'PER', 'ECU', 'PAN', 'GTM', 'SLV', 'CRI', 'USA', 'CHL'];
        $taxcodes = [];
        $taxcodes = $this->get_taxcodes();
        // return $taxcodes;

        //creacion warehouses
        $warehouses = $this->get_warehouses();
        // return $warehouses;

        if ($taxcodes == 0 || $warehouses == 0) {
            $data['status'] = 300;
            $data['error_info'] = 'Error al obtener taxcodes y warehouses';
            return json_encode($data);
        }

        //Garantías 
        $warrantys_items = $this->get_items_warranty();
        if ($warrantys_items == 0) {
            $data['status'] = 326;
            $data['error_info'] = 'Error al obtener los items de garantía';
            return json_encode($data);
        }
        // return $warrantys_items;
        //Obtener datos de Ventas
        try {
            $sale = sales_tv::select('sales.*', 'countries.code')
                ->join('countries', 'sales.country_id', '=', 'countries.id')
                ->where('sales.id', trim($request->id))
                ->where('status', 'pagada')
                ->first();
        } catch (\Throwable $th) {
            $data['status'] = 302;
            $data['error_info'] = $th;
            return json_encode($data);
        }
        if (empty($sale)) {
            $data['status'] = 303;
            $data['error_info'] = 'No hay venta con ese id. ' . $request->id;
            return json_encode($data);
        }
        // return $sale;

        // if ($sale->code == 'PAN') {
        //     $data['status'] = 300;
        //     $data['error_info'] = 'Detendio a Petición - WEB-COL-' . $request->id;
        //     return json_encode($data);
        // }
        //validar alcancia
        //se valida que sea una venta padre
        // try {
        //     $sale_in_dues = sales_in_dues::where('sale_id', trim($sale->id))->first();
        //     $sale_dues = sales_dues::where('quota_sale_id', trim($sale->id))->first();
        // } catch (\Throwable $th) {
        //     $data['status'] = 302;
        //     $data['error_info'] = $th;
        //     return json_encode($data);
        // }

        // if (!empty($sale_in_dues) || !empty($sale_dues)) {
        //     $data['status'] = 327;
        //     $data['error_info'] = 'Esta venta pertenece a una alcancía' . $request->id;
        //     return json_encode($data);
        // }


        //Obtener Datos de Pago
        try {
            $payment = sales_payments::where('sale_id', trim($request->id))
                ->where('status', 'pagada')
                ->first();
        } catch (\Throwable $th) {
            $data['status'] = 304;
            $data['error_info'] = $th;
            return json_encode($data);
        }
        if (empty($payment)) {
            $data['status'] = 305;
            $data['error_info'] = 'No hay Pago con ese id. ' . $request->id;
            return json_encode($data);
        }
        // return $payment->detalle;
        // if ($sale->code == 'PAN') {
        //     if ($payment->payment_provider == 'Institución Bancaria') {
        //         $data['status'] = 300;
        //         $data['error_info'] = 'Detendio a Petición - WEB-' . $sale->code . '-' . $request->id;
        //         return json_encode($data);
        //     }
        // }
        //Obtener Lines
        try {
            $products = sales_products::where('sale_id', trim($request->id))->get();
        } catch (\Throwable $th) {
            $data['status'] = 306;
            $data['error_info'] = $th;
            return json_encode($data);
        }
        if (empty($products)) {
            $data['status'] = 307;
            $data['error_info'] = 'No hay Productos con ese id. ' . $request->id;
            return json_encode($data);
        }
        // return $products;

        //Obtener Dirección
        try {
            $address_logbook = Address_Logbook::select('shipping_address')->where('sale_id', trim($request->id))
                ->orderByDesc('id')->first();
        } catch (\Throwable $th) {
            $data['status'] = 306;
            $data['error_info'] = $th;
            return json_encode($data);
        }
        if (!isset($address_logbook->shipping_address)) {
            $data['status'] = 307;
            $data['error_info'] = 'No hay dirección con ese id. ' . $request->id;
            return json_encode($data);
        }
        $shipping_address = json_decode($address_logbook->shipping_address);
        // return json_decode($address_logbook->shipping_address);
        //Obtener Usuario.
        if (isset($request->user_asigned)) {
            $val_user = is_numeric($request->user_asigned);
            if ($val_user) {
                $user = User_TV::where('sap_code', trim($request->user_asigned))->first();
                if (empty($user)) {
                    $data['status'] = 308;
                    $data['error_info'] = 'No hay existe usuario asignado para la venta. - ' . $request->id;
                    return json_encode($data);
                }
            } else {
                $data['status'] = 308;
                $data['error_info'] = 'Código incorrecto para asignación. - ' . $request->user_asigned;
                return json_encode($data);
            }
        } else {
            try {
                $user = User_TV::where('id', $sale->user_id)->first();
                if (!empty($user)) {
                    if ($user->client_type == 'CLIENTE') {
                        try {
                            if (Sap_User::where('Cardcode', strval($user->sap_code_sponsor))->exists()) {
                                $user = User_TV::where('sap_code', $user->sap_code_sponsor)
                                    ->where('client_type', 'CI')
                                    ->where('status', 1)
                                    ->first();
                                if (empty($user)) {
                                    $data['status'] = 308;
                                    $data['error_info'] = 'No hay existe usuario para la venta. - ' . $request->id;
                                    return json_encode($data);
                                }
                            } else {
                                $data['status'] = 308;
                                $data['error_info'] = 'No hay existe usuario para la venta ó esta inactivo. - WEB-' . $sale->code . '-' . $request->id;
                                return json_encode($data);
                            }
                        } catch (\Throwable $th) {
                            $data['status'] = 325;
                            $data['error_info'] = $th;
                            return json_encode($data);
                        }
                    }
                } else {
                    $data['status'] = 308;
                    $data['error_info'] = 'No hay existe usuario para la venta. - WEB-' . $sale->code . '-' . $request->id;
                    return json_encode($data);
                }
            } catch (\Throwable $th) {
                //throw $th;
                $data['status'] = 309;
                $data['error_info'] = $th;
                return json_encode($data);
            }
        }
        // return $user;

        //Validar Inscripción
        try {
            $bplatam = Sap_User::where('Cardcode', strval($user->sap_code))->first();
            $bplatam_2 = BPLatam::where('CardCode', strval($user->sap_code))->first();
        } catch (\Throwable $th) {
            $data['status'] = 310;
            $data['error'] = $th;
            return json_encode($data);
        }


        //Validar Autoship 
        $autoship = $sale->alone_ref == 'autoship' ? 1 : 0;

        //Validar NikkenPoints
        $nikkenpoints = $sale->alone_ref == 'nikkenpoints' ? 1 : 0;

        //Validar Republica Dominicana
        $tvrepdom = $sale->alone_ref == 'tvrepdom' ? 1 : 0;

        // return $bplatam_2;
        if (isset($request->user_asigned)) {
            $incorporacion = 0;
        } else {
            $incorporacion = isset($bplatam->Cardcode)  || isset($bplatam_2->CardCode)  ? 0 : 1; //1 es incorporación, 0 es usuario registrado.
        }
        if (isset($request->incorporacion)) {
            $incorporacion = $request->incorporacion;
        }
        $inactivo = 0;
        if (isset($bplatam_2->CardCode)) { //Ajuste debido a que sucito un caso que en sap estaba activo pero en bplatam no por lo que se la prioridad a SAP.
            if (empty($bplatam) && $bplatam_2->FrozenFor == 'Y') {
                $inactivo = 1;
            }
        }
        if ($inactivo == 1) {
            $data['status'] = 321;
            $data['error'] = 'Inactive User';
            $data['error_info'] = 'El usuario se encuentra inactivo. WEB-' . $sale->code . '-' . $request->id;
            return json_encode($data);
        }
        //validar si es nacional o internacional
        $nacional = $user->country_id == $sale->country_id ? 1 : 0;
        $internacional = $incorporacion == 0 && $nacional == 0 ? 1 : 0;
        // $incorporacion = 1;
        // return $incorporacion;
        //Validar bono con propósito
        $items_bono = ['5023', '5024', '5027', '50234', '50244', '50274'];
        $bono = 0;
        $user_bono = '';
        $qty = 0;
        $garantía = 0;
        $validate_warranty = '';
        foreach ($products as $product) {
            $qty = $qty + $product->quantity;
            if (in_array($product->sku, $items_bono) && $incorporacion == 1) {
                //buscar si encontramos patrocinador con 5005
                try {
                    $user_bono = Contracts::where('code', $user->sap_code_sponsor)->where('kit', '5005')
                        // ->where('status', 0)
                        // ->where('payment', 0)
                        ->first();
                } catch (\Throwable $th) {
                    //throw $th;
                    $data['status'] = 320;
                    $data['error'] = $th;
                    $data['error_info'] = 'Error al localizar al patrocinador de bono con propósito';
                    return json_encode($data);
                }
                if (!empty($user_bono)) {
                    $bono = 1;
                }
            }
            if (isset($warrantys_items[$sale->code])) {
                if (in_array($product->sku, $warrantys_items[$sale->code])) {
                    $garantía = 1;
                    $validate_warranty = $this->validate_warranty($sale->id);
                    if ($validate_warranty['status'] != 200) {
                        $data['status'] = 328;
                        $data['error_info'] = 'Error al localizar las garantías';
                        return json_encode($data);
                    }
                }
            }
        }


        // if(isset($validate_warranty)) {
        //     return $validate_warranty['process']['103202']->status;
        // }


        if ($sale->code == 'CHL') {
            $ol_response = $this->get_OrderLines_chl($sale, $products, $user, $taxcodes[$sale->code], $warehouses[$sale->code], $incorporacion, $autoship, $nikkenpoints, $bono, $garantía, $validate_warranty);
        } else {
            $ol_response = $this->get_OrderLines($sale, $products, $user, $taxcodes[$sale->code], $warehouses[$sale->code], $incorporacion, $autoship, $nikkenpoints, $bono, $validate_warranty, $tvrepdom);
        }
        if ($ol_response['status'] != 200) {
            $data['status'] = 318;
            $data['error_info'] = isset($ol_response['error_info']) ? $ol_response['error_info'] : '';
            $data['error'] = isset($ol_response['error']) ? $ol_response['error'] : '';
            return json_encode($data);
        }
        if ($stopper == 1) {
            return $ol_response;
        }
        $ol = $ol_response['lines'];
        $ol_bono = $ol_response['lines_bono'];
        //OrderHeader
        if ($sale->code == 'CHL') {
            $oh_response = $this->get_OrderHeader_chl($sale, $garantía, $shipping_address->adjusted, $user, $taxcodes[$sale->code], $warehouses[$sale->code], $autoship, $nikkenpoints, $bono, $user_bono, $qty, $incorporacion);
        } else {
            $oh_response = $this->get_OrderHeader($sale, $garantía, $shipping_address->adjusted, $user, $taxcodes[$sale->code], $warehouses[$sale->code], $autoship, $nikkenpoints, $bono, $user_bono, $tvrepdom);
        }
        if ($stopper == 2) {
            return $oh_response;
        }
        if ($oh_response['status'] != 200) {
            $data['status'] = 319;
            $data['error_info'] = isset($oh_response['error_info']) ? $oh_response['error_info'] : '';
            $data['error'] = isset($oh_response['error']) ? $oh_response['error'] : '';
            return json_encode($data);
        }

        $oh = $oh_response['oh'];
        $oh_bono = $oh_response['oh_bono'];

        $op_response = $this->get_OrderPayments($sale, $payment, $autoship, $bono,  $tvrepdom);
        if ($stopper == 3) {
            return $op_response;
        }
        if ($op_response['status'] != 200) {
            $data['status'] = 314;
            $data['error_info'] = isset($op_response['error_info']) ? $op_response['error_info'] : '';
            $data['error'] = isset($op_response['error']) ? $op_response['error'] : '';
            return json_encode($data);
        }
        $op = $op_response['op'];
        $op_bono = $op_response['op_bono'];
        // return $op_response;
        $data['oh'] = $oh;
        $data['op'] = $op;
        $data['ol'] = $ol;
        $data['bono'] = $bono;
        $data['nacional'] = $nacional;
        $data['internacional'] = $internacional;
        $data['incorporacion'] = $incorporacion;
        $data['inactivo'] = $inactivo;
        $data['tvrepdom'] = $tvrepdom;
        if ($stopper == 4) {
            return $data;
        }
        // return $data;
        if ($incorporacion == 1 && $inactivo == 0) {
            //Obtener datos del CI
            try {
                $contracts = Contracts::where('code', $user->sap_code)->first();
            } catch (\Throwable $th) {
                //throw $th;
                $data['status'] = 313;
                $data['error_info'] = $th;
                return json_encode($data);
            }
            if ($sale->code == 'CHL') {
                $businesspartner_response = $this->get_businesspartner($contracts, $bono, $user_bono);
                if ($businesspartner_response['status'] != 200) {
                    $data['status'] = 322;
                    $data['error_info'] = $businesspartner_response['error_info'];
                    return json_encode($data);
                }
                $businesspartner = $businesspartner_response['businesspartner'];
                $businesspartner_bono = $businesspartner_response['businesspartner_bono'];

                $businesspartneraddress_response = $this->get_businesspartneraddress($contracts, $bono, $user_bono);
                if ($businesspartneraddress_response['status'] != 200) {
                    $data['status'] = 323;
                    $data['error_info'] = $businesspartneraddress_response['error_info'];
                    return json_encode($data);
                }
                $businesspartneraddress = $businesspartneraddress_response['businesspartneraddress'];
                $businesspartneraddress_bono = $businesspartneraddress_response['businesspartneraddress_bono'];

                $businesspartneraccinfo_response = $this->get_businesspartneraccinfo($contracts, $bono, $user_bono);
                if ($businesspartneraccinfo_response['status'] != 200) {
                    $data['status'] = 324;
                    $data['error_info'] = $businesspartneraccinfo_response['error_info'];
                    return json_encode($data);
                }
                $businesspartneraccinfo = $businesspartneraccinfo_response['businesspartneraccinfo'];
                $businesspartneraccinfo_bono = $businesspartneraccinfo_response['businesspartneraccinfo_bono'];

                $businesspartnertaxinfo_response = $this->get_businesspartnertaxinfo($contracts, $bono, $user_bono);
                if ($businesspartnertaxinfo_response['status'] != 200) {
                    $data['status'] = 324;
                    $data['error_info'] = $businesspartnertaxinfo_response['error_info'];
                    return json_encode($data);
                }
                $businesspartnertaxinfo = $businesspartnertaxinfo_response['businesspartnertaxinfo'];
                $businesspartnertaxinfo_bono = $businesspartnertaxinfo_response['businesspartnertaxinfo_bono'];
            } else {
                //CIINFO
                $ciinfo_response = $this->get_ciinfo($sale, $incorporacion, $contracts, $bono, $user_bono);
                // return $ciinfo_response;
                if ($ciinfo_response['status'] != 200) {
                    $data['status'] = 311;
                    $data['error_info'] = $ciinfo_response['error_info'];
                    return json_encode($data);
                }
                $ciinfo = $ciinfo_response['ciinfo'];
                $ciinfo_bono = $ciinfo_response['ciinfo_bono'];
                //CIINFOCOMP
                $ciinfocomp_response = $this->get_ciinfocomp($contracts, $bono, $user_bono, $tvrepdom);
                if ($ciinfocomp_response['status'] != 200) {
                    $data['status'] = 312;
                    $data['error_info'] = $ciinfocomp_response['error_info'];
                    return json_encode($data);
                }
                $ciinfocomp = $ciinfocomp_response['ciinfocomp'];
                $ciinfocomp_bono = $ciinfocomp_response['ciinfocomp_bono'];
                // return $ciinfocomp_response;
            }
        }
        if ($internacional == 1) {
            if ($sale->code == 'CHL') {
                $businesspartner_response = $this->get_businesspartner_update($bplatam_2);
                if ($businesspartner_response['status'] != 200) {
                    $data['status'] = 322;
                    $data['error_info'] = $businesspartner_response['error_info'];
                    return json_encode($data);
                }
                $businesspartner_update = $businesspartner_response['businesspartner_update'];
                // return $businesspartner_update;
                $businesspartneraddress_response = $this->get_businesspartneraddress_update($bplatam_2);
                if ($businesspartneraddress_response['status'] != 200) {
                    $data['status'] = 323;
                    $data['error_info'] = $businesspartneraddress_response['error_info'];
                    return json_encode($data);
                }
                $businesspartneraddress_update = $businesspartneraddress_response['businesspartneraddress_update'];
                // return $businesspartneraddress_update;
                $businesspartneraccinfo_response = $this->get_businesspartneraccinfo_update($bplatam_2);
                if ($businesspartneraccinfo_response['status'] != 200) {
                    $data['status'] = 324;
                    $data['error_info'] = $businesspartneraccinfo_response['error_info'];
                    return json_encode($data);
                }
                $businesspartneraccinfo_update = $businesspartneraccinfo_response['businesspartneraccinfo_update'];
                // return $businesspartneraccinfo_update;
                $businesspartnertaxinfo_response = $this->get_businesspartnertaxinfo_update($bplatam_2);
                if ($businesspartnertaxinfo_response['status'] != 200) {
                    $data['status'] = 324;
                    $data['error_info'] = $businesspartnertaxinfo_response['error_info'];
                    return json_encode($data);
                }
                $businesspartnertaxinfo_update = $businesspartnertaxinfo_response['businesspartnertaxinfo_update'];
            } else {
                $ciinfo_response = $this->get_ciinfo_update($sale, $bplatam_2);
                // return $ciinfo;
                if ($ciinfo_response['status'] != 200) {
                    $data['status'] = 311;
                    $data['error_info'] = $ciinfo_response['error_info'];
                    return json_encode($data);
                }
                $ciinfo_update = $ciinfo_response['ciinfo'];
                //CIINFOCOMP
                $ciinfocomp_response = $this->get_ciinfocomp_update($sale);
                if ($ciinfocomp_response['status'] != 200) {
                    $data['status'] = 312;
                    $data['error_info'] = $ciinfocomp_response['error_info'];
                    return json_encode($data);
                }
                $ciinfocomp_update = $ciinfocomp_response['ciinfocomp'];
            }
        }
        if ($sale->code == 'MEX' || ($tvrepdom == 1 && $incorporacion == 1)) {
            if ($tvrepdom == 1 && $incorporacion == 1) {
                $CIState['cistate'] = new stdClass();
                $CIState['cistate']->CIState = '';
                try {
                    $contracts_envio = Contracts::where('code', $user->sap_code)->first();
                } catch (\Throwable $th) {
                    //throw $th;
                    $data['status'] = 313;
                    $data['error_info'] = $th;
                    return json_encode($data);
                }
            } else if ($sale->code == 'MEX') {
                $contracts_envio = '';
                $CIState = $this->get_CIState($shipping_address->department);
                if ($CIState['status'] != 200) {
                    $data['status'] = 315;
                    $data['error_info'] = $CIState['error_info'];
                    return json_encode($data);
                }
            }
            $ciinfoenvio_response = $this->get_ciinfoenvio($user, $shipping_address, $CIState['cistate']->CIState, $bono, $user_bono, $tvrepdom, $contracts_envio);
            if ($ciinfoenvio_response['status'] != 200) {
                $data['status'] = 316;
                $data['error_info'] = $ciinfoenvio_response['error_info'];
                $data['cinfo'] = $ciinfoenvio_response;
                return json_encode($data);
            }
            $ciinfoenvio = $ciinfoenvio_response['ciinfoenvio'];
            $ciinfoenvio_bono = $ciinfoenvio_response['ciinfoenvio_bono'];
        }
        if ($bono == 1) {
            $data['oh_bono'] = isset($oh_bono) ? $oh_bono : '';
            $data['ol_bono'] = isset($ol_bono) ? $ol_bono : '';
            $data['op_bono'] = isset($op_bono) ? $op_bono : '';
            if ($sale->code == 'CHL') {
                $data['businesspartner_bono'] = isset($businesspartner) ? $businesspartner : '';
                $data['businesspartneraddress_bono'] = isset($businesspartneraddress_bono) ? $businesspartneraddress_bono : '';
                $data['businesspartneraccinfo_bono'] = isset($businesspartneraccinfo_bono) ? $businesspartneraccinfo_bono : '';
                $data['businesspartnertaxinfo_bono'] = isset($businesspartnertaxinfo_bono) ? $businesspartnertaxinfo_bono : '';
            } else {
                $data['ciinfo_bono'] = isset($ciinfo_bono) ? $ciinfo_bono : '';
                $data['ciinfocomp_bono'] = isset($ciinfocomp_bono) ? $ciinfocomp_bono : '';
                $data['ciinfoenvio_bono'] = isset($ciinfoenvio_bono) ? $ciinfoenvio_bono : '';
            }
        }
        if ($sale->code == 'CHL') {
            if ($internacional == 1) {
                $data['businesspartner_update'] = isset($businesspartner_update) ? $businesspartner_update : '';
                $data['businesspartneraddress_update'] = isset($businesspartneraddress_update) ? $businesspartneraddress_update : '';
                $data['businesspartneraccinfo_update'] = isset($businesspartneraccinfo_update) ? $businesspartneraccinfo_update : '';
                $data['businesspartnertaxinfo_update'] = isset($businesspartnertaxinfo_update) ? $businesspartnertaxinfo_update : '';
            } else {
                $data['businesspartner'] = isset($businesspartner) ? $businesspartner : '';
                $data['businesspartneraddress'] = isset($businesspartneraddress) ? $businesspartneraddress : '';
                $data['businesspartneraccinfo'] = isset($businesspartneraccinfo) ? $businesspartneraccinfo : '';
                $data['businesspartnertaxinfo'] = isset($businesspartnertaxinfo) ? $businesspartnertaxinfo : '';
            }
        } else {
            if ($internacional == 1) {
                $data['ciinfo_update'] = isset($ciinfo_update) ? $ciinfo_update : '';
                $data['ciinfocomp_update'] = isset($ciinfocomp_update) ? $ciinfocomp_update : '';
                $data['ciinfoenvio'] = isset($ciinfoenvio_response['ciinfoenvio']) ? $ciinfoenvio_response['ciinfoenvio'] : '';
            } else {
                $data['ciinfo'] = isset($ciinfo_response['ciinfo']) ? $ciinfo_response['ciinfo'] : '';
                $data['ciinfocomp'] = isset($ciinfocomp_response['ciinfocomp']) ? $ciinfocomp_response['ciinfocomp'] : '';
                $data['ciinfoenvio'] = isset($ciinfoenvio_response['ciinfoenvio']) ? $ciinfoenvio_response['ciinfoenvio'] : '';
            }
        }
        //    return $data;
        if ($stopper == 5) {
            return $data;
        }

        //Procedemos a crear la información

        try {
            DB::beginTransaction();

            // if ($tvrepdom == 1) {
            if ($sale->code == 'CHL') {
                foreach ($ol as $o) {
                    $ol_create[] = OL_CHL::create($o);
                }
                $oh_create = OH_CHL::create($oh);
                $op_create = OP_CHL::create($op);
            } else {
                foreach ($ol as $o) {
                    $ol_create[] = OL::create($o);
                }
                $oh_create = OH::create($oh);
                $op_create = OP::create($op);
            }
            if ($sale->code == 'MEX' || ($tvrepdom == 1 && $incorporacion == 1)) {
                if ($tvrepdom == 1 && $incorporacion == 1) {
                    $ciinfoenvio_db = CIINFOENVIO::create($ciinfoenvio);
                } else {
                    if (CIINFOENVIO::where('CardCode', strval($user->sap_code))->exists()) {
                        $ciinfoenvio_db = CIINFOENVIO::where('CardCode', strval($user->sap_code))->limit(1)->update($ciinfoenvio);
                    } else {
                        $ciinfoenvio_db = CIINFOENVIO::create($ciinfoenvio);
                    }
                }
            }
            if ($incorporacion == 1) {
                if ($sale->code == 'CHL') {
                    $data['businesspartner_create'] = BusinessPartner::create($businesspartner);
                    $data['businesspartnerAddress_create'] = BusinessPartnerAddress::create($businesspartneraddress);
                    $data['businesspartnerTaxInfo_create'] = BusinessPartnerTaxInfo::create($businesspartnertaxinfo);
                    $data['businesspartnerAccInfo_create'] = BusinessPartnerAccInfo::create($businesspartneraccinfo);
                    $data['control_ci_update'] = Control_CI::where('codigo', $user->sap_code)->limit(1)->update(['estatus' => 1, 'b4' => 7, 'b9' => 0]);
                } else {
                    $ciinfo_db = CIINFO::create($ciinfo);
                    $ciinfocomp_db = CIINFOCOMP::create($ciinfocomp);
                    $data['contracts_update'] = Contracts::where('code', $user->sap_code)->limit(1)->update(['status' => 1, 'payment' => $sale->id]);
                    $data['control_ci_update'] = Control_ci::where('codigo', $user->sap_code)->limit(1)->update(['estatus' => 1, 'b4' => 7, 'b9' => 0]);
                }
            }
            if ($internacional == 1) {
                if ($sale->code == 'CHL') {
                    if (BusinessPartner::where('CardCode', strval($user->sap_code))->limit(1)->exists()) {
                        $data['Businesspartner_update'] = BusinessPartner::where('CardCode', strval($user->sap_code))->limit(1)->update($businesspartner_update);
                    } else {
                        $data['Businesspartner_update'] = BusinessPartner::create($businesspartner_update);
                    }
                    if (BusinessPartnerAddress::where('CardCode', strval($user->sap_code))->limit(1)->exists()) {
                        $data['BusinessPartnerAddress_update'] = BusinessPartnerAddress::where('CardCode', strval($user->sap_code))->limit(1)->update($businesspartneraddress_update);
                    } else {
                        $data['BusinessPartnerAddress_update'] = BusinessPartnerAddress::create($businesspartneraddress_update);
                    }
                    if (BusinessPartnerTaxInfo::where('CardCode', strval($user->sap_code))->limit(1)->exists()) {
                        $data['BBusinessPartnerTaxInfo_update'] = BusinessPartnerTaxInfo::where('CardCode', strval($user->sap_code))->limit(1)->update($businesspartnertaxinfo_update);
                    } else {
                        $data['BusinessPartnerTaxInfo_update'] = BusinessPartnerTaxInfo::create($businesspartnertaxinfo_update);
                    }
                    if (BusinessPartnerAccInfo::where('CardCode', strval($user->sap_code))->limit(1)->exists()) {
                        $data['usinessPartnerAccInfo_update'] = BusinessPartnerAccInfo::where('CardCode', strval($user->sap_code))->limit(1)->update($businesspartneraccinfo_update);
                    } else {
                        $data['usinessPartnerAccInfo_update'] = BusinessPartnerAccInfo::create($businesspartneraccinfo_update);
                    }
                } else {
                    $ciinfo_db = CIINFO::where('CardCode', $user->sap_code)->limit(1)->update($ciinfo_update);
                    $ciinfocomp_db = CIINFOCOMP::where('CardCode', $user->sap_code)->limit(1)->update($ciinfocomp_update);
                }
            }
            if ($bono == 1) {
                if ($sale->code == 'CHL') {
                    $data['oh_bono_create'] = OH_CHL::create($oh_bono);
                    $data['ol_bono_create'] = OL_CHL::create($ol_bono);
                    $data['op_bono_create'] = OP_CHL::create($op_bono);
                    $data['businessparter_bono_create'] = BusinessPartner::create($businesspartner_bono);
                    $data['businessparteraddress_bono_create'] = BusinessPartnerAddress::create($businesspartneraddress_bono);
                    $data['businesspartertaxinfo_bono_create'] = BusinessPartnerTaxInfo::create($businesspartnertaxinfo_bono);
                    $data['businessparteraccinfo_bono_create'] = BusinessPartnerAccInfo::create($businesspartneraccinfo_bono);
                } else {
                    $data['oh_bono_create'] = OH::create($oh_bono);
                    $data['ol_bono_create'] = OL::create($ol_bono);
                    $data['op_bono_create'] = OP::create($op_bono);
                    $data['ciinfo_bono_create'] = CIINFO::create($ciinfo_bono);
                    $data['ciinfocomp_bono_create'] = CIINFOCOMP::create($ciinfocomp_bono);
                    if ($sale->code == 'MEX') {
                        $data['ciinfoenvio_bono_create'] = CIINFOENVIO::create($ciinfoenvio_bono);
                    }
                }
                $data['contracts_bono'] = Contracts::where('code', $user_bono->code)->limit(1)->update(['status' => 1, 'payment' => '55' . $sale->id]);
                $data['control_ci_bono'] = control_ci::where('codigo', $user_bono->code)->limit(1)->update(['estatus' => 1, 'b4' => 7, 'b9' => 0]);
            }
            // $sales_update = sales_tv::where('id', strval($sale->id))
            //     ->limit(1)
            //     ->update(['processed' => 1, 'validate' => 1]);
            // } else {
            //     if ($sale->code == 'CHL') {
            //         foreach ($ol as $o) {
            //             $ol_create[] = OL_CHL_TEST::create($o);
            //         }
            //         $oh_create = OH_CHL_TEST::create($oh);
            //         $op_create = OP_CHL_TEST::create($op);
            //     } else {
            //         foreach ($ol as $o) {
            //             $ol_create[] = OL_TEST::create($o);
            //         }
            //         $oh_create = OH_TEST::create($oh);
            //         $op_create = OP_TEST::create($op);
            //     }
            //     //Procedemos con los CIINFOS

            //     //CIINFOENVIO siempre que sea una venta de mexico se va necesitar.
            //     if ($sale->code == 'MEX') {
            //         if (CIINFOENVIO_TEST::where('CardCode', strval($user->sap_code))->exists()) {
            //             $ciinfoenvio_db = CIINFOENVIO_TEST::where('CardCode', strval($user->sap_code))->limit(1)->update($ciinfoenvio);
            //         } else {
            //             $ciinfoenvio_db = CIINFOENVIO_TEST::create($ciinfoenvio);
            //         }
            //     }
            //     if ($incorporacion == 1) {
            //         if ($sale->code == 'CHL') {
            //             $data['businesspartner_create'] = BusinessPartner_Test::create($businesspartner);
            //             $data['businesspartnerAddress_create'] = BusinessPartnerAddress_Test::create($businesspartneraddress);
            //             $data['businesspartnerTaxInfo_create'] = BusinessPartnerTaxInfo_Test::create($businesspartnertaxinfo);
            //             $data['businesspartnerAccInfo_create'] = BusinessPartnerAccInfo_Test::create($businesspartneraccinfo);
            //         } else {
            //             $ciinfo_db = CIINFO_TEST::create($ciinfo);
            //             $ciinfocomp_db = CIINFOCOMP_TEST::create($ciinfocomp);
            //             $data['contracts_update'] = Contracts_test::where('code', strval($user->sap_code))->limit(1)->update(['status' => 1, 'payment' => $sale->id]);
            //             $data['control_ci_update'] = Control_ci_test::where('codigo', strval($user->sap_code))->limit(1)->update(['estatus' => 1, 'b4' => 7]);
            //         }
            //     }
            //     if ($internacional == 1) {
            //         if ($sale->code == 'CHL') {
            //             if (BusinessPartner_Test::where('CardCode', strval($user->sap_code))->limit(1)->exists()) {
            //                 $data['Businesspartner_update'] = BusinessPartner_Test::where('CardCode', strval($user->sap_code))->limit(1)->update($businesspartner_update);
            //             } else {
            //                 $data['Businesspartner_update'] = BusinessPartner_Test::create($businesspartner_update);
            //             }
            //             if (BusinessPartnerAddress_Test::where('CardCode', strval($user->sap_code))->limit(1)->exists()) {
            //                 $data['BusinessPartnerAddress_update'] = BusinessPartnerAddress_Test::where('CardCode', strval($user->sap_code))->limit(1)->update($businesspartneraddress_update);
            //             } else {
            //                 $data['BusinessPartnerAddress_update'] = BusinessPartnerAddress_Test::create($businesspartneraddress_update);
            //             }
            //             if (BusinessPartnerTaxInfo_Test::where('CardCode', strval($user->sap_code))->limit(1)->exists()) {
            //                 $data['BusinessPartnerTaxInfo_update'] = BusinessPartnerTaxInfo_Test::where('CardCode', strval($user->sap_code))->limit(1)->update($businesspartnertaxinfo_update);
            //             } else {
            //                 $data['BusinessPartnerTaxInfo_update'] = BusinessPartnerTaxInfo_Test::create($businesspartnertaxinfo_update);
            //             }
            //             if (BusinessPartnerAccInfo_Test::where('CardCode', strval($user->sap_code))->limit(1)->exists()) {
            //                 $data['usinessPartnerAccInfo_update'] = BusinessPartnerAccInfo_Test::where('CardCode', strval($user->sap_code))->limit(1)->update($businesspartneraccinfo_update);
            //             } else {
            //                 $data['usinessPartnerAccInfo_update'] = BusinessPartnerAccInfo_Test::create($businesspartneraccinfo_update);
            //             }
            //         } else {
            //             $ciinfo_db = CIINFO_TEST::where('CardCode', strval($user->sap_code))->limit(1)->update($ciinfo_update);
            //             $ciinfocomp_db = CIINFOCOMP_TEST::where('CardCode', strval($user->sap_code))->limit(1)->update($ciinfocomp_update);
            //         }
            //     }
            //     if ($bono == 1) {
            //         if ($sale->code == 'CHL') {
            //             $data['oh_bono_create'] = OH_CHL_Test::create($oh_bono);
            //             $data['ol_bono_create'] = OL_CHL_Test::create($ol_bono);
            //             $data['op_bono_create'] = OP_CHL_Test::create($op_bono);
            //             $data['businessparter_bono_create'] = BusinessPartner_Test::create($businesspartner_bono);
            //             $data['businessparteraddress_bono_create'] = BusinessPartnerAddress_Test::create($businesspartneraddress_bono);
            //             $data['businesspartertaxinfo_bono_create'] = BusinessPartnerTaxInfo_Test::create($businesspartnertaxinfo_bono);
            //             $data['businessparteraccinfo_bono_create'] = BusinessPartnerAccInfo_Test::create($businesspartneraccinfo_bono);
            //         } else {
            //             $data['oh_bono_create'] = OH_Test::create($oh_bono);
            //             $data['ol_bono_create'] = OL_Test::create($ol_bono);
            //             $data['op_bono_create'] = OP_Test::create($op_bono);
            //             $data['ciinfo_bono_create'] = CIINFO_TEST::create($ciinfo_bono);
            //             $data['ciinfocomp_bono_create'] = CIINFOCOMP_TEST::create($ciinfocomp_bono);
            //             $data['ciinfoenvio_bono_create'] = CIINFOENVIO_TEST::create($ciinfoenvio_bono);
            //         }
            //         $data['contracts_bono'] = Contracts_test::where('code', $user_bono->code)->limit(1)->update(['status' => 1, 'payment' => '55' . $sale->id]);
            //         $data['control_ci_bono'] = control_ci_test::where('codigo', $user_bono->code)->limit(1)->update(['estatus' => 1, 'b4' => 7]);
            //     }
            //     // $sales_update = sales_tv_test::where('id', strval($sale->id))
            //     //     ->limit(1)
            //     //     ->update(['processed' => 1, 'validate' => 1]);
            // }

            DB::commit();
        } catch (\Throwable $th) {
            DB::rollback();
            $data['status'] = 317;
            $data['error'] = $th;
            return json_encode($data);
        }

        $data['oh'] = $oh_create;
        $data['ol'] = $ol_create;
        $data['op'] = $op_create;
        if ($sale->code != 'CHL') {
            $data['ciinfo_db'] = isset($ciinfo_db) ? $ciinfo_db : '';
            $data['ciinfocomp_db'] = isset($ciinfocomp_db) ? $ciinfocomp_db : '';
            $data['ciinfoenvio_db'] = isset($ciinfoenvio_db) ? $ciinfoenvio_db : '';
        } else {
            try {
                $conection = DB::connection('170');
                $response = $conection->select("SET NOCOUNT ON; EXEC NIKKENREG_STG.dbo.sp_ins_orderNumIntoMQ 'WEB-$sale->code-$sale->id';");
                DB::disconnect('170');
            } catch (\Throwable $th) {
                $data['error'] = $th;
            }
        }
        // $data['sales_update'] = isset($sales_update) ? $sales_update : '';

        return json_encode($data);
    }

    public function get_taxcodes()
    {
        $key = sprintf('get_taxcodes');
        $timeCaching = 21600; #in seconds 21600 = 6 horas        
        return cache()->remember(
            $key,
            $timeCaching,
            static function () {
                $taxcode = [];
                $countrys = ['tst', 'COL', 'MEX', 'PER', 'ECU', 'PAN', 'GTM', 'SLV', 'CRI', 'USA', 'CHL'];
                try {
                    $response =  Taxcodes::all();
                } catch (\Throwable $th) {
                    return 0;
                }
                foreach ($response as $tax) {
                    $taxcode[$countrys[$tax->idcountry]] = $tax;
                }
                return $taxcode;
            }
        );
    }

    public function get_warehouses()
    {
        $key = sprintf('getWHs');
        $timeCaching = 21600; #in seconds 21600 = 6 horas        
        return cache()->remember(
            $key,
            $timeCaching,
            static function () {
                $warehouse = [];
                $countrys = ['tst', 'COL', 'MEX', 'PER', 'ECU', 'PAN', 'GTM', 'SLV', 'CRI', 'USA', 'CHL'];
                try {
                    $response =  Warehouses::all();
                } catch (\Throwable $th) {
                    return 0;
                }
                foreach ($response as $war) {
                    $warehouse[$countrys[trim($war->idcountry)]] = $war;
                }
                return $warehouse;
            }
        );
    }

    public function get_CIState($department)
    {
        $timeCaching = 21600; #in seconds 21600 = 6 horas   
        $department2 = $this->eliminar_acentos($department);
        $key = sprintf('get_state_%s_%s', $department, $department2);
        return cache()->remember(
            $key,
            $timeCaching,
            static function () use ($department, $department2) {
                $data = [];
                $data['status'] = 300;
                try {
                    $response =  EstadosCI::select('CIState')
                        ->where('StateOrigen', trim($department))
                        ->where('Pais', 'MEX')
                        ->first();
                    if (empty($response)) {
                        $response =  EstadosCI::select('CIState')
                            ->where('StateOrigen', trim($department2))
                            ->where('Pais', 'MEX')
                            ->first();
                    }
                } catch (\Throwable $th) {
                    $data['error_info'] = $th;
                    return $data;
                }
                if (!isset($response->CIState)) {
                    $data['error_info'] = 'No existe el state para envío';
                    return $data;
                }
                $data['status'] = 200;
                $data['cistate'] = $response;
                return $data;
            }
        );
    }

    public function get_OrderHeader($sale, $garantía, $address_logbook, $user, $taxcodes, $warehouses, $autoship, $nikkenpoints, $bono, $user_bono, $tvrepdom)
    {
        $countrys = ['tst', 'COL', 'MEX', 'PER', 'ECU', 'PAN', 'GTM', 'SLV', 'CRI', 'USA', 'CHL'];
        $docdate  = new DateTime($sale->approval_date);
        $createdate = new DateTime($sale->created_at);
        $U_Precio = $user->client_type == 'CI' ? 'S' : 'C';
        $updatedate = new DateTime($sale->updated_at);
        $periodo = $updatedate->diff($createdate);
        // $date_actual = new DateTime("now", $utc_timezone);
        $utc_timezone = new DateTimeZone("America/Mexico_City");
        $date_actual = new DateTime("now", $utc_timezone);
        $U_Periodo = $periodo->m == 0 ? 'Actual' : 'Anterior';
        $salecode = $tvrepdom == 1 ? 'DOM' : $sale->code;
        $NumAtCard = $autoship == 1 ? 'WEB-AUTOSHIP-' . $salecode . '-' . $sale->id : 'WEB-' . $salecode . '-' . $sale->id;
        //Tipo de Ventas
        if ($sale->extras == '') {
            $U_Tipo_venta = 'Tienda Virtual';
        } else {
            $tipoventa = json_decode($sale->extras);
            if (isset($tipoventa->ref)) {
                switch ($tipoventa->ref) {
                    case 'un':
                        $U_Tipo_venta = "Universidad Nikken";
                        break;
                    case 'kit':
                        $U_Tipo_venta = "Inscripción On line";
                        break;
                    case '710':
                        $U_Tipo_venta = "Estrategia 7-10";
                        break;
                    case 'repuestos':
                        $U_Tipo_venta = "Micrositio de repuestos";
                        break;
                    case 'repuestoscp':
                        $U_Tipo_venta = "Micrositio de repuestos";
                        break;
                    case 'ae':
                        $U_Tipo_venta = "Arma tu entorno";
                        break;
                        //Por validar
                    case 'ae-personal':
                        $U_Tipo_venta = "Arma tu entorno";
                        break;
                    case 'nikkenpoints':
                        $U_Tipo_venta = "NikkenPoints";
                        break;
                    default:
                        $U_Tipo_venta = "Tienda Virtual";
                        break;
                }
            } else {
                $U_Tipo_venta = 'Tienda Virtual';
            }
        }
        // $iva = 0;
        // //Validar si es con o sin IV
        // foreach ($products as $p) {
        //     if ($p->tax != 0) {
        //         $iva = 1;
        //     }
        // }

        ///Ajustes REPDOM
        try {
            $U_Flete_incluido = '';
            $Insurance = '';
            $ExtraTax = '';
            $U_Bodega_Direccion = '';
            $U_Numero_Envio = '';
            $U_Comen_Envio = '';
            if ($tvrepdom == 1) {
                $U_Bodega_Direccion = $address_logbook->referencia;
                $U_Numero_Envio = $address_logbook->nombre_conjunto;
                $U_Comen_Envio = $address_logbook->numero;
                foreach ($tipoventa->rep_dom_extra_costs_details as $dato) {
                    # code...
                    if ($dato->label == 'Flete' || $dato->label == 'Flete Internacional') {
                        $U_Flete_incluido = $dato->value;
                    }
                    if ($dato->label == 'Seguro') {
                        $Insurance = $dato->value;
                    }
                    if ($dato->label == 'Impuestos internacionales') {
                        $ExtraTax = $dato->value;
                    }
                }
            }
        } catch (\Throwable $th) {
            $data['status'] = 300;
            $data['error_info'] = 'Error TVREPDOM : ' . substr($th, 0, 200) . 'json = ' . json_encode($address_logbook);
            return $data;
        }

        $oh = [];
        try {
            $U_Comen_Envio_final = '';
            if($tvrepdom == 1){
                $U_Comen_Envio_final = $U_Comen_Envio;
            }
            else if(intval($user->country_id) == 5 || intval($user->country_id) == 8){
                $U_Comen_Envio_final = $address_logbook->referencia;
            }
            else{
                $U_Comen_Envio_final = null;
            }
            $header = [
                'CardCode' => trim($user->sap_code),
                'CardCountry' => $countrys[$user->country_id],
                'Intrnal' => trim($sale->code),
                'NumAtCard' => trim($NumAtCard),
                'DocDate' => trim($docdate->format('Y-m-d')),
                'CreateDate' => trim($createdate->format('Y-m-d')),
                'DocEntry' => trim($sale->id),
                'Descuento' => trim($sale->discount),
                'Doctotal' => trim($sale->total),
                'ExtraTax' => $tvrepdom == 1 ? $ExtraTax : trim($sale->extra_perception_total),
                'U_Periodo' => trim($U_Periodo),
                'Entorno' => '',
                'U_Ccostos' => '',
                'U_Menudeo_comis' => trim($sale->retail),
                'U_Flete_incluido' => $tvrepdom == 1 ? $U_Flete_incluido : trim($sale->lading),
                'U_ItemType' => 'Venta',
                'U_Destinatario' => trim($address_logbook->nombre),
                'U_Direccion_Destino' => trim($address_logbook->direccion),
                'U_Col_Destino' => trim($address_logbook->direccion_3),
                'U_Cuidad_Envio' => trim($address_logbook->direccion_2),
                'U_Estado_Envio' => trim($address_logbook->direccion_1),
                'U_Telefono_Dest' => $tvrepdom == 1 ? trim($address_logbook->telefono_celular_con_prefijo) . ',' . trim($address_logbook->telefono_fijo_con_prefijo) : trim($address_logbook->telefono_celular),
                'U_CP_Destino' => trim($address_logbook->codigo_postal),
                'U_Bodega_Direccion' => $U_Bodega_Direccion != '' ? $U_Bodega_Direccion : null,
                'U_Tipo_Despacho' => 'Envio',
                'U_Puntos' => trim($sale->points),
                'U_vol_calc' => trim($sale->vc),
                'U_Precio' => trim($U_Precio),
                'U_Tipo_venta' => trim($U_Tipo_venta),
                'U_Tipo_Nota' => 'Default',
                'CreateOrder' => null,
                'CreateInvoice' => null,
                'CreatePayment' => null,
                'CreateBsug' => null,
                'CreditMemoUnitPrice' => 0,
                'CreditMemoItemCode' => '978a',
                'CreditMemoTaxCode' => trim($taxcodes->SalesTaxCode),
                'CreditMemoWhsCode' => $garantía == 1 ? null : trim($warehouses->SalesWhsCode),
                'U_autoship' => $autoship,
                // 'U_Numero_Envio' => $tvrepdom == 1 ? $U_Numero_Envio : null,
                'U_Numero_Envio' => trim($address_logbook->numero),
                'U_Comen_Envio' => $U_Comen_Envio_final,
                'insurance' => $tvrepdom == 1 ? $Insurance : 0,
                'Email' => $user->email,
                'fecha_en_stgin' => trim($date_actual->format('Y-m-d H:i:s')),
                'U_Telefono_Fijo' => trim($address_logbook->telefono_celular_con_prefijo),
            ];
            if ($bono == 1) {
                $header_bono = [
                    'CardCode' => trim($user_bono->code),
                    'CardCountry' => $countrys[$user_bono->country],
                    'Intrnal' => trim($sale->code),
                    'NumAtCard' => trim($NumAtCard . '_B'),
                    'DocDate' => trim($docdate->format('Y-m-d')),
                    'CreateDate' => trim($createdate->format('Y-m-d')),
                    'DocEntry' => '55' . trim($sale->id),
                    'Descuento' => 0,
                    'Doctotal' => 1,
                    'ExtraTax' => 0,
                    'U_Periodo' => trim($U_Periodo),
                    'U_Menudeo_comis' => 0,
                    'U_Flete_incluido' => 0,
                    'U_ItemType' => 'Venta',
                    'U_Destinatario' => trim($address_logbook->nombre),
                    'U_Direccion_Destino' => trim($address_logbook->direccion),
                    'U_Col_Destino' => trim($address_logbook->direccion_3),
                    'U_Cuidad_Envio' => trim($address_logbook->direccion_2),
                    'U_Estado_Envio' => trim($address_logbook->direccion_1),
                    'U_Telefono_Dest' => trim($address_logbook->telefono_celular),
                    'U_CP_Destino' => trim($address_logbook->codigo_postal),
                    'U_Tipo_Despacho' => 'Envio',
                    'U_Puntos' => 0,
                    'U_vol_calc' => 0,
                    'U_Precio' => 'S',
                    'U_Tipo_venta' => 'Inscripción On line',
                    'U_Tipo_Nota' => 'Default',
                    'CreateOrder' => null,
                    'CreateInvoice' => null,
                    'CreatePayment' => null,
                    'CreateBsug' => null,
                    'CreditMemoUnitPrice' => 0,
                    'CreditMemoItemCode' => '978a',
                    'CreditMemoTaxCode' => trim($taxcodes->SalesTaxCode),
                    'CreditMemoWhsCode' => trim($warehouses->SalesWhsCode),
                    'U_autoship' => 0,
                    'Email' => $user_bono->email,
                    'fecha_en_stgin' => trim($date_actual->format('Y-m-d H:i:s')),
                    'U_Telefono_Fijo' => trim($user_bono->cellular),
                ];
            }
        } catch (\Throwable $th) {
            $data['status'] = 300;
            $data['error_info'] = 'Error OH : ' . $th;
            return $data;
        }

        $oh['status'] = 200;
        $oh['oh'] = $header;
        $oh['oh_bono'] = isset($header_bono) ? $header_bono : '';
        return $oh;
    }

    public function get_OrderLines($sale, $products, $user, $taxcodes, $warehouses, $incorporacion, $autoship, $nikkenpoints, $bono, $validate_warranty, $tvrepdom)
    {
        $accounts_codes = ['0', '41359508', '410-000-005', '70152', '410-000-006', '410-000-001', '410-000-001', '410-000-001', '410-000-001', '9', '107-002-002'];
        $iva = 0;
        $lines = [];
        $data = [];
        $ol = [];
        $salecode = $tvrepdom == 1 ? 'DOM' : $sale->code;
        $NumAtCard = $autoship == 1 ? 'WEB-AUTOSHIP-' . $salecode . '-' . $sale->id : 'WEB-' . $salecode . '-' . $sale->id;
        $docdate  = new DateTime($sale->approval_date);

        try {
            foreach ($products as $product) {
                //Validar si es con o sin IV
                if ($product->tax != 0) {
                    $iva = 1;
                }
                $estrategia = '';
                $fecha_estrategia = '';
                $fecha_inicial_estrategia = null;
                $fecha_final_estrategia = null;
                $warranty = 'N';
                if ($product->extras != '') {
                    try {
                        $json_resp = json_decode($product->extras);
                        if (is_object($json_resp)) {
                            $estrategia = isset($json_resp->campaign->name) ? $json_resp->campaign->name : '';
                            $fecha_inicial_estrategia = isset($json_resp->campaign->start_date) ? $json_resp->campaign->start_date : null;
                            $fecha_final_estrategia = isset($json_resp->campaign->ending_date) ? $json_resp->campaign->ending_date : null;
                            if (isset($validate_warranty['process'][$product->sku])) {
                                $warranty = $validate_warranty['process'][$product->sku]->status == 'in_process' ? 'Y' : 'D';
                            }
                        }
                    } catch (\Throwable $th) {
                        $data['status'] = 300;
                        $data['error_info'] = 'Error OL : Json no válido ' . $th;
                        return $data;
                    }
                }
                if ($tvrepdom == 1) $iva = 0;
                
                ## obtiene el WholePrice para STGN
                $WholePrice = '';
                $conection = \DB::connection('mysqlTV');
                    $data = $conection->select("SELECT (wp.wholesale_price + wp.wholesale_tax) AS WholePrice, wp.wholesale_price, wp.wholesale_tax
                    FROM warehouses_products wp 
                    INNER JOIN products p ON p.id = wp.product_id
                    WHERE p.sku IN ('" . $product->sku .  "')
                    AND wp.country_id = " . $sale->country_id);
                \DB::disconnect('mysqlTV');
                if(sizeof($data) > 0){
                    $WholePrice = intval($data[0]->WholePrice) * intval($product->quantity);
                }

                $lines[] = [
                    'DocEntry' => trim($sale->id),
                    'NumAtCard' => trim($NumAtCard),
                    'ItemCode' => trim($product->sku),
                    'Quantity' => trim($product->quantity),
                    'UnitPrice' => trim($product->price),
                    'U_Descto' => trim($product->discount),
                    'PriceAfVat' => trim($product->unit_price_with_tax),
                    'TaxCode' => $iva == 1 ? trim($taxcodes->SalesTaxCode) : trim($taxcodes->SalesExeTaxCode),
                    'U_ItemType' => 'Venta',
                    'WarehouseCode' => trim($warehouses->SalesWhsCode),
                    'AccountCode' => trim($accounts_codes[$sale->country_id]),
                    'U_menudeo_comis' => trim($product->retail),
                    'U_Puntos' => trim($product->points),
                    'U_vol_calc' => trim($product->vc),
                    'U_Flete_incluido' => trim($product->lading),
                    'ExtraTax' => trim($product->extra_perception_total),
                    'Estrategia' => $nikkenpoints == 1 ? 'Nikkenpoints' : $estrategia,
                    'Garantia' => $warranty,
                    'Fecha_Estrategia' => $fecha_inicial_estrategia != '' ? $fecha_inicial_estrategia : null,
                    
                    'WholePrice' => $WholePrice,

                ];
            }
            if ($incorporacion == 1 && $user->client_type == 'CLUB') {
                $lines[] = [
                    'DocEntry' => trim($sale->id),
                    'NumAtCard' => trim($NumAtCard),
                    'ItemCode' => '5031',
                    'Quantity' => 1,
                    'UnitPrice' =>  0,
                    'U_Descto' => 0,
                    'PriceAfVat' =>  0,
                    'TaxCode' =>  $tvrepdom == 1 ? trim($taxcodes->SalesExeTaxCode) : $taxcodes->SalesTaxCode,
                    'U_ItemType' => 'Venta',
                    'WarehouseCode' => $warehouses->SalesWhsCode,
                    'CostingCode' => '',
                    'AccountCode' => $accounts_codes[$sale->country_id],
                    'U_menudeo_comis' => 0,
                    'U_Puntos' => 0,
                    'U_vol_calc' => 0,
                    'U_Flete_incluido' => $sale->code == 'MEX' ? 17.50 : 0,
                    'ExtraTax' => 0,
                    'WholePrice' => 0
                ];
            }
            if ($bono == 1) {
                $lines_bono = [
                    'DocEntry' => '55' . trim($sale->id),
                    'NumAtCard' => trim($NumAtCard) . '_B',
                    'ItemCode' => '5005',
                    'Quantity' => '1',
                    'UnitPrice' => '1',
                    'U_Descto' => '0',
                    'PriceAfVat' => '1',
                    'TaxCode' => trim($taxcodes->SalesExeTaxCode),
                    'U_ItemType' => 'Venta',
                    'WarehouseCode' => trim($warehouses->SalesWhsCode),
                    'AccountCode' => trim($accounts_codes[$sale->country_id]),
                    'U_menudeo_comis' => 0,
                    'U_Puntos' => 0,
                    'U_vol_calc' => 0,
                    'U_Flete_incluido' => 0,
                    'ExtraTax' => 0,
                    'Fecha_Estrategia' => trim($docdate->format('Y-m-d')),
                    'WholePrice' => 0
                ];
            }
        } catch (\Throwable $th) {
            $data['status'] = 300;
            $data['error_info'] = 'Error OL : ' . $th;
            return $data;
        }
        $ol['status'] = 200;
        $ol['lines'] = $lines;
        $ol['lines_bono'] = isset($lines_bono) ? $lines_bono : '';
        return $ol;
    }

    public function get_OrderPayments($sale, $payment, $autoship,  $bono, $tvrepdom)
    {
        $utc_timezone = new DateTimeZone("America/Mexico_City");
        $fecha_actual = new DateTime("now", $utc_timezone);
        $fecha = new DateTime("now", $utc_timezone);
        $año = date("Y");
        $mes = date("m");
        $fecha->setDate($año + 2, $mes, 1);
        $fecha_until = $fecha->format('Y-m-d');
        $salecode = $tvrepdom == 1 ? 'DOM' : $sale->code;
        $NumAtCard = $autoship == 1 ? 'WEB-AUTOSHIP-' . $salecode . '-' . $sale->id : 'WEB-' . $salecode . '-' . $sale->id;
        $installments = preg_replace('/[^0-9]/', '', $payment->installments);
        if ($installments == 0) $installments = 1;
        // Prueba OP
        switch ($sale->country_id) {
            case 1:
                $data_payment_col = $this->get_payment_col($payment);
                if ($data_payment_col['status'] == 200) {
                    try {
                        $op = [
                            'DocEntry' => trim($sale->id),
                            'NumAtCard' => trim($NumAtCard),
                            'PaymentType' => trim($data_payment_col['payment']->payment_type),
                            'PaymentTransferAccount' => trim($data_payment_col['payment']->payment_transfer_account),
                            'PaymentTransferSum' => trim($payment->payment_amount),
                            'PaymentCreditCardName' => $data_payment_col['payment']->payment_creditcard_name,
                            'PaymentCreditCardNo' => trim($sale->id),
                            'PaymentValidUntil' => trim($fecha_until),
                            'PaymentAmountDue' => trim($payment->payment_amount),
                            // 'PaymentVoucherNum' => trim($payment->sale_id),
                            'PaymentVoucherNum' => (trim($payment->payment_provider) == 'NIKKENYA') ? $payment->confirmation_code : trim($payment->sale_id),
                            'PaymentMethodCode' => trim($data_payment_col['payment']->payment_method_code),
                            'PaymentTransferDate' => trim($fecha_actual->format('Y-m-d')),
                            'PaymentTransferReference' => trim($payment->sale_id),
                            'PaymentMonths' => $installments
                        ];
                        if ($bono == 1) {
                            $op_bono = [
                                'DocEntry' => '55' . trim($sale->id),
                                'NumAtCard' => trim($NumAtCard) . '_B',
                                'PaymentType' => 'TP',
                                'PaymentTransferAccount' => '42505010',
                                'PaymentTransferSum' => 1,
                                'PaymentCreditCardName' => '0',
                                'PaymentCreditCardNo' => '55' . trim($sale->id),
                                'PaymentValidUntil' => trim($fecha_until),
                                'PaymentAmountDue' => 1,
                                'PaymentVoucherNum' => '55' . trim($sale->id),
                                'PaymentMethodCode' => '0',
                                'PaymentTransferDate' => trim($fecha_actual->format('Y-m-d')),
                                'PaymentTransferReference' => '55' . trim($sale->id)
                            ];
                        }
                    } catch (\Throwable $th) {
                        $data['status'] = 307;
                        $data['error'] = 'Error en construccion del payment provider';
                        $data['error_info'] = $th;
                        return $data;
                    }
                } else {
                    $data['status'] = 307;
                    $data['error'] = 'Error no existe en BD el payment method';
                    $data['error_info'] = $data_payment_col;
                    return $data;
                }
                $data['status'] = 200;
                $data['op'] = $op;
                $data['op_bono'] = isset($op_bono) ? $op_bono : '';
                return $data;
                break;
            case 2:
                $data_payment_mex = $this->get_payment_mex($payment);
                // return $data_payment_mex;
                if ($data_payment_mex['status'] == 200) {
                    try {
                        $op = [
                            'DocEntry' => trim($sale->id),
                            'NumAtCard' => trim($NumAtCard),
                            'PaymentType' => trim($data_payment_mex['payment']->payment_type),
                            'PaymentTransferAccount' => trim($data_payment_mex['payment']->payment_transfer_account),
                            'PaymentTransferSum' => trim($payment->payment_amount),
                            'PaymentCreditCardName' => trim($data_payment_mex['payment']->payment_creditcard_name),
                            'PaymentCreditCardNo' => trim('1234'),
                            'PaymentValidUntil' => trim($fecha_until),
                            'PaymentAmountDue' => trim($payment->payment_amount),
                            'PaymentVoucherNum' => trim($payment->confirmation_code),
                            'PaymentMethodCode' => trim($data_payment_mex['payment']->payment_method_code),
                            'PaymentTransferDate' => trim($fecha_actual->format('Y-m-d')),
                            'PaymentTransferReference' => trim($payment->confirmation_code),
                            'PaymentMonths' => $installments
                        ];
                        if ($bono == 1) {
                            $op_bono = [
                                'DocEntry' => '55' . trim($sale->id),
                                'NumAtCard' => trim($NumAtCard) . '_B',
                                'PaymentType' => 'TP',
                                'PaymentTransferAccount' => '610-006-017',
                                'PaymentTransferSum' => 1,
                                'PaymentCreditCardName' => '0',
                                'PaymentCreditCardNo' => '1234',
                                'PaymentValidUntil' => trim($fecha_until),
                                'PaymentAmountDue' => 1,
                                'PaymentVoucherNum' => trim($payment->confirmation_code),
                                'PaymentMethodCode' => '0',
                                'PaymentTransferDate' => trim($fecha_actual->format('Y-m-d')),
                                'PaymentTransferReference' => trim($payment->confirmation_code)
                            ];
                        }
                    } catch (\Throwable $th) {
                        $data['status'] = 307;
                        $data['error'] = 'Error en construccion del payment provider México v2';
                        $data['error_info'] = $th;
                        $data['info'] = $data_payment_mex;
                        return $data;
                    }
                } else {
                    $data['status'] = 307;
                    $data['error'] = 'Error no existe en BD el payment provider';
                    $data['error_info'] = $data_payment_mex;
                    return $data;
                }
                $data['status'] = 200;
                $data['op'] = $op;
                $data['op_bono'] = isset($op_bono) ? $op_bono : '';
                return $data;
                break;
            case 3:
                $data_payment_per = $this->get_payment_per($payment);
                if ($data_payment_per['status'] == 200) {
                    try {
                        $op = [
                            'DocEntry' => $sale->id,
                            'NumAtCard' => trim($NumAtCard),
                            'PaymentType' => $data_payment_per['payment']->payment_type,
                            'PaymentTransferAccount' => $data_payment_per['payment']->payment_transfer_account,
                            'PaymentTransferSum' => $payment->payment_amount,
                            'PaymentCreditCardName' => $data_payment_per['payment']->payment_creditcard_name,
                            'PaymentCreditCardNo' => '1234',
                            'PaymentValidUntil' => $fecha_until,
                            'PaymentAmountDue' => $payment->payment_amount,
                            'PaymentVoucherNum' => $payment->confirmation_code,
                            'PaymentMethodCode' => $data_payment_per['payment']->payment_method_code,
                            'PaymentTransferDate' => $fecha_actual->format('Y-m-d'),
                            'PaymentTransferReference' => $payment->confirmation_code,
                            'PaymentMonths' => $installments
                        ];
                        if ($bono == 1) {
                            $op_bono = [
                                'DocEntry' => '55' . trim($sale->id),
                                'NumAtCard' => trim($NumAtCard) . '_B',
                                'PaymentType' => 'TP',
                                'PaymentTransferAccount' => '7011101',
                                'PaymentTransferSum' => 1,
                                'PaymentCreditCardName' => '',
                                'PaymentCreditCardNo' => '1234',
                                'PaymentValidUntil' => trim($fecha_until),
                                'PaymentAmountDue' => 1,
                                'PaymentVoucherNum' => trim($payment->confirmation_code),
                                'PaymentMethodCode' => '0',
                                'PaymentTransferDate' => trim($fecha_actual->format('Y-m-d')),
                                'PaymentTransferReference' => trim($payment->confirmation_code)
                            ];
                        }
                    } catch (\Throwable $th) {
                        $data['status'] = 307;
                        $data['error'] = 'Error en construccion del payment provider';
                        return $data;
                    }
                } else {
                    $data['status'] = 307;
                    $data['error'] = 'Error no existe en BD el payment provider';
                    return $data;
                }
                $data['status'] = 200;
                $data['op'] = $op;
                $data['op_bono'] = isset($op_bono) ? $op_bono : '';
                return $data;
                break;
            case 4:
                $data_payment_ecu = $this->get_payment_ecu($payment);
                // return $data_payment_ecu;
                if ($data_payment_ecu['status'] == 200) {
                    try {
                        $op = [
                            'DocEntry' => $sale->id,
                            'NumAtCard' => trim($NumAtCard),
                            'PaymentType' => $data_payment_ecu['payment']->payment_type,
                            'PaymentTransferAccount' => $data_payment_ecu['payment']->payment_transfer_account,
                            'PaymentTransferSum' => $payment->payment_amount,
                            'PaymentCreditCardName' => $data_payment_ecu['payment']->payment_creditcard_name,
                            'PaymentCreditCardNo' => '1234',
                            'PaymentValidUntil' => $fecha_until,
                            'PaymentAmountDue' => $payment->payment_amount,
                            'PaymentVoucherNum' => $payment->confirmation_code,
                            'PaymentMethodCode' => $data_payment_ecu['payment']->payment_method_code,
                            'PaymentTransferDate' => $fecha_actual->format('Y-m-d'),
                            'PaymentTransferReference' => $payment->confirmation_code,
                            'PaymentMonths' => $installments
                        ];
                        if ($bono == 1) {
                            $op_bono = [
                                'DocEntry' => '55' . trim($sale->id),
                                'NumAtCard' => trim($NumAtCard) . '_B',
                                'PaymentType' => 'TP',
                                'PaymentTransferAccount' => '610-006-017',
                                'PaymentTransferSum' => 1,
                                'PaymentCreditCardName' => '0',
                                'PaymentCreditCardNo' => '1234',
                                'PaymentValidUntil' => trim($fecha_until),
                                'PaymentAmountDue' => 1,
                                'PaymentVoucherNum' => trim($payment->confirmation_code),
                                'PaymentMethodCode' => '0',
                                'PaymentTransferDate' => trim($fecha_actual->format('Y-m-d')),
                                'PaymentTransferReference' => trim($payment->confirmation_code)
                            ];
                        }
                    } catch (\Throwable $th) {
                        $data['status'] = 307;
                        $data['error'] = 'Error en construccion del payment provider';
                        return $data;
                    }
                } else {
                    $data['status'] = 307;
                    $data['error'] = 'Error no existe en BD el payment provider';
                    return $data;
                }
                $data['status'] = 200;
                $data['op'] = $op;
                $data['op_bono'] = isset($op_bono) ? $op_bono : '';
                return $data;
                break;
            case 5:
                $data_payment_pan = $this->get_payment_pan($payment);
                if ($data_payment_pan['status'] == 200) {
                    try {
                        $op = [
                            'DocEntry' => $sale->id,
                            'NumAtCard' => trim($NumAtCard),
                            'PaymentType' => $data_payment_pan['payment']->payment_type,
                            'PaymentTransferAccount' => $data_payment_pan['payment']->payment_transfer_account,
                            'PaymentTransferSum' => $payment->payment_amount,
                            'PaymentCreditCardName' => $data_payment_pan['payment']->payment_creditcard_name,
                            'PaymentCreditCardNo' => '1234',
                            'PaymentValidUntil' => $fecha_until,
                            'PaymentAmountDue' => $payment->payment_amount,
                            'PaymentVoucherNum' => $payment->confirmation_code,
                            'PaymentMethodCode' => '1',
                            'PaymentTransferDate' => $fecha_actual->format('Y-m-d'),
                            'PaymentTransferReference' => $payment->confirmation_code,
                            'PaymentMonths' => $installments
                        ];
                        if ($bono == 1) {
                            $op_bono = [
                                'DocEntry' => '55' . trim($sale->id),
                                'NumAtCard' => trim($NumAtCard) . '_B',
                                'PaymentType' => 'TP',
                                'PaymentTransferAccount' => '610-006-001',
                                'PaymentTransferSum' => 1,
                                'PaymentCreditCardName' => '0',
                                'PaymentCreditCardNo' => '1234',
                                'PaymentValidUntil' => trim($fecha_until),
                                'PaymentAmountDue' => 1,
                                'PaymentVoucherNum' => trim($payment->confirmation_code),
                                'PaymentMethodCode' => '0',
                                'PaymentTransferDate' => trim($fecha_actual->format('Y-m-d')),
                                'PaymentTransferReference' => trim($payment->confirmation_code)
                            ];
                        }
                    } catch (\Throwable $th) {
                        $data['status'] = 307;
                        $data['error'] = 'Error en construccion del payment provider';
                        return $data;
                    }
                } else {
                    $data['status'] = 307;
                    $data['error'] = 'Error no existe en BD el payment provider';
                    return $data;
                }
                $data['status'] = 200;
                $data['op'] = $op;
                $data['op_bono'] = isset($op_bono) ? $op_bono : '';
                return $data;
                break;
            case 6:
                $data_payment_gtm = $this->get_payment_gtm($payment);
                // return $data_payment_gtm;
                if ($data_payment_gtm['status'] == 200) {
                    try {
                        $op = [
                            'DocEntry' => $sale->id,
                            'NumAtCard' => trim($NumAtCard),
                            'PaymentType' => $data_payment_gtm['payment']->payment_type,
                            'PaymentTransferAccount' => $data_payment_gtm['payment']->payment_transfer_account,
                            'PaymentTransferSum' => $payment->payment_amount,
                            'PaymentCreditCardName' => $data_payment_gtm['payment']->payment_creditcard_name,
                            'PaymentCreditCardNo' => '1234',
                            'PaymentValidUntil' => $fecha_until,
                            'PaymentAmountDue' => $payment->payment_amount,
                            'PaymentVoucherNum' => $payment->confirmation_code,
                            'PaymentMethodCode' => '1',
                            'PaymentTransferDate' => $fecha_actual->format('Y-m-d'),
                            'PaymentTransferReference' => $payment->confirmation_code,
                            'PaymentMonths' => $installments
                        ];
                        if ($bono == 1) {
                            $op = [
                                'DocEntry' => '55' . trim($sale->id),
                                'NumAtCard' => trim($NumAtCard) . '_B',
                                'PaymentType' => 'TP',
                                'PaymentTransferAccount' => '610-006-001',
                                'PaymentTransferSum' => 1,
                                'PaymentCreditCardName' => '0',
                                'PaymentCreditCardNo' => '1234',
                                'PaymentValidUntil' => trim($fecha_until),
                                'PaymentAmountDue' => 1,
                                'PaymentVoucherNum' => trim($payment->confirmation_code),
                                'PaymentMethodCode' => '0',
                                'PaymentTransferDate' => trim($fecha_actual->format('Y-m-d')),
                                'PaymentTransferReference' => trim($payment->confirmation_code)
                            ];
                        }
                    } catch (\Throwable $th) {
                        $data['status'] = 307;
                        $data['error'] = 'Error en construccion del payment provider';
                        return $data;
                    }
                } else {
                    $data['status'] = 307;
                    $data['error'] = 'Error no existe en BD el payment provider';
                    return $data;
                }
                $data['status'] = 200;
                $data['op'] = $op;
                $data['op_bono'] = isset($op_bono) ? $op_bono : '';
                return $data;
                break;
            case 7:
                $data_payment_slv = $this->get_payment_slv($payment);
                // return $data_payment_slv;
                if ($data_payment_slv['status'] == 200) {
                    try {
                        $op = [
                            'DocEntry' => $sale->id,
                            'NumAtCard' => trim($NumAtCard),
                            'PaymentType' => $data_payment_slv['payment']->payment_type,
                            'PaymentTransferAccount' => $data_payment_slv['payment']->payment_transfer_account,
                            'PaymentTransferSum' => $payment->payment_amount,
                            'PaymentCreditCardName' => $data_payment_slv['payment']->payment_creditcard_name,
                            'PaymentCreditCardNo' => '1234',
                            'PaymentValidUntil' => $fecha_until,
                            'PaymentAmountDue' => $payment->payment_amount,
                            'PaymentVoucherNum' => $payment->confirmation_code,
                            'PaymentMethodCode' => '1',
                            'PaymentTransferDate' => $fecha_actual->format('Y-m-d'),
                            'PaymentTransferReference' => $payment->confirmation_code,
                            'PaymentMonths' => $installments
                        ];
                        if ($bono == 1) {
                            $op = [
                                'DocEntry' => '55' . trim($sale->id),
                                'NumAtCard' => trim($NumAtCard) . '_B',
                                'PaymentType' => 'TP',
                                'PaymentTransferAccount' => '610-006-001',
                                'PaymentTransferSum' => 1,
                                'PaymentCreditCardName' => '0',
                                'PaymentCreditCardNo' => '1234',
                                'PaymentValidUntil' => trim($fecha_until),
                                'PaymentAmountDue' => 1,
                                'PaymentVoucherNum' => trim($payment->confirmation_code),
                                'PaymentMethodCode' => '0',
                                'PaymentTransferDate' => trim($fecha_actual->format('Y-m-d')),
                                'PaymentTransferReference' => trim($payment->confirmation_code)
                            ];
                        }
                    } catch (\Throwable $th) {
                        $data['status'] = 307;
                        $data['error'] = 'Error en construccion del payment provider';
                        return $data;
                    }
                } else {
                    $data['status'] = 307;
                    $data['error'] = 'Error no existe en BD el payment provider';
                    return $data;
                }
                $data['status'] = 200;
                $data['op'] = $op;
                $data['op_bono'] = isset($op_bono) ? $op_bono : '';
                return $data;
                break;
            case 8:
                $data_payment_cri = $this->get_payment_cri($payment);
                // return $data_payment_slv;
                if ($data_payment_cri['status'] == 200) {
                    try {
                        $op = [
                            'DocEntry' => $sale->id,
                            'NumAtCard' => trim($NumAtCard),
                            'PaymentType' => $data_payment_cri['payment']->payment_type,
                            'PaymentTransferAccount' => $data_payment_cri['payment']->payment_transfer_account,
                            'PaymentTransferSum' => $payment->payment_amount,
                            'PaymentCreditCardName' => $data_payment_cri['payment']->payment_creditcard_name,
                            'PaymentCreditCardNo' => '1234',
                            'PaymentValidUntil' => $fecha_until,
                            'PaymentAmountDue' => $payment->payment_amount,
                            'PaymentVoucherNum' => substr($payment->confirmation_code, 0, 11),
                            'PaymentMethodCode' => '1',
                            'PaymentTransferDate' => $fecha_actual->format('Y-m-d'),
                            'PaymentTransferReference' => substr($payment->confirmation_code, 0, 11),
                            'PaymentMonths' => $installments
                        ];
                        if ($bono == 1) {
                            $op_bono = [
                                'DocEntry' => '55' . trim($sale->id),
                                'NumAtCard' => trim($NumAtCard) . '_B',
                                'PaymentType' => 'TP',
                                'PaymentTransferAccount' => '610-006-013',
                                'PaymentTransferSum' => 1,
                                'PaymentCreditCardName' => '0',
                                'PaymentCreditCardNo' => '1234',
                                'PaymentValidUntil' => trim($fecha_until),
                                'PaymentAmountDue' => 1,
                                'PaymentVoucherNum' => trim($payment->confirmation_code),
                                'PaymentMethodCode' => '0',
                                'PaymentTransferDate' => trim($fecha_actual->format('Y-m-d')),
                                'PaymentTransferReference' => trim($payment->confirmation_code)
                            ];
                        }
                    } catch (\Throwable $th) {
                        $data['status'] = 307;
                        $data['error'] = 'Error en construccion del payment provider';
                        return $data;
                    }
                } else {
                    $data['status'] = 307;
                    $data['error'] = 'Error no existe en BD el payment provider';
                    return $data;
                }
                $data['status'] = 200;
                $data['op'] = $op;
                $data['op_bono'] = isset($op_bono) ? $op_bono : '';
                return $data;
                break;
            case 10:
                $data_payment_chl = $this->get_payment_chl($payment);
                // return $data_payment_slv;
                if ($data_payment_chl['status'] == 200) {
                    try {
                        $op = [
                            'DocEntry' => $sale->id,
                            'NumAtCard' => trim($NumAtCard),
                            'PaymentType' => $data_payment_chl['payment']->payment_type,
                            'PaymentTransferAccount' => $data_payment_chl['payment']->payment_transfer_account,
                            'PaymentTransferSum' => $payment->payment_amount,
                            'PaymentTransferDate' => $fecha_actual->format('Y-m-d H:i:s'),
                            'PaymentTransferReference' => $payment->confirmation_code,
                            'PaymentCreditCardName' => $data_payment_chl['payment']->payment_creditcard_name,
                            'PaymentCreditCardNo' => '1234',
                            'PaymentAmountDue' => $payment->payment_amount,
                            'PaymentVoucherNum' => $payment->confirmation_code,
                            'PaymentMethodCode' => '1',
                            'PaymentValidUntil' => $fecha_until,
                            'PaymentMonths' => $installments
                        ];

                        if ($bono == 1) {
                            $op_bono = [
                                'DocEntry' => '55' . trim($sale->id),
                                'NumAtCard' => trim($NumAtCard) . '_B',
                                'PaymentType' => 'TP',
                                'PaymentTransferAccount' => '601-006-012',
                                'PaymentTransferSum' => 1,
                                'PaymentTransferDate' => $fecha_actual->format('Y-m-d H:i:s'),
                                'PaymentTransferReference' => $payment->confirmation_code,
                                'PaymentCreditCardName' => '0',
                                'PaymentCreditCardNo' => '1234',
                                'PaymentAmountDue' => 1,
                                'PaymentVoucherNum' => $payment->confirmation_code,
                                'PaymentMethodCode' => '0',
                                'PaymentValidUntil' => $fecha_until,
                            ];
                        }
                    } catch (\Throwable $th) {
                        $data['status'] = 307;
                        $data['error'] = 'Error en construccion del payment provider';
                        return $data;
                    }
                } else {
                    $data['status'] = 307;
                    $data['error'] = 'Error no existe en BD el payment provider';
                    return $data;
                }
                $data['status'] = 200;
                $data['op'] = $op;
                $data['op_bono'] = isset($op_bono) ? $op_bono : '';
                return $data;
                break;

            default:
                # code...
                break;
        }
    }

    public function get_payment_mex($payment)
    {
        $payment_account = [];
        $account_code = [];
        $payments_methods_filter = ['MercadoPago - consumer_credits'];
        try {
            if (in_array($payment->payment_method, $payments_methods_filter)) {
                $info_payments = PaymentAccounts::where('country', 2)
                    ->where('payment_provider', $payment->payment_provider)
                    ->where('payment_method', $payment->payment_method)
                    ->first();
            } else {
                $info_payments = PaymentAccounts::where('country', 2)->where('payment_provider', $payment->payment_provider)->first();
            }
        } catch (\Throwable $th) {
            $data['status'] = 310;
            $data['error'] = $th;
            return $data;
        }
        if (!isset($info_payments->payment_provider)) {
            $data['status'] = 310;
            return $data;
        }
        // return $info_payments;

        $payment_account = new stdClass();
        $payment_account->payment_provider = $info_payments->payment_provider;
        $payment_account->payment_method_code = $info_payments->payment_method_code;
        $payment_account->payment_creditcard_name = $info_payments->payment_creditcard_name;
        $payment_account->payment_transfer_account = $info_payments->payment_transfer_account;
        $payment_account->country = $info_payments->country;
        $payment_account->payment_type = $info_payments->payment_type;
        $payment_account->installments = $info_payments->installments;
        $account_code['payment'] = $payment_account;

        if (empty($account_code)) {
            $data['status'] = 310;
            return $data;
        }
        $account_code['status'] = 200;
        return $account_code;
    }

    public function get_payment_col($payment)
    {
        $payment_account = [];
        $account_code = [];
        try {
            $info_payments = PaymentAccounts::where('country', 1)
                ->where('payment_method', trim($payment->payment_method))->first();
        } catch (\Throwable $th) {
            $payment_account['status'] = 310;
            $payment_account['error_info'] = $th;
            return $payment_account;
        }
        if (empty($info_payments)) {
            $account_code['status'] = 310;
            $account_code['error_info'] = 'No existe el metodo de pago - "' . $payment->payment_method . '"';
            return $account_code;
        }
        $account_code['status'] = 200;
        $payment_account = new stdClass();
        $payment_account->payment_provider = trim($info_payments->payment_provider);
        $payment_account->payment_method_code = trim($info_payments->payment_method_code);
        $payment_account->payment_method = trim($info_payments->payment_method);
        $payment_account->payment_creditcard_name = trim($info_payments->payment_creditcard_name);
        $payment_account->payment_transfer_account = trim($info_payments->payment_transfer_account);
        $payment_account->country = trim($info_payments->country);
        $payment_account->payment_type = trim($info_payments->payment_type);
        $payment_account->installments = trim($info_payments->installments);
        $account_code['payment'] = $payment_account;


        return $account_code;
    }

    public function get_payment_per($payment)
    {
        $payment_account = [];
        $account_code = [];
        $payment_method = $payment->payment_method;
        //Atencion en esta linea ya que comprobamos que el metodo de pago sea 
        //diferente en mercado pago. ya que el metodo puede ser indefinido
        //pero tenemos que considerarlo siempre y cuando sea MercadoPago el proveedor
        if ($payment->payment_provider == 'Mercado Pago') {
            $mercadopago = ['MercadoPago - visa', 'MercadoPago - diners'];
            if (!array_search($payment->payment_method, $mercadopago)) {
                $payment_method = 'MercadoPago - other';
            }
        }
        $banco_search = 0;
        if ($payment->payment_provider == 'Institución Bancaria') {
            $banco = 'INTERBANK';
            if (strpos($payment->detalle, $banco) !== false) {
                $banco_search = 1;
            }
        }
        if ($banco_search == 1) {
            $payment_method = 'INTERBANK';
        }
        try {
            $info_payment = PaymentAccounts::where('country', 3)
                ->where('payment_provider', $payment->payment_provider)
                ->where('payment_method', $payment_method)
                ->first();
        } catch (\Throwable $th) {
            $payment_account['status'] = 310;
            return $payment_account;
        }
        if (empty($info_payment)) {
            $account_code['status'] = 310;
            return $account_code;
        }
        $account_code['status'] = 200;

        $payment_account = new stdClass();
        $payment_account->payment_provider = $info_payment->payment_provider;
        $payment_account->payment_method_code = $info_payment->payment_method_code;
        $payment_account->payment_method = $info_payment->payment_method;
        $payment_account->payment_creditcard_name = $info_payment->payment_creditcard_name;
        $payment_account->payment_transfer_account = $info_payment->payment_transfer_account;
        $payment_account->country = $info_payment->country;
        $payment_account->payment_type = $info_payment->payment_type;
        $payment_account->installments = $info_payment->installments;
        $account_code['payment'] = $payment_account;


        return $account_code;
    }

    public function get_payment_ecu($payment)
    {
        $payment_account = [];
        $account_code = [];
        $bancos = ['PRODUBANCO', 'BANCO GUAYAQUIL', 'BANCO DEL PACÍFICO', 'BANCO DEL PICHINCHA', 'BANCO PICHINCHA'];
        $banco_search = '';
        if ($payment->payment_provider == 'Institución Bancaria') {
            foreach ($bancos as $banco) {
                if (strpos($payment->detalle, $banco) !== false) {
                    $banco_search = $banco;
                    break;
                }
            }
        }
        try {
            if ($banco_search == '') {
                if ($payment->payment_provider == 'Paymentez nuvei') {
                    $info_payments = PaymentAccounts::where('country', 4)
                        ->where('payment_provider', trim($payment->payment_provider))
                        ->first();
                } else {
                    $info_payments = PaymentAccounts::where('country', 4)
                        ->where('payment_provider', $payment->payment_provider)
                        ->where('payment_method', $payment->payment_method)
                        ->where('installments', $payment->installments)
                        ->first();
                }
            } else {
                $info_payments = PaymentAccounts::where('country', 4)
                    ->where('payment_method', $banco_search)
                    ->where('payment_provider', $payment->payment_provider)
                    ->first();
            }
        } catch (\Throwable $th) {
            $data['status'] = 310;
            $data['error'] = $th;
            return $data;
        }
        if (empty($info_payments)) {
            $data['status'] = 310;
            return $data;
        }
        $account_code['status'] = 200;

        $payment_account = new stdClass();
        $payment_account->payment_provider = $info_payments->payment_provider;
        $payment_account->payment_method_code = $info_payments->payment_method_code;
        $payment_account->payment_creditcard_name = $info_payments->payment_creditcard_name;
        $payment_account->payment_transfer_account = $info_payments->payment_transfer_account;
        $payment_account->country = $info_payments->country;
        $payment_account->payment_type = $info_payments->payment_type;
        $payment_account->installments = $info_payments->installments;
        $account_code['payment'] = $payment_account;

        return $account_code;
    }

    public function get_payment_pan($payment)
    {
        $payment_account = [];
        $account_code = [];
        try {
            $info_payments = PaymentAccounts::where('country', 5)->where('payment_method', $payment->payment_method)->first();
        } catch (\Throwable $th) {
            $payment_account['status'] = 310;
            $payment_account['error_info'] = $th;
            return $payment_account;
        }
        if (empty($info_payments)) {
            $account_code['status'] = 310;
            $account_code['error_info'] = 'no existe registro en la base de cuentas latam.';
            return $account_code;
        }
        $account_code['status'] = 200;

        $payment_account = new stdClass();
        $payment_account->payment_provider = $info_payments->payment_provider;
        $payment_account->payment_method_code = $info_payments->payment_method_code;
        $payment_account->payment_method = $info_payments->payment_method;
        $payment_account->payment_creditcard_name = $info_payments->payment_creditcard_name;
        $payment_account->payment_transfer_account = $info_payments->payment_transfer_account;
        $payment_account->country = $info_payments->country;
        $payment_account->payment_type = $info_payments->payment_type;
        $payment_account->installments = $info_payments->installments;
        $account_code['payment'] = $payment_account;

        return $account_code;
    }

    public function get_payment_gtm($payment)
    {
        $payment_account = [];
        $account_code = [];
        $payment_method = 'Pago con Boleta';
        if ($payment->payment_method != 'Pago con Boleta' && $payment->payment_method != 'NikkenPoints') $payment_method = 'Others';
        try {
            $info_payments = PaymentAccounts::where('country', 6)->where('payment_method', $payment_method)->first();
        } catch (\Throwable $th) {
            $data['error'] = $th;
            $data['status'] = 310;
            return $data;
        }
        if (!isset($info_payments->payment_method)) {
            $data['status'] = 311;
            return $data;
        }
        // return $info_payments;



        $payment_account = new stdClass();
        $payment_account->payment_provider = $info_payments->payment_provider;
        $payment_account->payment_method_code = $info_payments->payment_method_code;
        $payment_account->payment_creditcard_name = $info_payments->payment_creditcard_name;
        $payment_account->payment_transfer_account = $info_payments->payment_transfer_account;
        $payment_account->country = $info_payments->country;
        $payment_account->payment_type = $info_payments->payment_type;
        $payment_account->installments = $info_payments->installments;
        $account_code['payment'] = $payment_account;

        if (empty($account_code)) {
            $data['status'] = 310;
            return $data;
        }
        $account_code['status'] = 200;

        return $account_code;
    }

    public function get_payment_slv($payment)
    {
        $payment_account = [];
        $account_code = [];
        try {
            $info_payments = PaymentAccounts::where('country', 7)->where('payment_provider', $payment->payment_provider)->first();
        } catch (\Throwable $th) {
            $data['error'] = $th;
            $data['status'] = 310;
            return $data;
        }
        if (!isset($info_payments->payment_method)) {
            $data['status'] = 311;
            return $data;
        }
        // return $info_payments;
        $payment_account = new stdClass();
        $payment_account->payment_provider = $info_payments->payment_provider;
        $payment_account->payment_method_code = $info_payments->payment_method_code;
        $payment_account->payment_creditcard_name = $info_payments->payment_creditcard_name;
        $payment_account->payment_transfer_account = $info_payments->payment_transfer_account;
        $payment_account->country = $info_payments->country;
        $payment_account->payment_type = $info_payments->payment_type;
        $payment_account->installments = $info_payments->installments;
        $account_code['payment'] = $payment_account;

        if (empty($account_code)) {
            $data['status'] = 310;
            return $data;
        }
        $account_code['status'] = 200;

        return $account_code;
    }

    public function get_payment_cri($payment)
    {
        $payment_account = [];
        $account_code = [];
        try {
            if ($payment->provider == 'NikkenPoints') {
                $info_payments = PaymentAccounts::where('country', 8)
                    ->where('payment_provider', 'NikkenPoints')
                    ->first();
            } else {
                $info_payments = PaymentAccounts::where('country', 8)
                    ->where('payment_provider', $payment->payment_provider)
                    ->where('installments', strval($payment->installments))
                    ->first();
            }
        } catch (\Throwable $th) {
            $data['error'] = $th;
            $data['status'] = 310;
            return $data;
        }
        if (!isset($info_payments->payment_method)) {
            $data['status'] = 311;
            $data['error'] = 'Sin información en payments';
            return $data;
        }
        // return $info_payments;
        $payment_account = new stdClass();
        $payment_account->payment_provider = $info_payments->payment_provider;
        $payment_account->payment_method_code = $info_payments->payment_method_code;
        $payment_account->payment_creditcard_name = $info_payments->payment_creditcard_name;
        $payment_account->payment_transfer_account = $info_payments->payment_transfer_account;
        $payment_account->country = $info_payments->country;
        $payment_account->payment_type = $info_payments->payment_type;
        $payment_account->installments = $info_payments->installments;
        $account_code['payment'] = $payment_account;

        if (empty($account_code)) {
            $data['status'] = 310;
            return $data;
        }
        $account_code['status'] = 200;
        return $account_code;
    }

    public function get_payment_chl($payment)
    {
        $payment_account = [];
        $account_code = [];
        try {
            $info_payments = PaymentAccounts::where('country', 10)->where('payment_method', $payment->payment_method)->first();
        } catch (\Throwable $th) {
            $payment_account['status'] = 310;
            return $payment_account;
        }
        if (empty($info_payments)) {
            $account_code['status'] = 310;
            $account_code['error_info'] = 'No existe el método de pago.';
            return $account_code;
        }
        $account_code['status'] = 200;
        $payment_account = new stdClass();
        $payment_account->payment_provider = trim($info_payments->payment_provider);
        $payment_account->payment_method_code = trim($info_payments->payment_method_code);
        $payment_account->payment_method = trim($info_payments->payment_method);
        $payment_account->payment_creditcard_name = trim($info_payments->payment_creditcard_name);
        $payment_account->payment_transfer_account = trim($info_payments->payment_transfer_account);
        $payment_account->country = trim($info_payments->country);
        $payment_account->payment_type = trim($info_payments->payment_type);
        $payment_account->installments = trim($info_payments->installments);
        $account_code['payment'] = $payment_account;
        return $account_code;
    }

    public function get_ciinfo($sale, $incorporacion, $contracts, $bono, $user_bono)
    {
        $data['status'] = 300;
        $countrys = ['tst', 'COL', 'MEX', 'PER', 'ECU', 'PAN', 'GTM', 'SLV', 'CRI', 'USA', 'CHL'];
        if (!isset($contracts->code)) {
            $data['error_info'] = 'No existe registro en contracts del CI';
            return $data;
        }

        if ($contracts->type_incorporate == 1) {
            if ($contracts->country != 2) {
                $name_explode = explode(",", $contracts->name);
                $name = isset($name_explode[1]) ? trim($name_explode[1]) : $contracts->name;
                $last_name = isset($name_explode[0]) ? trim($name_explode[0]) : '';
            } else {
                $name = trim($contracts->name);
            }
        } else {
            $name = trim($contracts->name);
        }
        $birthday = new DateTime($contracts->birthday);
        try {
            $ciinfo = [
                'NumContract' => $contracts->id_contract,
                'CardCode' => $contracts->code,
                'Cardtype' => $contracts->type == 1 ? 'ab' : 'cb',
                'CreateDate' => $contracts->create_at,
                'CompleteDate' => '',
                'ABFirstName' => $name,
                'ABLastName' => isset($last_name) ? trim($last_name) : '',
                'CIPais' => $countrys[$contracts->country],
                'Pais_Intrnal' => $sale->code,
                'SponsorId' => $contracts->sponsor,
                'BirthDay' => trim($birthday->format('Y-m-d')),
                'Phone1' => trim($contracts->cellular),
                'Email' => $contracts->email,
                'SourceApp' => 'Tienda Virtual',
                'Captured' => 'N',
                'Updated' => 'N',
                'Cancelled' => 'N',
                'FederalTaxID' => $contracts->country == 2 ? $contracts->rfc : $contracts->number_document,
                'SponsorName' => '',
                'CreateSAP' => null,
                'CreateVista' => null,
                'CreateMN' => null,
                'CmpPrivate' => $contracts->type_incorporate == 0 ? 'C' : 'I',
                'OrigenCI' => $contracts->country == 8 ? 1 : null,
                // 'OrigenCI' => null
            ];
            if ($bono == 1) {
                if ($user_bono->type_incorporate == 1) {
                    if ($user_bono->country != 2) {
                        $name_explode_bono = explode(",", $user_bono->name);
                        $name_bono = isset($name_explode_bono[1]) ? trim($name_explode_bono[1]) : $user_bono->name;
                        $last_name_bono = isset($name_explode_bono[0]) ? trim($name_explode_bono[0]) : '';
                    } else {
                        $name_bono = trim($user_bono->name);
                    }
                } else {
                    $name_bono = trim($user_bono->name);
                }
                $ciinfo_bono = [
                    'NumContract' => $user_bono->id_contract,
                    'CardCode' => $user_bono->code,
                    'Cardtype' => 'ab',
                    'CreateDate' => $user_bono->create_at,
                    'CompleteDate' => '',
                    'ABFirstName' => $name_bono,
                    'ABLastName' => isset($last_name_bono) ? trim($last_name_bono) : '',
                    'CIPais' => $countrys[$user_bono->country],
                    'Pais_Intrnal' => $sale->code,
                    'SponsorId' => $user_bono->sponsor,
                    'BirthDay' => trim($birthday->format('Y-m-d')),
                    'Phone1' => trim($user_bono->cellular),
                    'Email' => $user_bono->email,
                    'SourceApp' => 'Tienda Virtual',
                    'Captured' => 'N',
                    'Updated' => 'N',
                    'Cancelled' => 'N',
                    'FederalTaxID' => $user_bono->country == 2 ? $user_bono->rfc : $user_bono->number_document,
                    'SponsorName' => '',
                    'CreateSAP' => null,
                    'CreateVista' => null,
                    'CreateMN' => null,
                    'CmpPrivate' => $user_bono->type_incorporate == 0 ? 'C' : 'I',
                    'OrigenCI' => $contracts->country == 8 ? 1 : null,
                    // 'OrigenCI' => null
                ];
            }
        } catch (\Throwable $th) {
            $data['status'] = 300;
            $data['error_info'] = 'Error CIINFO - ' . $th;
            return $data;
        }


        $data['status'] = 200;
        $data['ciinfo'] = $ciinfo;
        $data['ciinfo_bono'] = isset($ciinfo_bono) ? $ciinfo_bono : '';
        return $data;
    }

    public function get_ciinfocomp($contracts, $bono, $user_bono, $tvrepdom)
    {
        try {
            $data['status'] = 300;
            $countrys = ['tst', 'COL', 'MEX', 'PER', 'ECU', 'PAN', 'GTM', 'SLV', 'CRI', 'USA', 'CHL'];
            //para agregar datos x pais en dni_route
            $dni_route = '';
            $regimen = '';
            $dni_type = '';
            $dni_number = '';
            $number_account = '';
            $identificacion = '';
            $type_ident = ['1' => 'Cédula de Ciudadania', '2' => 'Cédula de Extranjería', '12' => 'Régimen Común', '13' => 'Régimen Simplificado'];
            switch (intval($contracts->country)) {
                case 1:
                    $regimen = $contracts->type_incorporate == 1 ? 'RS' : 'RC';
                    $dni_number = $contracts->number_document;
                    $number_account = $contracts->number_account;
                    $identificacion = isset($type_ident[$contracts->number_document]) ? $type_ident($contracts->number_document) : '';
                    break;
                case 2:
                    $dni_route = $contracts->regimen;
                    $regimen = $contracts->type_incorporate == 0 ? 'TPM' : 'TPF';
                    break;
                case 3:
                    $regimen = $contracts->type_incorporate == 1 ? 'TPN' : 'TPJ';
                    if ($contracts->bank_code == 46) {
                        $number_account = trim($contracts->number_account);
                    } else {
                        $number_account = trim($contracts->number_clabe);
                    }
                    break;
                case 4:
                    $dni_number = $contracts->number_document;
                    $regimen = $contracts->type_incorporate == 1 ? 'PN' : 'PJ';
                    $number_account = trim($contracts->number_account);
                    break;
                case 5:
                    $dni_route = $contracts->verify_digit;
                    $dni_type = $contracts->dgi;
                    $regimen = $contracts->type_incorporate == 1 ? 'SIN RUC' : 'RUC';
                    $dni_number = $contracts->number_document;
                    $number_account = $contracts->number_account;
                    break;
                case 6:
                    $dni_number = $contracts->number_document;
                    $regimen = $contracts->type_incorporate == 1 ? 'PC' : 'PT';
                    $number_account = trim($contracts->number_account);
                    break;
                case 7:
                    $regimen = $contracts->type_incorporate == 1 ? 'NIV' : 'RUC';
                    $dni_exp = explode(',', $contracts->number_document);
                    $dni_number = isset($dni_exp[0]) ? $dni_exp[0] : $contracts->number_document;
                    $number_account = trim($contracts->number_account);
                    break;
                case 8:
                    $dni_number = $contracts->number_document;
                    $regimen = $contracts->type_incorporate == 1 ? 'RT' : 'CJ';
                    $number_account = trim($contracts->number_account);
                    $identificacion = intval($contracts->type_document);
                    switch($identificacion){
                        case 10: 
                            $identificacion = "01";
                            break;
                        case 40: 
                            $identificacion = "02";
                            break;
                        case 21: 
                            $identificacion = "03";
                            break;
                        default:
                            $identificacion = "01";
                            break;
                    }
                    break;
                default:
                    $dni_route = '';
                    $regimen = '';
                    $dni_type = '';
                    $dni_number = '';
                    $number_account = '';
                    break;
            }
        } catch (\Throwable $th) {
            //throw $th;
            $data['error_info'] = $th;
            return $data;
        }

        $AccountTypePan = [
            "Ahorros" => 32,
            "Corriente" => 22,
            "" => '',
        ];

        try {
            $ciinfocomp = [];
            if ($tvrepdom == 1) {
                $ciinfocomp = [
                    'CardCode' => trim($contracts->code),
                    'DNIType' => $dni_type,
                    'DNINumber' => $dni_number,
                    'DNIRoute' => $dni_route == null ? '' : $dni_route,
                    'Regimen' => $regimen,
                    'CIAddress' => $contracts->address,
                    'CICity' =>  $contracts->residency_four,
                    'CIMunicipio' => $contracts->residency_three,
                    'CIState' =>  '',
                    'CICounty' => 'DOM',
                    'CIZipCode' => $contracts->residency_one,
                    'Phone2' => $contracts->cellular,
                    'BankCode' => trim($contracts->bank_code),
                    'AccountType' => trim($contracts->type_account),
                    'AccountNumber' => $number_account,
                    'InterAccNumber' => '',
                    'Identificacion' => '02',
                    'Req_Factura' => $contracts->cfdi,
                    'CotitName' => $contracts->name_cotitular,
                    'CotitDNIType' => $contracts->type_document_cotitular == 0 ? '' : $contracts->type_document_cotitular,
                    'CotitDNINumber' => $contracts->number_document_cotitular,
                    'CotitDNIRoute' => '',
                    'CotitRegimen' => '',
                    'Genero' => $contracts->gender,
                ];
            } else {
                $ciinfocomp = [
                    'CardCode' => trim($contracts->code),
                    'DNIType' => $dni_type,
                    'DNINumber' => $dni_number,
                    'DNIRoute' => $dni_route == null ? '' : $dni_route,
                    'Regimen' => $regimen,
                    'CIAddress' => $contracts->address_invoice != '' ? $contracts->address_invoice : $contracts->address,
                    'CICity' =>  $contracts->residency_four_invoice != '' ? $contracts->residency_four_invoice : $contracts->residency_four,
                    'CIMunicipio' => $contracts->residency_three_invoice != '' ? $contracts->residency_three_invoice : $contracts->residency_three,
                    'CIState' =>  $contracts->residency_two_invoice != '' ? $contracts->residency_two_invoice : $contracts->residency_two,
                    'CICounty' => $countrys[$contracts->country],
                    'CIZipCode' =>  $contracts->residency_one_invoice != '' ? $contracts->residency_one_invoice : $contracts->residency_one,
                    'Phone2' => $contracts->cellular,
                    'BankCode' => trim($contracts->bank_code),
                    'AccountType' => (intval($contracts->country) === 5) ? $AccountTypePan[trim($contracts->type_account)] : trim($contracts->type_account),
                    'AccountNumber' => $number_account,
                    'InterAccNumber' => '',
                    // 'Identificacion' => $contracts->country == 8 ? '01' : $identificacion,
                    'Identificacion' => $identificacion,
                    'Req_Factura' => $contracts->cfdi,
                    'CotitName' => $contracts->name_cotitular,
                    'CotitDNIType' => $contracts->type_document_cotitular == 0 ? '' : $contracts->type_document_cotitular,
                    'CotitDNINumber' => $contracts->number_document_cotitular,
                    'CotitDNIRoute' => '',
                    'CotitRegimen' => '',
                    'Genero' => $contracts->gender,
                ];
            }
            if ($bono == 1) {
                switch (intval($user_bono->country)) {
                    case 1:
                        $dni_number = $user_bono->number_document;
                        $number_account = $user_bono->number_account;
                        break;
                    case 2:
                        $dni_route = $user_bono->regimen;
                        $regimen = $user_bono->type_incorporate == 0 ? 'TPM' : 'TPF';
                        break;
                    case 3:
                        $regimen = $contracts->type_incorporate == 1 ? 'TPN' : 'TPJ';
                        if ($user_bono->bank_code == 46) {
                            $number_account = trim($user_bono->number_account);
                        } else {
                            $number_account = trim($user_bono->number_clabe);
                        }
                        break;
                    case 4:
                        $number_account = trim($user_bono->number_account);
                        break;
                    case 5:
                        $dni_route = $user_bono->verify_digit;
                        $dni_type = $user_bono->dgi;
                        $regimen = $user_bono->type_incorporate == 0 ? 'RUC' : 'SIN RUC';
                        $dni_number = $user_bono->number_document;
                        $number_account = $user_bono->number_account;
                        break;
                    case 6:
                        $number_account = trim($user_bono->number_account);
                        break;
                    case 7:
                        $regimen = 'NIV';
                        $dni_exp = explode(',', $contracts->number_document);
                        $dni_number = trim($dni_exp[0]);
                        $number_account = trim($user_bono->number_account);
                        break;
                    case 8:
                        $number_account = trim($user_bono->number_account);
                        break;
                    default:
                        $dni_route = '';
                        $regimen = '';
                        $dni_type = '';
                        $dni_number = '';
                        $number_account = '';
                        break;
                }
                $ciinfocomp_bono = [
                    'CardCode' => trim($user_bono->code),
                    'DNIType' => $dni_type,
                    'DNINumber' => $dni_number,
                    'DNIRoute' => $dni_route == null ? '' : $dni_route,
                    'Regimen' => $regimen,
                    'CIAddress' => $user_bono->address_invoice != '' ? $user_bono->address_invoice : $user_bono->address,
                    'CICity' =>  $user_bono->residency_four_invoice != '' ? $user_bono->residency_four_invoice : $user_bono->residency_four,
                    'CIMunicipio' => $user_bono->residency_three_invoice != '' ? $user_bono->residency_three_invoice : $user_bono->residency_three,
                    'CIState' =>  $user_bono->residency_two_invoice != '' ? $user_bono->residency_two_invoice : $user_bono->residency_two,
                    'CICounty' => $countrys[$user_bono->country],
                    'CIZipCode' =>  $user_bono->residency_one_invoice != '' ? $user_bono->residency_one_invoice : $user_bono->residency_one,
                    'Phone2' => $user_bono->cellular,
                    'BankCode' => trim($user_bono->bank_code),
                    'AccountType' => trim($user_bono->type_account),
                    'AccountNumber' => $number_account,
                    'InterAccNumber' => '',
                    'Identificacion' => $user_bono->country == 8 ? '01' : $identificacion,
                    'Req_Factura' => $user_bono->cfdi,
                    'CotitName' => $user_bono->name_cotitular,
                    'CotitDNIType' => $user_bono->type_document_cotitular == 0 ? '' : $user_bono->type_document_cotitular,
                    'CotitDNINumber' => $user_bono->number_document_cotitular,
                    'CotitDNIRoute' => '',
                    'CotitRegimen' => '',
                    'Genero' => $user_bono->gender,
                ];
            }
        } catch (\Throwable $th) {
            // throw $th;
            $data['error_info'] = $th;
            return $data;
        }
        $data['ciinfocomp'] = $ciinfocomp;
        $data['ciinfocomp_bono'] = isset($ciinfocomp_bono) ? $ciinfocomp_bono : '';
        $data['status'] = 200;

        return $data;
    }

    public function get_ciinfo_update($sale, $bplatam)
    {
        $data['status'] = 300;
        $countrys = ['tst', 'COL', 'MEX', 'PER', 'ECU', 'PAN', 'GTM', 'SLV', 'CRI', 'USA', 'CHL'];
        switch ($sale->country_id) {
            case 1:
                $ciinfo = [
                    'Pais_Intrnal' => $sale->code,
                    'FederalTaxID' => '55555555',
                    'CreateSAP' => null,
                    'CreateVista' => null,
                    'CreateMN' => null
                ];
                break;
            case 2:
                $ciinfo = [
                    'Pais_Intrnal' => $sale->code,
                    'FederalTaxID' => 'XAXX010101000',
                    'CreateSAP' => null,
                    'CreateVista' => null,
                    'CreateMN' => null
                ];
                break;
            case 3:
                $ciinfo = [
                    'Pais_Intrnal' => $sale->code,
                    'FederalTaxID' => '55555555',
                    'CreateSAP' => null,
                    'CreateVista' => null,
                    'CreateMN' => null
                ];
                break;
            case 4:
                $ciinfo = [
                    'Pais_Intrnal' => $sale->code,
                    'FederalTaxID' => '55555555',
                    'CreateSAP' => null,
                    'CreateVista' => null,
                    'CreateMN' => null
                ];
                break;
            case 5:
                $ciinfo = [
                    'Pais_Intrnal' => $sale->code,
                    'FederalTaxID' => '55555555',
                    'CreateSAP' => null,
                    'CreateVista' => null,
                    'CreateMN' => null
                ];
                break;
            case 6:
                $ciinfo = [
                    'Pais_Intrnal' => $sale->code,
                    'FederalTaxID' => '55555555',
                    'CreateSAP' => null,
                    'CreateVista' => null,
                    'CreateMN' => null
                ];
                break;
            case 7:
                $ciinfo = [
                    'Pais_Intrnal' => $sale->code,
                    'FederalTaxID' => '55555555',
                    'CreateSAP' => null,
                    'CreateVista' => null,
                    'CreateMN' => null
                ];
                break;
            case 8:
                $ciinfo = [
                    'Pais_Intrnal' => $sale->code,
                    'FederalTaxID' => '55555555',
                    'CreateSAP' => null,
                    'CreateVista' => null,
                    'CreateMN' => null
                ];
                break;
            default:
                $ciinfo = [];
                break;
        }

        $data['status'] = empty($ciinfo) ? 300 : 200;
        $data['ciinfo'] = $ciinfo;
        return $data;
    }

    public function get_ciinfocomp_update($sale)
    {
        $data['status'] = 300;
        $countrys = ['tst', 'COL', 'MEX', 'PER', 'ECU', 'PAN', 'GTM', 'SLV', 'CRI', 'USA', 'CHL'];
        //para agregar datos x pais en dni_route
        $dni_route = '';
        $regimen = '';
        $dni_type = '';
        $dni_number = '';
        $number_account = '';
        switch (intval($sale->country_id)) {
            case 1:
                $ciinfocomp = [
                    'CICity' =>  'BOGOTA',
                    'CIMunicipio' => 'BOGOTA',
                    'CIState' =>  'CN',
                    'CICounty' => 'COL',
                    'BankCode' => '',
                    'AccountType' => '',
                    'AccountNumber' => ''

                ];
                break;

            case 2:
                $ciinfocomp = [
                    'CICity' =>  'Benito Juárez',
                    'CIMunicipio' => 'Benito Juárez',
                    'CIState' =>  'CMX',
                    'CICounty' => 'MEX',
                    'BankCode' => '',
                    'AccountType' => '',
                    'AccountNumber' => ''
                ];
                break;
            case 3:
                $ciinfocomp = [
                    'CICity' =>  'Lima',
                    'CIMunicipio' => 'Miraflores',
                    'CIState' =>  '15',
                    'CICounty' => 'PER',
                    'BankCode' => '',
                    'AccountType' => '',
                    'AccountNumber' => ''
                ];
                break;
            case 4:
                $ciinfocomp = [
                    'CICity' =>  'Guayaquil',
                    'CIMunicipio' => 'Guayaquil',
                    'CIState' =>  'GU',
                    'CICounty' => 'ECU',
                    'BankCode' => '',
                    'AccountType' => '',
                    'AccountNumber' => ''
                ];
                break;
            case 5:
                $ciinfocomp = [
                    'CICity' =>  'Panamá',
                    'CIMunicipio' => 'Panamá',
                    'CIState' =>  'PN',
                    'CICounty' => 'PAN',
                    'BankCode' => '',
                    'AccountType' => '',
                    'AccountNumber' => ''
                ];
                break;
            case 6:
                $ciinfocomp = [
                    'CICity' =>  'Guatemala',
                    'CIMunicipio' => 'Guatemala',
                    'CIState' =>  'GU',
                    'CICounty' => 'GTM',
                    'BankCode' => '',
                    'AccountType' => '',
                    'AccountNumber' => ''
                ];
                break;
            case 7:
                $ciinfocomp = [
                    'CICity' =>  'San Salvador',
                    'CIMunicipio' => 'San Salvador',
                    'CIState' =>  'SS',
                    'CICounty' => 'SLV',
                    'BankCode' => '',
                    'AccountType' => '',
                    'AccountNumber' => ''
                ];
                break;
            case 8:
                $ciinfocomp = [
                    'CICity' =>  '031',
                    'CIMunicipio' => '196',
                    'CIState' =>  'HER',
                    'CICounty' => 'CRI',
                    'Identificacion' => '02',
                    'BankCode' => '',
                    'AccountType' => '',
                    'AccountNumber' => ''
                ];
                break;
            default:
                $ciinfocomp = [];
                break;
        }


        $data['ciinfocomp'] = $ciinfocomp;
        $data['status'] = empty($ciinfocomp) ? 300 : 200;

        return $data;
    }

    public function get_ciinfoenvio($user, $address_logbook, $CIState, $bono, $user_bono, $tvrepdom, $contracts)
    {
        $data['status'] = 300;
        try {
            if ($tvrepdom == 1) {
                $array = explode('|', $contracts->address_invoice);
                if (isset($array[2])) {
                    $address = trim($array[0]);
                    $residency = trim($array[1]);
                    $number_residency = trim($array[2]);
                } else {
                    $address = $contracts->address;
                    $residency = '';
                    $number_residency = '';
                }
                $ciinfoenvio = [
                    'CardCode' => strval($contracts->code),
                    'CICountry' => 'DOM',
                    'CIAddress' => $address,
                    'CIMunicipio' => $residency,
                    'CICity' => $number_residency,
                    'CICounty' => 'DOM',
                    'CIZipCode' => '',
                    'CIState' => '',
                ];
            } else {
                $ciinfoenvio = [
                    'CardCode' => strval($user->sap_code),
                    'CICountry' => 'MEX',
                    'CIAddress' => trim($address_logbook->address),
                    'CIMunicipio' => trim($address_logbook->district),
                    'CICity' => trim($address_logbook->province),
                    'CIState' => $CIState,
                    'CICounty' => 'MEX',
                    'CIZipCode' => strval(trim($address_logbook->zip_code)),
                ];
            }
            if ($bono == 1) {
                $ciinfoenvio_bono = [
                    'CardCode' => $user_bono->code,
                    'CICountry' => 'MEX',
                    'CIAddress' => trim($address_logbook->address),
                    'CIMunicipio' => trim($address_logbook->district),
                    'CICity' => trim($address_logbook->province),
                    'CIState' => $CIState,
                    'CICounty' => 'MEX',
                    'CIZipCode' => trim($address_logbook->zip_code),
                ];
            }
        } catch (\Throwable $th) {
            //throw $th;
            $data['error_info'] = $th;
            return $data;
        }

        $data['status'] = 200;
        $data['ciinfoenvio'] = $ciinfoenvio;
        $data['ciinfoenvio_bono'] = isset($ciinfoenvio_bono) ? $ciinfoenvio_bono : '';
        return $data;
    }

    public function get_OrderHeader_chl($sale, $garantía, $address_logbook, $user, $taxcodes, $warehouses, $autoship, $nikkenpoints, $bono, $user_bono, $qty, $incorporacion)
    {
        $countrys = ['tst', 'COL', 'MEX', 'PER', 'ECU', 'PAN', 'GTM', 'SLV', 'CRI', 'USA', 'CHL'];
        $docdate  = new DateTime($sale->approval_date);
        $createdate = new DateTime($sale->created_at);
        $updatedate = new DateTime($sale->updated_at);
        $utc_timezone = new DateTimeZone("America/Mexico_City");
        $now = new DateTime("now", $utc_timezone);
        $periodo = $updatedate->diff($createdate);
        $U_Periodo = $periodo->m == 0 ? 'Actual' : 'Anterior';
        $NumAtCard = $autoship == 1 ? 'WEB-AUTOSHIP-' . $sale->code . '-' . $sale->id : 'WEB-' . $sale->code . '-' . $sale->id;
        //Tipo de Ventas
        if ($sale->extras == '') {
            $U_Tipo_venta = 'Tienda Virtual';
        } else {
            $tipoventa = json_decode($sale->extras);
            if (isset($tipoventa->ref)) {
                switch ($tipoventa->ref) {
                    case 'un':
                        $U_Tipo_venta = "Universidad Nikken";
                        break;
                    case 'kit':
                        $U_Tipo_venta = "Inscripción On line";
                        break;
                    case '710':
                        $U_Tipo_venta = "Estrategia 7-10";
                        break;
                    case 'repuestos':
                        $U_Tipo_venta = "Micrositio de repuestos";
                        break;
                    case 'repuestoscp':
                        $U_Tipo_venta = "Micrositio de repuestos";
                        break;
                    case 'ae':
                        $U_Tipo_venta = "Arma tu entorno";
                        break;
                        //Por validar
                    case 'ae-personal':
                        $U_Tipo_venta = "Arma tu entorno";
                        break;
                    case 'nikkenpoints':
                        $U_Tipo_venta = "NikkenPoints";
                        break;
                    default:
                        $U_Tipo_venta = "Tienda Virtual";
                        break;
                }
            } else {
                $U_Tipo_venta = 'Tienda Virtual';
            }
        }
        $iva = 0;
        //Validar si es con o sin IV
        $QtyItem = ($incorporacion == 1 && $user->client_type == 'CLUB') ? $qty + 1 : $qty;
        // $DocCurrency = $this->get_DocCurrency();
        $oh = [];
        try {
            $header = [
                'DocEntry' => trim($sale->id),
                'CardCountry' => trim($sale->code),
                'OrderCountry' => $countrys[$user->country_id],
                'CardCode' => trim($user->sap_code),
                'NumAtCard' => trim($NumAtCard),
                'DocDate' => trim($docdate->format('Y-m-d')),
                'DocCurrency' => 'CLP',
                'Precio' => $user->client_type == 'CI' ? 'S' : 'C',
                'Doctotal' => trim($sale->total),
                'QtyItem' => $QtyItem,
                'ExtraTax' => trim($sale->extra_perception_total),
                'Puntos' => trim($sale->points),
                'vol_calc' => trim($sale->vc),
                'Menudeo_comis' => trim($sale->retail),
                'Flete_incluido' => trim($sale->lading),
                'Descuento' => trim($sale->discount),
                'Entorno' => '',
                'Periodo' => trim($U_Periodo),
                'Tipo_venta' => trim($U_Tipo_venta),
                'Tipo_Despacho' => 'Envio',
                'Destinatario' => trim($address_logbook->nombre),
                'Direccion_Envio' => trim($address_logbook->direccion),
                'Colonia_Envio' => trim($address_logbook->direccion_3),
                'Ciudad_Envio' => trim($address_logbook->direccion_2),
                'Estado_Envio' => trim($address_logbook->direccion_1),
                'Telefono_Envio' => trim($address_logbook->telefono_celular),
                'CP_Envio' => trim($address_logbook->codigo_postal),
                'Bodega_Direccion' => '',
                'Staffvtatv' => '',
                'CreateOrder' => null,
                'CreateInvoice' => null,
                'CreatePayment' => null,
                'CreateBsug' => null,
                'Comen_Envio' => trim($address_logbook->referencia),
                'Email' => trim($address_logbook->email),
                // 'fecha_en_stgin' => $now->format('Y-m-d H:i:s'),
                'U_Numero_Envio' => trim($address_logbook->numero),
                'U_Comen_Envio' => trim($address_logbook->referencia),
                'U_Telefono_Dest' => trim($address_logbook->telefono_celular_con_prefijo),
                'U_Telefono_Fijo' => trim($address_logbook->telefono_fijo_con_prefijo),
            ];
            if ($bono == 1) {
                $header_bono = [
                    'DocEntry' => '55' . trim($sale->id),
                    'CardCountry' => trim($sale->code),
                    'OrderCountry' => $countrys[$user_bono->country],
                    'CardCode' => trim($user_bono->code),
                    'NumAtCard' => trim($NumAtCard) . '_B',
                    'DocDate' => trim($docdate->format('Y-m-d')),
                    'DocCurrency' => 'CLP',
                    'Precio' => 'S',
                    'Doctotal' => 1,
                    'QtyItem' => 1,
                    'ExtraTax' => 0,
                    'Puntos' => 0,
                    'vol_calc' => 0,
                    'Menudeo_comis' => 0,
                    'Flete_incluido' => 0,
                    'Descuento' => 0,
                    'Entorno' => '',
                    'Periodo' => trim($U_Periodo),
                    'Tipo_venta' => trim($U_Tipo_venta),
                    'Tipo_Despacho' => 'Envio',
                    'Destinatario' => trim($address_logbook->nombre),
                    'Direccion_Envio' => trim($address_logbook->direccion),
                    'Colonia_Envio' => trim($address_logbook->direccion_3),
                    'Ciudad_Envio' => trim($address_logbook->direccion_2),
                    'Estado_Envio' => trim($address_logbook->direccion_1),
                    'Telefono_Envio' => trim($address_logbook->telefono_celular),
                    'CP_Envio' => trim($address_logbook->codigo_postal),
                    'Bodega_Direccion' => '',
                    'Staffvtatv' => '',
                    'CreateOrder' => null,
                    'CreateInvoice' => null,
                    'CreatePayment' => null,
                    'CreateBsug' => null,
                    'DocNumSAP' => '',
                    'Comen_Envio' => trim($address_logbook->referencia),
                    'Email' => trim($address_logbook->email),
                    // 'fecha_en_stgin' => $now->format('Y-m-d H:i:s'),
                    'U_Numero_Envio' => trim($address_logbook->numero),
                    'U_Comen_Envio' => trim($address_logbook->referencia),
                    'U_Telefono_Dest' => trim($address_logbook->telefono_celular_con_prefijo),
                    'U_Telefono_Fijo' => trim($address_logbook->telefono_fijo_con_prefijo),
                ];
            }
        } catch (\Throwable $th) {
            $data['status'] = 300;
            $data['error_info'] = 'Error OH_CHL : ' . substr($th, 0, 200);
            return $data;
        }

        $oh['status'] = 200;
        $oh['oh'] = $header;
        $oh['oh_bono'] = isset($header_bono) ? $header_bono : '';
        return $oh;
    }

    public function get_OrderLines_chl($sale, $products, $user, $taxcodes, $warehouses, $incorporacion, $autoship, $nikkenpoints, $bono, $garantía, $validate_warranty)
    {
        $iva = 0;
        $lines = [];
        $data = [];
        $ol = [];
        $NumAtCard = $autoship == 1 ? 'WEB-AUTOSHIP-' . $sale->code . '-' . $sale->id : 'WEB-' . $sale->code . '-' . $sale->id;
        try {
            foreach ($products as $product) {
                //Validar si es con o sin IV
                if ($product->tax != 0) {
                    $iva = 1;
                }

                ## obtiene el WholePrice para STGN
                $WholePrice = '';
                $conection = \DB::connection('mysqlTV');
                    $data = $conection->select("SELECT (wp.wholesale_price + wp.wholesale_tax) AS WholePrice, wp.wholesale_price, wp.wholesale_tax
                    FROM warehouses_products wp 
                    INNER JOIN products p ON p.id = wp.product_id
                    WHERE p.sku IN ('" . $product->sku .  "')
                    AND wp.country_id = " . $sale->country_id);
                \DB::disconnect('mysqlTV');
                if(sizeof($data) > 0){
                    $WholePrice = intval($data[0]->WholePrice) * intval($product->quantity);
                }

                $lines[] = [
                    'DocEntry' => trim($sale->id),
                    'NumAtCard' => trim($NumAtCard),
                    'ItemCode' => trim($product->sku),
                    'Quantity' => trim($product->quantity),
                    'UnitPrice' => trim($product->price),
                    'Tax' => trim($product->tax),
                    'PriceAfVat' => trim($product->unit_price_with_tax),
                    'TaxCode' => $iva == 1 ? trim($taxcodes->SalesTaxCode) : trim($taxcodes->SalesExeTaxCode),
                    'WhsCode' => trim($warehouses->SalesWhsCode),
                    'Menudeo_comis' => trim($product->retail),
                    'Puntos' => trim($product->points),
                    'vol_calc' => trim($product->vc),
                    'Flete_incluido' => trim($product->lading),
                    'Descto' => trim($product->discount),
                    'Linetotal' => floor(trim($product->price)),
                    'LineAfVat' => floor(trim($product->unit_price_with_tax)),
                    'ExtraTax' => trim($product->extra_perception_total),
                    
                    'WholePrice' => $WholePrice,
                ];
            }
            if ($incorporacion == 1 && $user->client_type == 'CLUB') {
                $lines[] = [
                    'DocEntry' => trim($sale->id),
                    'NumAtCard' => trim($NumAtCard),
                    'ItemCode' => '5031',
                    'Quantity' => '1',
                    'UnitPrice' => 0,
                    'Tax' => 0,
                    'PriceAfVat' => 0,
                    'TaxCode' => trim($taxcodes->SalesTaxCode),
                    'WhsCode' => trim($warehouses->SalesWhsCode),
                    'Menudeo_comis' => 0,
                    'Puntos' => 0,
                    'vol_calc' => 0,
                    'Flete_incluido' => 0,
                    'Descto' => 0,
                    'Linetotal' => 0,
                    'LineAfVat' => 0,
                    'ExtraTax' => 0
                ];
            }
            if ($bono == 1) {
                $lines_bono = [
                    'DocEntry' => '55' . trim($sale->id),
                    'NumAtCard' => trim($NumAtCard) . '_B',
                    'ItemCode' => '5005',
                    'Quantity' => 1,
                    'UnitPrice' => 1,
                    'Tax' => 0,
                    'PriceAfVat' => 1,
                    'TaxCode' => trim($taxcodes->SalesExeTaxCode),
                    'WhsCode' => trim($warehouses->SalesWhsCode),
                    'Menudeo_comis' => 0,
                    'Puntos' => 0,
                    'vol_calc' => 0,
                    'Flete_incluido' => 0,
                    'Descto' => 0,
                    'Linetotal' => 1,
                    'LineAfVat' => 1,
                    'ExtraTax' => 0,
                    'Estrategia' => '',
                    'Garantia' => '',
                    'Fecha_Estrategia' => '',
                    'RetailPrice' => '',
                    'WholeDisc' => '',
                    'WholePrice' => 0
                ];
            }
        } catch (\Throwable $th) {
            $data['status'] = 300;
            $data['error_info'] = 'Error OL : ' . $th;
            return $data;
        }
        $ol['status'] = 200;
        $ol['lines'] = $lines;
        $ol['lines_bono'] = isset($lines_bono) ? $lines_bono : '';
        return $ol;
    }

    public function eliminar_acentos($cadena)
    {

        //Reemplazamos la A y a
        $cadena = str_replace(
            array('Á', 'À', 'Â', 'Ä', 'á', 'à', 'ä', 'â', 'ª'),
            array('A', 'A', 'A', 'A', 'a', 'a', 'a', 'a', 'a'),
            $cadena
        );

        //Reemplazamos la E y e
        $cadena = str_replace(
            array('É', 'È', 'Ê', 'Ë', 'é', 'è', 'ë', 'ê'),
            array('E', 'E', 'E', 'E', 'e', 'e', 'e', 'e'),
            $cadena
        );

        //Reemplazamos la I y i
        $cadena = str_replace(
            array('Í', 'Ì', 'Ï', 'Î', 'í', 'ì', 'ï', 'î'),
            array('I', 'I', 'I', 'I', 'i', 'i', 'i', 'i'),
            $cadena
        );

        //Reemplazamos la O y o
        $cadena = str_replace(
            array('Ó', 'Ò', 'Ö', 'Ô', 'ó', 'ò', 'ö', 'ô'),
            array('O', 'O', 'O', 'O', 'o', 'o', 'o', 'o'),
            $cadena
        );

        //Reemplazamos la U y u
        $cadena = str_replace(
            array('Ú', 'Ù', 'Û', 'Ü', 'ú', 'ù', 'ü', 'û'),
            array('U', 'U', 'U', 'U', 'u', 'u', 'u', 'u'),
            $cadena
        );

        //Reemplazamos la N, n, C y c
        $cadena = str_replace(
            array('Ñ', 'ñ', 'Ç', 'ç'),
            array('N', 'n', 'C', 'c'),
            $cadena
        );

        return $cadena;
    }


    public function get_businesspartner($contracts, $bono, $user_bono)
    {
        $data['status'] = 300;
        if (!isset($contracts->code)) {
            $data['error_info'] = 'No existe registro en contracts del CI';
            return $data;
        }
        $countrys = ['tst', 'COL', 'MEX', 'PER', 'ECU', 'PAN', 'GTM', 'SLV', 'CRI', 'USA', 'CHL'];
        // $date_actual = ;
        $utc_timezone = new DateTimeZone("America/Mexico_City");
        $date_actual = new DateTime("now", $utc_timezone);
        if ($contracts->type_incorporate == 1) {
            if ($contracts->country != 2) {
                $name_explode = explode(",", $contracts->name);
                $name = isset($name_explode[1]) ? trim($name_explode[1]) : $contracts->name;
                $last_name = isset($name_explode[0]) ? trim($name_explode[0]) : '';
            } else {
                $name = trim($contracts->name);
            }
        } else {
            $name = trim($contracts->name);
        }
        if ($bono == 1) {
            if ($user_bono->type_incorporate == 1) {
                if ($user_bono->country != 2) {
                    $name_explode = explode(",", $user_bono->name);
                    $name_bono = isset($name_explode[1]) ? trim($name_explode[1]) : $user_bono->name;
                    $last_name_bono = isset($name_explode[0]) ? trim($name_explode[0]) : '';
                } else {
                    $name_bono = trim($user_bono->name);
                }
            } else {
                $name_bono = trim($user_bono->name);
            }
        }
        $birthday = new DateTime($contracts->birthday);
        try {
            $businesspartner = [
                'NumContract' => $contracts->id_contract,
                'CardCode' => $contracts->code,
                'Cardtype' => $contracts->type == 1 ? 'AB' : 'CB',
                'CreateDate' => $contracts->create_at,
                'CardFirstName' => $name,
                'CardLastName' => isset($last_name) ? $last_name : '',
                'CardCountry' => $countrys[trim($contracts->country)],
                'BirthDay' => trim($birthday->format('Y-m-d')),
                'Phone1' => trim($contracts->cellular),
                'Phone2' => trim($contracts->cellular),
                'Email' => $contracts->email,
                'FederalTaxID' => $contracts->number_document,
                'CmpPrivate' => $contracts->type_incorporate == 0 ? 'C' : 'I',
                'SponsorId' => $contracts->sponsor,
                'SponsorName' => '',
                'CoOwnerName' => $contracts->name_cotitular,
                'CoOwnerFedTaxId' => $contracts->number_document_cotitular,
                'CreateSAP' => null,
                'CreateVista' => null,
                'CreateMN' => null,
                'SystemDate' => $date_actual->format('Y-m-d H:i:s'),
                'Genero' => $contracts->gender,
                'Giro_CI' => $contracts->socio_econ,
                'SignupType' => ''
            ];
            if ($bono == 1) {
                $businesspartner_bono = [
                    'NumContract' => $user_bono->id_contract,
                    'CardCode' => $user_bono->code,
                    'Cardtype' => $user_bono->type == 1 ? 'AB' : 'CB',
                    'CreateDate' => $user_bono->create_at,
                    'CardFirstName' => $name_bono,
                    'CardLastName' => isset($last_name_bono) ? $last_name_bono : '',
                    'CardCountry' => $countrys[trim($contracts->country)],
                    'BirthDay' => trim($birthday->format('Y-m-d')),
                    'Phone1' => trim($user_bono->cellular),
                    'Phone2' => trim($user_bono->cellular),
                    'Email' => $user_bono->email,
                    'FederalTaxID' => $user_bono->number_document,
                    'CmpPrivate' => $user_bono->type_incorporate == 0 ? 'C' : 'I',
                    'SponsorId' => $user_bono->sponsor,
                    'SponsorName' => '',
                    'CoOwnerName' => $user_bono->name_cotitular,
                    'CoOwnerFedTaxId' => $user_bono->number_document_cotitular,
                    'CreateSAP' => null,
                    'CreateVista' => null,
                    'CreateMN' => null,
                    'SystemDate' => $date_actual->format('Y-m-d H:i:s'),
                    'Genero' => $user_bono->gender,
                    'Giro_CI' => $user_bono->socio_econ,
                    'SignupType' => ''
                ];
            }
        } catch (\Throwable $th) {
            $data['status'] = 300;
            $data['error_info'] = 'Error CIINFO - ' . $th;
            return $data;
        }
        $data['status'] = 200;
        $data['businesspartner'] = $businesspartner;
        $data['businesspartner_bono'] = isset($businesspartner_bono) ? $businesspartner_bono : '';
        return $data;
    }

    public function get_businesspartner_update($bplatam)
    {
        $genders = ['Femenino' => 'F', 'Masculino' => 'M', 'femenino' => 'F', 'masculino' => 'M'];
        try {
            $businesspartner = [
                'NumContract' => trim($bplatam->NumContract),
                'CardCode' => trim($bplatam->CardCode),
                'Cardtype' => $bplatam->Groupcode == '111' ? 'CB' : 'AB',
                'CreateDate' => trim($bplatam->CreateDate),
                'CardFirstName' => trim($bplatam->CardFirstName),
                'CardLastName' => trim($bplatam->CardLastName),
                'CardCountry' => trim($bplatam->CardCountry),
                'BirthDay' => trim($bplatam->BirthDay),
                'Phone1' => trim($bplatam->Phone1),
                'Phone2' => trim($bplatam->Phone2),
                'Email' => trim($bplatam->Email),
                'FederalTaxID' => trim($bplatam->FederalTaxId),
                'CmpPrivate' => trim($bplatam->CmpPrivate),
                'SponsorId' => trim($bplatam->SponsorId),
                'SponsorName' => $bplatam->SponsorName,
                'CreateSAP' => null,
                'CreateVista' => null,
                'CreateMN' => null,
                'Giro_CI' => 'Sin Actividad',
                'SystemDate' => trim($bplatam->SystemDate),
                'Genero' => isset($genders[trim($bplatam->Genero)]) ? $genders[trim($bplatam->Genero)] : trim($bplatam->Genero),
            ];
        } catch (\Throwable $th) {
            $data['status'] = 300;
            $data['error_info'] = 'Error bussinespartner - ' . $th;
            return $data;
        }
        $data['status'] = 200;
        $data['businesspartner_update'] = $businesspartner;
        return $data;
    }

    public function get_businesspartneraddress($contracts, $bono, $user_bono)
    {
        $countrys = ['tst', 'COL', 'MEX', 'PER', 'ECU', 'PAN', 'GTM', 'SLV', 'CRI', 'USA', 'CHL'];
        $utc_timezone = new DateTimeZone("America/Mexico_City");
        $fecha_actual = new DateTime("now", $utc_timezone);
        try {
            $businesspartneraddress = [
                'CardCode' => $contracts->code,
                'CardCountry' => 'CHL',
                'OrderCountry' => 'CHL',
                'CardAddress' => trim($contracts->address_invoice) != '' ? $contracts->address_invoice : $contracts->address,
                'CardBlock' => trim($contracts->residency_four_invoice)  != '' ? trim($contracts->residency_four_invoice) : trim($contracts->residency_four),
                'CardCity' => trim($contracts->residency_three_invoice)  != '' ? trim($contracts->residency_three_invoice) : trim($contracts->residency_three),
                'CardStateId' => trim($contracts->residency_two_invoice)  != '' ? trim($contracts->residency_two_invoice) : trim($contracts->residency_two),
                'CardZipCode' => trim($contracts->residency_one_invoice)  != '' ? trim($contracts->residency_one_invoice) : trim($contracts->residency_one),
                'AdresType' => 'S',
                'SystemDate' => $fecha_actual->format('Y-m-d H:i:s'),
            ];
            if ($bono == 1) {
                $businesspartneraddress_bono = [
                    'CardCode' => $user_bono->code,
                    'CardCountry' => $countrys[$user_bono->country],
                    'OrderCountry' => 'CHL',
                    'CardAddress' => trim($user_bono->address_invoice) != '' ? $user_bono->address_invoice : $user_bono->address,
                    'CardBlock' => trim($user_bono->residency_four_invoice)  != '' ? trim($user_bono->residency_four_invoice) : trim($user_bono->residency_four),
                    'CardCity' => trim($user_bono->residency_three_invoice)  != '' ? trim($user_bono->residency_three_invoice) : trim($user_bono->residency_three),
                    'CardStateId' => trim($user_bono->residency_two_invoice)  != '' ? trim($user_bono->residency_two_invoice) : trim($user_bono->residency_two),
                    'CardZipCode' => trim($user_bono->residency_one_invoice)  != '' ? trim($user_bono->residency_one_invoice) : trim($user_bono->residency_one),
                    'AdresType' => 'S',
                    'SystemDate' => $fecha_actual->format('Y-m-d H:i:s'),
                ];
            }
        } catch (\Throwable $th) {
            $data['status'] = 300;
            $data['error_info'] = 'Error CIINFO - ' . $th;
            return $data;
        }
        $data['status'] = 200;
        $data['businesspartneraddress'] = $businesspartneraddress;
        $data['businesspartneraddress_bono'] = isset($businesspartneraddress_bono) ? $businesspartneraddress_bono : '';
        return $data;
    }

    public function get_businesspartneraddress_update($bplatam)
    {
        $utc_timezone = new DateTimeZone("America/Mexico_City");
        $now = new DateTime("now", $utc_timezone);
        try {
            switch ($bplatam->CardCountry) {
                case 'COL':
                    $CardBlock = 'BOGOTA';
                    $CardCity = 'BOGOTA';
                    $CardStateId = 'CN';
                    break;
                case 'MEX':
                    $CardBlock = 'Benito Juárez';
                    $CardCity = 'Benito Juárez';
                    $CardStateId = 'CMX';
                    break;
                case 'PER':
                    $CardBlock = 'Lima';
                    $CardCity = 'Miraflores';
                    $CardStateId = '15';
                    break;
                case 'ECU':
                    $CardBlock = 'Guayaquil';
                    $CardCity = 'Guayaquil';
                    $CardStateId = 'GU';
                    break;
                case 'PAN':
                    $CardBlock = 'Panamá';
                    $CardCity = 'Panamá';
                    $CardStateId = 'PN';
                    break;
                case 'GTM':
                    $CardBlock = 'Guatemala';
                    $CardCity = 'Guatemala';
                    $CardStateId = 'GU';
                    break;
                case 'SLV':
                    $CardBlock = 'San Salvador';
                    $CardCity = 'San Salvador';
                    $CardStateId = 'SS';
                    break;
                case 'CRI':
                    $CardBlock = '031';
                    $CardCity = '196';
                    $CardStateId = 'HER';
                    break;
                default:
                    $ciinfocomp = [];
                    break;
            }
            $businesspartner = [
                'CardCode' => $bplatam->CardCode,
                'CardCountry' => $bplatam->CardCountry,
                'OrderCountry' => 'CHL',
                'CardAddress' => trim($bplatam->CardAddress),
                'CardBlock' => $CardBlock,
                'CardCity' => $CardCity,
                'CardStateId' => $CardStateId,
                'CardZipCode' => trim($bplatam->CardZipCode),
                'AdresType' => trim($bplatam->AdresType),
                'SystemDate' => $now->format('Y-m-d H:i:s'),
            ];
        } catch (\Throwable $th) {
            $data['status'] = 300;
            $data['error_info'] = 'Error BUSINESSPARTNERADDRESSUPDATE - ' . $th;
            return $data;
        }
        $data['status'] = 200;
        $data['businesspartneraddress_update'] = $businesspartner;
        return $data;
    }

    public function get_businesspartnertaxinfo($contracts, $bono, $user_bono)
    {
        $countrys = ['tst', 'COL', 'MEX', 'PER', 'ECU', 'PAN', 'GTM', 'SLV', 'CRI', 'USA', 'CHL'];
        $utc_timezone = new DateTimeZone("America/Mexico_City");
        $fecha_actual = new DateTime("now", $utc_timezone);
        try {
            $businesspartnertaxinfo = [
                'CardCode' => $contracts->code,
                'CardCountry' => $countrys[$contracts->country],
                'OrderCountry' => 'CHL',
                'CreateDate' => $fecha_actual->format('Y-m-d H:i:s'),
                'IdRegimen' => $contracts->type_incorporate == 1 ? 'TPN' : 'TPJ',
                'FederalTaxId' => trim($contracts->number_document),
                'CardIdCode' => '',
                'CardIdnum' => 'RUT',
                'CreateSAP' => null,
                'SystemDate' => $fecha_actual->format('Y-m-d H:i:s'),
            ];
            if ($bono == 1) {
                $businesspartnertaxinfo_bono = [
                    'CardCode' => $user_bono->code,
                    'CardCountry' => $countrys[$user_bono->country],
                    'OrderCountry' => 'CHL',
                    'CreateDate' => $fecha_actual,
                    'IdRegimen' => $contracts->type_incorporate == 1 ? 'TPN' : 'TPJ',
                    'FederalTaxId' => trim($user_bono->number_document),
                    'CardIdCode' => '',
                    'CardIdnum' => 'RUT',
                    'CreateSAP' => null,
                    'SystemDate' => $fecha_actual->format('Y-m-d H:i:s'),
                ];
            }
        } catch (\Throwable $th) {
            $data['status'] = 300;
            $data['error_info'] = 'Error businesspartnertaxinfo - ' . $th;
            return $data;
        }
        $data['status'] = 200;
        $data['businesspartnertaxinfo'] = $businesspartnertaxinfo;
        $data['businesspartnertaxinfo_bono'] = isset($businesspartnertaxinfo_bono) ? $businesspartnertaxinfo_bono : '';
        return $data;
    }

    public function get_businesspartnertaxinfo_update($bplatam)
    {
        $utc_timezone = new DateTimeZone("America/Mexico_City");
        $fecha_actual = new DateTime("now", $utc_timezone);
        try {
            $businesspartnertaxinfo_update = [
                'CardCode' => $bplatam->CardCode,
                'CardCountry' => $bplatam->CardCountry,
                'OrderCountry' => 'CHL',
                'CreateDate' => $fecha_actual->format('Y-m-d H:i:s'),
                'IdRegimen' => 'TPN',
                'FederalTaxId' => trim($bplatam->FederalTaxId),
                'CardIdCode' => '',
                'CardIdnum' => '',
                'CreateSAP' => null,
                'SystemDate' => $fecha_actual->format('Y-m-d H:i:s'),
            ];
        } catch (\Throwable $th) {
            $data['status'] = 300;
            $data['error_info'] = 'Error businesspartnertaxinfo - ' . $th;
            return $data;
        }
        $data['status'] = 200;
        $data['businesspartnertaxinfo_update'] = $businesspartnertaxinfo_update;
        return $data;
    }

    public function get_businesspartneraccinfo($contracts, $bono, $user_bono)
    {
        $countrys = ['tst', 'COL', 'MEX', 'PER', 'ECU', 'PAN', 'GTM', 'SLV', 'CRI', 'USA', 'CHL'];
        $utc_timezone = new DateTimeZone("America/Mexico_City");
        $fecha_actual = new DateTime("now", $utc_timezone);
        try {
            $businesspartneraccinfo = [
                'CardCode' => $contracts->code,
                'CardCountry' => $countrys[$contracts->country],
                'BankCode' => trim($contracts->bank_code),
                'AccountType' => trim($contracts->type_account),
                'AccountNumber' => trim($contracts->number_account),
                'InterAccNumber' => null,
                'CreateSAP' => null,
                'SystemDate' => $fecha_actual->format('Y-m-d H:i:s'),
            ];
            if ($bono == 1) {
                $businesspartneraccinfo_bono = [
                    'CardCode' => $user_bono->code,
                    'CardCountry' => $countrys[$contracts->country],
                    'BankCode' => trim($user_bono->bank_code),
                    'AccountType' => trim($user_bono->type_account),
                    'AccountNumber' => trim($user_bono->number_account),
                    'InterAccNumber' => null,
                    'CreateSAP' => null,
                    'SystemDate' => $fecha_actual->format('Y-m-d H:i:s'),
                ];
            }
        } catch (\Throwable $th) {
            $data['status'] = 300;
            $data['error_info'] = 'Error businesspartneraccinfo - ' . $th;
            return $data;
        }
        $data['status'] = 200;
        $data['businesspartneraccinfo'] = $businesspartneraccinfo;
        $data['businesspartneraccinfo_bono'] = isset($businesspartneraccinfo_bono) ? $businesspartneraccinfo_bono : '';
        return $data;
    }

    public function get_businesspartneraccinfo_update($bplatam)
    {
        $countrys = ['tst', 'COL', 'MEX', 'PER', 'ECU', 'PAN', 'GTM', 'SLV', 'CRI', 'USA', 'CHL'];
        $utc_timezone = new DateTimeZone("America/Mexico_City");
        $fecha_actual = new DateTime("now", $utc_timezone);
        try {
            $businesspartneraccinfo_update = [
                'CardCode' => $bplatam->CardCode,
                'CardCountry' => $bplatam->CardCountry,
                'BankCode' => '',
                'AccountType' => '',
                'AccountNumber' =>  '',
                'InterAccNumber' => null,
                'CreateSAP' => null,
                'SystemDate' => $fecha_actual->format('Y-m-d H:i:s'),
            ];
        } catch (\Throwable $th) {
            $data['status'] = 300;
            $data['error_info'] = 'Error businesspartneraccinfo - ' . $th;
            return $data;
        }
        $data['status'] = 200;
        $data['businesspartneraccinfo_update'] = $businesspartneraccinfo_update;
        return $data;
    }

    public function get_DocCurrency()
    {
        $data['status'] = 300;
        try {
            $info = countrys::where('idcountry', 10)->first();
        } catch (\Throwable $th) {
            $data['error_info'] = substr($th, 0, 200);
            return $data;
        }
        if (empty($info)) {
            $data['error_info'] = 'Error ocurrency';
            return $data;
        }
        $data['status'] = 200;
        $data['info'] = $info->Currency;
        return $data;
    }

    public function get_items_warranty()
    {
        // $key = sprintf('get_items_warranty');
        $timeCaching = 21600; #in seconds 21600 = 6 horas        
        return cache()->remember(
            'get_items_warranty',
            $timeCaching,
            function () {
                $taxcode = [];
                $countrys = ['tst', 'COL', 'MEX', 'PER', 'ECU', 'PAN', 'GTM', 'SLV', 'CRI', 'USA', 'CHL'];
                try {
                    $response =  Products_Warranty::all();
                } catch (\Throwable $th) {
                    return 0;
                }
                $info = [];
                $items = [];
                foreach ($response as $product) {
                    $items[] = trim($product->sku);
                    $info[$countrys[trim($product->country_warranty_id)]] = $items;
                }

                return $info;
            }
        );
    }

    public function validate_warranty($sale_id)
    {
        $data['status'] = 200;
        try {
            $process =  warranties_in_process::where('sale_id', $sale_id)->get();
            $rejected = warranties_rejected::where('sale_id', $sale_id)->get();
        } catch (\Throwable $th) {
            $data['status'] = 300;
            $data['error_info'] = $th;
            return $data;
        }
        if (isset($process[0]->sale_id)) {
            $data['validate_process'] = 1;
        } else {
            $data['validate_process'] = 0;
        }
        if (isset($rejected[0]->sale_id)) {
            $data['validate_rejected'] = 1;
        } else {
            $data['validate_rejected'] = 0;
        }
        $response_process = [];
        foreach ($process as $p) {
            $response_process[$p->sku] = $p;
        }
        $data['process'] = $response_process;
        $data['rejected'] = $rejected;
        return $data;
    }
}
