<?php
$this->load->view('template/header');
?>
<div class="panel panel-primary">
	<div class="panel-heading">
		<h3 class="panel-title">
			Master User
		</h3>
	</div>
	<div class="panel-body">
		<div class="row" style="margin-bottom:15px;">
			<div class="col-sm-12">
				<button class="btn btn-sm btn-primary" onclick="add()"><i class="k-icon k-i-plus"></i> Tambah</button>
			</div>
		</div>
		<?= $grid ?>

	</div>

</div>
</div>
<script lenguage="javascript">
	function add() {
		showForm(600, 400, '_new', "<?= base_url('user/add') ?>");
	}
	function btnEdit(id) {
		showForm(600, 400, '_new', "<?= base_url('user/add/') ?>" + id+"?sType=edit");
	}
	function btnDelete(id) {
		showForm(600, 400, '_new', "<?= base_url('user/add/') ?>" + id+"?sType=delete");
	}
</script>
<?php
$this->load->view('template/footer');
?>