<style>
</style>
<div class="row">
    <div class="col-sm-12">
        <div class="card card-primary">
            <div class="card-body">
                <h3 class="card-title">
                    Buka Kasir
                </h3>
                <div class="row">
                <?php
                foreach ($kasir as $key => $value) {
                    echo '<div class="col-3"><div class="card"><img src="'.base_url('assets/images/kasir.png').'" class="card-img-top" alt="..."><div class="card-body"><h5 class="card-title">'.$value['kasir_label'].'</h5><input name="kasir_'.$value['kasir_id'].'" class="number mb-1" type="text"/><button type="button" onclick="buka_kasir(\'kasir_'.$value['kasir_id'].'\',\''.$value['kasir_id'].'\')" class="btn btn-sm btn-primary"><i class="k-icon k-i-login"></i> Buka Kasir</button></div></div></div>';
                }
                ?>
                </div>
            </div>
        </div>
    </div>
</div>
<script>
$(document).ready(function(){
    $(".number").kendoNumericTextBox({decimals:0,format:"n0"});
});

function buka_kasir(kasir, kasir_id) {
    let nominal = $('input[name='+kasir+']').val();
    if(nominal==''){
        alert('Masukan Nominal Uang Kas');

    }else{
        $.post('<?= base_url('home/buka_kasir')?>', {
            'kasir_id'  : kasir_id,
            'nominal'   : nominal
        }, function(result) {
            if(result.status){
                window.location.href = '<?=  base_url('home/')?>';
            }
        },'json');
    }

}
</script>
