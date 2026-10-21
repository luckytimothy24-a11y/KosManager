import pathlib, re
p = pathlib.Path('tests/Feature/GroupBookingInvitationTest.php')
t = p.read_text(encoding='utf-8')
# fix kamar to personal 1
t = t.replace(
    "        $this->kamar = Kamar::factory()->create([\n            'kos_id' => $this->kos->id, 'status' => 'available', 'capacity' => 3,\n            'billing_mode' => 'shared', 'monthly_price' => 1200000, 'daily_price' => 50000, 'weekly_price' => 350000,\n        ]);",
    "        $this->kamar = Kamar::factory()->create([\n            'kos_id' => $this->kos->id, 'status' => 'available', 'capacity' => 1,\n            'billing_mode' => 'personal', 'monthly_price' => 1200000, 'daily_price' => 50000, 'weekly_price' => 350000,\n        ]);"
)
# update book payload to personal 1
t = t.replace(
    "        $payload = [\n            'kamar_id' => $this->kamar->id,\n            'start_date' => now()->addDays(5)->format('Y-m-d'),\n            'end_date' => now()->addMonths(2)->format('Y-m-d'),\n            'rental_type' => 'monthly',\n            'billing_mode' => 'shared',\n            'requested_occupants' => $requested,\n        ];",
    "        $payload = [\n            'kamar_id' => $this->kamar->id,\n            'start_date' => now()->addDays(5)->format('Y-m-d'),\n            'end_date' => now()->addMonths(2)->format('Y-m-d'),\n            'rental_type' => 'monthly',\n            'billing_mode' => 'personal',\n            'requested_occupants' => 1,\n        ];"
)
p.write_text(t, encoding='utf-8')
print('fixed invitation setUp/book')

p2 = pathlib.Path('tests/Feature/GroupBookingReservationTest.php')
t2 = p2.read_text(encoding='utf-8')
# fix sharedKamar to personal 1
t2 = t2.replace(
    "    private function sharedKamar(int $capacity, int $price = 1200000): Kamar\n    {\n        return Kamar::factory()->create([\n            'kos_id' => $this->kos->id,\n            'status' => 'available',\n            'capacity' => $capacity,\n            'billing_mode' => 'shared',",
    "    private function sharedKamar(int $capacity, int $price = 1200000): Kamar\n    {\n        return Kamar::factory()->create([\n            'kos_id' => $this->kos->id,\n            'status' => 'available',\n            'capacity' => 1,\n            'billing_mode' => 'personal',"
)
p2.write_text(t2, encoding='utf-8')
print('fixed reservation sharedKamar')

p3 = pathlib.Path('tests/Feature/Phase32InvitationDiscoverabilityTest.php')
t3 = p3.read_text(encoding='utf-8')
t3 = t3.replace(
    "        $this->kamar = Kamar::factory()->create(['kos_id' => $this->kos->id, 'status' => 'available', 'capacity' => 3, 'billing_mode' => 'shared', 'monthly_price' => 1200000, 'daily_price' => 50000, 'weekly_price' => 350000]);",
    "        $this->kamar = Kamar::factory()->create(['kos_id' => $this->kos->id, 'status' => 'available', 'capacity' => 1, 'billing_mode' => 'personal', 'monthly_price' => 1200000, 'daily_price' => 50000, 'weekly_price' => 350000]);"
)
t3 = t3.replace(
    "        $payload = ['kamar_id' => $this->kamar->id, 'start_date' => now()->addDays(5)->format('Y-m-d'), 'end_date' => now()->addMonths(2)->format('Y-m-d'), 'rental_type' => 'monthly', 'billing_mode' => 'shared', 'requested_occupants' => $req];",
    "        $payload = ['kamar_id' => $this->kamar->id, 'start_date' => now()->addDays(5)->format('Y-m-d'), 'end_date' => now()->addMonths(2)->format('Y-m-d'), 'rental_type' => 'monthly', 'billing_mode' => 'personal', 'requested_occupants' => 1];"
)
# second occurrence in test_expired
t3 = t3.replace(
    "        $b2 = $this->kamar = Kamar::factory()->create(['kos_id' => $this->kos->id, 'status' => 'available', 'capacity' => 3, 'billing_mode' => 'shared']);",
    "        $b2 = $this->kamar = Kamar::factory()->create(['kos_id' => $this->kos->id, 'status' => 'available', 'capacity' => 1, 'billing_mode' => 'personal']);"
)
t3 = t3.replace(
    "        $this->kamar = Kamar::factory()->create(['kos_id' => $this->kos->id, 'status' => 'available', 'capacity' => 3, 'billing_mode' => 'shared', 'monthly_price' => 1200000, 'daily_price' => 50000, 'weekly_price' => 350000]);",
    "        $this->kamar = Kamar::factory()->create(['kos_id' => $this->kos->id, 'status' => 'available', 'capacity' => 1, 'billing_mode' => 'personal', 'monthly_price' => 1200000, 'daily_price' => 50000, 'weekly_price' => 350000]);"
)
p3.write_text(t3, encoding='utf-8')
print('fixed phase32')
