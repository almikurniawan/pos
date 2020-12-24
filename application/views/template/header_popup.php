<!DOCTYPE html>
<html lang="en">

<head>
	<title>Juru Bayar</title>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">

	<link href="<?= base_url("/assets/kendo/styles/kendo.common-bootstrap.min.css")?>" rel="stylesheet" />
	<link href="<?= base_url("/assets/kendo/styles/kendo.default.min.css")?>" rel="stylesheet" />
	<link href="<?= base_url("/assets/kendo/styles/kendo.bootstrap-v4.min.css")?>" rel="stylesheet" />

	<link href="<?= base_url("/assets/bootstrap/css/bootstrap.min.css")?>" rel="stylesheet">
	<link href="<?= base_url("/assets/default-style.css")?>" rel="stylesheet">
	<!-- <link href="<?= base_url("/assets/theme/fiori/kendo.custom.css")?>" rel="stylesheet"> -->
	<link href="<?= base_url("/assets/style-thin.css")?>" rel="stylesheet">

	<script src="<?= base_url("/assets/kendo/js/jquery.min.js")?>"></script>
	<script src="<?= base_url("/assets/kendo/js/kendo.web.min.js")?>"></script>
	<script src="<?= base_url("/assets/kendo/js/cultures/kendo.culture.id-ID.min.js")?>"></script>

	<script src="<?= base_url("/assets/bootstrap/js/bootstrap.min.js")?>"></script>
	<script src="<?= base_url("/assets/app.js")?>"></script>
	<script>
	function confirm_delete(url, id, clb) {
		if(confirm("Yakin ingin menghapus data ini?")){
			$.post(url, {
				'id' : id
			}, function(result) {
				clb(result);
			},'json');
		}
	}

	function formatNumber(num) {
		let number = num ?? 0;
		return number.toString().replace(/(\d)(?=(\d{3})+(?!\d))/g, '$1.')
	}
	</script>

</head>

<body>
	<div class="container mt-3">
		<?php
			$this->load->view($view, $data);
		?>
	</div>
</body>
</html>	
