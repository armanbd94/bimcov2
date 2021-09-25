<script src="js/config.js" type="text/javascript"></script>
<script src="css/global/plugins.bundle.js" type="text/javascript"></script>
<script src="js/datatables.bundle.js" type="text/javascript"></script>
<script src="js/scripts.bundle.js" type="text/javascript"></script>
<script src="js/custom.js" type="text/javascript"></script>
<script>
    var _token = "{{ csrf_token() }}";
    $(document).ready(function(){
        <?php 
        if (session('status')){
        ?>
        notification("{{session('status')}}","{{session('message')}}");
        <?php
        }
        ?>
        <?php 
        if (session('success')){
        ?>
        notification("success","{{session('success')}}");
        <?php
        }
        ?>
        <?php 
        if (session('error')){
        ?>
        notification("error","{{session('message')}}");
        <?php
        }
        ?>
    });
</script>
@stack('scripts') <!-- Load Scripts Dynamically -->
