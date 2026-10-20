<?php
// Desactivar avisos deprecados en este script (por seguridad extra)
error_reporting(E_ALL & ~E_DEPRECATED & ~E_NOTICE);
ini_set('display_errors', 0);

require('fpdf.php');

// Conexión a la DB
include '../conexion_db.php';

// ================================
// OBTENER ID DEL PACIENTE
// ================================
$id_paciente = isset($_GET['id_paciente']) ? intval($_GET['id_paciente']) : 0;
if ($id_paciente <= 0) {
    // OJO: no uses echo aquí porque rompe el PDF
    // Podés loguear el error si querés, pero no hacer salida HTML
    die("Error: ID de paciente inválido.");
}

// Helper para convertir a ISO-8859-1 sin usar utf8_decode
function t($texto) {
    return iconv('UTF-8', 'ISO-8859-1//TRANSLIT', $texto);
}

class PDF extends FPDF
{
    function Header()
    {
        global $dato_paciente;

        $this->Image('../assets/img/logo.png', 185, 5, 20);
        $this->SetFont('Arial', 'B', 19);
        $this->Cell(45);
        $this->SetTextColor(0, 0, 0);
        $this->Cell(110, 15, t('Clínica Esperanza'), 1, 1, 'C', 0);
        $this->Ln(3);
        $this->SetTextColor(103);

        // UBICACIÓN
        $this->Cell(110);
        $this->SetFont('Arial', 'B', 10);
        $this->Cell(96, 10, t("Ubicación: Las Eras 123"), 0, 0, '', 0);
        $this->Ln(12);

        // TELÉFONO
        $this->Cell(110);
        $this->SetFont('Arial', 'B', 10);
        $this->Cell(59, 10, t("Teléfono: 3757457896"), 0, 0, '', 0);
        $this->Ln(8);

        // DATOS DEL PACIENTE
        if ($dato_paciente) {
            $this->SetFont('Arial', 'B', 12);
            $this->Cell(0, 8, t("Paciente: {$dato_paciente->apellido} {$dato_paciente->nombre}"), 0, 1, 'L');
            $this->Cell(0, 8, t("DNI: {$dato_paciente->dni}"), 0, 1, 'L');
            $this->Cell(0, 8, t("Obra Social: {$dato_paciente->nombre_obrasocial}"), 0, 1, 'L');
            $this->Cell(0, 8, t("N° Afiliado: {$dato_paciente->nro_afiliado}"), 0, 1, 'L');
        } else {
            $this->SetFont('Arial', 'B', 12);
            $this->Cell(0, 10, t("Paciente no encontrado."), 0, 1, 'C');
        }

        $this->Ln(6);

        // ENCABEZADOS TABLA
        $this->SetFillColor(228, 100, 0);
        $this->SetTextColor(255, 255, 255);
        $this->SetDrawColor(163, 163, 163);
        $this->SetFont('Arial', 'B', 11);
        $this->Cell(30, 10, t('Fecha'), 1, 0, 'C', 1);
        $this->Cell(60, 10, t('Motivo'), 1, 0, 'C', 1);
        $this->Cell(90, 10, t('Tratamiento'), 1, 1, 'C', 1);
    }

    function Footer()
    {
        $this->SetY(-15);
        $this->SetFont('Arial', 'I', 8);
        $this->Cell(0, 10, t('Página ') . $this->PageNo() . '/{nb}', 0, 0, 'C');

        $this->SetY(-15);
        $this->SetFont('Arial', 'I', 8);
        $hoy = date('d/m/Y');
        $this->Cell(0, 10, t($hoy), 0, 0, 'R');
    }
}

// ================================
// DATOS DEL PACIENTE POR ID
// ================================
$consulta_paciente = $conexion->query("
    SELECT p.*, o.nombre_obrasocial
    FROM pacientes p
    LEFT JOIN obras_sociales o ON p.obra_social = o.id_obrasocial
    WHERE p.id_paciente = '$id_paciente'
");

$dato_paciente = $consulta_paciente->fetch_object();

// ================================
// HISTORIA CLÍNICA POR ID_PACIENTE
// ================================
$consulta_historia = $conexion->query("
    SELECT *
    FROM historia
    WHERE id_paciente = '$id_paciente'
    ORDER BY fecha
");

// ================================
// CREAR PDF
// ================================
$pdf = new PDF();
$pdf->AliasNbPages();
$pdf->AddPage();

$pdf->SetFont('Arial', '', 12);
$pdf->SetDrawColor(163, 163, 163);

if ($consulta_historia && $consulta_historia->num_rows > 0) {
    while ($row = $consulta_historia->fetch_object()) {
        $pdf->Cell(30, 10, t($row->fecha), 1, 0, 'C', 0);
        $pdf->Cell(60, 10, t($row->motivo), 1, 0, 'L', 0);
        $pdf->Cell(90, 10, t($row->tratamiento), 1, 1, 'L', 0);
    }
} else {
    $pdf->Cell(180, 10, t('No hay registros en la historia clínica para este paciente.'), 1, 1, 'C', 0);
}

// ================================
// SALIDA DEL PDF
// ================================
$pdf->Output('I', 'Historia_Clinica_' . $id_paciente . '.pdf');