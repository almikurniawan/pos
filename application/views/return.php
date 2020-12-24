<div id="dialog"></div>
<div class="row">
    <div class="col-sm-8">
        <div class="card card-primary">
            <div class="card-body">
                <h3 class="card-title">
                    Return Penjualan
                </h3>
                <?= $form?>
                <div id="alert"></div>

                <div class="row mt-3">
                    <div class="col-6">
                        <div id="total"><h4>Rp. <?= number_format($pos_master['pos_total'],0,',','.')?></h4></div>
                    </div>
                </div>
                <?php if($this->session->flashdata('warning')){ ?>
                    <div class="alert alert-warning" role="alert">
                        <?= $this->session->flashdata('warning')?>
                    </div>
                <?php }?>
                <form method="get" action="<?= base_url("returnOrder/process")?>">
                    <input type="hidden" name="order_id" value="<?= $this->input->get('order_id')?>"/>
                    <?= $grid?>
                    <button class="btn btn-warning mt-3">Return</button>
                </form>
            </div>
        </div>
    </div>
    <div class="col-sm-4">
        <div class="card card-primary">
            <div class="card-body">
                <h3 class="card-title">
                    Info Uang
                </h3>
                <table class="table">
                    <tr>
                        <td width="130">
                            Waktu Buka
                        </td>
                        <td>
                            <span id="waktu_buka">0</span>
                        </td>
                    </tr>
                    <tr>
                        <td width="130">
                            Modal
                        </td>
                        <td>
                            Rp. <span id="modal">0</span>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            Tunai
                        </td>
                        <td>
                            Rp. <span id="tunai">0</span>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            Non Tunai
                        </td>
                        <td>
                            Rp. <span id="non_tunai">0</span>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            Return
                        </td>
                        <td>
                            Rp. <span id="return">0</span>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            Total Uang
                        </td>
                        <td>
                            Rp. <span id="total_kasir">0</span>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            Total Uang Tunai
                        </td>
                        <td>
                            Rp. <span id="total_tunai">0</span>
                        </td>
                    </tr>
                </table>
                <button onclick="tutup_kasir()" class="btn btn-success btn-sm w-100">Tutup Kasir / Closing</button>
            </div>
        </div>
    </div>
</div>
<script>
    $(document).ready(function($){
        get_data_kasir();
        $('form[name=penjualan_auto]').submit(function(event){
            event.preventDefault();
            let data = $(this).serialize();
            $.post("<?= base_url('home/add_barang')?>", data, function(result){
                if(!result.status){
                    if(result.flag=='error'){
                        $('#alert').html('<div class="alert alert-danger" role="alert">'+result.message+'</div>');
                    }else if(result.flag=='warning'){
                        $('#alert').html('<div class="alert alert-warning" role="alert">'+result.message+'</div>');
                    }else{
                        $('#alert').html('');
                    }
                }else{
                    $('#alert').html('');
                }
                $('#barcode').val('');
                $('#barcode').focus();
                gridReload();
                $('#total').html('<h4>Rp. '+formatNumber(result.data.pos_total)+'</h4>');
            },'json');
        })

        $('form[name=penjualan_manual]').submit(function(event){
            event.preventDefault();
            let data = $(this).serialize();
            $.post("<?= base_url('home/add_barang_manual')?>", data, function(result){
                if(!result.status){
                    if(result.flag=='error'){
                        $('#alert').html('<div class="alert alert-danger" role="alert">'+result.message+'</div>');
                    }else if(result.flag=='warning'){
                        $('#alert').html('<div class="alert alert-warning" role="alert">'+result.message+'</div>');
                    }else{
                        $('#alert').html('');
                    }
                }else{
                    $('#alert').html('');
                }
                $('#nama_barang').val('');
                $('#nama_barang').focus();
                gridReload();
                $('#total').html('<h4>Rp. '+formatNumber(result.data.pos_total)+'</h4>');
            },'json');
        });
    })

    function tambah(pos_det_id){
        $.post("<?= base_url('home/update')?>", {
            'pos_det_id' : pos_det_id,
            'jumlah'    : 1
        }, function(result){
            gridReload();
            get_total();
        });
    }

    function kurang(pos_det_id){
        $.post("<?= base_url('home/update')?>", {
            'pos_det_id' : pos_det_id,
            'jumlah'    : -1
        }, function(result){
            gridReload();
            get_total();
        });
    }

    function delete_pos_detail(id){
        confirm_delete("<?= base_url('home/delete')?>",id, function (result) {
            gridReload();
            $('#barcode').focus();
            get_total();
        });
    }

    function get_total(){
        $.get("<?= base_url('home/get_total')?>",{}, function(result){
            $('#total').html('<h4>Rp. '+formatNumber(result.pos_total)+'</h4>');
        },'json');
    }

    function open_dialog(){
        $("#dialog").kendoWindow({
            content: '<?= base_url("home/bayar")?>',
            width: "400px",
            height: "300px",
            // modal: true,
            iframe: true,
            title: "Form Bayar",
        });
        
        var popup = $("#dialog").data('kendoWindow');
        popup.open();
        popup.center();
    }

    function tutup_kasir(){
        if(confirm("Yakin ingin tutup kasir?")){
            $.get('<?= base_url("home/tutup_kasir")?>', {}, function(result){
                if(result.status){
                    window.location.reload();
                }
            },'json');
        }
    }

    function get_data_kasir(){
        $.get('<?= base_url("home/data_kasir")?>', {}, function(result){
            let total_penjualan = parseFloat(result.kasir_log_total_penjualan) || 0;
            let total_return = parseFloat(result.kasir_log_total_return) || 0;
            let modal = parseFloat(result.kasir_log_cash_in) || 0;
            let total_non_tunai = parseFloat(result.kasir_log_total_penjualan_non_tunai) || 0;
            let total_tunai = ( total_penjualan + modal - total_return);
            
            let total = (total_penjualan + modal + total_non_tunai - total_return);
            $('#modal').html(formatNumber(result.kasir_log_cash_in));
            $('#tunai').html(formatNumber(result.kasir_log_total_penjualan));
            $('#non_tunai').html(formatNumber(result.kasir_log_total_penjualan_non_tunai));
            $('#return').html(formatNumber(result.kasir_log_total_return));
            $('#total_kasir').html(formatNumber(total));
            $('#total_tunai').html(formatNumber(total_tunai));
            $('#waktu_buka').html(result.kasir_log_waktu_buka);
            
            
        },'json');
    }
</script>