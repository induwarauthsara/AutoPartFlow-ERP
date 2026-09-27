window.INVENTORY_MOCK_DATA = {
    incomingPurchases: 42,
    items: [
        {
            id: 1,
            partNo: 'BRK-8921-A',
            name: 'Ceramic Brake Pads (Front)',
            category: 'Brakes',
            qty: 245,
            reorderLevel: 20,
            status: 'optimal',
            lastMovement: '2 hours ago (In)',
            unitCost: 4200
        },
        {
            id: 2,
            partNo: 'ALT-4450-X',
            name: 'High Output Alternator',
            category: 'Electrical',
            qty: 12,
            reorderLevel: 15,
            status: 'low',
            lastMovement: 'Yesterday (Out)',
            unitCost: 18500
        },
        {
            id: 3,
            partNo: 'FLT-OIL-99',
            name: 'Synthetic Oil Filter Pack',
            category: 'Filters',
            qty: 1020,
            reorderLevel: 80,
            status: 'optimal',
            lastMovement: '3 days ago (In)',
            unitCost: 950
        },
        {
            id: 4,
            partNo: 'STR-8822-M',
            name: 'Starter Motor Assembly',
            category: 'Electrical',
            qty: 5,
            reorderLevel: 10,
            status: 'critical',
            lastMovement: '1 week ago (Out)',
            unitCost: 16200
        },
        {
            id: 5,
            partNo: 'RDT-1100-C',
            name: 'Aluminum Radiator Core',
            category: 'Cooling',
            qty: 45,
            reorderLevel: 20,
            status: 'restocking',
            lastMovement: 'Pending Arrival',
            unitCost: 24800
        }
    ]
};
