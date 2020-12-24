
<div class="form-row">
    <div class="form-group col-sm-4">
        <label class="<?= $field['nama_barang']['class']?>"><?= $field['nama_barang']['title']?></label>
        <?= $field['nama_barang']['field']?>
    </div>
    <div class="form-group col-sm-4">
        <label class="<?= $field['jumlah_barang_manual']['class']?>"><?= $field['jumlah_barang_manual']['title']?></label>
        <?= $field['jumlah_barang_manual']['field']?>
    </div>
    <div class="form-group col-sm-2 pt-4">
        <?= $submit_btn?>
    </div>
</div>