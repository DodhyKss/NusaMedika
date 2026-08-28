<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cetak SOAP - {{ $emr_id }}</title>
    <style>
        body { font-family: Arial, sans-serif; padding: 20px; max-width: 800px; margin: 0 auto; }
        @media print {
            body { padding: 0; }
        }
    </style>
</head>
<body onload="setTimeout(() => { window.print(); window.close(); }, 500);">
    @include('moduls.EMR.Soap.CetakSoap', ['emr_id' => $emr_id])
</body>
</html>
