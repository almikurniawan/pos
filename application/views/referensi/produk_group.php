<div class="row">
    <div class="col-sm-12">
        <div class="card card-primary">
            <div class="card-body">
                <h3 class="card-title">
                    Kategori Produk
                </h3>
                <?= $grid?>
            </div>
        </div>
    </div>
</div>
<script>
function delete_kategori(id) {
    confirm_delete('<?= base_url('referensi/produk_group/delete')?>',id, function (result) {
        gridReload();
    });
}
</script>
