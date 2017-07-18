<?php

namespace Billingo\Plugin\Helper;

use Billingo\API\Connector\HTTP\Request;
use Billingo\Plugin\Helper\Utils;
use Billingo\Plugin\Helper\Invoice;
use Billingo\Plugin\Helper\Client;

class Helper
{
    public $debug_info = '';
    private $send_email;
    private $billingo_request;
    private $logger;

    public function __construct($public_key, $private_key, $send_email = false, $logger = false)
    {
        $this->send_email = $send_email;

        $this->billingo_request = new Request(array(
            'public_key'  => $public_key,
            'private_key' => $private_key
        ));

        $this->logger = $logger;
    }

    public function createInvoice($client_data, $invoice_data)
    {
        try {
            $client = new Client($this->billingo_request);
            $client = $client->create($client_data);
        } catch (\Exception $e) {
            $error_message = 'Billingo Error with client: ' . $e->getMessage();
            error_log($error_message);
            $this->log($error_message);
            return array('error' => $error_message);
        }

        try {
            $utils = new Utils($this->billingo_request);
            $invoice_data = $utils->createBillingoInvoiceArray($invoice_data, $client['id']);
        } catch (\Exception $e) {
            $error_message = 'Billingo Error with utils: ' . $e->getMessage();
            error_log($error_message);
            $this->log($error_message);
            return array('error' => $error_message);
        }

        try {
            $invoice = new Invoice($this->billingo_request);
            $invoice = $invoice->create($invoice_data);
        } catch (\Exception $e) {
            $error_message = 'Billingo Error with invoice: ' . $e->getMessage();
            error_log($error_message);
            $this->log($error_message);
            return array('error' => $error_message);
        }

        return $invoice['id'];
    }

    public function sendEmail($invoice_id)
    {
        try {
            $invoice = new Invoice($this->billingo_request);
            $invoice->get(array($invoice_id, 'send'));
        } catch (\Exception $e) {
            $error_message = 'Billingo Error with sending email: ' . $e->getMessage();
            error_log($error_message);
            $this->log($error_message);
            return array('error' => $error_message);
        }

        return true;
    }

    public function getDownloadLink($invoice_id)
    {
        try {
            $invoice = new Invoice($this->billingo_request);
            $download_code = $invoice->get(array($invoice_id, 'code'));
            $download_code = $download_code['code'];
            $download_link = "https://www.billingo.hu/access/c:{$download_code}";
        } catch (\Exception $e) {
            $error_message = 'Billingo Error with download link: ' . $e->getMessage();
            error_log($error_message);
            $this->log($error_message);
            return array('error' => $error_message);
        }

        return $download_link;
    }

    private function log($message)
    {
        if ($this->logger === false) return false;

        $this->logger->log('DEBUG INFO: ' . $this->debug_info . ' MESSAGE: ' . $message, LOG_DEBUG);
    }
}
