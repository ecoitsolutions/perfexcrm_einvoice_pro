<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Einvoice_pro extends AdminController
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('invoices_model');
        $this->load->helper('einvoice_pro');
    }

    public function download($invoice_id)
    {
        if (!$invoice_id) {
            show_404();
        }

        $invoice = $this->invoices_model->get($invoice_id);

        if (!$invoice) {
            show_404();
        }

        // Încarcă fișierul de limbă selectat în setări
        $xml_lang = get_option('einvoice_pro_xml_language');
        if ($xml_lang) {
            $this->lang->load('einvoice_pro', $xml_lang);
        }

        // Generează numele fișierului XML
        $invoice_number = format_invoice_number($invoice->id);
        $file_name = str_replace('/', '-', $invoice_number) . '.xml';

        // Obține toate datele formatate corect din helper
        $data['invoice_data'] = generate_einvoice_data_for_template($invoice);

        // Setează headerele pentru a forța descărcarea
        header('Content-Type: application/xml; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $file_name . '"');

        // Încarcă și afișează template-ul XML cu datele pregătite
        $this->load->view('einvoice_pro/xml/template', $data);
    }
}
