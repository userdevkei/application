<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Print Invoice</title>
</head>
<body>
<iframe id="pdfFrame" src="{{ asset($pdfPath) }}" style="width: 100%; height: 100vh;" frameborder="0"></iframe>

<script type="text/javascript">
    window.onload = function() {
        document.getElementById('pdfFrame').contentWindow.focus();
        document.getElementById('pdfFrame').contentWindow.print();
    }
</script>
</body>
</html>
