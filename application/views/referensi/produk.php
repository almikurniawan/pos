<div class="row">
    <div class="col-sm-12">
        <div class="card card-primary">
            <div class="card-body">
                <h3 class="card-title">
                    Produk
                </h3>
                <a href="<?= base_url("referensi/produk/add")?>" class="mb-3 btn btn-sm btn-primary"><i class="k-icon k-i-plus"></i> Tambah Produk</a>
                <a href="<?= base_url("referensi/produk/import")?>" class="mb-3 btn btn-sm btn-success"><i class="k-icon k-i-upload"></i> Import Produk</a>
                <?= $search . $grid?>
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
