<form action="{{ $TPV->getPath('/realizarPago') }}" method="post">
    {!! $TPV->getFormHiddens() !!}
</form>
<script>
    document.forms[0].submit();
</script>