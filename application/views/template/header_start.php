<!DOCTYPE html>
<html lang="en">

<head>
	<title>Kang Bayar</title>
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
		if(isNaN(number)){
			number = 0;
		}
		return number.toString().replace(/(\d)(?=(\d{3})+(?!\d))/g, '$1.')
	}
	</script>

</head>

<body>
<div class="container-fluid">
    <div class="row min-vh-100 flex-column flex-md-row">
        <aside class="col-12 col-md-2 p-0 flex-shrink-1" style="background:#4a89dc;">
            <nav class="navbar navbar-expand navbar-white bg-white flex-md-column flex-row align-items-start">
                <div class="collapse navbar-collapse w-100">
					<div id="jquery-accordion-menu" class="jquery-accordion-menu blue">
						<div class="jquery-accordion-menu-header">Kang Bayar <span style="font-size:12px; color:white;">Pro v1</span></div>
						<ul>
							<?= $menu?>
						</ul>
					</div>
                </div>
            </nav>
        </aside>
        <main class="col bg-faded py-3 flex-grow-1 main">