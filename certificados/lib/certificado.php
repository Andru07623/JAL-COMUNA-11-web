<?php
/**
 * ============================================================
 *  GENERACIÓN DEL CERTIFICADO DE RESIDENCIA EN PDF (Fase 5)
 * ============================================================
 *  Usa FPDF (librería liviana, ideal para InfinityFree).
 *  Reproduce fielmente la plantilla oficial de la JAL C11.
 *
 *  Requiere: lib/fpdf/fpdf.php
 *  (En el chat te explico cómo descargar FPDF.)
 *
 *  Función principal:
 *     generar_certificado_pdf($solicitud, $consecutivo, $dirigido_a)
 *  Devuelve el NOMBRE del archivo PDF creado (dentro de
 *  certificados_generados/). Lanza Exception si algo falla.
 * ============================================================
 */

require_once __DIR__ . '/utilidades.php';

/**
 * Convierte texto UTF-8 a la codificación que entiende FPDF (latin1),
 * para que acentos y ñ salgan bien.
 */
function _pdf($texto) {
    return iconv('UTF-8', 'windows-1252//TRANSLIT', (string)$texto);
}

/**
 * Genera el certificado PDF y lo guarda en certificados_generados/.
 *
 * @param array  $s           Fila de la solicitud (de la BD)
 * @param string $consecutivo Ej: 091126-01
 * @param string $dirigido_a  Ej: SAPIENCIA
 * @return string Nombre del archivo PDF generado
 */
function generar_certificado_pdf(array $s, $consecutivo, $dirigido_a) {

    $fpdf = __DIR__ . '/fpdf/fpdf.php';
    if (!file_exists($fpdf)) {
        throw new Exception('FPDF no está instalado en lib/fpdf/.');
    }
    require_once $fpdf;

    // ----- Datos a imprimir -----
    $meses = [1=>'Enero','Febrero','Marzo','Abril','Mayo','Junio','Julio',
              'Agosto','Septiembre','Octubre','Noviembre','Diciembre'];
    $hoy = new DateTime('today');
    $fecha_txt = 'Medellín, ' . $hoy->format('d') . ' de ' . $meses[(int)$hoy->format('n')] . ' de ' . $hoy->format('Y');

    $nombre    = mb_strtoupper($s['nombre'], 'UTF-8');
    $documento = $s['documento'];
    $direccion = $s['direccion'];
    $barrio    = $s['barrio'] === 'Otro' ? $s['barrio_otro'] : $s['barrio'];
    $tiempo    = tiempo_residiendo($s['fecha_residencia']);
    $celular   = $s['celular'];
    $correo    = $s['correo'];

    // ----- Crear el PDF (A4, mm) -----
    $pdf = new FPDF('P', 'mm', 'A4');
    $pdf->SetAutoPageBreak(false); // controlamos el pie manualmente (evita página extra)
    $pdf->AddPage();
    $pdf->SetMargins(25, 15, 25);

    // ---------- LOGO ----------
    $logo = __DIR__ . '/../assets/logo.png';
    if (file_exists($logo)) {
        // centrado, ancho 40 mm
        $ancho = 40;
        $x = (210 - $ancho) / 2;
        $pdf->Image($logo, $x, 15, $ancho);
        $pdf->Ln(38);
    } else {
        // placeholder si aún no hay logo
        $pdf->SetFont('Arial', 'B', 10);
        $pdf->SetTextColor(150);
        $pdf->Cell(0, 30, _pdf('[ LOGO JAL ]'), 0, 1, 'C');
        $pdf->SetTextColor(0);
        $pdf->Ln(2);
    }

    // ---------- FECHA ----------
    $pdf->SetFont('Arial', '', 11);
    $pdf->Cell(0, 7, _pdf($fecha_txt), 0, 1, 'L');
    $pdf->Ln(2);

    // ---------- CONSECUTIVO (a la derecha, en negrita) ----------
    $pdf->SetFont('Arial', 'B', 11);
    $pdf->Cell(0, 7, _pdf('Consecutivo ' . $consecutivo), 0, 1, 'R');
    $pdf->Ln(4);

    // ---------- DESTINATARIO ----------
    $pdf->SetFont('Arial', '', 11);
    $pdf->Cell(0, 6, _pdf('Señores'), 0, 1, 'L');
    $pdf->SetFont('Arial', '', 11);
    $pdf->Cell(0, 6, _pdf($dirigido_a), 0, 1, 'L');
    $pdf->Ln(4);

    // ---------- ASUNTO ----------
    $pdf->SetFont('Arial', 'B', 11);
    $pdf->Cell(0, 7, _pdf('ASUNTO: CERTIFICADO DE RESIDENCIA'), 0, 1, 'L');
    $pdf->Ln(8);

    // ---------- CUERPO ----------
    $pdf->SetFont('Arial', '', 11);
    $cuerpo = 'Respetados señores: por medio de la presente manifiesto que el (la) señor (a) '
            . $nombre . ', identificado (a) con documento ' . $documento
            . ' reside actualmente en la comuna 11 Laureles –Estadio en la ' . $direccion
            . ', del barrio ' . $barrio . ' desde hace ' . $tiempo
            . ' siendo parte del territorio de nuestra comuna 11, con teléfono ' . $celular
            . ' y correo ' . $correo;
    $pdf->MultiCell(0, 7, _pdf($cuerpo), 0, 'J');
    $pdf->Ln(10);

    // ---------- VIGENCIA ----------
    $pdf->SetFont('Arial', '', 9);
    $pdf->MultiCell(0, 6, _pdf('Este certificado tiene vigencia por 30 días y se emite a solicitud del interesado bajo el principio de buena fe.'), 0, 'L');
    $pdf->Ln(10);

    // ---------- FIRMA ----------
    $firma = __DIR__ . '/../assets/firma.png';
    if (file_exists($firma)) {
        $pdf->Image($firma, 25, $pdf->GetY(), 50);
        $pdf->Ln(22);
    } else {
        $pdf->SetTextColor(150);
        $pdf->SetFont('Arial', 'I', 9);
        $pdf->Cell(0, 20, _pdf('[ firma ]'), 0, 1, 'L');
        $pdf->SetTextColor(0);
    }

    // ---------- DATOS DEL EDIL (en negrita cursiva, como la plantilla) ----------
    $pdf->SetFont('Arial', 'BI', 11);
    $pdf->Cell(0, 6, _pdf('MARÍA PATRICIA BAHAMÓN TRUJILLO'), 0, 1, 'L');
    $pdf->SetFont('Arial', 'BI', 10);
    $pdf->Cell(0, 5, _pdf('Edil JAL C11'), 0, 1, 'L');
    $pdf->Cell(0, 5, _pdf('Comuna 11 Laureles Estadio'), 0, 1, 'L');
    $pdf->Cell(0, 5, _pdf('Celular: 3001414895'), 0, 1, 'L');
    $pdf->Cell(0, 5, _pdf('Correo: jalpatriciabahamon.c11@gmail.com'), 0, 1, 'L');

    // ---------- PIE DE PÁGINA (correo + dirección) ----------
    $pdf->SetY(-25);
    $pdf->SetDrawColor(21, 101, 192);
    $pdf->SetLineWidth(0.4);
    $pdf->Line(25, $pdf->GetY(), 185, $pdf->GetY());
    $pdf->Ln(2);
    $pdf->SetFont('Arial', 'B', 9);
    $pdf->SetTextColor(21, 101, 192);
    $pdf->Cell(95, 6, _pdf('jal.laureles11@gmail.com'), 0, 0, 'L');
    $pdf->SetTextColor(0);
    $pdf->SetFont('Arial', 'I', 9);
    $pdf->Cell(0, 6, _pdf('Avenida 39 D #73-113'), 0, 1, 'R');

    // ----- Guardar el archivo -----
    $dir = __DIR__ . '/../certificados_generados';
    if (!is_dir($dir)) { mkdir($dir, 0755, true); }

    $nombre_archivo = 'certificado_' . $consecutivo . '_' . bin2hex(random_bytes(6)) . '.pdf';
    $ruta = $dir . '/' . $nombre_archivo;
    $pdf->Output('F', $ruta);

    if (!file_exists($ruta)) {
        throw new Exception('No se pudo generar el archivo PDF.');
    }
    return $nombre_archivo;
}
