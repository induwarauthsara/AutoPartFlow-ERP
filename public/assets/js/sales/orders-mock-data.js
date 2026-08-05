/**
 * Frontend-only fixtures for the Sales Rep order screens.
 * Replace these arrays with escaped PHP view data or controller JSON responses.
 */
window.ORDER_MOCK_DATA = {
    orders: [
        {
            id: 'ORD-2026-1048',
            customer: 'City Auto Works',
            initials: 'CA',
            accountType: 'Trade Account',
            date: '2026-08-05',
            time: '10:42 AM',
            status: 'Processing',
            total: 425000,
            rep: 'Sales Rep',
            items: [
                { name: 'High-Flow Fuel Injector', sku: 'INJ-882-X', quantity: 8, total: 199200 },
                { name: 'Brake Rotor FX-9', sku: 'BRK-FX9', quantity: 6, total: 225800 }
            ]
        },
        {
            id: 'ORD-2026-1047',
            customer: 'Highway Garage & Parts',
            initials: 'HG',
            accountType: 'Trade Account',
            date: '2026-08-05',
            time: '9:18 AM',
            status: 'Pending',
            total: 85050,
            rep: 'Sales Rep',
            items: [
                { name: 'Timing Belt Tensioner', sku: 'BLT-204-T', quantity: 3, total: 85050 }
            ]
        },
        {
            id: 'ORD-2026-1046',
            customer: 'Nuwara Motors',
            initials: 'NM',
            accountType: 'Trade Account',
            date: '2026-08-04',
            time: '3:35 PM',
            status: 'Delivered',
            total: 189000,
            rep: 'Sales Rep',
            items: [
                { name: '12V Auto Battery', sku: 'BAT-12V-90', quantity: 10, total: 189000 }
            ]
        },
        {
            id: 'ORD-2026-1045',
            customer: 'Metro Repair Centre',
            initials: 'MR',
            accountType: 'Trade Account',
            date: '2026-08-03',
            time: '11:10 AM',
            status: 'Cancelled',
            total: 46000,
            rep: 'Sales Rep',
            items: [
                { name: 'Synthetic Oil Filter V8', sku: 'FLT-V8-01', quantity: 20, total: 46000 }
            ]
        },
        {
            id: 'ORD-2026-1044',
            customer: 'Lanka Auto Care',
            initials: 'LA',
            accountType: 'Trade Account',
            date: '2026-08-02',
            time: '1:05 PM',
            status: 'Processing',
            total: 278500,
            rep: 'Sales Rep',
            items: [
                { name: 'Shock Absorber Hilux', sku: 'SUS-HLX-22', quantity: 10, total: 278500 }
            ]
        }
    ],
    products: [
        {
            id: 1,
            sku: 'INJ-882-X',
            name: 'High-Flow Fuel Injector (V8)',
            description: 'OEM-certified direct replacement for V8 engines.',
            category: 'Engine',
            stock: 45,
            price: 24900
        },
        {
            id: 2,
            sku: 'BLT-204-T',
            name: 'Timing Belt Tensioner',
            description: 'Heavy-duty tensioner assembly with sealed bearing.',
            category: 'Engine',
            stock: 12,
            price: 28350
        },
        {
            id: 3,
            sku: 'BAT-12V-90',
            name: '12V Maintenance-Free Battery',
            description: '90 Ah automotive battery with 18-month warranty.',
            category: 'Electrical',
            stock: 26,
            price: 18900
        },
        {
            id: 4,
            sku: 'SUS-HLX-22',
            name: 'Shock Absorber — Hilux',
            description: 'Gas-filled front shock absorber for Toyota Hilux.',
            category: 'Suspension',
            stock: 18,
            price: 27850
        },
        {
            id: 5,
            sku: 'FLT-V8-01',
            name: 'Synthetic Oil Filter V8',
            description: 'High-efficiency filter for synthetic engine oil.',
            category: 'Filtration',
            stock: 68,
            price: 2300
        },
        {
            id: 6,
            sku: 'BRK-FX9',
            name: 'Brake Rotor FX-9',
            description: 'Ventilated front brake rotor with anti-corrosion coating.',
            category: 'Suspension',
            stock: 34,
            price: 45900
        }
    ],
    customers: {
        'CUS-00001': { name: 'City Auto Works', detail: 'Dehiwala · Trade customer', initials: 'CA' },
        'CUS-00002': { name: 'Highway Garage & Parts', detail: 'Kadawatha · Trade customer', initials: 'HG' },
        'CUS-00003': { name: 'Nuwara Motors', detail: 'Nuwara Eliya · Trade customer', initials: 'NM' }
    }
};
