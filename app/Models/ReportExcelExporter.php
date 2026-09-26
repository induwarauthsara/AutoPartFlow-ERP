<?php

declare(strict_types=1);

namespace App\Models;

/**
 * Microsoft Excel XML (SpreadsheetML) Exporter for AutoPartFlow ERP Reports.
 * Generates styled, multi-sheet spreadsheets compatible natively with Excel, Google Sheets, Calc, and Office 365.
 */
class ReportExcelExporter
{
    public function generate(array $data): string
    {
        $xml = [];
        $xml[] = '<?xml version="1.0" encoding="UTF-8"?>';
        $xml[] = '<?mso-application progid="Excel.Sheet"?>';
        $xml[] = '<Workbook xmlns="urn:schemas-microsoft-com:office:spreadsheet"';
        $xml[] = ' xmlns:o="urn:schemas-microsoft-com:office:office"';
        $xml[] = ' xmlns:x="urn:schemas-microsoft-com:office:excel"';
        $xml[] = ' xmlns:ss="urn:schemas-microsoft-com:office:spreadsheet"';
        $xml[] = ' xmlns:html="http://www.w3.org/TR/REC-html40">';

        // Document Styles
        $xml[] = ' <Styles>';
        $xml[] = '  <Style ss:ID="Default" ss:Name="Normal">';
        $xml[] = '   <Alignment ss:Vertical="Center"/>';
        $xml[] = '   <Borders/>';
        $xml[] = '   <Font ss:FontName="Segoe UI" ss:Size="10" ss:Color="#121C2C"/>';
        $xml[] = '  </Style>';

        // Brand Banner Title Style
        $xml[] = '  <Style ss:ID="TitleBanner">';
        $xml[] = '   <Alignment ss:Horizontal="Left" ss:Vertical="Center"/>';
        $xml[] = '   <Font ss:FontName="Segoe UI" ss:Size="15" ss:Color="#FFFFFF" ss:Bold="1"/>';
        $xml[] = '   <Interior ss:Color="#002045" ss:Pattern="Solid"/>';
        $xml[] = '  </Style>';

        // Section Header Style
        $xml[] = '  <Style ss:ID="SectionHeader">';
        $xml[] = '   <Alignment ss:Horizontal="Left" ss:Vertical="Center"/>';
        $xml[] = '   <Font ss:FontName="Segoe UI" ss:Size="11" ss:Color="#FFFFFF" ss:Bold="1"/>';
        $xml[] = '   <Interior ss:Color="#1A365D" ss:Pattern="Solid"/>';
        $xml[] = '  </Style>';

        // Table Column Header Style
        $xml[] = '  <Style ss:ID="ColHeader">';
        $xml[] = '   <Alignment ss:Horizontal="Center" ss:Vertical="Center" ss:WrapText="1"/>';
        $xml[] = '   <Font ss:FontName="Segoe UI" ss:Size="9.5" ss:Color="#FFFFFF" ss:Bold="1"/>';
        $xml[] = '   <Interior ss:Color="#002045" ss:Pattern="Solid"/>';
        $xml[] = '   <Borders>';
        $xml[] = '    <Border ss:Position="Bottom" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#CBD5E1"/>';
        $xml[] = '   </Borders>';
        $xml[] = '  </Style>';

        // Data Styles
        $xml[] = '  <Style ss:ID="TextLeft">';
        $xml[] = '   <Alignment ss:Horizontal="Left" ss:Vertical="Center"/>';
        $xml[] = '  </Style>';

        $xml[] = '  <Style ss:ID="TextCenter">';
        $xml[] = '   <Alignment ss:Horizontal="Center" ss:Vertical="Center"/>';
        $xml[] = '  </Style>';

        $xml[] = '  <Style ss:ID="Currency">';
        $xml[] = '   <Alignment ss:Horizontal="Right" ss:Vertical="Center"/>';
        $xml[] = '   <NumberFormat ss:Format="&quot;Rs. &quot;#,##0.00"/>';
        $xml[] = '  </Style>';

        $xml[] = '  <Style ss:ID="CurrencyBold">';
        $xml[] = '   <Alignment ss:Horizontal="Right" ss:Vertical="Center"/>';
        $xml[] = '   <Font ss:FontName="Segoe UI" ss:Size="10" ss:Color="#002045" ss:Bold="1"/>';
        $xml[] = '   <NumberFormat ss:Format="&quot;Rs. &quot;#,##0.00"/>';
        $xml[] = '  </Style>';

        $xml[] = '  <Style ss:ID="Percent">';
        $xml[] = '   <Alignment ss:Horizontal="Right" ss:Vertical="Center"/>';
        $xml[] = '   <NumberFormat ss:Format="0.0%"/>';
        $xml[] = '  </Style>';

        $xml[] = '  <Style ss:ID="TotalRow">';
        $xml[] = '   <Alignment ss:Horizontal="Right" ss:Vertical="Center"/>';
        $xml[] = '   <Font ss:FontName="Segoe UI" ss:Size="10.5" ss:Color="#002045" ss:Bold="1"/>';
        $xml[] = '   <Interior ss:Color="#EEF4FF" ss:Pattern="Solid"/>';
        $xml[] = '   <Borders>';
        $xml[] = '    <Border ss:Position="Top" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#1A365D"/>';
        $xml[] = '    <Border ss:Position="Bottom" ss:LineStyle="Double" ss:Weight="3" ss:Color="#1A365D"/>';
        $xml[] = '   </Borders>';
        $xml[] = '   <NumberFormat ss:Format="&quot;Rs. &quot;#,##0.00"/>';
        $xml[] = '  </Style>';

        $xml[] = ' </Styles>';

        // Worksheet 1: Executive Summary
        $xml[] = $this->buildSummarySheet($data);

        // Worksheet 2: Filtered Sales Records
        $xml[] = $this->buildRecordsSheet($data);

        $xml[] = '</Workbook>';

        return implode("\n", $xml);
    }

    private function buildSummarySheet(array $data): string
    {
        $out = [];
        $out[] = ' <Worksheet ss:Name="Executive Summary">';
        $out[] = '  <Table ss:DefaultColumnWidth="120">';
        $out[] = '   <Column ss:Width="160"/>';
        $out[] = '   <Column ss:Width="180"/>';
        $out[] = '   <Column ss:Width="140"/>';
        $out[] = '   <Column ss:Width="140"/>';

        // Title row
        $out[] = '   <Row ss:Height="32">';
        $out[] = '    <Cell ss:MergeAcross="3" ss:StyleID="TitleBanner"><Data ss:Type="String"> AutoPartFlow ERP - Business Intelligence &amp; Analytics</Data></Cell>';
        $out[] = '   </Row>';

        // Subtitle row
        $out[] = '   <Row ss:Height="20">';
        $out[] = '    <Cell ss:MergeAcross="3"><Data ss:Type="String">Generated: ' . date('d M Y, H:i:s') . ' | Period: ' . $this->xmlEscape($data['periodLabel'] ?? 'All') . '</Data></Cell>';
        $out[] = '   </Row>';
        $out[] = '   <Row ss:Height="10"/>'; // blank spacer

        // 1. KPI Cards
        $out[] = '   <Row ss:Height="22">';
        $out[] = '    <Cell ss:MergeAcross="3" ss:StyleID="SectionHeader"><Data ss:Type="String"> KEY PERFORMANCE INDICATORS</Data></Cell>';
        $out[] = '   </Row>';

        $cards = $data['cards'] ?? [];
        foreach ($cards as $c) {
            $chg = $c['change'] ?? null;
            $chgStr = ($chg !== null) ? (($chg >= 0 ? '+' : '') . number_format($chg, 1) . '% ' . ($data['compareText'] ?? '')) : 'Baseline established';
            $out[] = '   <Row ss:Height="20">';
            $out[] = '    <Cell ss:StyleID="TextLeft"><Data ss:Type="String">' . $this->xmlEscape($c['label'] ?? '') . '</Data></Cell>';
            $out[] = '    <Cell ss:StyleID="TextLeft"><Data ss:Type="String">' . $this->xmlEscape($c['value'] ?? '') . '</Data></Cell>';
            $out[] = '    <Cell ss:MergeAcross="1" ss:StyleID="TextLeft"><Data ss:Type="String">' . $this->xmlEscape($chgStr) . '</Data></Cell>';
            $out[] = '   </Row>';
        }
        $out[] = '   <Row ss:Height="12"/>';

        // 2. Seasonal Variation Intelligence (if active)
        $seasonal = $data['seasonalIntel'] ?? null;
        if (!empty($seasonal)) {
            $out[] = '   <Row ss:Height="22">';
            $out[] = '    <Cell ss:MergeAcross="3" ss:StyleID="SectionHeader"><Data ss:Type="String"> SEASONAL VARIATION INTELLIGENCE</Data></Cell>';
            $out[] = '   </Row>';

            $out[] = '   <Row ss:Height="20">';
            $out[] = '    <Cell ss:StyleID="TextLeft"><Data ss:Type="String">Seasonal Cycle Model</Data></Cell>';
            $out[] = '    <Cell ss:MergeAcross="2" ss:StyleID="TextLeft"><Data ss:Type="String">' . $this->xmlEscape($seasonal['season_name'] ?? '') . ' (' . ($seasonal['season_year'] ?? '') . ')</Data></Cell>';
            $out[] = '   </Row>';

            $out[] = '   <Row ss:Height="20">';
            $out[] = '    <Cell ss:StyleID="TextLeft"><Data ss:Type="String">Seasonal Lift Index</Data></Cell>';
            $out[] = '    <Cell ss:StyleID="TextLeft"><Data ss:Type="String">' . ($seasonal['seasonal_lift_pct'] !== null ? (($seasonal['seasonal_lift_pct'] >= 0 ? '+' : '') . number_format($seasonal['seasonal_lift_pct'], 1) . '% vs off-season baseline') : '0.0%') . '</Data></Cell>';
            $out[] = '    <Cell ss:StyleID="TextLeft"><Data ss:Type="String">Annual Revenue Share</Data></Cell>';
            $out[] = '    <Cell ss:StyleID="TextLeft"><Data ss:Type="String">' . number_format($seasonal['annual_share_pct'] ?? 0, 1) . '% of annual business</Data></Cell>';
            $out[] = '   </Row>';

            $out[] = '   <Row ss:Height="20">';
            $out[] = '    <Cell ss:StyleID="TextLeft"><Data ss:Type="String">Top Surging Category</Data></Cell>';
            $out[] = '    <Cell ss:StyleID="TextLeft"><Data ss:Type="String">' . $this->xmlEscape($seasonal['top_surging_category'] ?? '') . '</Data></Cell>';
            $out[] = '    <Cell ss:StyleID="TextLeft"><Data ss:Type="String">Category Revenue</Data></Cell>';
            $out[] = '    <Cell ss:StyleID="Currency"><Data ss:Type="Number">' . (float)($seasonal['top_category_revenue'] ?? 0) . '</Data></Cell>';
            $out[] = '   </Row>';

            $out[] = '   <Row ss:Height="12"/>';
        }

        // 3. Category Revenue Breakdown
        $categories = $data['categoryBreakdown'] ?? [];
        if (!empty($categories)) {
            $out[] = '   <Row ss:Height="22">';
            $out[] = '    <Cell ss:MergeAcross="3" ss:StyleID="SectionHeader"><Data ss:Type="String"> CATEGORY REVENUE &amp; PROFITABILITY</Data></Cell>';
            $out[] = '   </Row>';

            $out[] = '   <Row ss:Height="20">';
            $out[] = '    <Cell ss:StyleID="ColHeader"><Data ss:Type="String">Category Name</Data></Cell>';
            $out[] = '    <Cell ss:StyleID="ColHeader"><Data ss:Type="String">Units Sold</Data></Cell>';
            $out[] = '    <Cell ss:StyleID="ColHeader"><Data ss:Type="String">Revenue (Rs.)</Data></Cell>';
            $out[] = '    <Cell ss:StyleID="ColHeader"><Data ss:Type="String">Revenue Share %</Data></Cell>';
            $out[] = '   </Row>';

            foreach ($categories as $cat) {
                $out[] = '   <Row ss:Height="19">';
                $out[] = '    <Cell ss:StyleID="TextLeft"><Data ss:Type="String">' . $this->xmlEscape($cat['name'] ?? '') . '</Data></Cell>';
                $out[] = '    <Cell ss:StyleID="TextCenter"><Data ss:Type="Number">' . (int)($cat['total_units'] ?? 0) . '</Data></Cell>';
                $out[] = '    <Cell ss:StyleID="Currency"><Data ss:Type="Number">' . (float)($cat['revenue'] ?? 0) . '</Data></Cell>';
                $out[] = '    <Cell ss:StyleID="Percent"><Data ss:Type="Number">' . round(((float)($cat['pct'] ?? 0)) / 100, 4) . '</Data></Cell>';
                $out[] = '   </Row>';
            }
            $out[] = '   <Row ss:Height="12"/>';
        }

        // 4. Sales Representative Performance
        $reps = $data['employeePerformance'] ?? [];
        if (!empty($reps)) {
            $out[] = '   <Row ss:Height="22">';
            $out[] = '    <Cell ss:MergeAcross="3" ss:StyleID="SectionHeader"><Data ss:Type="String"> SALES TEAM PERFORMANCE</Data></Cell>';
            $out[] = '   </Row>';

            $out[] = '   <Row ss:Height="20">';
            $out[] = '    <Cell ss:StyleID="ColHeader"><Data ss:Type="String">Staff Member</Data></Cell>';
            $out[] = '    <Cell ss:StyleID="ColHeader"><Data ss:Type="String">Employee Code</Data></Cell>';
            $out[] = '    <Cell ss:StyleID="ColHeader"><Data ss:Type="String">Transactions</Data></Cell>';
            $out[] = '    <Cell ss:StyleID="ColHeader"><Data ss:Type="String">Total Revenue (Rs.)</Data></Cell>';
            $out[] = '   </Row>';

            foreach ($reps as $rp) {
                $out[] = '   <Row ss:Height="19">';
                $out[] = '    <Cell ss:StyleID="TextLeft"><Data ss:Type="String">' . $this->xmlEscape($rp['full_name'] ?? '') . '</Data></Cell>';
                $out[] = '    <Cell ss:StyleID="TextCenter"><Data ss:Type="String">' . $this->xmlEscape($rp['employee_code'] ?? '') . '</Data></Cell>';
                $out[] = '    <Cell ss:StyleID="TextCenter"><Data ss:Type="Number">' . (int)($rp['total_sales'] ?? 0) . '</Data></Cell>';
                $out[] = '    <Cell ss:StyleID="Currency"><Data ss:Type="Number">' . (float)($rp['total_revenue'] ?? 0) . '</Data></Cell>';
                $out[] = '   </Row>';
            }
        }

        $out[] = '  </Table>';
        $out[] = ' </Worksheet>';

        return implode("\n", $out);
    }

    private function buildRecordsSheet(array $data): string
    {
        $records = $data['records'] ?? [];
        $out = [];

        $out[] = ' <Worksheet ss:Name="Filtered Sales Records">';
        $out[] = '  <Table>';
        $out[] = '   <Column ss:Width="95"/>';  // Invoice
        $out[] = '   <Column ss:Width="115"/>'; // Date
        $out[] = '   <Column ss:Width="150"/>'; // Customer
        $out[] = '   <Column ss:Width="75"/>';  // Code
        $out[] = '   <Column ss:Width="75"/>';  // Channel
        $out[] = '   <Column ss:Width="95"/>';  // Phone
        $out[] = '   <Column ss:Width="130"/>'; // Rep
        $out[] = '   <Column ss:Width="65"/>';  // Items
        $out[] = '   <Column ss:Width="180"/>'; // Items preview
        $out[] = '   <Column ss:Width="85"/>';  // Payment
        $out[] = '   <Column ss:Width="75"/>';  // Status
        $out[] = '   <Column ss:Width="95"/>';  // Subtotal
        $out[] = '   <Column ss:Width="80"/>';  // Discount
        $out[] = '   <Column ss:Width="80"/>';  // Tax
        $out[] = '   <Column ss:Width="110"/>'; // Total
        $out[] = '   <Column ss:Width="100"/>'; // Paid

        // Header Row
        $out[] = '   <Row ss:Height="24">';
        $out[] = '    <Cell ss:StyleID="ColHeader"><Data ss:Type="String">Invoice #</Data></Cell>';
        $out[] = '    <Cell ss:StyleID="ColHeader"><Data ss:Type="String">Date &amp; Time</Data></Cell>';
        $out[] = '    <Cell ss:StyleID="ColHeader"><Data ss:Type="String">Customer Name</Data></Cell>';
        $out[] = '    <Cell ss:StyleID="ColHeader"><Data ss:Type="String">Cust Code</Data></Cell>';
        $out[] = '    <Cell ss:StyleID="ColHeader"><Data ss:Type="String">Channel</Data></Cell>';
        $out[] = '    <Cell ss:StyleID="ColHeader"><Data ss:Type="String">Contact Phone</Data></Cell>';
        $out[] = '    <Cell ss:StyleID="ColHeader"><Data ss:Type="String">Sales Staff</Data></Cell>';
        $out[] = '    <Cell ss:StyleID="ColHeader"><Data ss:Type="String">Items</Data></Cell>';
        $out[] = '    <Cell ss:StyleID="ColHeader"><Data ss:Type="String">Items Preview</Data></Cell>';
        $out[] = '    <Cell ss:StyleID="ColHeader"><Data ss:Type="String">Payment</Data></Cell>';
        $out[] = '    <Cell ss:StyleID="ColHeader"><Data ss:Type="String">Status</Data></Cell>';
        $out[] = '    <Cell ss:StyleID="ColHeader"><Data ss:Type="String">Subtotal</Data></Cell>';
        $out[] = '    <Cell ss:StyleID="ColHeader"><Data ss:Type="String">Discount</Data></Cell>';
        $out[] = '    <Cell ss:StyleID="ColHeader"><Data ss:Type="String">Tax (VAT)</Data></Cell>';
        $out[] = '    <Cell ss:StyleID="ColHeader"><Data ss:Type="String">Total Amount</Data></Cell>';
        $out[] = '    <Cell ss:StyleID="ColHeader"><Data ss:Type="String">Amount Paid</Data></Cell>';
        $out[] = '   </Row>';

        $totalSubtotal = 0.0;
        $totalDiscount = 0.0;
        $totalTax      = 0.0;
        $totalAmount   = 0.0;
        $totalPaid     = 0.0;

        foreach ($records as $r) {
            $sub  = (float)($r['subtotal'] ?? 0);
            $disc = (float)($r['discount_amount'] ?? 0);
            $tx   = (float)($r['tax_amount'] ?? 0);
            $tot  = (float)($r['total_amount'] ?? 0);
            $pd   = (float)($r['amount_paid'] ?? 0);

            $totalSubtotal += $sub;
            $totalDiscount += $disc;
            $totalTax      += $tx;
            $totalAmount   += $tot;
            $totalPaid     += $pd;

            $out[] = '   <Row ss:Height="18">';
            $out[] = '    <Cell ss:StyleID="TextCenter"><Data ss:Type="String">' . $this->xmlEscape($r['invoice_number'] ?? '') . '</Data></Cell>';
            $out[] = '    <Cell ss:StyleID="TextCenter"><Data ss:Type="String">' . $this->xmlEscape($r['sale_date'] ?? '') . '</Data></Cell>';
            $out[] = '    <Cell ss:StyleID="TextLeft"><Data ss:Type="String">' . $this->xmlEscape($r['customer_name'] ?? 'Walk-in') . '</Data></Cell>';
            $out[] = '    <Cell ss:StyleID="TextCenter"><Data ss:Type="String">' . $this->xmlEscape($r['customer_code'] ?? 'N/A') . '</Data></Cell>';
            $out[] = '    <Cell ss:StyleID="TextCenter"><Data ss:Type="String">' . strtoupper((string)($r['customer_type'] ?? 'shop')) . '</Data></Cell>';
            $out[] = '    <Cell ss:StyleID="TextCenter"><Data ss:Type="String">' . $this->xmlEscape($r['customer_phone'] ?? 'N/A') . '</Data></Cell>';
            $out[] = '    <Cell ss:StyleID="TextLeft"><Data ss:Type="String">' . $this->xmlEscape($r['sales_rep_name'] ?? 'Direct POS') . '</Data></Cell>';
            $out[] = '    <Cell ss:StyleID="TextCenter"><Data ss:Type="Number">' . (int)($r['item_count'] ?? 1) . '</Data></Cell>';
            $out[] = '    <Cell ss:StyleID="TextLeft"><Data ss:Type="String">' . $this->xmlEscape($r['items_summary'] ?? '') . '</Data></Cell>';
            $out[] = '    <Cell ss:StyleID="TextCenter"><Data ss:Type="String">' . strtoupper((string)($r['payment_method'] ?? 'cash')) . '</Data></Cell>';
            $out[] = '    <Cell ss:StyleID="TextCenter"><Data ss:Type="String">' . strtoupper((string)($r['payment_status'] ?? 'paid')) . '</Data></Cell>';
            $out[] = '    <Cell ss:StyleID="Currency"><Data ss:Type="Number">' . $sub . '</Data></Cell>';
            $out[] = '    <Cell ss:StyleID="Currency"><Data ss:Type="Number">' . $disc . '</Data></Cell>';
            $out[] = '    <Cell ss:StyleID="Currency"><Data ss:Type="Number">' . $tx . '</Data></Cell>';
            $out[] = '    <Cell ss:StyleID="CurrencyBold"><Data ss:Type="Number">' . $tot . '</Data></Cell>';
            $out[] = '    <Cell ss:StyleID="Currency"><Data ss:Type="Number">' . $pd . '</Data></Cell>';
            $out[] = '   </Row>';
        }

        // Summary Total Row
        $out[] = '   <Row ss:Height="22">';
        $out[] = '    <Cell ss:MergeAcross="10" ss:StyleID="TotalRow"><Data ss:Type="String">GRAND TOTAL (' . count($records) . ' TRANSACTIONS):</Data></Cell>';
        $out[] = '    <Cell ss:StyleID="TotalRow"><Data ss:Type="Number">' . $totalSubtotal . '</Data></Cell>';
        $out[] = '    <Cell ss:StyleID="TotalRow"><Data ss:Type="Number">' . $totalDiscount . '</Data></Cell>';
        $out[] = '    <Cell ss:StyleID="TotalRow"><Data ss:Type="Number">' . $totalTax . '</Data></Cell>';
        $out[] = '    <Cell ss:StyleID="TotalRow"><Data ss:Type="Number">' . $totalAmount . '</Data></Cell>';
        $out[] = '    <Cell ss:StyleID="TotalRow"><Data ss:Type="Number">' . $totalPaid . '</Data></Cell>';
        $out[] = '   </Row>';

        $out[] = '  </Table>';
        $out[] = ' </Worksheet>';

        return implode("\n", $out);
    }

    private function xmlEscape(?string $text): string
    {
        return htmlspecialchars($text ?? '', ENT_XML1 | ENT_QUOTES, 'UTF-8');
    }
}
