<div class="row mb-2">
    <div class="col-sm-6">
        <?php
        if(!empty($head_left)){
            foreach($head_left as $key=> $value){
                if($key=='add')
                    echo '<a class="btn btn-sm btn-primary" href="'.$value.'"><i class="k-icon k-i-plus"></i> Tambah Data</a>';
                
            }
        }
        ?>
    </div>
</div>
<div class="row">
    <div class="col-sm-12">
        <div id="grid_<?= $grid_name?>"></div>
    </div>
</div>

<script type="text/javascript">
    var ds_<?= $datasouce_name?>;
    var grid_<?= $grid_name?>;

    function <?= $gridReload?>{        
        ds_<?= $datasouce_name?>.read();
    }

    $(document).ready(function ($) {
        ds_<?= $datasouce_name?> = new kendo.data.DataSource({
            pageSize: <?= $pageSize?>,
            serverAggregates: true,
            serverFiltering: true,
            serverGrouping: true,
            serverPaging: true,
            serverSorting: true,
            autoSync: true,
            schema: {
                total: "total",
                data: "result",
                aggregates: "aggregate",
                errors: "error",
                groups: "group",
                model: {
                    id: "id",
                    fields: <?= json_encode($fields)?>,
                },
                type: "json"
            },
            transport: {
                read: {
                    url: "<?= $datasouce_url?>",
                data: {
                    wSQL: 1,
                    q: function () {
                        return $("#elh_sgk694_searchbox").val();
                    }
                },
                dataType: "json"
            }
        }
    });

    grid_<?= $grid_name?> = $("#grid_<?= $grid_name?>").kendoGrid({
        dataSource: ds_<?= $datasouce_name?>,
        // height: "<?= $grid_height?>",
        scrollable : false,
        groupable: true,
        sortable: {
            mode: "multiple",
            allowUnsort: true,
            showIndexes: true
        },
        selectable: "row",
        pageable: {
            refresh: true,
            pageSizes: true,
            buttonCount: 5
        },
        columns: <?= json_encode($grid_columns)?>
    });
});
</script>