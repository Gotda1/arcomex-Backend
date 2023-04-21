<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>
<body style="background-color: #ddd; font-family: 'Gill Sans', 'Gill Sans MT', Calibri, 'Trebuchet MS', sans-serif; font-size: 2rem;">
    <div style="width: 540px; margin:0 auto; padding:30px; text-align:center">
        <h2 style="font-size: 1.8rem; color:#7A5747">Pedido eliminado</h2>

        <img src="{{ env('FRONT_URL')}}/assets/images/arcomex.png" alt="" style="width: 250px;">
        
        <p style="text-align: center; font-size:1.5rem">
            El pedido con el folio {{ $folio }} ha sido <strong>ELIMINADO</strong> <br> <br>

            <a style="color:#fff; padding:10px; text-decoration:none; background-color: #7A5747;" href="{{ env('FRONT_URL') }}/#/pedidos">
                Ir a módulo de pedidos
            </a>
        </p>

        <hr>

        <p style="color: #444; font-size: 20px; line-height: 20px;">
            {{ $datosempresa->nombre}} {{ $datosempresa->direccion_1}} {{ $datosempresa->direccion_2}} <br>
            {{ $datosempresa->telefonos }} | {{ $datosempresa->web }}
        </p>
    </div>
</body>
</html>