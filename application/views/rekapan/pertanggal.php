<div id="dialog"></div>
<div class="row">
    <div class="col-sm-12">
        <div class="card card-primary">
            <div class="card-body">
                <h3 class="card-title">
                    Rekapan per tanggal
                </h3>
                <?= $search?>
                <ul class="nav nav-tabs" id="myTab" role="tablist">
                    <li class="nav-item" role="presentation">
                        <a class="nav-link active" id="home-tab" data-toggle="tab" href="#home" role="tab" aria-controls="home" aria-selected="true">Penjualan</a>
                    </li>
                    <li class="nav-item" role="presentation">
                        <a class="nav-link" id="profile-tab" data-toggle="tab" href="#profile" role="tab" aria-controls="profile" aria-selected="false">Return</a>
                    </li>
                </ul>
                <div class="tab-content pt-3" id="myTabContent">
                    <div class="tab-pane fade show active" id="home" role="tabpanel" aria-labelledby="home-tab"><?= $grid?></div>
                    <div class="tab-pane fade" id="profile" role="tabpanel" aria-labelledby="profile-tab"><?= $return?></div>
                </div>
            </div>
        </div>
    </div>
</div>
<script>
function detail_pos(pos_id) {
    $("#dialog").kendoWindow({
        content: '<?= base_url("rekapan/detail_penjualan/")?>'+pos_id,
        width: "700px",
        height: "500px",
        // modal: true,
        iframe: true,
        title: "Detail Penjualan",
    });
    
    var popup = $("#dialog").data('kendoWindow');
    popup.open();
    popup.center();
}
function detail_return(return_id) {
    $("#dialog").kendoWindow({
        content: '<?= base_url("rekapan/detail_return/")?>'+return_id,
        width: "700px",
        height: "500px",
        // modal: true,
        iframe: true,
        title: "Detail Return",
    });
    
    var popup = $("#dialog").data('kendoWindow');
    popup.open();
    popup.center();
}
</script>