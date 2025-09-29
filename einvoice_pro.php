<?php
defined('BASEPATH') or exit('No direct script access allowed');

/*
Module Name: E-Invoice Pro
Description: Minimalist module for generating RO-UBL compatible e-invoices.
Version: 1.4.3
Author: Lucian Lazar 
*/

// Înregistrează fișierele de limbă
register_language_files('einvoice_pro', ['einvoice_pro']);

hooks()->add_action('activate_einvoice_pro_module', 'einvoice_pro_module_activation_hook');
function einvoice_pro_module_activation_hook(): void
{
    add_option('einvoice_pro_registration_number', '');
    add_option('einvoice_pro_payment_iban', '');
    add_option('einvoice_pro_payment_bank_name', '');
    add_option('einvoice_pro_company_legal_form', '200');
    add_option('einvoice_pro_note_1', 'TVA la incasare');
    add_option('einvoice_pro_note_2', 'Factura este valabila fara semnatura si stampila, conform art. 319 alin. 29 din legea 227/2015');
    add_option('einvoice_pro_note_3', 'Modalitate plata -OP Bancar');
    
    // Setarea pentru limba XML-ului, cu valoare implicită 'romanian'
    add_option('einvoice_pro_xml_language', 'romanian');
}

hooks()->add_action('admin_init', 'einvoice_pro_module_init');
function einvoice_pro_module_init(): void
{
    $CI = &get_instance();
    $CI->app->add_settings_section_child(
        'finance', 
        'einvoice_pro', 
        [
            'name'     => _l('e_invoice_pro'),
            'view'     => 'einvoice_pro/settings/settings',
            'position' => 45,
        ]
    );
}

hooks()->add_action('before_invoice_preview_more_menu_button', 'einvoice_pro_invoice_button');
function einvoice_pro_invoice_button($invoice): void
{
    $ci = &get_instance();
    echo $ci->load->view('einvoice_pro/buttons/invoice_button', ['invoice' => $invoice], true);
}

hooks()->add_filter('module_einvoice_pro_action_links', 'einvoice_pro_module_action_links');
function einvoice_pro_module_action_links(array $actions): array
{
    $settings_link = '<a href="' . admin_url('settings?group=einvoice_pro') . '">' . _l('settings') . '</a>';
    array_unshift($actions, $settings_link);
    return $actions;
}
