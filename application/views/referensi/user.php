<div class="row">
    <div class="col-sm-12">
        <div class="card card-primary">
            <div class="card-body">
                <h3 class="card-title">
                    User
                </h3>
                <?= $grid?>
            </div>
        </div>
    </div>
</div>
<script>
function delete_user(id) {
    confirm_delete('<?= base_url('referensi/user/delete')?>',id, function (result) {
        gridReload();
    });
}
</script>
