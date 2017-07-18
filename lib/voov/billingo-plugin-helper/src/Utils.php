<?php

namespace Billingo\Plugin\Helper;

class Utils
{
    private $billingo_request;

    public function __construct($billingo_request)
    {
        $this->billingo_request = $billingo_request;
    }

    public function createBillingoInvoiceArray($invoice_data, $client_id)
    {
        $vat_codes = $this->getAvailableVatCodes();
        $payment_methods = $this->getPaymentMethods();

        $invoice_data['client_uid'] = $client_id;
        $invoice_data['payment_method'] = $this->matchPaymentMethod($invoice_data['payment_method'], $payment_methods);

        $invoice_data['items'] = array_map(function ($item) use ($vat_codes) {
            $item['vat_id'] = $this->matchVatCodes($item['vat_rate'], $vat_codes);
            unset($item['vat_rate']);
            return $item;
        }, $invoice_data['items']);

        return $invoice_data;
    }

    public function getAvailableVatCodes()
    {
        return $this->billingo_request->get('vat');
    }

    public function getPaymentMethods()
    {
        $response = $this->billingo_request->get('payment_methods/en');
  
        if (!is_array($response)) {
            return false;
        }

        return $response;
    }

    private function matchVatCodes($rate, $vat_codes)
    {
        $rate   = floatval($rate);
        $rate   = $rate < 1 ? $rate : $rate / 100;
        $rate   = round($rate, 2);
        $vat_id = false;
        for ($i = 0; $i < count($vat_codes) && $vat_id === false; ++ $i) {
            if (isset($vat_codes[ $i ]['attributes']['value']) &&
                isset($vat_codes[ $i ]['attributes']['id']) &&
                floatval($vat_codes[ $i ]['attributes']['value']) === $rate
            ) {
                $vat_id = $vat_codes[ $i ]['attributes']['id'];
            }
        }
        if ($vat_id === false) {
            throw new \Exception('No matching VAT id for ' . $rate . ' rate!');
        }

        return $vat_id;
    }

    private function matchPaymentMethod($method, $payment_methods)
    {
        $method = trim(mb_strtolower($method));

        $compatible_payment_methods = array();
        foreach ($payment_methods as $pm) {
            $name                                = strtolower(str_replace(' ', '', $pm['attributes']['name']));
            $compatible_payment_methods[ $name ] = $pm['id'];
        }

        switch ($method) {
            case 'cod':
                return $compatible_payment_methods['cashondelivery'];
            case 'cheque':
                return $compatible_payment_methods['postalcheck'];
            case 'bacs':
                return $compatible_payment_methods['wiretransfer'];
            case 'bankwire':
                return $compatible_payment_methods['wiretransfer'];
            default:
                if (array_key_exists($method, $compatible_payment_methods)) {
                    return $compatible_payment_methods[ $method ];
                }

                return false;
        }
    }
}
