<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Funcție de escape pentru XML care este sigură și pentru valorile nule.
 */
function xml_escape($string) {
    $string = is_null($string) ? '' : (string) $string;
    return htmlspecialchars($string, ENT_XML1, 'UTF-8');
}

/**
 * Funcția principală care generează toate datele necesare pentru template-ul e-factura.
 */
function generate_einvoice_data_for_template($invoice)
{
    // Inițializăm obiectul $CI la începutul funcției, înainte de a fi folosit.
    $CI = &get_instance();
    $CI->load->model('currencies_model');

    // ############ DATE FURNIZOR ############
    $company_vat = get_option('company_vat');
    $company_country = get_country(get_option('invoice_company_country'));
    $company_state_raw = xml_escape(get_option('company_state'));
    $company_state = (strpos($company_state_raw, 'RO-') === 0) ? $company_state_raw : 'RO-' . $company_state_raw;
    
    $supplier = [
        'COMPANY_EMAIL'           => xml_escape(get_option('smtp_email')),
        'COMPANY_ID_NUMBER'       => xml_escape(preg_replace('/[^0-9]/', '', $company_vat)),
        'COMPANY_NAME'            => xml_escape(get_option('invoice_company_name')),
        'COMPANY_ADDRESS'         => xml_escape(get_option('invoice_company_address')),
        'COMPANY_CITY'            => xml_escape(get_option('invoice_company_city')),
        'COMPANY_STATE'           => $company_state,
        'COMPANY_COUNTRY_ISO2'    => $company_country ? xml_escape($company_country->iso2) : 'RO',
        'COMPANY_VAT_NUMBER'      => xml_escape($company_vat),
        'COMPANY_REG_NUMBER'      => xml_escape(get_option('einvoice_pro_registration_number')),
        'COMPANY_LEGAL_FORM'      => 'Capital social: ' . xml_escape(get_option('einvoice_pro_company_legal_form')),
        'COMPANY_CONTACT_NAME'    => xml_escape(get_staff_full_name(get_staff_user_id())),
        'COMPANY_CONTACT_PHONE'   => xml_escape(get_option('invoice_company_phonenumber')),
        'PAYMENT_IBAN'            => xml_escape(get_option('einvoice_pro_payment_iban')),
        'PAYMENT_BANK_NAME'       => xml_escape(get_option('einvoice_pro_payment_bank_name')),
        'PAYMENT_MEANS_CODE'      => '42',
    ];

    // ############ DATE CLIENT ############
    $client_country = get_country($invoice->client->country);
    $client_state_raw = xml_escape($invoice->billing_state);
    $client_state = (strpos($client_state_raw, 'RO-') === 0) ? $client_state_raw : 'RO-' . $client_state_raw;

    $customer = [
        'CUSTOMER_ID'                  => xml_escape($invoice->client->vat),
        'CUSTOMER_NAME'                => xml_escape($invoice->client->company),
        'INVOICE_BILLING_ADRESS'       => xml_escape($invoice->billing_street),
        'INVOICE_BILLING_CITY'         => xml_escape($invoice->billing_city),
        'INVOICE_BILLING_STATE'        => $client_state,
        'INVOICE_BILLING_COUNTRY_ISO2' => $client_country ? xml_escape($client_country->iso2) : 'RO',
        'CUSTOMER_VAT_NUMBER'          => xml_escape($invoice->client->vat),
    ];

    // ############ DATE FACTURĂ ############
    $currency = $CI->currencies_model->get($invoice->currency);
    
    $invoice_notes = [];
    if (get_option('einvoice_pro_note_1')) {
        $invoice_notes[] = ['NOTE' => xml_escape(_l('e_invoice_option_tva'))];
    }
    if (get_option('einvoice_pro_note_2')) {
        $invoice_notes[] = ['NOTE' => xml_escape(_l('e_invoice_option_validity'))];
    }
    if (get_option('einvoice_pro_note_3')) {
        $invoice_notes[] = ['NOTE' => xml_escape(_l('e_invoice_option_payment'))];
    }

    $invoice_details = [
        'INVOICE_ID'         => xml_escape(format_invoice_number($invoice->id)),
        'INVOICE_DATE'       => date('Y-m-d', strtotime($invoice->date)),
        'INVOICE_DUE_DATE'   => date('Y-m-d', strtotime($invoice->duedate)),
        'CURRENCY_CODE'      => $currency ? xml_escape($currency->name) : 'RON',
        'TAX_CURRENCY_CODE'  => $currency ? xml_escape($currency->name) : 'RON',
        'INVOICE_NOTES'      => $invoice_notes,
        'INVOICE_SUBTOTAL'   => number_format($invoice->subtotal, 2, '.', ''),
        'INVOICE_TOTAL_TAX'  => number_format($invoice->total_tax, 2, '.', ''),
        'INVOICE_TOTAL'      => number_format($invoice->total, 2, '.', ''),
        'INVOICE_BALANCE_DUE'=> number_format($invoice->total, 2, '.', ''),
    ];
    
    // ############ LINII FACTURĂ ȘI CALCUL SUBTOTALURI TVA ############
    $line_items = [];
    $tax_subtotals = [];
    foreach ($invoice->items as $item) {
        $item_taxes = get_invoice_item_taxes($item['id']);
        $tax_rate = 0;
        if (!empty($item_taxes)) {
            $tax_rate = $item_taxes[0]['taxrate'];
        }
        $line_items[] = [
            'LINE_ITEM_ORDER'           => $item['item_order'],
            'LINE_ITEM_QUANTITY_NUMBER' => number_format($item['qty'], 3, '.', ''),
            'LINE_ITEM_QUANTITY_UNIT'   => empty($item['unit']) ? 'H87' : xml_escape($item['unit']),
            'LINE_ITEM_TOTAL'           => number_format($item['qty'] * $item['rate'], 2, '.', ''),
            'LINE_ITEM_DESCRIPTION'     => xml_escape($item['long_description']),
            'LINE_ITEM_NAME'            => xml_escape($item['description']),
            'TAX_RATE'                  => number_format($tax_rate, 2, '.', ''),
            'LINE_ITEM_UNIT_PRICE'      => number_format($item['rate'], 4, '.', ''),
        ];
        foreach ($item_taxes as $tax) {
            $current_tax_rate = (float)$tax['taxrate'];
            if (!isset($tax_subtotals[$current_tax_rate])) {
                $tax_subtotals[$current_tax_rate] = [
                    'TAXABLE_AMOUNT' => 0,
                    'TAX_AMOUNT'     => 0,
                    'TAX_RATE'       => number_format($current_tax_rate, 2, '.', ''),
                ];
            }
            $item_base_amount = $item['rate'] * $item['qty'];
            $tax_subtotals[$current_tax_rate]['TAXABLE_AMOUNT'] += $item_base_amount;
            $tax_subtotals[$current_tax_rate]['TAX_AMOUNT'] += ($item_base_amount / 100) * $current_tax_rate;
        }
    }
    
    $final_subtotals = [];
    foreach($tax_subtotals as $subtotal) {
        $final_subtotals[] = [
            'TAXABLE_AMOUNT' => number_format($subtotal['TAXABLE_AMOUNT'], 2, '.', ''),
            'TAX_AMOUNT'     => number_format($subtotal['TAX_AMOUNT'], 2, '.', ''),
            'TAX_RATE'       => $subtotal['TAX_RATE'],
        ];
    }

    return array_merge($supplier, $customer, $invoice_details, ['LINE_ITEMS' => $line_items, 'TAX_SUBTOTALS' => $final_subtotals]);
}
