<?= $form?>
<script>
    $(document).ready(function(){
        
        setTimeout(function() {
            $('#nominal').siblings('input:visible').focus();
            var numerictextbox = $("#nominal").data("kendoNumericTextBox");
            if(typeof numerictextbox!="undefined"){
                numerictextbox.focus();
            }else{
                var numerictextbox = window.parent.$("#nominal").data("kendoNumericTextBox");
                numerictextbox.focus();
            }
        }, 300);
    });
</script>