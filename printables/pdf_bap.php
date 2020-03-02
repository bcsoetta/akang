<?php

include_once 'libraries/fpdf/fpdf.php';

class BapPdf extends FPDF {
    protected $data;

    public function __construct($data)
    {
        $this->data = $data;

        parent::__construct('P', 'mm', 'A4');

        $this->SetAutoPageBreak(true, 20);
        $this->SetMargins(20, 20, 20);
        $this->AliasNbPages();

        $this->generatePdf();
        // $this->AddPage();
        // $this->SetFont('Arial', 'B', 10);
        // $this->Cell(0, 4, "FUCK");
    }

    // add table header
    function tableHeader() {
        // $p = new FPDF();
    
        $this->SetFont('ARIAL', 'B', 8);
    
        // marker
        $this->Cell(0, 4, "Halaman {$this->PageNo()} dari {nb}", 0, 1, 'R');
    
        $this->Cell(10, 4, 'No', 1, 0, 'C');   // No
        $this->Cell(30, 4, 'HAWB', 1, 0, 'C'); // HAWB
        $this->Cell(55, 4, 'PENERIMA', 1, 0, 'C'); // PENERIMA 
        $this->Cell(17.5, 4, 'KOLI', 1, 0, 'C'); // KOLI 
        $this->Cell(17.5, 4, 'KILO (KG)', 1, 0, 'C'); // KILO 
        $this->Cell(40, 4, 'KETERANGAN', 1, 0, 'C'); // KILO 
    
        $this->SetFont('ARIAL', '', 8);
        $this->Ln();
    }

    // generate pdf
    function generatePdf() {
        // modify pointer
        $p = $this;

        // extract data?
        $nomor_bap  = $this->data['nomor_lengkap'];
        $tanggal_bap= $this->data['tanggal_formatted'];
        $pjt        = $this->data['pjt'];
        $gudang     = $this->data['gudang'];

        $data   = $this->data['data'];

        $nama_pemeriksa = $this->data['nama_pemeriksa'];
        $nip_pemeriksa  = $this->data['nip_pemeriksa'];
        // first page
        $p->AddPage();

        // Set font
        $p->SetFont('Arial', '', 10);

        $p->Cell(0, 5, 'KEMENTERIAN KEUANGAN REPUBLIK INDONESIA', 0, 1);
        $p->Cell(0, 5, 'DIREKTORAT JENDERAL BEA DAN CUKAI', 0, 1);
        $p->Cell(0, 5, 'KANTOR PELAYANAN UTAMA TIPE C SOEKARNO HATTA', 0, 1);

        // grab x
        $currX = $p->GetX();
        $currY = $p->GetY();

        // draw line
        $p->SetLineWidth(0.5);
        $p->Line($currX, $currY, 210-20, $currY);

        // double space
        $p->Ln(8);

        // Berita acara pemeriksaan fisik barang impor
        $p->SetFont('Arial', 'BU');
        $p->Cell(0, 5, 'BERITA ACARA PEMERIKSAAN FISIK BARANG IMPOR', 0, 1, 'C');

        $p->SetFont('Arial', '');
        $p->Cell(0, 5, "NOMOR: {$nomor_bap} TANGGAL: {$tanggal_bap}", 0, 1, 'C');

        // double space
        $p->Ln(8);

        // kalimat pembuka
        $p->MultiCell(0, 5, "TERHADAP IMPOR BARANG CN/PIBK DENGAN DATA SEBAGAI BERIKUT TELAH DILAKUKAN PEMERIKSAAN FISIK BERSAMA PETUGAS PJT:");

        $p->SetXY(102.5, $p->GetY()-5);
        $p->SetFont('ARIAL', 'BU');

        $tw = $p->GetStringWidth("({$pjt}) ");

        $p->Cell($tw, 5, "({$pjt})");
        $p->SetFont('ARIAL', '');

        $tw = $p->GetStringWidth('DI GUDANG: ');
        $p->Cell($tw, 5, 'DI GUDANG: ');

        $p->SetFont('ARIAL', 'BU');
        $p->Cell(0, 5, "({$gudang})");
        $p->SetFont('ARIAL', '');

        // single line
        $p->Ln(10);

        // reset line width
        $p->SetLineWidth(0);

        // table header
        $this->tableHeader();
        // $p->Ln();
        // repeat for all data, up to
        foreach($data as $k => $v) {
            // fill with multicell first,
            // then draw rect around it
            // record y
            $currY  = $p->GetY();
            $currX  = $p->GetX();
            $endY   = $currY;

            // no
            $p->MultiCell(10, 4, $k + 1);
            
            // hawb
            $p->SetXY($currX + 10, $currY);
            $p->MultiCell(30, 4, $v['no_dok'], 0, 'C');

            $endY   = max($endY, $p->GetY());

            // penerima
            $p->SetXY($currX + 40, $currY);
            $p->MultiCell(55, 4, $v['importir'], 0, 'C');

            $endY   = max($endY, $p->GetY());

            // koli
            $p->SetXY($currX + 95, $currY);
            $p->MultiCell(17.5, 4, $v['jml_item'], 0, 'C');

            $endY   = max($endY, $p->GetY());

            // kilo
            $p->SetXY($currX + 112.5, $currY);
            $p->MultiCell(17.5, 4, $v['berat_kg'], 0, 'C');

            $endY   = max($endY, $p->GetY());

            // keterangan
            $p->SetXY($currX + 130, $currY);
            $p->MultiCell(40, 4, $v['keterangan'], 0, 'L');

            $endY   = max($endY, $p->GetY());

            // Draw rectangle
            $h = $endY - $currY;

            $p->Rect($currX, $currY, 10, $h);   // no
            $p->Rect($currX + 10, $currY, 30, $h);   // hawb
            $p->Rect($currX + 40, $currY, 55, $h);   // consignee
            $p->Rect($currX + 95, $currY, 17.5, $h);    // koli
            $p->Rect($currX + 112.5, $currY, 17.5, $h);   // kilo
            $p->Rect($currX + 130, $currY, 40, $h); // keterangan

            // if we reach end of page, start anew with header
            if ($endY >= 240) {
                $p->AddPage();
                $this->tableHeader();
                // $p->Ln();   

                $currY  = $p->GetY();
                $currX  = $p->GetX();
                $endY   = $currY;
            }
        }

        // Tanda tangan
        $p->Ln(5);
        $p->SetFont('Arial', '', 10);

        $p->Cell(0, 5, 'Mengetahui,', 0, 1);

        $currX  = $p->GetX();
        $currY  = $p->GetY();

        $p->Cell(0, 5, 'Petugas PJT ' . $pjt);

        $p->SetXY(130, $currY);
        $p->Cell(0, 5, 'PEJABAT PEMERIKSA FISIK', 0, 1);

        // spaces for signature
        $p->Ln(15);

        // Name and sheiit
        $currX  = $p->GetX();
        $currY  = $p->GetY();

        // petugas PJT
        $p->Cell(60, 5, "(........................................................)", 0, 1);

        // pemeriksa
        $p->SetXY(130, $currY);
        $p->Cell(0, 5, $nama_pemeriksa, 0, 2);
        $p->Cell(0, 5, "NIP {$nip_pemeriksa}", 0, 0);
    }
}