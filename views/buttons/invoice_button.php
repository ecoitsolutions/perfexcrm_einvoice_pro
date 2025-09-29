<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<a href="<?= admin_url('einvoice_pro/download/' . $invoice->id); ?>" class="btn btn-default">
    <i class="fa-solid fa-file-code"></i> <?= _l('e_invoice_button_text'); ?>
</a>
