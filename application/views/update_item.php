<?= $form?>
<script>
    $(document).ready(function(){
        $('#entered_item_jumlah').siblings('input:visible').focus();
        var numerictextbox = $("#entered_item_jumlah").data("kendoNumericTextBox");
        if(typeof numerictextbox!="undefined"){
            numerictextbox.focus();
        }else{
            var numerictextbox = window.parent.$("#entered_item_jumlah").data("kendoNumericTextBox");
            numerictextbox.focus();
        }
    })
</script>