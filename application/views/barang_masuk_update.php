<div id="dialog"></div>
<div class="row">
    <div class="col-sm-12">
        <div class="card card-primary">
            <div class="card-body">
                <h3 class="card-title">
                    Form Barang Masuk
                </h3>
                <div class="tab-pane fade show active" id="profile" role="tabpanel" aria-labelledby="profile-tab">
                    <ul class="nav nav-tabs" id="myTab" role="tablist">
                        <li class="nav-item" role="presentation">
                            <a class="nav-link" id="home-tab" data-toggle="tab" href="#header" role="tab" aria-controls="home" aria-selected="true">Header</a>
                        </li>
                        <li class="nav-item" role="presentation">
                            <a class="nav-link active" id="profile-tab" data-toggle="tab" href="#detail" role="tab" aria-controls="profile" aria-selected="false">Detail</a>
                        </li>
                    </ul>
                    <div class="tab-content pt-3" id="myTabContent">
                        <div class="tab-pane fade" id="header" role="tabpanel" aria-labelledby="otomatis-tab">
                            <?= $form?>
                        </div>
                        <div class="tab-pane fade active show" id="detail" role="tabpanel" aria-labelledby="manual-tab">
                            <div class="tab-pane fade show active" id="profile" role="tabpanel" aria-labelledby="profile-tab">
                                <ul class="nav nav-pills" id="myTab" role="tablist">
                                    <li class="nav-item" role="presentation">
                                        <a class="nav-link active" id="home-tab" data-toggle="tab" href="#otomatis" role="tab" aria-controls="home" aria-selected="true">Barcode</a>
                                    </li>
                                    <li class="nav-item" role="presentation">
                                        <a class="nav-link" id="profile-tab" data-toggle="tab" href="#manual" role="tab" aria-controls="profile" aria-selected="false">Manual</a>
                                    </li>
                                </ul>
                                <?php if($this->session->flashdata('error')){ ?>
                                    <div class="alert alert-danger mt-3" role="alert">
                                        <?= $this->session->flashdata('error')?>
                                    </div>
                                <?php }?>
                                <div class="tab-content pt-2" id="myTabContent">
                                    <div class="tab-pane fade show active" id="otomatis" role="tabpanel" aria-labelledby="otomatis-tab">
                                        <?= $form_detail?>
                                    </div>
                                    <div class="tab-pane fade" id="manual" role="tabpanel" aria-labelledby="manual-tab">
                                        <?= $form_detail_manual?>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-12">
                                        <?php if($this->session->flashdata('success')){ ?>
                                            <div class="alert alert-success mt-3" role="alert">
                                                <?= $this->session->flashdata('success')?>
                                            </div>
                                        <?php }?>
                                    </div>
                                    <div class="col-12">
                                        <a href="<?= base_url('barangMasuk/proses/'.$id)?>" class="btn btn-success btn-sm float-right"><i class="k-icon k-i-success"></i> Proses</a>
                                    </div>
                                </div>
                                <?= $grid?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
</div>

<script>
    $(document).ready(function ($) {
        $('#barcode').focus();
    })

    function delete_item(id) {
        confirm_delete('<?= base_url('barangMasuk/deleteItem')?>',id, function (result) {
            if(result.status){
                gridReload();
            }
        });
    }

    function update_item(id) {
        $("#dialog").kendoWindow({
            content: '<?= base_url("barangMasuk/update_item/")?>'+id,
            width: "400px",
            height: "300px",
            // modal: true,
            iframe: true,
            title: "Form Edit",
        });
        
        var popup = $("#dialog").data('kendoWindow');
        popup.open();
        popup.center();
    }
</script>