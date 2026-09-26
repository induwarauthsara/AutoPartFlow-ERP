<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;
use DateTimeImmutable;
use PDO;

class ReportManager
{
    private PDO $db;

    public const SEASONS = [
        'south_asian_monsoon' => [
            'key' => 'south_asian_monsoon',
            'name' => 'South Asian Monsoon',
            'label' => 'South Asian Monsoon (May – Sep)',
            'start_month' => 5,
            'end_month' => 9,
            'description' => 'Heavy rainfall & waterlogged roads drive elevated demand for wiper blades, brake systems, suspension components, fog lighting, and fluids.',
            'focus_categories' => ['Brakes', 'Suspension', 'Fluids'],
        ],
        'dry_summer' => [
            'key' => 'dry_summer',
            'name' => 'Dry Summer Heat Wave',
            'label' => 'Dry Summer & Heat Wave (Feb – Apr)',
            'start_month' => 2,
            'end_month' => 4,
            'description' => 'High ambient temperatures induce stress on engine cooling, AC systems, radiators, coolants, alternators, and vehicle batteries.',
            'focus_categories' => ['Electrical', 'Engine Parts', 'Fluids'],
        ],
        'festive_travel' => [
            'key' => 'festive_travel',
            'name' => 'Year-End & Festive Travel',
            'label' => 'Festive & Year-End Travel (Oct – Jan)',
            'start_month' => 10,
            'end_month' => 1,
            'description' => 'Holiday travel peaks and long-distance road trips drive surge in comprehensive servicing: oil, filters, spark plugs, and brake pads.',
            'focus_categories' => ['Filters', 'Fluids', 'Ignition', 'Brakes'],
        ],
        'spring_overhaul' => [
            'key' => 'spring_overhaul',
            'name' => 'Spring & New Year Overhaul',
            'label' => 'Spring & New Year Overhaul (Mar – Apr)',
            'start_month' => 3,
            'end_month' => 4,
            'description' => 'Annual personal and commercial fleet maintenance rush leading up to Sinhala & Tamil New Year festivities.',
            'focus_categories' => ['Engine Parts', 'Suspension', 'Brakes'],
        ],
    ];

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    public function getCategories(): array
    {
        return $this->db->query("SELECT id, name, slug FROM categories ORDER BY name ASC")->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getBrands(): array
    {
        return $this->db->query("SELECT id, name, slug, country FROM brands ORDER BY name ASC")->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getSalesReps(): array
    {
        return $this->db->query(
            "SELECT e.id, e.employee_code, u.full_name, e.designation 
             FROM employees e 
             JOIN users u ON u.id = e.user_id 
             WHERE e.deleted_at IS NULL 
             ORDER BY u.full_name ASC"
        )->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getAvailableYears(): array
    {
        $rows = $this->db->query(
            "SELECT DISTINCT YEAR(sale_date) as y FROM sales WHERE deleted_at IS NULL ORDER BY y DESC"
        )->fetchAll(PDO::FETCH_COLUMN);

        $currentYear = (int) date('Y');
        $years = array_map('intval', $rows ?: []);
        if (!in_array($currentYear, $years, true)) {
            array_unshift($years, $currentYear);
        }
        if (!in_array($currentYear - 1, $years, true)) {
            $years[] = $currentYear - 1;
        }
        rsort($years);
        return array_values(array_unique($years));
    }

    /**
     * Build and compute all report analytics, KPIs, chart trends, and filtered records.
     */
    public function getReportData(array $params): array
    {
        $today = new DateTimeImmutable('today');
        $period = (string) ($params['period'] ?? 'monthly');
        $allowedPeriods = ['daily', 'yesterday', 'weekly', 'monthly', 'last_month', 'quarterly', 'ytd', 'last_year', 'all', 'custom', 'seasonal'];
        if (!in_array($period, $allowedPeriods, true)) {
            $period = 'monthly';
        }

        // Dimension filters
        $categoryId    = !empty($params['category_id']) ? (int) $params['category_id'] : null;
        $brandId       = !empty($params['brand_id']) ? (int) $params['brand_id'] : null;
        $salesRepId    = !empty($params['sales_rep_id']) ? (int) $params['sales_rep_id'] : null;
        $customerType  = !empty($params['customer_type']) && in_array($params['customer_type'], ['shop', 'walking'], true) ? $params['customer_type'] : null;
        $paymentMethod = !empty($params['payment_method']) && in_array($params['payment_method'], ['cash', 'card', 'bank_transfer', 'credit'], true) ? $params['payment_method'] : null;
        $paymentStatus = !empty($params['payment_status']) && in_array($params['payment_status'], ['paid', 'partial', 'unpaid', 'refunded'], true) ? $params['payment_status'] : null;
        $search        = trim((string) ($params['q'] ?? ''));
        $sort          = (string) ($params['sort'] ?? 'date_desc');
        $page          = max(1, (int) ($params['page'] ?? 1));
        $limit         = max(5, min(100, (int) ($params['limit'] ?? 15)));

        // Seasonal variation settings
        $seasonPreset = (string) ($params['season_preset'] ?? 'south_asian_monsoon');
        if (!isset(self::SEASONS[$seasonPreset]) && $seasonPreset !== 'custom') {
            $seasonPreset = 'south_asian_monsoon';
        }
        $seasonYear = (string) ($params['season_year'] ?? $today->format('Y'));
        $seasonBaseline = (string) ($params['compare_baseline'] ?? 'prev_season');
        if (!in_array($seasonBaseline, ['prev_season', 'annual_avg', 'prev_period'], true)) {
            $seasonBaseline = 'prev_season';
        }

        $seasonConfig = null;
        if ($period === 'seasonal') {
            if ($seasonPreset === 'custom') {
                $startMonth = max(1, min(12, (int) ($params['season_start_month'] ?? 5)));
                $endMonth = max(1, min(12, (int) ($params['season_end_month'] ?? 9)));
                $customTitle = trim((string) ($params['season_title'] ?? 'Custom Seasonal Window'));
                if ($customTitle === '') {
                    $customTitle = 'Custom Season (' . date('M', mktime(0, 0, 0, $startMonth, 1)) . ' – ' . date('M', mktime(0, 0, 0, $endMonth, 1)) . ')';
                }
                $seasonConfig = [
                    'key' => 'custom',
                    'name' => $customTitle,
                    'label' => $customTitle,
                    'start_month' => $startMonth,
                    'end_month' => $endMonth,
                    'description' => 'Custom user-defined seasonal variation window across monthly cycles.',
                    'focus_categories' => [],
                ];
            } else {
                $seasonConfig = self::SEASONS[$seasonPreset];
            }
        }

        // Date Resolution
        $dateResolution = $this->resolveDates($period, $today, $params, $seasonConfig, $seasonYear);
        $from = $dateResolution['from'];
        $to = $dateResolution['to'];
        $prevFrom = $dateResolution['prev_from'];
        $prevTo = $dateResolution['prev_to'];
        $periodLabel = $dateResolution['label'];
        $compareText = $dateResolution['compare_text'];
        $isSeasonal = ($period === 'seasonal');
        $seasonalMonthMode = $dateResolution['seasonal_month_mode'] ?? false; // when year is 'all'

        // Common filter constraints builder
        $filterResult = $this->buildFilterConditions([
            'category_id'    => $categoryId,
            'brand_id'       => $brandId,
            'sales_rep_id'   => $salesRepId,
            'customer_type'  => $customerType,
            'payment_method' => $paymentMethod,
            'payment_status' => $paymentStatus,
            'search'         => $search,
        ]);
        $filterSql = $filterResult['sql'];
        $filterParams = $filterResult['params'];

        // 1. Compute Primary Window Stats & Comparison Window Stats
        $curStats = $this->queryStatsForWindow($from, $to, $seasonalMonthMode, $seasonConfig, $filterSql, $filterParams);
        $prevStats = $this->queryStatsForWindow($prevFrom, $prevTo, $seasonalMonthMode, $seasonConfig, $filterSql, $filterParams, true);

        $curGross = (float) ($curStats['gross'] ?? 0);
        $prevGross = (float) ($prevStats['gross'] ?? 0);
        $curTx = (int) ($curStats['tx'] ?? 0);
        $prevTx = (int) ($prevStats['tx'] ?? 0);
        $curDisc = (float) ($curStats['disc'] ?? 0);
        $prevDisc = (float) ($prevStats['disc'] ?? 0);
        $curAvg = $curTx > 0 ? $curGross / $curTx : 0.0;
        $prevAvg = $prevTx > 0 ? $prevGross / $prevTx : 0.0;

        $pct = fn(float $c, float $p): ?float => $p > 0 ? (($c - $p) / $p) * 100 : null;

        $cards = [
            [
                'icon' => 'Rs',
                'label' => 'Gross Revenue',
                'value' => 'Rs. ' . number_format($curGross, 2),
                'change' => $pct($curGross, $prevGross),
                'goodWhenUp' => true,
                'raw' => $curGross
            ],
            [
                'icon' => '#',
                'label' => 'Transactions',
                'value' => number_format($curTx),
                'change' => $pct((float) $curTx, (float) $prevTx),
                'goodWhenUp' => true,
                'raw' => $curTx
            ],
            [
                'icon' => 'Avg',
                'label' => 'Average Sale',
                'value' => 'Rs. ' . number_format($curAvg, 2),
                'change' => $pct($curAvg, $prevAvg),
                'goodWhenUp' => true,
                'raw' => $curAvg
            ],
            [
                'icon' => '%',
                'label' => 'Discounts Given',
                'value' => 'Rs. ' . number_format($curDisc, 2),
                'change' => $pct($curDisc, $prevDisc),
                'goodWhenUp' => false,
                'raw' => $curDisc
            ],
        ];

        // 2. Seasonal Variation Intelligence
        $seasonalIntel = null;
        if ($isSeasonal && $seasonConfig) {
            $seasonalIntel = $this->computeSeasonalIntelligence(
                $seasonConfig,
                $seasonYear,
                $curGross,
                $curTx,
                $filterSql,
                $filterParams,
                $prevGross
            );
        }

        // 3. Chart Series Generation
        $chartData = $this->buildChartData($period, $from, $to, $today, $seasonConfig, $seasonYear, $filterSql, $filterParams);

        // 4. Category Breakdown
        $categoryBreakdown = $this->queryCategoryBreakdown($from, $to, $seasonalMonthMode, $seasonConfig, $filterSql, $filterParams);

        // 5. Employee Performance
        $employeePerformance = $this->queryEmployeePerformance($from, $to, $seasonalMonthMode, $seasonConfig, $filterSql, $filterParams);

        // 6. Filtered Sales Records Table (Paginated)
        $recordsData = $this->queryFilteredRecords($from, $to, $seasonalMonthMode, $seasonConfig, $filterSql, $filterParams, $sort, $page, $limit);

        return [
            'period'              => $period,
            'periodLabel'         => $periodLabel,
            'compareText'         => $compareText,
            'from'                => $from ? $from->format('Y-m-d') : null,
            'to'                  => $to ? $to->format('Y-m-d') : null,
            'cards'               => $cards,
            'chartTitle'          => $chartData['title'],
            'chartLabels'         => $chartData['labels'],
            'chartValues'         => $chartData['values'],
            'chartComparison'     => $chartData['comparisonValues'] ?? [],
            'chartHasComparison'  => !empty($chartData['comparisonValues']),
            'categoryBreakdown'   => $categoryBreakdown,
            'employeePerformance' => $employeePerformance,
            'records'             => $recordsData['rows'],
            'totalRecords'        => $recordsData['total'],
            'page'                => $page,
            'limit'               => $limit,
            'totalPages'          => $recordsData['totalPages'],
            'seasonalIntel'       => $seasonalIntel,
            'seasonConfig'        => $seasonConfig,
            'seasonPreset'        => $seasonPreset,
            'seasonYear'          => $seasonYear,
            'seasonBaseline'      => $seasonBaseline,
            'activeFilters'       => [
                'category_id'    => $categoryId,
                'brand_id'       => $brandId,
                'sales_rep_id'   => $salesRepId,
                'customer_type'  => $customerType,
                'payment_method' => $paymentMethod,
                'payment_status' => $paymentStatus,
                'search'         => $search,
                'sort'           => $sort,
            ],
            'categories'          => $this->getCategories(),
            'brands'              => $this->getBrands(),
            'salesReps'           => $this->getSalesReps(),
            'availableYears'      => $this->getAvailableYears(),
            'seasons'             => self::SEASONS,
        ];
    }

    /**
     * Resolve date ranges for given period.
     */
    private function resolveDates(string $period, DateTimeImmutable $today, array $params, ?array $seasonConfig, string $seasonYear): array
    {
        $seasonalMonthMode = false;
        $compareText = 'vs previous period';

        switch ($period) {
            case 'daily':
                $from = $today;
                $to = $today;
                $prevFrom = $today->modify('-1 day');
                $prevTo = $prevFrom;
                $compareText = 'vs yesterday';
                $label = 'Today (' . $today->format('j M Y') . ')';
                break;

            case 'yesterday':
                $from = $today->modify('-1 day');
                $to = $from;
                $prevFrom = $today->modify('-2 day');
                $prevTo = $prevFrom;
                $compareText = 'vs day before';
                $label = 'Yesterday (' . $from->format('j M Y') . ')';
                break;

            case 'weekly':
                $from = $today->modify('monday this week');
                $to = $today;
                $prevFrom = $from->modify('-7 days');
                $prevTo = $from->modify('-1 day');
                $compareText = 'vs last week';
                $label = 'This Week (' . $from->format('j M') . ' – ' . $today->format('j M Y') . ')';
                break;

            case 'last_month':
                $from = $today->modify('first day of last month');
                $to = $today->modify('last day of last month');
                $prevFrom = $from->modify('-1 month');
                $prevTo = $prevFrom->modify('last day of this month');
                $compareText = 'vs month prior';
                $label = 'Last Month (' . $from->format('F Y') . ')';
                break;

            case 'quarterly':
                $month = (int) $today->format('n');
                $quarter = (int) ceil($month / 3);
                $qStartMonth = (($quarter - 1) * 3) + 1;
                $from = new DateTimeImmutable(sprintf('%s-%02d-01', $today->format('Y'), $qStartMonth));
                $to = $today;
                $prevFrom = $from->modify('-3 months');
                $prevTo = $from->modify('-1 day');
                $compareText = 'vs previous quarter';
                $label = 'Q' . $quarter . ' ' . $today->format('Y') . ' (' . $from->format('j M') . ' – ' . $today->format('j M Y') . ')';
                break;

            case 'ytd':
                $from = $today->modify('first day of january this year');
                $to = $today;
                $prevFrom = $from->modify('-1 year');
                $prevTo = $today->modify('-1 year');
                $compareText = 'vs same period last year';
                $label = 'Year to date (' . $from->format('j M') . ' – ' . $today->format('j M Y') . ')';
                break;

            case 'last_year':
                $from = $today->modify('first day of january last year');
                $to = $today->modify('last day of december last year');
                $prevFrom = $from->modify('-1 year');
                $prevTo = $to->modify('-1 year');
                $compareText = 'vs year prior';
                $label = 'Full Last Year (' . $from->format('Y') . ')';
                break;

            case 'all':
                $from = null;
                $to = null;
                $prevFrom = null;
                $prevTo = null;
                $compareText = '';
                $label = 'All Recorded History';
                break;

            case 'custom':
                $dateFromStr = trim((string) ($params['date_from'] ?? ''));
                $dateToStr   = trim((string) ($params['date_to'] ?? ''));
                $fromDt = $dateFromStr !== '' ? DateTimeImmutable::createFromFormat('Y-m-d', $dateFromStr) : null;
                $toDt   = $dateToStr !== '' ? DateTimeImmutable::createFromFormat('Y-m-d', $dateToStr) : null;

                if (!$fromDt) {
                    $fromDt = $today->modify('-30 days');
                }
                if (!$toDt) {
                    $toDt = $today;
                }
                if ($fromDt > $toDt) {
                    [$fromDt, $toDt] = [$toDt, $fromDt];
                }
                $from = $fromDt;
                $to = $toDt;
                $days = (int) $from->diff($to)->format('%a') + 1;
                $prevTo = $from->modify('-1 day');
                $prevFrom = $prevTo->modify('-' . ($days - 1) . ' days');
                $compareText = 'vs prior ' . $days . ' days';
                $label = 'Custom Range (' . $from->format('j M Y') . ' – ' . $to->format('j M Y') . ')';
                break;

            case 'seasonal':
                $startM = $seasonConfig['start_month'] ?? 5;
                $endM = $seasonConfig['end_month'] ?? 9;

                if ($seasonYear === 'all') {
                    $seasonalMonthMode = true;
                    $from = null;
                    $to = null;
                    $prevFrom = null;
                    $prevTo = null;
                    $compareText = 'vs non-seasonal baseline';
                    $label = 'Seasonal Variation: ' . ($seasonConfig['name'] ?? 'Season') . ' (All Recorded Years)';
                } else {
                    $yearNum = (int) $seasonYear;
                    if ($startM <= $endM) {
                        // Standard window within single calendar year
                        $from = new DateTimeImmutable(sprintf('%04d-%02d-01', $yearNum, $startM));
                        $endMonthDt = new DateTimeImmutable(sprintf('%04d-%02d-01', $yearNum, $endM));
                        $to = $endMonthDt->modify('last day of this month');

                        // Comparison season: Prior year
                        $prevFrom = $from->modify('-1 year');
                        $prevTo = $to->modify('-1 year');
                        $compareText = 'vs same season in ' . ($yearNum - 1);
                    } else {
                        // Wrap-around window across year boundary (e.g. Oct to Jan)
                        $from = new DateTimeImmutable(sprintf('%04d-%02d-01', $yearNum, $startM));
                        $endMonthDt = new DateTimeImmutable(sprintf('%04d-%02d-01', $yearNum + 1, $endM));
                        $to = $endMonthDt->modify('last day of this month');

                        $prevFrom = $from->modify('-1 year');
                        $prevTo = $to->modify('-1 year');
                        $compareText = 'vs same season in ' . ($yearNum - 1);
                    }
                    $label = 'Seasonal Variation: ' . ($seasonConfig['name'] ?? 'Season') . ' ' . $seasonYear . ' (' . $from->format('j M Y') . ' – ' . $to->format('j M Y') . ')';
                }
                break;

            case 'monthly':
            default:
                $from = $today->modify('first day of this month');
                $to = $today;
                $prevFrom = $today->modify('first day of last month');
                $prevTo = $prevFrom->modify('last day of this month');
                $compareText = 'vs last month';
                $label = 'This Month (' . $from->format('j M') . ' – ' . $today->format('j M Y') . ')';
                break;
        }

        return [
            'from'                => $from,
            'to'                  => $to,
            'prev_from'           => $prevFrom,
            'prev_to'             => $prevTo,
            'label'               => $label,
            'compare_text'        => $compareText,
            'seasonal_month_mode' => $seasonalMonthMode,
        ];
    }

    /**
     * Build WHERE conditions for multi-criteria filters.
     */
    private function buildFilterConditions(array $filters): array
    {
        $where = [];
        $params = [];

        if (!empty($filters['category_id'])) {
            $where[] = "s.id IN (SELECT DISTINCT si.sale_id FROM sale_items si JOIN products p ON p.id = si.product_id WHERE p.category_id = ?)";
            $params[] = (int) $filters['category_id'];
        }

        if (!empty($filters['brand_id'])) {
            $where[] = "s.id IN (SELECT DISTINCT si.sale_id FROM sale_items si JOIN products p ON p.id = si.product_id WHERE p.brand_id = ?)";
            $params[] = (int) $filters['brand_id'];
        }

        if (!empty($filters['sales_rep_id'])) {
            $where[] = "s.sales_rep_id = ?";
            $params[] = (int) $filters['sales_rep_id'];
        }

        if (!empty($filters['customer_type'])) {
            $where[] = "c.customer_type = ?";
            $params[] = $filters['customer_type'];
        }

        if (!empty($filters['payment_method'])) {
            $where[] = "s.payment_method = ?";
            $params[] = $filters['payment_method'];
        }

        if (!empty($filters['payment_status'])) {
            $where[] = "s.payment_status = ?";
            $params[] = $filters['payment_status'];
        }

        if (!empty($filters['search'])) {
            $kw = '%' . $filters['search'] . '%';
            $where[] = "(s.invoice_number LIKE ? OR c.name LIKE ? OR c.phone LIKE ? OR s.id IN (
                SELECT DISTINCT si2.sale_id FROM sale_items si2 
                JOIN products p2 ON p2.id = si2.product_id 
                WHERE p2.name LIKE ? OR p2.product_code LIKE ?
            ))";
            $params[] = $kw;
            $params[] = $kw;
            $params[] = $kw;
            $params[] = $kw;
            $params[] = $kw;
        }

        $sql = $where ? ' AND ' . implode(' AND ', $where) : '';
        return ['sql' => $sql, 'params' => $params];
    }

    /**
     * Build date/season condition clause.
     */
    private function buildDateClause(?DateTimeImmutable $from, ?DateTimeImmutable $to, bool $seasonalMonthMode, ?array $seasonConfig, bool $isInverseBaseline = false): array
    {
        if ($seasonalMonthMode && $seasonConfig) {
            $startM = (int) $seasonConfig['start_month'];
            $endM = (int) $seasonConfig['end_month'];
            if ($startM <= $endM) {
                if ($isInverseBaseline) {
                    return ["MONTH(s.sale_date) NOT BETWEEN ? AND ?", [$startM, $endM]];
                }
                return ["MONTH(s.sale_date) BETWEEN ? AND ?", [$startM, $endM]];
            } else {
                if ($isInverseBaseline) {
                    return ["(MONTH(s.sale_date) < ? AND MONTH(s.sale_date) > ?)", [$startM, $endM]];
                }
                return ["(MONTH(s.sale_date) >= ? OR MONTH(s.sale_date) <= ?)", [$startM, $endM]];
            }
        }

        if ($from !== null && $to !== null) {
            return [
                "s.sale_date >= ? AND s.sale_date <= ?",
                [$from->format('Y-m-d 00:00:00'), $to->format('Y-m-d 23:59:59')]
            ];
        }

        return ["1=1", []];
    }

    /**
     * Compute summary statistics for a given time window.
     */
    private function queryStatsForWindow(?DateTimeImmutable $from, ?DateTimeImmutable $to, bool $seasonalMonthMode, ?array $seasonConfig, string $filterSql, array $filterParams, bool $isInverseBaseline = false): array
    {
        [$dateSql, $dateParams] = $this->buildDateClause($from, $to, $seasonalMonthMode, $seasonConfig, $isInverseBaseline);

        $sql = "SELECT 
                    COALESCE(SUM(s.total_amount), 0)    AS gross,
                    COUNT(s.id)                         AS tx,
                    COALESCE(SUM(s.discount_amount), 0) AS disc,
                    COALESCE(SUM(s.tax_amount), 0)      AS tax
                FROM sales s
                JOIN customers c ON c.id = s.customer_id
                WHERE s.deleted_at IS NULL AND {$dateSql} {$filterSql}";

        $stmt = $this->db->prepare($sql);
        $stmt->execute(array_merge($dateParams, $filterParams));
        $res = $stmt->fetch(PDO::FETCH_ASSOC);
        return $res ?: ['gross' => 0, 'tx' => 0, 'disc' => 0, 'tax' => 0];
    }

    /**
     * Compute rich seasonal intelligence metrics.
     */
    private function computeSeasonalIntelligence(array $seasonConfig, string $seasonYear, float $seasonalGross, int $seasonalTx, string $filterSql, array $filterParams, float $prevSeasonGross): array
    {
        $yearInt = $seasonYear !== 'all' ? (int) $seasonYear : (int) date('Y');

        // Annual sales for the year (for share percentage calculation)
        $annualStmt = $this->db->prepare(
            "SELECT COALESCE(SUM(s.total_amount), 0) as total, COUNT(s.id) as tx 
             FROM sales s 
             JOIN customers c ON c.id = s.customer_id
             WHERE s.deleted_at IS NULL 
               AND YEAR(s.sale_date) = ? {$filterSql}"
        );
        $annualStmt->execute(array_merge([$yearInt], $filterParams));
        $annual = $annualStmt->fetch(PDO::FETCH_ASSOC);
        $annualGross = (float) ($annual['total'] ?? 0);

        // Share of annual revenue
        $annualSharePct = $annualGross > 0 ? ($seasonalGross / $annualGross) * 100 : 0.0;

        // Season duration in months
        $startM = (int) $seasonConfig['start_month'];
        $endM = (int) $seasonConfig['end_month'];
        $seasonMonthsCount = ($startM <= $endM) ? ($endM - $startM + 1) : (12 - $startM + 1 + $endM);
        $nonSeasonMonthsCount = max(1, 12 - $seasonMonthsCount);

        // Monthly average in-season vs non-season
        $seasonalMonthlyAvg = $seasonMonthsCount > 0 ? $seasonalGross / $seasonMonthsCount : 0.0;
        $nonSeasonalGross = max(0, $annualGross - $seasonalGross);
        $nonSeasonalMonthlyAvg = $nonSeasonMonthsCount > 0 ? $nonSeasonalGross / $nonSeasonMonthsCount : 0.0;

        // Seasonal lift index % (how much higher/lower is monthly sales during this season vs off-season)
        $seasonalLiftPct = $nonSeasonalMonthlyAvg > 0 ? (($seasonalMonthlyAvg - $nonSeasonalMonthlyAvg) / $nonSeasonalMonthlyAvg) * 100 : null;

        // Season-over-Season Growth %
        $sOsGrowthPct = $prevSeasonGross > 0 ? (($seasonalGross - $prevSeasonGross) / $prevSeasonGross) * 100 : null;

        // Top surging category in this season
        $startYear = $yearInt;
        $endYear = ($startM <= $endM) ? $yearInt : ($yearInt + 1);
        $fromStr = sprintf('%04d-%02d-01 00:00:00', $startYear, $startM);
        $toDt = (new DateTimeImmutable(sprintf('%04d-%02d-01', $endYear, $endM)))->modify('last day of this month');
        $toStr = $toDt->format('Y-m-d 23:59:59');

        $catStmt = $this->db->prepare(
            "SELECT cat.name, SUM(si.line_total) as cat_revenue, SUM(si.quantity) as cat_qty
             FROM sale_items si
             JOIN sales s ON s.id = si.sale_id
             JOIN customers c ON c.id = s.customer_id
             JOIN products p ON p.id = si.product_id
             JOIN categories cat ON cat.id = p.category_id
             WHERE s.deleted_at IS NULL
               AND s.sale_date BETWEEN ? AND ?
               {$filterSql}
             GROUP BY cat.id, cat.name
             ORDER BY cat_revenue DESC
             LIMIT 1"
        );
        $catStmt->execute(array_merge([$fromStr, $toStr], $filterParams));
        $topCat = $catStmt->fetch(PDO::FETCH_ASSOC);

        // Top velocity products in this season
        $partsStmt = $this->db->prepare(
            "SELECT p.product_code, p.name, cat.name as category, SUM(si.quantity) as total_qty, SUM(si.line_total) as revenue
             FROM sale_items si
             JOIN sales s ON s.id = si.sale_id
             JOIN customers c ON c.id = s.customer_id
             JOIN products p ON p.id = si.product_id
             JOIN categories cat ON cat.id = p.category_id
             WHERE s.deleted_at IS NULL
               AND s.sale_date BETWEEN ? AND ?
               {$filterSql}
             GROUP BY p.id, p.product_code, p.name, cat.name
             ORDER BY revenue DESC
             LIMIT 4"
        );
        $partsStmt->execute(array_merge([$fromStr, $toStr], $filterParams));
        $topParts = $partsStmt->fetchAll(PDO::FETCH_ASSOC);

        return [
            'season_name'             => $seasonConfig['name'],
            'season_label'            => $seasonConfig['label'],
            'season_year'             => $seasonYear,
            'description'             => $seasonConfig['description'],
            'focus_categories'        => $seasonConfig['focus_categories'] ?? [],
            'seasonal_gross'          => $seasonalGross,
            'seasonal_tx'             => $seasonalTx,
            'seasonal_monthly_avg'    => $seasonalMonthlyAvg,
            'annual_gross'            => $annualGross,
            'annual_share_pct'        => $annualSharePct,
            'seasonal_lift_pct'       => $seasonalLiftPct,
            'sos_growth_pct'          => $sOsGrowthPct,
            'prev_season_gross'       => $prevSeasonGross,
            'top_surging_category'    => $topCat ? $topCat['name'] : 'N/A',
            'top_category_revenue'    => (float) ($topCat['cat_revenue'] ?? 0),
            'top_parts'               => $topParts,
        ];
    }

    /**
     * Build Chart data (labels, current values, and optional comparison values).
     */
    private function buildChartData(string $period, ?DateTimeImmutable $from, ?DateTimeImmutable $to, DateTimeImmutable $today, ?array $seasonConfig, string $seasonYear, string $filterSql, array $filterParams): array
    {
        if ($period === 'seasonal' && $seasonConfig) {
            $startM = (int) $seasonConfig['start_month'];
            $endM = (int) $seasonConfig['end_month'];
            $yearNum = $seasonYear !== 'all' ? (int) $seasonYear : (int) $today->format('Y');

            $monthList = [];
            if ($startM <= $endM) {
                for ($m = $startM; $m <= $endM; $m++) {
                    $monthList[] = ['month' => $m, 'year_offset' => 0];
                }
            } else {
                for ($m = $startM; $m <= 12; $m++) {
                    $monthList[] = ['month' => $m, 'year_offset' => 0];
                }
                for ($m = 1; $m <= $endM; $m++) {
                    $monthList[] = ['month' => $m, 'year_offset' => 1];
                }
            }

            $labels = [];
            $curValues = [];
            $compValues = [];

            foreach ($monthList as $item) {
                $m = $item['month'];
                $yOffset = $item['year_offset'];
                $actualY = $yearNum + $yOffset;
                $prevY = $actualY - 1;

                $labels[] = date('M', mktime(0, 0, 0, $m, 1)) . ($yOffset > 0 ? " '" . substr((string) $actualY, 2) : '');

                // Current season month
                $curStart = sprintf('%04d-%02d-01 00:00:00', $actualY, $m);
                $curEnd = (new DateTimeImmutable(sprintf('%04d-%02d-01', $actualY, $m)))->modify('last day of this month')->format('Y-m-d 23:59:59');
                $stmt = $this->db->prepare(
                    "SELECT COALESCE(SUM(s.total_amount), 0) FROM sales s
                     JOIN customers c ON c.id = s.customer_id
                     WHERE s.deleted_at IS NULL AND s.sale_date BETWEEN ? AND ? {$filterSql}"
                );
                $stmt->execute(array_merge([$curStart, $curEnd], $filterParams));
                $curValues[] = (float) $stmt->fetchColumn();

                // Comparison season month (previous year)
                $prevStart = sprintf('%04d-%02d-01 00:00:00', $prevY, $m);
                $prevEnd = (new DateTimeImmutable(sprintf('%04d-%02d-01', $prevY, $m)))->modify('last day of this month')->format('Y-m-d 23:59:59');
                $stmt->execute(array_merge([$prevStart, $prevEnd], $filterParams));
                $compValues[] = (float) $stmt->fetchColumn();
            }

            return [
                'title'            => 'Seasonal Variation Progression: ' . $seasonConfig['name'] . ' (' . $yearNum . ' vs ' . ($yearNum - 1) . ')',
                'labels'           => $labels,
                'values'           => $curValues,
                'comparisonValues' => $compValues,
            ];
        }

        // Daily / Weekly chart: Days
        if (in_array($period, ['daily', 'yesterday', 'weekly'], true) || ($from !== null && $to !== null && $from->diff($to)->days <= 14)) {
            $startDt = $from ?? $today->modify('-6 days');
            $endDt = $to ?? $today;
            $daysCount = (int) $startDt->diff($endDt)->format('%a') + 1;
            $daysCount = min(14, max(1, $daysCount));

            $labels = [];
            $values = [];
            $cur = $startDt;
            for ($i = 0; $i < $daysCount; $i++) {
                $labels[] = $cur->format('D, j M');
                $dStart = $cur->format('Y-m-d 00:00:00');
                $dEnd = $cur->format('Y-m-d 23:59:59');
                $stmt = $this->db->prepare(
                    "SELECT COALESCE(SUM(s.total_amount), 0) FROM sales s
                     JOIN customers c ON c.id = s.customer_id
                     WHERE s.deleted_at IS NULL AND s.sale_date BETWEEN ? AND ? {$filterSql}"
                );
                $stmt->execute(array_merge([$dStart, $dEnd], $filterParams));
                $values[] = (float) $stmt->fetchColumn();
                $cur = $cur->modify('+1 day');
            }

            return [
                'title'  => 'Sales Trend (Daily)',
                'labels' => $labels,
                'values' => $values,
            ];
        }

        // Default: Last 6 or 12 Months
        $numMonths = ($period === 'last_year' || $period === 'all') ? 12 : 6;
        $labels = [];
        $values = [];
        $baseDate = ($to ?? $today)->modify('first day of this month');

        for ($i = $numMonths - 1; $i >= 0; $i--) {
            $mStart = $baseDate->modify("-{$i} month");
            $labels[] = $mStart->format('M Y');
            $mEnd = $mStart->modify('last day of this month');

            $stmt = $this->db->prepare(
                "SELECT COALESCE(SUM(s.total_amount), 0) FROM sales s
                 JOIN customers c ON c.id = s.customer_id
                 WHERE s.deleted_at IS NULL 
                   AND s.sale_date >= ? AND s.sale_date <= ? {$filterSql}"
            );
            $stmt->execute(array_merge([$mStart->format('Y-m-d 00:00:00'), $mEnd->format('Y-m-d 23:59:59')], $filterParams));
            $values[] = (float) $stmt->fetchColumn();
        }

        return [
            'title'  => 'Sales Trend (' . $numMonths . ' Months Overview)',
            'labels' => $labels,
            'values' => $values,
        ];
    }

    /**
     * Category Profitability and Revenue Breakdown.
     */
    private function queryCategoryBreakdown(?DateTimeImmutable $from, ?DateTimeImmutable $to, bool $seasonalMonthMode, ?array $seasonConfig, string $filterSql, array $filterParams): array
    {
        [$dateSql, $dateParams] = $this->buildDateClause($from, $to, $seasonalMonthMode, $seasonConfig);

        $sql = "SELECT 
                    cat.id,
                    cat.name,
                    COUNT(DISTINCT s.id)            AS sales_count,
                    COALESCE(SUM(si.quantity), 0)   AS total_units,
                    COALESCE(SUM(si.line_total), 0) AS revenue
                FROM categories cat
                LEFT JOIN products p ON p.category_id = cat.id AND p.deleted_at IS NULL
                LEFT JOIN sale_items si ON si.product_id = p.id
                LEFT JOIN sales s ON s.id = si.sale_id AND s.deleted_at IS NULL AND {$dateSql}
                LEFT JOIN customers c ON c.id = s.customer_id
                WHERE 1=1 {$filterSql}
                GROUP BY cat.id, cat.name
                HAVING revenue > 0
                ORDER BY revenue DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute(array_merge($dateParams, $filterParams));
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $totalRevenue = array_sum(array_column($rows, 'revenue'));
        foreach ($rows as &$r) {
            $rev = (float) $r['revenue'];
            $r['revenue'] = $rev;
            $r['pct'] = $totalRevenue > 0 ? ($rev / $totalRevenue) * 100 : 0.0;
        }
        unset($r);

        return $rows;
    }

    /**
     * Query top employee performance for current filter criteria.
     */
    private function queryEmployeePerformance(?DateTimeImmutable $from, ?DateTimeImmutable $to, bool $seasonalMonthMode, ?array $seasonConfig, string $filterSql, array $filterParams): array
    {
        [$dateSql, $dateParams] = $this->buildDateClause($from, $to, $seasonalMonthMode, $seasonConfig);

        $sql = "SELECT 
                    e.id AS employee_id,
                    e.employee_code,
                    u.full_name,
                    e.designation,
                    e.commission_rate,
                    COUNT(s.id) AS total_sales,
                    COALESCE(SUM(s.total_amount), 0) AS total_revenue,
                    COALESCE(SUM(s.total_amount * e.commission_rate / 100), 0) AS commission_earned
                FROM employees e
                JOIN users u ON u.id = e.user_id
                LEFT JOIN sales s ON s.sales_rep_id = e.id AND s.deleted_at IS NULL AND {$dateSql}
                LEFT JOIN customers c ON c.id = s.customer_id
                WHERE e.deleted_at IS NULL {$filterSql}
                GROUP BY e.id, e.employee_code, u.full_name, e.designation, e.commission_rate
                ORDER BY total_revenue DESC
                LIMIT 8";

        $stmt = $this->db->prepare($sql);
        $stmt->execute(array_merge($dateParams, $filterParams));
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Query paginated filtered sales records table with item summary.
     */
    private function queryFilteredRecords(?DateTimeImmutable $from, ?DateTimeImmutable $to, bool $seasonalMonthMode, ?array $seasonConfig, string $filterSql, array $filterParams, string $sort, int $page, int $limit): array
    {
        [$dateSql, $dateParams] = $this->buildDateClause($from, $to, $seasonalMonthMode, $seasonConfig);

        // Count total matching
        $countSql = "SELECT COUNT(DISTINCT s.id) 
                     FROM sales s
                     JOIN customers c ON c.id = s.customer_id
                     WHERE s.deleted_at IS NULL AND {$dateSql} {$filterSql}";
        $countStmt = $this->db->prepare($countSql);
        $countStmt->execute(array_merge($dateParams, $filterParams));
        $total = (int) $countStmt->fetchColumn();

        $totalPages = max(1, (int) ceil($total / $limit));
        $offset = ($page - 1) * $limit;

        // Sort order
        $sortOrder = match ($sort) {
            'date_asc'    => 's.sale_date ASC, s.id ASC',
            'amount_desc' => 's.total_amount DESC, s.id DESC',
            'amount_asc'  => 's.total_amount ASC, s.id ASC',
            'date_desc'   => 's.sale_date DESC, s.id DESC',
            default       => 's.sale_date DESC, s.id DESC',
        };

        $sql = "SELECT 
                    s.id,
                    s.invoice_number,
                    s.sale_date,
                    s.sale_type,
                    s.payment_method,
                    s.payment_status,
                    s.subtotal,
                    s.discount_amount,
                    s.tax_amount,
                    s.total_amount,
                    s.amount_paid,
                    c.name            AS customer_name,
                    c.phone           AS customer_phone,
                    c.customer_type,
                    c.customer_code,
                    u.full_name       AS sales_rep_name,
                    e.employee_code,
                    (SELECT COUNT(*) FROM sale_items si WHERE si.sale_id = s.id) AS item_count,
                    (SELECT GROUP_CONCAT(CONCAT(p.name, ' (x', si2.quantity, ')') SEPARATOR ', ')
                     FROM sale_items si2
                     JOIN products p ON p.id = si2.product_id
                     WHERE si2.sale_id = s.id
                     LIMIT 3) AS items_summary
                FROM sales s
                JOIN customers c ON c.id = s.customer_id
                LEFT JOIN employees e ON e.id = s.sales_rep_id
                LEFT JOIN users u ON u.id = e.user_id
                WHERE s.deleted_at IS NULL AND {$dateSql} {$filterSql}
                ORDER BY {$sortOrder}
                LIMIT ? OFFSET ?";

        $stmt = $this->db->prepare($sql);
        $boundParams = array_merge($dateParams, $filterParams);
        $boundParams[] = $limit;
        $boundParams[] = $offset;

        // Execute with strict PDO types
        foreach ($boundParams as $idx => $val) {
            $type = is_int($val) ? PDO::PARAM_INT : PDO::PARAM_STR;
            $stmt->bindValue($idx + 1, $val, $type);
        }
        $stmt->execute();
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return [
            'rows'       => $rows,
            'total'      => $total,
            'totalPages' => $totalPages,
        ];
    }

    /**
     * Get detailed line items and customer header for an individual sale.
     */
    public function getSaleDetails(int $saleId): ?array
    {
        $saleStmt = $this->db->prepare(
            "SELECT 
                s.*,
                c.name as customer_name,
                c.phone as customer_phone,
                c.customer_type,
                c.customer_code,
                u.full_name as sales_rep_name,
                e.employee_code
             FROM sales s
             JOIN customers c ON c.id = s.customer_id
             LEFT JOIN employees e ON e.id = s.sales_rep_id
             LEFT JOIN users u ON u.id = e.user_id
             WHERE s.id = ? AND s.deleted_at IS NULL"
        );
        $saleStmt->execute([$saleId]);
        $sale = $saleStmt->fetch(PDO::FETCH_ASSOC);
        if (!$sale) {
            return null;
        }

        $itemsStmt = $this->db->prepare(
            "SELECT 
                si.*,
                p.product_code,
                p.name as product_name,
                cat.name as category_name,
                b.name as brand_name
             FROM sale_items si
             JOIN products p ON p.id = si.product_id
             JOIN categories cat ON cat.id = p.category_id
             LEFT JOIN brands b ON b.id = p.brand_id
             WHERE si.sale_id = ?
             ORDER BY si.id ASC"
        );
        $itemsStmt->execute([$saleId]);
        $items = $itemsStmt->fetchAll(PDO::FETCH_ASSOC);

        $sale['items'] = $items;
        return $sale;
    }

    /**
     * Stream CSV export of filtered sales records.
     */
    public function exportCsv(array $params): void
    {
        $today = new DateTimeImmutable('today');
        $period = (string) ($params['period'] ?? 'monthly');
        $seasonPreset = (string) ($params['season_preset'] ?? 'south_asian_monsoon');
        $seasonConfig = self::SEASONS[$seasonPreset] ?? null;
        $seasonYear = (string) ($params['season_year'] ?? $today->format('Y'));

        $dateResolution = $this->resolveDates($period, $today, $params, $seasonConfig, $seasonYear);
        $from = $dateResolution['from'];
        $to = $dateResolution['to'];
        $seasonalMonthMode = $dateResolution['seasonal_month_mode'] ?? false;

        $categoryId    = !empty($params['category_id']) ? (int) $params['category_id'] : null;
        $brandId       = !empty($params['brand_id']) ? (int) $params['brand_id'] : null;
        $salesRepId    = !empty($params['sales_rep_id']) ? (int) $params['sales_rep_id'] : null;
        $customerType  = !empty($params['customer_type']) && in_array($params['customer_type'], ['shop', 'walking'], true) ? $params['customer_type'] : null;
        $paymentMethod = !empty($params['payment_method']) && in_array($params['payment_method'], ['cash', 'card', 'bank_transfer', 'credit'], true) ? $params['payment_method'] : null;
        $paymentStatus = !empty($params['payment_status']) && in_array($params['payment_status'], ['paid', 'partial', 'unpaid', 'refunded'], true) ? $params['payment_status'] : null;
        $search        = trim((string) ($params['q'] ?? ''));
        $sort          = (string) ($params['sort'] ?? 'date_desc');

        $filterResult = $this->buildFilterConditions([
            'category_id'    => $categoryId,
            'brand_id'       => $brandId,
            'sales_rep_id'   => $salesRepId,
            'customer_type'  => $customerType,
            'payment_method' => $paymentMethod,
            'payment_status' => $paymentStatus,
            'search'         => $search,
        ]);
        $filterSql = $filterResult['sql'];
        $filterParams = $filterResult['params'];

        // Retrieve all records matching criteria without limit (up to 5000 max)
        $records = $this->queryFilteredRecords($from, $to, $seasonalMonthMode, $seasonConfig, $filterSql, $filterParams, $sort, 1, 5000);

        $filename = 'AutoPartFlow_Report_' . date('Ymd_His') . '.csv';
        header('Content-Type: text/csv; charset=UTF-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Pragma: no-cache');
        header('Expires: 0');

        $output = fopen('php://output', 'w');
        // UTF-8 BOM for Microsoft Excel
        fputs($output, "\xEF\xBB\xBF");

        fputcsv($output, [
            'Invoice Number',
            'Date & Time',
            'Customer Name',
            'Customer Code',
            'Customer Type',
            'Customer Phone',
            'Sales Representative',
            'Items Count',
            'Items Summary',
            'Payment Method',
            'Payment Status',
            'Subtotal (Rs.)',
            'Discount (Rs.)',
            'Tax (Rs.)',
            'Total Amount (Rs.)',
            'Amount Paid (Rs.)',
        ]);

        foreach ($records['rows'] as $r) {
            fputcsv($output, [
                $r['invoice_number'],
                $r['sale_date'],
                $r['customer_name'],
                $r['customer_code'],
                strtoupper((string) $r['customer_type']),
                $r['customer_phone'] ?? 'N/A',
                $r['sales_rep_name'] ?? 'Direct POS',
                $r['item_count'],
                $r['items_summary'] ?? '',
                strtoupper((string) $r['payment_method']),
                strtoupper((string) $r['payment_status']),
                number_format((float) $r['subtotal'], 2, '.', ''),
                number_format((float) $r['discount_amount'], 2, '.', ''),
                number_format((float) $r['tax_amount'], 2, '.', ''),
                number_format((float) $r['total_amount'], 2, '.', ''),
                number_format((float) $r['amount_paid'], 2, '.', ''),
            ]);
        }

        fclose($output);
        exit;
    }

    /**
     * Stream Excel (.xls / SpreadsheetML) export of report.
     */
    public function exportExcel(array $params): void
    {
        $params['limit'] = 5000;
        $params['page'] = 1;
        $reportData = $this->getReportData($params);

        $exporter = new ReportExcelExporter();
        $xml = $exporter->generate($reportData);

        $filename = 'AutoPartFlow_Report_' . date('Ymd_His') . '.xls';
        header('Content-Type: application/vnd.ms-excel; charset=UTF-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Pragma: no-cache');
        header('Expires: 0');

        echo $xml;
        exit;
    }

    /**
     * Stream PDF export of report.
     */
    public function exportPdf(array $params): void
    {
        $params['limit'] = 250;
        $params['page'] = 1;
        $reportData = $this->getReportData($params);

        $exporter = new ReportPdfExporter();
        $pdfContent = $exporter->generate($reportData);

        $filename = 'AutoPartFlow_Report_' . date('Ymd_His') . '.pdf';
        header('Content-Type: application/pdf');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Content-Length: ' . strlen($pdfContent));
        header('Pragma: no-cache');
        header('Expires: 0');

        echo $pdfContent;
        exit;
    }
}
