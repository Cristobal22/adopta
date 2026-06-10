<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Contrato de Adopción Responsable Firmado</title>
    <style>
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            color: #1e293b;
            line-height: 1.6;
            padding: 40px;
            max-width: 800px;
            margin: 0 auto;
        }
        .header {
            text-align: center;
            border-bottom: 2px solid #10b981;
            padding-bottom: 20px;
            margin-bottom: 30px;
        }
        .header h1 {
            margin: 0 0 10px 0;
            font-size: 24px;
            color: #065f46;
        }
        .certification-seal {
            background-color: #f0fdf4;
            border: 1px solid #a7f3d0;
            color: #047857;
            padding: 12px;
            border-radius: 8px;
            font-size: 13px;
            margin-bottom: 25px;
            text-align: center;
        }
        .meta-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
        }
        .meta-table th, .meta-table td {
            border: 1px solid #e2e8f0;
            padding: 10px 12px;
            text-align: left;
        }
        .meta-table th {
            background-color: #f8fafc;
            font-weight: bold;
            width: 30%;
        }
        .clauses h3 {
            color: #065f46;
            margin-top: 25px;
            border-bottom: 1px solid #e2e8f0;
            padding-bottom: 5px;
        }
        .clauses p {
            font-size: 14px;
            text-align: justify;
        }
        .signatures {
            margin-top: 50px;
            display: flex;
            justify-content: space-between;
        }
        .signature-block {
            width: 45%;
            text-align: center;
            border-top: 1px solid #94a3b8;
            padding-top: 15px;
            font-size: 14px;
        }
        .signature-img {
            max-height: 80px;
            margin-bottom: 5px;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>CONTRATO DE ADOPCIÓN DE MASCOTA - FIRMADO DIGITALMENTE</h1>
        <p>Ecosistema Integral de Rescate y Adopción Animal - Adopta.</p>
    </div>

    <div class="certification-seal">
        <strong>✓ Documento Firmado Electrónicamente</strong><br>
        Firmado por {{ $adoption->adopter->name }} el {{ $signed_at }} desde la dirección IP {{ $ip_address }}.<br>
        ID de Transacción: {{ md5($adoption->id . $signed_at . $ip_address) }}
    </div>

    <p>Con fecha de hoy, se celebra el presente Acuerdo de Adopción Responsable entre el <strong>Rescatista / Fundación</strong> y el <strong>Adoptante</strong> descritos a continuación:</p>

    <table class="meta-table">
        <tr>
            <th>Adoptante Definitivo</th>
            <td>{{ $adoption->adopter->name }} (Tel: {{ $adoption->adopter->phone }}, Ciudad: {{ $adoption->adopter->city }})</td>
        </tr>
        <tr>
            <th>Rescatista / ONG</th>
            <td>{{ $adoption->rescuer->name }}</td>
        </tr>
        <tr>
            <th>Mascota Adoptada</th>
            <td>{{ $adoption->pet->name }} (Especie: {{ $adoption->pet->species }}, Raza: {{ $adoption->pet->breed }}, Microchip: {{ $adoption->pet->microchip_code ?? 'Sin microchip' }})</td>
        </tr>
        <tr>
            <th>Fecha de Emisión</th>
            <td>{{ date('d/m/Y') }}</td>
        </tr>
    </table>

    <div class="clauses">
        <h3>CLÁUSULAS DEL COMPROMISO</h3>
        
        <p><strong>PRIMERA: TENENCIA RESPONSABLE.</strong> El Adoptante se compromete a proporcionar a la mascota alimento de calidad, agua limpia, refugio adecuado frente a climas adversos y un entorno afectivo seguro. Queda estrictamente prohibido mantener a la mascota encadenada, confinada en terrazas o en condiciones de hacinamiento.</p>

        <p><strong>SEGUNDA: SALUD Y BIENESTAR.</strong> El Adoptante asume la responsabilidad de mantener las vacunas y desparasitaciones al día (registradas en la Ficha Clínica Digital), realizar controles veterinarios anuales y atender cualquier emergencia médica de inmediato.</p>

        <p><strong>TERCERA: SEGUIMIENTO POST-ADOPCIÓN.</strong> El Adoptante acepta enviar periódicamente los reportes requeridos por el Diario de Adopción de la plataforma (Día 3, Día 15, Mes 3, Mes 6 y al cumplirse 1 año). Permitirá las inspecciones voluntarias presenciales o digitales por parte de la Fundación para verificar el bienestar del animal.</p>

        <p><strong>CUARTA: DEVOLUCIÓN DE LA MASCOTA.</strong> En caso de que el Adoptante no pueda continuar cuidando de la mascota por fuerza mayor, se compromete a notificarlo inmediatamente a la Fundación para coordinar un Hogar Temporal o el retorno del animal. Queda terminantemente prohibido abandonar, vender o regalar a la mascota a terceros sin aprobación previa por escrito.</p>

        <p><strong>QUINTA: REGISTRO E IDENTIFICACIÓN.</strong> La mascota cuenta con el código de microchip reflejado en este contrato. Los datos de propiedad se mantendrán sincronizados localmente y en el Registro Nacional de Tenencia Responsable correspondiente.</p>
    </div>

    <div class="signatures">
        <div class="signature-block">
            <p>Firma del Rescatista / Fundación</p>
            <p style="margin-top: 20px; font-weight: bold; color: #047857;">✓ Autorizado digitalmente</p>
            <p>{{ $adoption->rescuer->name }}</p>
        </div>
        <div class="signature-block">
            <p>Firma del Adoptante (Firma Electrónica)</p>
            <img class="signature-img" src="{{ $signature_path }}" alt="Firma Adoptante"><br>
            <p>{{ $adoption->adopter->name }}</p>
        </div>
    </div>
</body>
</html>
