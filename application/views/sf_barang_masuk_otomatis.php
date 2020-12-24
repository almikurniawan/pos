
<div class="form-row">
    <div class="form-group col-sm-4">
        <label class="<?= $field['barcode']['class']?>"><?= $field['barcode']['title']?></label>
        <?= $field['barcode']['field']?>
    </div>
    <div class="form-group col-sm-4">
        <label class="<?= $field['jumlah_barang']['class']?>"><?= $field['jumlah_barang']['title']?></label>
        <?= $field['jumlah_barang']['field']?>
    </div>
    <div class="form-group col-sm-2 pt-4">
        <?= $submit_btn?>
    </div>
</div>