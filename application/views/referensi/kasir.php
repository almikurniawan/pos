<div class="row">
    <div class="col-sm-12">
        <div class="card card-primary">
            <div class="card-body">
                <h3 class="card-title">
                    Kasir
                </h3>
                <?= $grid?>
            </div>
        </div>
    </div>
</div>
<script>
function delete_kasir(id) {
    confirm_delete('<?= base_url('referensi/kasir/delete')?>',id, function (result) {
        gridReload();
    });
}
</script>
