window.INVENTORY_MOCK_DATA = {
    incomingPurchases: 42,
    locations: ['All Locations', 'Detroit HQ', 'Chicago Hub', 'Atlanta Dist.'],
    items: [
        {
            id: 1,
            partNo: 'BRK-8921-A',
            name: 'Ceramic Brake Pads (Front)',
            location: 'Detroit HQ',
            bin: 'A12',
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
            location: 'Chicago Hub',
            bin: 'C04',
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
            location: 'Detroit HQ',
            bin: 'B02',
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
            location: 'Atlanta Dist.',
            bin: 'E11',
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
            location: 'Detroit HQ',
            bin: 'A05',
            qty: 45,
            reorderLevel: 20,
            status: 'restocking',
            lastMovement: 'Pending Arrival',
            unitCost: 24800
        }
    ]
};
