/**
 * Frontend-only fixtures for Sales Rep dashboard and customer screens.
 * Replace with escaped PHP view data or controller JSON responses.
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
    customers: [
        {
            id: 'CUS-00001',
            type: 'shop',
            name: 'City Auto Works',
            initials: 'CA',
            phone: '+94 77 123 4567',
            email: 'orders@cityautoworks.lk',
            address: '142 Galle Road, Dehiwala',
            active: true,
            accountSince: 'January 2022',
            outstanding: 425000,
            overdue: true,
            highVolume: true,
            ytdRevenue: 12400000,
            averageOrder: 185000,
            orderCount: 67,
            returnRate: 2.4,
            purchases: [
                { id: 'ORD-2026-1048', date: '2026-08-05', items: 'Fuel injectors, brake rotors', total: 425000, status: 'Processing' },
                { id: 'ORD-2026-1032', date: '2026-07-28', items: 'Alternator assemblies', total: 168000, status: 'Delivered' },
                { id: 'ORD-2026-1018', date: '2026-07-19', items: 'Bulk oil 5W-30', total: 375000, status: 'Delivered' }
            ]
        },
        {
            id: 'CUS-00002',
            type: 'shop',
            name: 'Highway Garage & Parts',
            initials: 'HG',
            phone: '+94 71 555 2901',
            email: 'purchasing@highwaygarage.lk',
            address: '88 Kandy Road, Kadawatha',
            active: true,
            accountSince: 'March 2023',
            outstanding: 0,
            overdue: false,
            highVolume: true,
            ytdRevenue: 9850000,
            averageOrder: 142000,
            orderCount: 53,
            returnRate: 1.2,
            purchases: [
                { id: 'ORD-2026-1047', date: '2026-08-05', items: 'Timing belt tensioners', total: 85050, status: 'Pending' },
                { id: 'ORD-2026-1024', date: '2026-07-23', items: 'Suspension parts', total: 278500, status: 'Delivered' }
            ]
        },
        {
            id: 'CUS-00003',
            type: 'shop',
            name: 'Nuwara Motors',
            initials: 'NM',
            phone: '+94 72 832 4410',
            email: 'sales@nuwaramotors.lk',
            address: '450 Badulla Road, Nuwara Eliya',
            active: true,
            accountSince: 'September 2021',
            outstanding: 112050,
            overdue: false,
            highVolume: false,
            ytdRevenue: 6420000,
            averageOrder: 118000,
            orderCount: 41,
            returnRate: 3.1,
            purchases: [
                { id: 'ORD-2026-1046', date: '2026-08-04', items: '12V auto batteries', total: 189000, status: 'Delivered' }
            ]
        },
        {
            id: 'CUS-00004',
            type: 'walking',
            name: 'Kasun Perera',
            initials: 'KP',
            phone: '+94 76 908 1123',
            email: 'kasun.perera@example.com',
            address: 'Nugegoda',
            active: true,
            accountSince: 'June 2026',
            outstanding: 0,
            overdue: false,
            highVolume: false,
            ytdRevenue: 86500,
            averageOrder: 28833,
            orderCount: 3,
            returnRate: 0,
            purchases: [
                { id: 'SALE-2026-814', date: '2026-08-01', items: 'Brake pads', total: 28500, status: 'Delivered' }
            ]
        },
        {
            id: 'CUS-00005',
            type: 'walking',
            name: 'Nimali Fernando',
            initials: 'NF',
            phone: '+94 77 443 6792',
            email: '',
            address: 'Moratuwa',
            active: true,
            accountSince: 'July 2026',
            outstanding: 0,
            overdue: false,
            highVolume: false,
            ytdRevenue: 46200,
            averageOrder: 23100,
            orderCount: 2,
            returnRate: 0,
            purchases: [
                { id: 'SALE-2026-802', date: '2026-07-27', items: 'Oil filter, engine oil', total: 23100, status: 'Delivered' }
            ]
        }
    ]
};
