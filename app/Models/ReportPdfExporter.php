<?php

declare(strict_types=1);

namespace App\Models;

/**
 * Pure PHP PDF Exporter for AutoPartFlow ERP Reports.
 * Generates standards-compliant PDF-1.4 binary documents without external dependencies.
 */
class ReportPdfExporter
{
    private array $pages = [];
    private int $currentPage = -1;
    private float $pageWidth = 595.28;  // A4 Width in points (72 dpi)
    private float $pageHeight = 841.89; // A4 Height in points
    private float $margin = 36.0;       // 0.5 inch margins

    public function generate(array $reportData): string
    {
        $this->pages = [];
        $this->currentPage = -1;

        $this->buildDocument($reportData);
        return $this->compilePdf();
    }

    private function addPage(): void
    {
        $this->pages[] = "";
        $this->currentPage++;
    }

    private function buildDocument(array $data): void
    {
        $this->addPage();
        $y = $this->margin;

        // 1. Header Banner
        $this->rect($this->margin, $y, $this->pageWidth - ($this->margin * 2), 48, "0.0 0.125 0.271", ""); // Navy #002045
        $this->text($this->margin + 12, $y + 18, "AutoPartFlow ERP", 16, true, "1.0 1.0 1.0");
        $this->text($this->margin + 12, $y + 34, "Business Intelligence, Seasonal Variation & Financial Analytics Report", 9, false, "0.85 0.90 0.98");
        $this->text($this->pageWidth - $this->margin - 140, $y + 26, "Generated: " . date('d M Y, H:i'), 8.5, false, "0.85 0.90 0.98");
        $y += 58;

        // 2. Report Period & Active Filter Summary Box
        $this->rect($this->margin, $y, $this->pageWidth - ($this->margin * 2), 34, "0.96 0.97 0.99", "0.82 0.86 0.92");
        $periodLabel = $this->cleanText($data['periodLabel'] ?? 'Report');
        $this->text($this->margin + 10, $y + 14, "Reporting Window: " . $periodLabel, 9.5, true, "0.07 0.11 0.17");
        
        $filterSummary = $this->buildFilterSummaryText($data['activeFilters'] ?? []);
        $this->text($this->margin + 10, $y + 26, "Applied Criteria: " . $this->cleanText($filterSummary), 8.5, false, "0.35 0.40 0.48");
        $y += 44;

        // 3. 4 KPI Metric Cards
        $cards = $data['cards'] ?? [];
        $cardW = ($this->pageWidth - ($this->margin * 2) - 24) / 4;
        $cardH = 46;
        $cx = $this->margin;

        foreach ($cards as $idx => $card) {
            $this->rect($cx, $y, $cardW, $cardH, "1.0 1.0 1.0", "0.85 0.88 0.92");
            $this->text($cx + 8, $y + 13, $this->cleanText($card['label'] ?? ''), 8, true, "0.39 0.45 0.55");
            $this->text($cx + 8, $y + 28, $this->cleanText($card['value'] ?? ''), 11, true, "0.0 0.125 0.271");

            $chg = $card['change'] ?? null;
            if ($chg !== null) {
                $chgText = ($chg >= 0 ? "+" : "") . number_format($chg, 1) . "% " . ($data['compareText'] ?? '');
                $color = ($chg >= 0) === ($card['goodWhenUp'] ?? true) ? "0.08 0.42 0.26" : "0.73 0.10 0.10";
                $this->text($cx + 8, $y + 40, $this->cleanText($chgText), 7, false, $color);
            } else {
                $this->text($cx + 8, $y + 40, "Baseline Established", 7, false, "0.55 0.60 0.65");
            }
            $cx += $cardW + 8;
        }
        $y += $cardH + 14;

        // 4. Seasonal Variation Intelligence Box (if active)
        $seasonal = $data['seasonalIntel'] ?? null;
        if (!empty($seasonal)) {
            $sH = 58;
            $this->rect($this->margin, $y, $this->pageWidth - ($this->margin * 2), $sH, "0.98 0.95 1.0", "0.82 0.68 0.96");
            $this->rect($this->margin, $y, 4, $sH, "0.42 0.13 0.66", "");

            $this->text($this->margin + 12, $y + 14, "Seasonal Variation Intelligence: " . $this->cleanText($seasonal['season_name'] ?? '') . " (" . ($seasonal['season_year'] ?? '') . ")", 10.5, true, "0.23 0.03 0.39");
            $this->text($this->margin + 12, $y + 26, $this->cleanText(substr((string)($seasonal['description'] ?? ''), 0, 110)), 8, false, "0.42 0.13 0.66");

            // Key metric badges
            $liftText = "Lift Index: " . ($seasonal['seasonal_lift_pct'] !== null ? (($seasonal['seasonal_lift_pct'] >= 0 ? '+' : '') . number_format($seasonal['seasonal_lift_pct'], 1) . '%') : '0.0%');
            $shareText = "Annual Share: " . number_format($seasonal['annual_share_pct'] ?? 0, 1) . "%";
            $topCatText = "Top Category: " . ($seasonal['top_surging_category'] ?? 'N/A') . " (Rs. " . number_format((float)($seasonal['top_category_revenue'] ?? 0), 2) . ")";
            $sosText = "Season Growth: " . ($seasonal['sos_growth_pct'] !== null ? (($seasonal['sos_growth_pct'] >= 0 ? '+' : '') . number_format($seasonal['sos_growth_pct'], 1) . '%') : 'New cycle');

            $this->text($this->margin + 12, $y + 42, $this->cleanText("{$liftText}  |  {$shareText}  |  {$topCatText}  |  {$sosText}"), 8.5, true, "0.23 0.03 0.39");
            $y += $sH + 14;
        }

        // 5. Category Breakdown Summary Strip
        $categories = $data['categoryBreakdown'] ?? [];
        if (!empty($categories)) {
            $this->text($this->margin, $y + 10, "Category Revenue Breakdown", 10, true, "0.0 0.125 0.271");
            $y += 16;

            $catTableW = $this->pageWidth - ($this->margin * 2);
            $this->rect($this->margin, $y, $catTableW, 16, "0.94 0.96 0.98", "0.85 0.88 0.92");
            $this->text($this->margin + 8, $y + 11, "Category", 8, true, "0.35 0.40 0.48");
            $this->text($this->margin + 180, $y + 11, "Units Sold", 8, true, "0.35 0.40 0.48");
            $this->text($this->margin + 300, $y + 11, "Revenue (Rs.)", 8, true, "0.35 0.40 0.48");
            $this->text($this->margin + 440, $y + 11, "Share %", 8, true, "0.35 0.40 0.48");
            $y += 16;

            foreach (array_slice($categories, 0, 4) as $cat) {
                $this->rect($this->margin, $y, $catTableW, 15, "1.0 1.0 1.0", "0.92 0.94 0.96");
                $this->text($this->margin + 8, $y + 11, $this->cleanText($cat['name'] ?? ''), 8, false, "0.07 0.11 0.17");
                $this->text($this->margin + 180, $y + 11, (string)($cat['total_units'] ?? 0), 8, false, "0.07 0.11 0.17");
                $this->text($this->margin + 300, $y + 11, "Rs. " . number_format((float)($cat['revenue'] ?? 0), 2), 8, true, "0.0 0.125 0.271");
                $this->text($this->margin + 440, $y + 11, number_format((float)($cat['pct'] ?? 0), 1) . "%", 8, false, "0.07 0.11 0.17");
                $y += 15;
            }
            $y += 14;
        }

        // 6. Filtered Sales Records Section Header
        $totalRecords = $data['totalRecords'] ?? count($data['records'] ?? []);
        $this->text($this->margin, $y + 10, "Filtered Sales Records & Transactions (Total: " . $totalRecords . " Records)", 11, true, "0.0 0.125 0.271");
        $y += 16;

        // Render Table Header
        $colWidths = [
            'invoice' => 70,
            'date'    => 82,
            'customer'=> 125,
            'rep'     => 75,
            'payment' => 55,
            'status'  => 45,
            'amount'  => 71,
        ];
        $tableW = array_sum($colWidths);

        $this->drawTableHeader($y, $colWidths);
        $y += 16;

        // Records rows
        $records = $data['records'] ?? [];
        $rowH = 15.5;

        foreach ($records as $r) {
            // Check page overflow
            if ($y + $rowH > $this->pageHeight - $this->margin - 20) {
                $this->drawFooter();
                $this->addPage();
                $y = $this->margin;

                $this->text($this->margin, $y + 10, "Filtered Sales Records (Continued)", 10.5, true, "0.0 0.125 0.271");
                $y += 18;
                $this->drawTableHeader($y, $colWidths);
                $y += 16;
            }

            $bg = "1.0 1.0 1.0";
            $this->rect($this->margin, $y, $tableW, $rowH, $bg, "0.90 0.92 0.95");

            $cx = $this->margin;

            // Invoice
            $this->text($cx + 4, $y + 11, $this->cleanText($r['invoice_number'] ?? ''), 7.5, true, "0.0 0.125 0.271");
            $cx += $colWidths['invoice'];

            // Date
            $d = date('d M Y, H:i', strtotime($r['sale_date'] ?? 'now'));
            $this->text($cx + 4, $y + 11, $this->cleanText($d), 7.5, false, "0.30 0.35 0.40");
            $cx += $colWidths['date'];

            // Customer
            $cName = substr((string)($r['customer_name'] ?? 'Walk-in'), 0, 22);
            $this->text($cx + 4, $y + 11, $this->cleanText($cName), 7.5, true, "0.07 0.11 0.17");
            $cx += $colWidths['customer'];

            // Rep
            $rName = substr((string)($r['sales_rep_name'] ?? 'Direct POS'), 0, 14);
            $this->text($cx + 4, $y + 11, $this->cleanText($rName), 7.5, false, "0.30 0.35 0.40");
            $cx += $colWidths['rep'];

            // Payment
            $pay = ucfirst((string)($r['payment_method'] ?? 'cash'));
            $this->text($cx + 4, $y + 11, $this->cleanText($pay), 7.5, false, "0.07 0.11 0.17");
            $cx += $colWidths['payment'];

            // Status
            $st = strtoupper((string)($r['payment_status'] ?? 'PAID'));
            $stColor = ($st === 'PAID') ? "0.08 0.42 0.26" : (($st === 'PARTIAL') ? "0.53 0.33 0.0" : "0.73 0.10 0.10");
            $this->text($cx + 4, $y + 11, $this->cleanText($st), 7, true, $stColor);
            $cx += $colWidths['status'];

            // Amount
            $amtStr = "Rs. " . number_format((float)($r['total_amount'] ?? 0), 2);
            $this->text($cx + 4, $y + 11, $this->cleanText($amtStr), 8, true, "0.0 0.125 0.271");

            $y += $rowH;
        }

        // Draw final page footer
        $this->drawFooter();
    }

    private function drawTableHeader(float $y, array $colWidths): void
    {
        $tableW = array_sum($colWidths);
        $this->rect($this->margin, $y, $tableW, 16, "0.0 0.125 0.271", "");

        $cx = $this->margin;
        $this->text($cx + 4, $y + 11, "Invoice #", 8, true, "1.0 1.0 1.0");
        $cx += $colWidths['invoice'];

        $this->text($cx + 4, $y + 11, "Date & Time", 8, true, "1.0 1.0 1.0");
        $cx += $colWidths['date'];

        $this->text($cx + 4, $y + 11, "Customer", 8, true, "1.0 1.0 1.0");
        $cx += $colWidths['customer'];

        $this->text($cx + 4, $y + 11, "Staff", 8, true, "1.0 1.0 1.0");
        $cx += $colWidths['rep'];

        $this->text($cx + 4, $y + 11, "Payment", 8, true, "1.0 1.0 1.0");
        $cx += $colWidths['payment'];

        $this->text($cx + 4, $y + 11, "Status", 8, true, "1.0 1.0 1.0");
        $cx += $colWidths['status'];

        $this->text($cx + 4, $y + 11, "Amount (Rs.)", 8, true, "1.0 1.0 1.0");
    }

    private function drawFooter(): void
    {
        $footerY = $this->pageHeight - 24;
        $pageNum = $this->currentPage + 1;
        $this->line($this->margin, $footerY - 6, $this->pageWidth - $this->margin, $footerY - 6, "0.85 0.88 0.92");
        $this->text($this->margin, $footerY + 4, "AutoPartFlow ERP - Business Intelligence & Financial Analytics Report", 7.5, false, "0.55 0.60 0.68");
        $this->text($this->pageWidth - $this->margin - 50, $footerY + 4, "Page " . $pageNum, 7.5, true, "0.55 0.60 0.68");
    }

    private function buildFilterSummaryText(array $f): string
    {
        $parts = [];
        if (!empty($f['category_id'])) $parts[] = "Category ID: " . $f['category_id'];
        if (!empty($f['brand_id'])) $parts[] = "Brand ID: " . $f['brand_id'];
        if (!empty($f['sales_rep_id'])) $parts[] = "Staff ID: " . $f['sales_rep_id'];
        if (!empty($f['customer_type'])) $parts[] = "Channel: " . ucfirst($f['customer_type']);
        if (!empty($f['payment_method'])) $parts[] = "Method: " . ucfirst($f['payment_method']);
        if (!empty($f['payment_status'])) $parts[] = "Status: " . ucfirst($f['payment_status']);
        if (!empty($f['search'])) $parts[] = "Query: \"" . $f['search'] . "\"";

        return !empty($parts) ? implode(" | ", $parts) : "All standard transactions included without dimensional restriction";
    }

    private function cleanText(string $text): string
    {
        $text = str_replace(["\r", "\n", "\t"], ' ', $text);
        // Replace en-dash and special unicode with clean ASCII equivalents
        $text = str_replace(["–", "—", "•", "·", "“", "”", "’", "‘", "⚙️", "🍂"], ["-", "-", "*", "|", "\"", "\"", "'", "'", "", ""], $text);
        $text = iconv('UTF-8', 'ISO-8859-1//TRANSLIT//IGNORE', $text) ?: $text;
        return str_replace(["\\", "(", ")"], ["\\\\", "\\(", "\\)"], $text);
    }

    private function text(float $x, float $y, string $text, float $size = 10, bool $bold = false, string $colorRgb = "0 0 0"): void
    {
        if ($this->currentPage < 0) return;
        $font = $bold ? "/F2" : "/F1";
        $pdfY = $this->pageHeight - $y;
        $this->pages[$this->currentPage] .= "BT {$colorRgb} rg {$font} {$size} Tf {$x} {$pdfY} Td ({$text}) Tj ET\n";
    }

    private function rect(float $x, float $y, float $w, float $h, string $fillRgb = "", string $strokeRgb = ""): void
    {
        if ($this->currentPage < 0) return;
        $pdfY = $this->pageHeight - $y - $h;
        $s = "";
        if ($fillRgb !== "")   $s .= "{$fillRgb} rg ";
        if ($strokeRgb !== "") $s .= "{$strokeRgb} RG ";
        $op = ($fillRgb !== "" && $strokeRgb !== "") ? "B" : ($fillRgb !== "" ? "f" : "S");
        $s .= "{$x} {$pdfY} {$w} {$h} re {$op}\n";
        $this->pages[$this->currentPage] .= $s;
    }

    private function line(float $x1, float $y1, float $x2, float $y2, string $strokeRgb = "0.8 0.8 0.8"): void
    {
        if ($this->currentPage < 0) return;
        $pdfY1 = $this->pageHeight - $y1;
        $pdfY2 = $this->pageHeight - $y2;
        $this->pages[$this->currentPage] .= "{$strokeRgb} RG {$x1} {$pdfY1} m {$x2} {$pdfY2} l S\n";
    }

    private function compilePdf(): string
    {
        $numPages = count($this->pages);
        if ($numPages === 0) {
            $this->addPage();
            $numPages = 1;
        }

        $out = "%PDF-1.4\n";
        $offsets = [];

        // 1: Catalog
        $offsets[1] = strlen($out);
        $out .= "1 0 obj\n<< /Type /Catalog /Pages 2 0 R >>\nendobj\n";

        // 2: Pages
        $kids = [];
        for ($i = 0; $i < $numPages; $i++) {
            $kids[] = (3 + $i * 2) . " 0 R";
        }
        $offsets[2] = strlen($out);
        $out .= "2 0 obj\n<< /Type /Pages /Kids [" . implode(" ", $kids) . "] /Count {$numPages} >>\nendobj\n";

        $font1Obj = 3 + $numPages * 2;
        $font2Obj = $font1Obj + 1;

        for ($i = 0; $i < $numPages; $i++) {
            $pageObj = 3 + $i * 2;
            $contentsObj = $pageObj + 1;

            $offsets[$pageObj] = strlen($out);
            $out .= "{$pageObj} 0 obj\n<< /Type /Page /Parent 2 0 R /MediaBox [0 0 {$this->pageWidth} {$this->pageHeight}] /Contents {$contentsObj} 0 R /Resources << /Font << /F1 {$font1Obj} 0 R /F2 {$font2Obj} 0 R >> >> >>\nendobj\n";

            $stream = $this->pages[$i];
            $streamLen = strlen($stream);
            $offsets[$contentsObj] = strlen($out);
            $out .= "{$contentsObj} 0 obj\n<< /Length {$streamLen} >>\nstream\n{$stream}endstream\nendobj\n";
        }

        $offsets[$font1Obj] = strlen($out);
        $out .= "{$font1Obj} 0 obj\n<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica /Encoding /WinAnsiEncoding >>\nendobj\n";

        $offsets[$font2Obj] = strlen($out);
        $out .= "{$font2Obj} 0 obj\n<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica-Bold /Encoding /WinAnsiEncoding >>\nendobj\n";

        $xrefOffset = strlen($out);
        $totalObjs = $font2Obj;
        $out .= "xref\n0 " . ($totalObjs + 1) . "\n0000000000 65535 f \n";
        for ($i = 1; $i <= $totalObjs; $i++) {
            $out .= sprintf("%010d 00000 n \n", $offsets[$i]);
        }
        $out .= "trailer\n<< /Size " . ($totalObjs + 1) . " /Root 1 0 R >>\nstartxref\n{$xrefOffset}\n%%EOF";

        return $out;
    }
}
