<div class="row">
    <div class="col-sm-12">
        <div class="card card-primary">
            <div class="card-body">
                <h3 class="card-title">
                    Import Produk
                </h3>
                <a target="_new" href="<?= base_url("referensi/produk/download")?>" class="mb-3 btn btn-sm btn-success"><i class="k-icon k-i-download"></i> Download Produk</a>
                <?= $form?>
            </div>
        </div>
    </div>
</div>
<script>
function delete_produk(id) {
    confirm_delete('<?= base_url('referensi/produk/delete')?>',id, function (result) {
        gridReload();
    });
}
</script>
