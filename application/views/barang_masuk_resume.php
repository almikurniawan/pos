<div class="row">
    <div class="col-sm-12">
        <div class="card card-primary">
            <div class="card-body">
                <h3 class="card-title">
                    Resume Barang Masuk
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
                            <div class="row">
                                <div class="col-12">
                                    <?php if($this->session->flashdata('success')){ ?>
                                        <div class="alert alert-success mt-3" role="alert">
                                            <?= $this->session->flashdata('success')?>
                                        </div>
                                    <?php }?>
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