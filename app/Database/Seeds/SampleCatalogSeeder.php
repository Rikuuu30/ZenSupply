<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

/**
 * Adds a realistic coursework catalog once when the inventory is empty.
 * These amounts and quantities are examples, not verified purchasing data.
 */
class SampleCatalogSeeder extends Seeder
{
    public function run()
    {
        if (! $this->db->tableExists('products') || $this->db->table('products')->countAllResults() > 0) {
            return;
        }

        $examples = [
            ['ZS-GLV-R14', 'Red Core Training Gloves · 14 oz', 'Boxing gloves · 14 oz · red synthetic-leather shell, layered impact foam, attached thumb, wide hook-and-loop wrist closure, and wipe-clean lining. Suitable for supervised bag work and pad drills. SAMPLE CATALOG ITEM — verify specifications, price, and stock before retail use.', 1850.00, 10, 4, 'red-training-gloves.jpg'],
            ['ZS-GLV-BK16', 'Black Core Sparring Gloves · 16 oz', 'Boxing gloves · 16 oz · black synthetic-leather shell, firm layered padding, attached thumb, and broad adjustable wrist strap. A heavier training option for supervised sparring sessions. SAMPLE CATALOG ITEM — verify specifications, price, and stock before retail use.', 1990.00, 8, 4, 'black-sparring-gloves.jpg'],
            ['ZS-GLV-BL12', 'Blue Core Training Gloves · 12 oz', 'Boxing gloves · 12 oz · blue synthetic-leather shell with layered foam padding, attached thumb, and hook-and-loop wrist support. Intended for bag and mitt work. SAMPLE CATALOG ITEM — verify specifications, price, and stock before retail use.', 1790.00, 9, 4, 'blue-training-gloves.jpg'],
            ['ZS-WRP-180', 'Cotton Hand Wraps · 180 in Pair', 'Hand protection · pair of 180-inch cotton-blend wraps with a thumb loop and hook-and-loop tab. Reusable, washable, and designed to add wrist and knuckle support beneath training gloves. SAMPLE CATALOG ITEM — verify specifications, price, and stock before retail use.', 350.00, 18, 6, 'cotton-hand-wraps.jpg'],
            ['ZS-SHG-M-BK', 'Striker Shin Guards · Medium', 'Muay Thai shin guards · medium pair · black synthetic-leather exterior, extended shin and instep coverage, high-density padding, and adjustable rear straps. Wipe clean and air dry after training. SAMPLE CATALOG ITEM — verify size, specifications, price, and stock before retail use.', 2250.00, 8, 3, 'shin-guards.jpg'],
            ['ZS-GI-KAR-04', 'White Karate Gi · Size 4', 'Karate uniform · size 4 sample · white lightweight cotton-blend jacket and trousers with reinforced seams and an included white belt. Confirm the maker’s size chart and fabric composition before sale. SAMPLE CATALOG ITEM — verify specifications, price, and stock before retail use.', 1650.00, 12, 4, 'karate-gi.jpg'],
            ['ZS-SHT-M-BL', 'Blue Muay Thai Shorts · Medium', 'Training shorts · medium sample · blue satin-look polyester shell, elastic waistband with drawcord, side vents, and relaxed leg openings for movement. Wash according to the verified garment label. SAMPLE CATALOG ITEM — verify size, material, price, and stock before retail use.', 1450.00, 14, 5, 'muay-thai-shorts.jpg'],
            ['ZS-MIT-PAIR-RB', 'Coach Focus Mitts · Red / Black Pair', 'Focus mitts · pair · curved striking surface, padded palm and wrist area, and adjustable hand straps for partner drills. Inspect stitching and padding before each session. SAMPLE CATALOG ITEM — verify specifications, price, and stock before retail use.', 1850.00, 6, 2, 'coach-focus-mitts.jpg'],
            ['ZS-MGD-ADULT', 'Adult Training Mouthguard with Case', 'Protective equipment · adult boil-and-bite style sample mouthguard supplied with a ventilated storage case. Follow the manufacturer fitting directions; not a substitute for dental advice. SAMPLE CATALOG ITEM — verify safety details, price, and stock before retail use.', 450.00, 20, 6, 'mouthguard.jpg'],
            ['ZS-BLT-BLK-280', 'Black Woven Martial Arts Belt · 280 cm', 'Uniform accessory · 280 cm woven black belt sample, approximately 4 cm wide, with reinforced stitching for regular practice. Check length and grading requirements with the intended discipline. SAMPLE CATALOG ITEM — verify dimensions, price, and stock before retail use.', 420.00, 15, 5, 'black-belt.jpg'],
            ['ZS-ROP-SPD-3M', 'Adjustable Speed Rope · 3 m', 'Conditioning equipment · adjustable rope up to 3 m with textured handles and a coated cable for indoor cardio and footwork drills. Adjust carefully and avoid abrasive surfaces. SAMPLE CATALOG ITEM — verify cable construction, price, and stock before retail use.', 650.00, 12, 4, 'speed-rope.jpg'],
            ['ZS-BAG-100-RD', 'Hanging Heavy Bag · 100 cm', 'Striking equipment · 100 cm red synthetic-cover heavy-bag sample with reinforced hanging points. Delivered unfilled in this example; mounting hardware and safe installation must be confirmed separately. SAMPLE CATALOG ITEM — verify dimensions, filling, mounting, price, and stock before retail use.', 5500.00, 3, 1, 'heavy-bag.jpg'],
        ];

        $now = date('Y-m-d H:i:s');
        $rows = [];
        foreach ($examples as [$sku, $name, $description, $price, $stock, $threshold, $image]) {
            $rows[] = [
                'sku'                 => $sku,
                'name'                => $name,
                'description'         => $description,
                'price'               => number_format($price, 2, '.', ''),
                'stock_quantity'      => $stock,
                'low_stock_threshold' => $threshold,
                'image'               => 'assets/images/catalog/' . $image,
                'is_active'           => 1,
                'created_at'          => $now,
                'updated_at'          => null,
            ];
        }

        $this->db->table('products')->insertBatch($rows);
    }
}
