<div class="row">
    <div class="col-12">
        <h2>Jika Tidak Tercetak Silahkan Tekan Reprint</h2>
    </div>
    <div class="col-12">
        <h4><?= $message?></h4>
    </div>
</div>
<div class="row">
    <div class="col-12">
        <button onclick="reprint()" class="btn btn-warning">Reprint</button>
        <button onclick="new_order()" class="btn btn-success">New Order</button>
    </div>
</div>
<script>
    function reprint(){
        $.get('<?= base_url("home/reprint")?>',{}, function(result){

        }, 'json');
    }

    function new_order(){
        $.get('<?= base_url("home/new_order")?>',{}, function(result){
            if(result.status){
                window.parent.get_data_kasir(); window.parent.$("#barcode").focus(); window.parent.gridReload(); window.parent.get_total(); window.parent.$("#dialog").data("kendoWindow").close();
            }
        }, 'json');
    }
</script>