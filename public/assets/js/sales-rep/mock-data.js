/**
 * Frontend mock data for Sales Rep screens.
 * Replace with PHP: pass JSON from SalesController or fetch your APIs.
 * Totals are subtotal minus discount — no tax rate in this module.
 */
window.SALES_REP_MOCK_DATA = {
    weeklySales: {
        week: [
            { label: 'Mon', value: 285000 },
            { label: 'Tue', value: 360000 },
            { label: 'Wed', value: 315000 },
            { label: 'Thu', value: 425000 },
            { label: 'Fri', value: 0 },
            { label: 'Sat', value: 0 }
        ],
        previous: [
            { label: 'Mon', value: 245000 },
            { label: 'Tue', value: 310000 },
            { label: 'Wed', value: 275000 },
            { label: 'Thu', value: 380000 },
            { label: 'Fri', value: 420000 },
            { label: 'Sat', value: 190000 }
        ]
    },
    routeShops: [
        { id: 'CUS-00001', name: 'City Auto Works', note: 'Visited 2 days ago', status: 'Needs Stock', initials: 'CA' },
        { id: 'CUS-00002', name: 'Highway Garage & Parts', note: 'Scheduled today at 2:00 PM', status: 'Scheduled', initials: 'HG' },
        { id: 'CUS-00003', name: 'Nuwara Motors', note: 'Visited last week', status: 'Healthy', initials: 'NM' }
    ],
    deliveries: [
        { id: 'ORD-2026-1041', items: 'Brake pads and rotors · 12 items', customer: 'City Auto Works', status: 'Delayed' },
        { id: 'ORD-2026-1043', items: 'Synthetic oil 5W-30 · 2 cases', customer: 'Nuwara Motors', status: 'In Transit' },
        { id: 'ORD-2026-1046', items: '12V batteries · 10 units', customer: 'Highway Garage & Parts', status: 'Ready' }
    ],
    recentSales: [
        { id: 'SALE-2026-814', customer: 'Kasun Perera', total: 28500, status: 'Delivered' },
        { id: 'ORD-2026-1048', customer: 'City Auto Works', total: 425000, status: 'Processing' },
        { id: 'ORD-2026-1046', customer: 'Nuwara Motors', total: 189000, status: 'Delivered' }
    ],
    customers: [
        {
            id: 'CUS-00001', type: 'shop', name: 'City Auto Works', initials: 'CA',
            phone: '+94 77 123 4567', email: 'orders@cityautoworks.lk', address: '142 Galle Road, Dehiwala',
            active: true, accountSince: 'January 2022', outstanding: 425000, overdue: true, highVolume: true,
            ytdRevenue: 12400000, averageOrder: 185000, orderCount: 67, returnRate: 2.4,
            purchases: [
                { id: 'ORD-2026-1048', date: '2026-08-05', items: 'Fuel injectors, brake rotors', total: 425000, status: 'Processing' },
                { id: 'ORD-2026-1032', date: '2026-07-28', items: 'Alternator assemblies', total: 168000, status: 'Delivered' },
                { id: 'ORD-2026-1018', date: '2026-07-19', items: 'Bulk oil 5W-30', total: 375000, status: 'Delivered' }
            ]
        },
        {
            id: 'CUS-00002', type: 'shop', name: 'Highway Garage & Parts', initials: 'HG',
            phone: '+94 71 555 2901', email: 'purchasing@highwaygarage.lk', address: '88 Kandy Road, Kadawatha',
            active: true, accountSince: 'March 2023', outstanding: 0, overdue: false, highVolume: true,
            ytdRevenue: 9850000, averageOrder: 142000, orderCount: 53, returnRate: 1.2,
            purchases: [
                { id: 'ORD-2026-1047', date: '2026-08-05', items: 'Timing belt tensioners', total: 85050, status: 'Pending' },
                { id: 'ORD-2026-1024', date: '2026-07-23', items: 'Suspension parts', total: 278500, status: 'Delivered' }
            ]
        },
        {
            id: 'CUS-00003', type: 'shop', name: 'Nuwara Motors', initials: 'NM',
            phone: '+94 72 832 4410', email: 'sales@nuwaramotors.lk', address: '450 Badulla Road, Nuwara Eliya',
            active: true, accountSince: 'September 2021', outstanding: 112050, overdue: false, highVolume: false,
            ytdRevenue: 6420000, averageOrder: 118000, orderCount: 41, returnRate: 3.1,
            purchases: [
                { id: 'ORD-2026-1046', date: '2026-08-04', items: '12V auto batteries', total: 189000, status: 'Delivered' }
            ]
        },
        {
            id: 'CUS-00004', type: 'walking', name: 'Kasun Perera', initials: 'KP',
            phone: '+94 76 908 1123', email: 'kasun.perera@example.com', address: 'Nugegoda',
            active: true, accountSince: 'June 2026', outstanding: 0, overdue: false, highVolume: false,
            ytdRevenue: 86500, averageOrder: 28833, orderCount: 3, returnRate: 0,
            purchases: [
                { id: 'SALE-2026-814', date: '2026-08-01', items: 'Brake pads', total: 28500, status: 'Delivered' }
            ]
        },
        {
            id: 'CUS-00005', type: 'walking', name: 'Nimali Fernando', initials: 'NF',
            phone: '+94 77 443 6792', email: '', address: 'Moratuwa',
            active: true, accountSince: 'July 2026', outstanding: 0, overdue: false, highVolume: false,
            ytdRevenue: 46200, averageOrder: 23100, orderCount: 2, returnRate: 0,
            purchases: [
                { id: 'SALE-2026-802', date: '2026-07-27', items: 'Oil filter, engine oil', total: 23100, status: 'Delivered' }
            ]
        }
    ]
};

window.ORDER_MOCK_DATA = {
    orders: [
        { id: 'ORD-2026-1048', customer: 'City Auto Works', initials: 'CA', accountType: 'Trade Account', date: '2026-08-05', time: '10:42 AM', status: 'Processing', total: 425000, rep: 'Sales Rep', items: [{ name: 'High-Flow Fuel Injector', sku: 'INJ-882-X', quantity: 8, total: 199200 }, { name: 'Brake Rotor FX-9', sku: 'BRK-FX9', quantity: 6, total: 225800 }] },
        { id: 'ORD-2026-1047', customer: 'Highway Garage & Parts', initials: 'HG', accountType: 'Trade Account', date: '2026-08-05', time: '9:18 AM', status: 'Pending', total: 85050, rep: 'Sales Rep', items: [{ name: 'Timing Belt Tensioner', sku: 'BLT-204-T', quantity: 3, total: 85050 }] },
        { id: 'ORD-2026-1046', customer: 'Nuwara Motors', initials: 'NM', accountType: 'Trade Account', date: '2026-08-04', time: '3:35 PM', status: 'Delivered', total: 189000, rep: 'Sales Rep', items: [{ name: '12V Auto Battery', sku: 'BAT-12V-90', quantity: 10, total: 189000 }] },
        { id: 'ORD-2026-1045', customer: 'Metro Repair Centre', initials: 'MR', accountType: 'Trade Account', date: '2026-08-03', time: '11:10 AM', status: 'Cancelled', total: 46000, rep: 'Sales Rep', items: [{ name: 'Synthetic Oil Filter V8', sku: 'FLT-V8-01', quantity: 20, total: 46000 }] },
        { id: 'ORD-2026-1044', customer: 'Lanka Auto Care', initials: 'LA', accountType: 'Trade Account', date: '2026-08-02', time: '1:05 PM', status: 'Processing', total: 278500, rep: 'Sales Rep', items: [{ name: 'Shock Absorber Hilux', sku: 'SUS-HLX-22', quantity: 10, total: 278500 }] }
    ],
    products: [
        { id: 1, sku: 'INJ-882-X', name: 'High-Flow Fuel Injector (V8)', description: 'OEM-certified direct replacement for V8 engines.', category: 'Engine', stock: 45, price: 24900 },
        { id: 2, sku: 'BLT-204-T', name: 'Timing Belt Tensioner', description: 'Heavy-duty tensioner assembly with sealed bearing.', category: 'Engine', stock: 12, price: 28350 },
        { id: 3, sku: 'BAT-12V-90', name: '12V Maintenance-Free Battery', description: '90 Ah automotive battery with 18-month warranty.', category: 'Electrical', stock: 26, price: 18900 },
        { id: 4, sku: 'SUS-HLX-22', name: 'Shock Absorber — Hilux', description: 'Gas-filled front shock absorber for Toyota Hilux.', category: 'Suspension', stock: 18, price: 27850 },
        { id: 5, sku: 'FLT-V8-01', name: 'Synthetic Oil Filter V8', description: 'High-efficiency filter for synthetic engine oil.', category: 'Filtration', stock: 68, price: 2300 },
        { id: 6, sku: 'BRK-FX9', name: 'Brake Rotor FX-9', description: 'Ventilated front brake rotor with anti-corrosion coating.', category: 'Suspension', stock: 34, price: 45900 }
    ],
    customers: {
        'CUS-00001': { name: 'City Auto Works', detail: 'Dehiwala · Trade customer', initials: 'CA' },
        'CUS-00002': { name: 'Highway Garage & Parts', detail: 'Kadawatha · Trade customer', initials: 'HG' },
        'CUS-00003': { name: 'Nuwara Motors', detail: 'Nuwara Eliya · Trade customer', initials: 'NM' }
    }
};

window.MOCK_PRODUCTS = [
    { id: 1, code: 'PRD-00001', name: 'Front Brake Pad Set - Corolla', shortName: 'Brake Rotor FX-9', price: 4599.00, stock: 85, reorderLevel: 15, icon: 'icon-brake' },
    { id: 2, code: 'PRD-00002', name: 'Oil Filter - Universal', shortName: 'SynOil Filter V8', price: 750.00, stock: 320, reorderLevel: 30, icon: 'icon-filter' },
    { id: 3, code: 'PRD-00003', name: 'Spark Plug Iridium (Set of 4)', shortName: 'Spark Plug Set (4)', price: 4200.00, stock: 48, reorderLevel: 20, icon: 'icon-spark' },
    { id: 4, code: 'PRD-00004', name: 'Alternator 90A - Honda Civic', shortName: '12V Auto Battery', price: 18900.00, stock: 12, reorderLevel: 5, icon: 'icon-battery' },
    { id: 5, code: 'PRD-00005', name: 'Front Shock Absorber - Hilux', shortName: 'Shock Absorber Hilux', price: 8500.00, stock: 36, reorderLevel: 10, icon: 'icon-brake' },
    { id: 6, code: 'PRD-00006', name: 'Air Filter - Suzuki Alto', shortName: 'Air Filter Alto K10', price: 600.00, stock: 200, reorderLevel: 25, icon: 'icon-filter' }
];
