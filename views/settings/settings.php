<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>

<?php echo form_open(admin_url('settings/update'), ['id' => 'einvoice-pro-settings-form']); ?>
<div class="panel_s">
    <div class="panel-body">
        <h4 class="no-margin">
            <?= _l('e_invoice_settings_title'); ?>
        </h4>
        <hr class="hr-panel-heading" />

        <?php
        // Selector pentru limba XML
        $language_options = [
            ['id' => 'romanian', 'name' => 'Română'],
            ['id' => 'english', 'name' => 'Engleză'],
        ];
        // CORECȚIE AICI: Am înlocuit textul fix cu funcția de traducere
        echo render_select('settings[einvoice_pro_xml_language]', $language_options, ['id', 'name'], _l('e_invoice_xml_language'), get_option('einvoice_pro_xml_language'));
        
        echo '<hr class="hr-panel-heading" />';

        echo render_input('settings[einvoice_pro_registration_number]', _l('e_invoice_reg_number'), get_option('einvoice_pro_registration_number'), 'text', ['placeholder' => _l('e_invoice_reg_number_placeholder')]);
        
        echo render_input('settings[einvoice_pro_company_legal_form]', _l('e_invoice_capital_social'), get_option('einvoice_pro_company_legal_form'), 'number');

        echo '<hr class="hr-panel-heading" />';

        echo render_input('settings[einvoice_pro_payment_iban]', _l('e_invoice_iban'), get_option('einvoice_pro_payment_iban'));

        echo render_input('settings[einvoice_pro_payment_bank_name]', _l('e_invoice_bank_name'), get_option('einvoice_pro_payment_bank_name'));
        
        echo '<hr class="hr-panel-heading" />';
        
        // Note...
        $note1_options = [
            ['id' => '', 'name' => _l('e_invoice_option_none')],
            ['id' => 'TVA la incasare', 'name' => _l('e_invoice_option_tva')],
        ];
        echo render_select('settings[einvoice_pro_note_1]', $note1_options, ['id', 'name'], _l('e_invoice_note1'), get_option('einvoice_pro_note_1'));

        $note2_options = [
            ['id' => '', 'name' => _l('e_invoice_option_none')],
            ['id' => 'Factura este valabila fara semnatura si stampila, conform art. 319 alin. 29 din legea 227/2015', 'name' => _l('e_invoice_option_validity')],
        ];
        echo render_select('settings[einvoice_pro_note_2]', $note2_options, ['id', 'name'], _l('e_invoice_note2'), get_option('einvoice_pro_note_2'));

        $note3_options = [
            ['id' => '', 'name' => _l('e_invoice_option_none')],
            ['id' => 'Modalitate plata -OP Bancar', 'name' => _l('e_invoice_option_payment')],
        ];
        echo render_select('settings[einvoice_pro_note_3]', $note3_options, ['id', 'name'], _l('e_invoice_note3'), get_option('einvoice_pro_note_3'));
        ?>

    </div>
</div>
<?php echo form_close(); ?>
